<?php

// Push all errors to error log
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Load settings
if (!isset($uid))
	$uid = 'api';
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/globals.php';
require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/CapsMySQL.php';
require_once dirname(__FILE__) . '/CapsKayakoApi.php';

/**
 * Provides standard way of interacting with CAPS.
 */
abstract class CapsApi {
	
	const PROJECT_ALL = 1;
	
	const STATUS_HOLD = 'Hold';
	const STATUS_REVIEW = 'Review';
	
	private static $_db;
	private static $_username;
	
	private static $_increment_major = 15;
	private static $_increment_minor = 5;
	
	private static $_users = array();
	
	/**
	 * @return CapsMySQL
	 */
	public static function db() {
		return self::$_db;
	}
	
	public static function init($db) {
		self::$_db = $db;
	}
	
	private static function _email($to, $subject, $body) {
		
		$headers = array(
			'From' => 'CAPS <caps@worldwebms.com>',
			'MIME-Version' => '1.0',
			'Content-type' => 'text/plain; charset=utf-8'
		);
		$header_string = '';
		foreach ($headers as $k => $v)
			$header_string .= $k . ': ' . $v . "\n";
		trim($header_string);
		
		$to = array_unique($to);
		foreach ($to as $email) {
			$headers =
			mail($email, $subject, $body, $header_string);
		}
		
	}
	
	public static function auth($username, $password) {
		if ($username == '' || $password == '')
			return false;
		self::$_username = $username;
		return self::db()->getrow('SELECT * FROM ps WHERE u=? AND p=?', array($username, $password));
	}
	
	public static function set_user($username) {
		self::$_username = $username;
	}
	
	public static function get_issue($issue_id) {
		$row = self::db()->getrow(
			'SELECT job_tasks.*, jobs.job_title, clients.client_id, clients.client_name, clients.trading_name, jobs.job_details FROM job_tasks ' .
				'LEFT JOIN jobs ON jobs.job_id=job_tasks.job_id ' .
				'LEFT JOIN clients ON clients.client_id=jobs.client_id ' .
				'WHERE job_task_id=?',
			array($issue_id)
		);
		if ($row == false)
			return false;
		
		$work_log = self::get_work_in_progress($issue_id);
		
		$summary = trim($row['description']);
		if ($row['job_task_number'])
			$summary .= ' [Ref# ' . $row['job_task_number'] . ']';
		/* - removed as Jira plugin can filter by assigned
		if ($row['employee'])
			$summary .= ' [' . $row['employee'] . ']';
		*/
		if ($row['status'] && $row['completed'] == null)
			$summary .= ' [' . $row['status'] . ']';
		if ($work_log)
			$summary .= ' [RECORDING TIME]';
		$row['summary'] = $summary;
	
		$description = trim($row['cr_description']);
		if ($row['cr_impact_cost'] || $row['job_task_number'])
			$description .=
				"\n" .
				"\n" .
				"#--------------------------------------------------------------#\n" .
				"# COST IMPACT COMMENTS\n" .
				"#--------------------------------------------------------------#\n" .
				"\n" .
				trim($row['cr_impact_cost']);
		if ($row['job_details']) {
			$description .=
				"\n" .
				"\n" .
				"#--------------------------------------------------------------#\n" .
				"# PROJECT DESCRIPTION\n" .
				"#--------------------------------------------------------------#\n" .
				"\n" .
				trim($row['job_details']);
		}
		$description = trim($description);
		$row['details'] = $description;
		
		$row['priority'] = array_safe(self::get_issue_priority_map(), $row['priority'], null);
		
		$row['chargeable'] = $row['chargeable'] == 2 ? 'F/C' : ($row['chargeable'] ? 'T&M' : 'N/C');
		
		return $row;
	}
	
	public static function parse_issue_summary($summary) {
		$pos = strpos($summary, '[');
		if ($pos)
			$summary = substr($summary, 0, $pos);
		return $summary;
	}
	
	public static function parse_issue_description($description) {
		
		$output = array(
			'cr_description' => ''
		);
		$section = 'cr_description';
		$new_section = false;
		$lines = explode("\n", str_replace("\r", '', $description));
		foreach ($lines as $line) {
			if (preg_match('/^#-------/', $line)) {
				if (!$new_section)
					$section = '';
				$new_section = false;
				continue;
			}
			if ($line == '# COST IMPACT COMMENTS') {
				$section = 'cr_impact_cost';
				$new_section = true;
				continue;
			}
			if ($section) {
				if (!array_key_exists($section, $output))
					$output[$section] = '';
				$output[$section] .= $line . "\n";
			}
		}
		
		$output = array_map('trim', $output);
		return $output;
	}
	
	public static function get_issue_ids($project_id, $start_at, $max_results) {
		
		$where = array();
		$params = array();
		
		// Project #1 is reserved for all personal tasks
		if ($project_id == self::PROJECT_ALL) {
			$params = self::get_project_ids();
			if (count($params) == 0)
				$params[] = 0;
			$where[] = 'job_tasks.job_id IN ( ' . trim(str_repeat('?, ', count($params)), ', ') . ' )';
			$where[] = 'job_tasks.employee=?';
			$params[] = self::$_username;
			
			$where[] = '(job_tasks.completed IS NULL or job_tasks.completed >= ?)';
			$params[] = date('Y-m-d H:i:s', strtotime('-1 week'));
			
			$where[] = 'jobs.api_hide=?';
			$params[] = 0;
			
		} else {
			$where[] = 'job_tasks.job_id=?';
			$params[] = $project_id;
			
		}
	
		$ids = array();
		$results = self::db()->execute(
			'SELECT DISTINCT job_tasks.job_task_id ' .
				'FROM job_tasks ' .
				'LEFT JOIN jobs ON jobs.job_id=job_tasks.job_id ' .
				'WHERE ' . implode(' AND ', $where) . ' ' .
				'ORDER BY job_tasks.last_modified DESC ' .
				'LIMIT ' . $start_at . ', ' . $max_results,
			$params
		);
		foreach ($results as $row)
			$ids[] = $row['job_task_id'];
		return $ids;
	}
	
	public static function issue_copy($issue_id, $job_id) {
		$new_issue_id = false;
		$row = self::db()->getrow('SELECT * FROM job_tasks WHERE job_task_id=?', array($issue_id));
		if ($row) {
			unset($row['job_task_id']);
			$row['job_id'] = $job_id;
			self::db()->execute(
				'INSERT INTO job_tasks (`' . trim(implode('`, `', array_keys($row)), ' ,') . '`) VALUES (' . trim(str_repeat('?, ', count($row)), ', ') . ')',
				array_values($row)
			);
			$new_issue_id = self::db()->insert_id();
		}
		return self::get_issue($new_issue_id);
	}
	
	public static function issue_update($issue_id, $details) {
		
		if ($issue_id == false) {
			$job_id = $details['job_id'];
			if ($job_id == false)
				throw new Exception('Missing job id when creating a new task');
			self::db()->execute(
				'INSERT INTO job_tasks ( job_id, created_by, created_on ) VALUES ( ?, ?, ? )',
				array($job_id, self::$_username, date('Y-m-d H:i:s'))
			);
			$issue_id = self::db()->insert_id();
		}
		
		$params = array();
		foreach ($details as $name => $value) {
			switch ($name) {
				case 'code':
					$params['job_task_number'] = $value;
					break;
				case 'chargeable':
					$params[$name] = $value == 'F/C' ? 2 : ($value == 'N/C' ? 0 : 1);
					break;
				case 'estimate':
				case 'quote':
					$params[$name] = parseHoursToMinutes($value);
					break;
				case 'due_date':
					$params[$name] = $value ? date('Y-m-d', strtotime($value)) : null;
					break;
				case 'priority':
					$options = array_flip(self::get_issue_priority_map());
					$params[$name] = array_safe($options, $value, null);
					break;
				case 'status':
					$params[$name] = $value ? $value : null;
					break;
				default:
					$params[$name] = $value;
			}
		}

		$sql = '';
		$params['last_modified'] = date('Y-m-d H:i:s');
		foreach ($params as $name => $value)
			$sql .= ($sql == '' ? '' : ', ') . '`' . $name . '`=?';
		$params[] = $issue_id;
		self::db()->execute(
			'UPDATE job_tasks SET ' . $sql . ' WHERE job_task_id=?',
			array_values($params)
		);
		
		return self::get_issue($issue_id);
	}
	
	public static function issue_complete($issue_id) {
		self::db()->execute(
			'UPDATE job_tasks SET completed=? WHERE job_task_id=? AND completed IS NULL',
			array(date('Y-m-d H:i:s'), $issue_id)
		);
		return true;
	}
	
	public static function issue_attachment_create($issue_id, $filepath, $filename, $description) {
		$filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
		self::db()->execute(
			'INSERT INTO attachments (job_task_id, file_name, file_date, description, created_by) VALUES (?, ?, ?, ?, ?)',
			array($issue_id, $filename, date('Y-m-d H:i:s'), $description, self::$_username)
		);
		
		// Ensure directory exists
		$attach_dir = dirname(__FILE__) . '/../attachments/task-' . $issue_id;
		if (!file_exists($attach_dir))
			mkdir($attach_dir, 0770);
		
		// Move file
		copy($filepath, $attach_dir . '/' . $filename);
		
		return self::db()->insert_id();
	}
	
	public static function issue_attachment_delete($issue_id, $attachment_id) {
		foreach (self::issue_attachments($issue_id) as $attachment) {
			if ($attachment['attach_id'] == $attachment_id) {
				self::db()->execute(
					'DELETE FROM attachments WHERE attach_id=?',
					array($attachment_id)
				);
				if (file_exists($attachment['filepath']))
					unlink($attachment['filepath']);
			}
		}
	}
	
	public static function issue_attachments($issue_id) {
		$attachments = array();
		$results = self::db()->execute(
			'SELECT * FROM attachments WHERE job_task_id=? ORDER BY file_date ASC',
			array($issue_id)
		);
		foreach ($results as $row) {
			$row['filepath'] = dirname(__FILE__) . '/../attachments/task-' . $row['job_task_id'] . '/' . $row['file_name'];
			$row['url'] = 'http://' . $_SERVER['HTTP_HOST'] . '/caps/attachments/task-' . $issue_id . '/' . $row['file_name'];
			$attachments[] = $row;
		}
		return $attachments;
	}
	
	public static function issue_comment_create($issue_id, $body) {
		self::db()->execute(
			'INSERT INTO job_task_notes (job_task_id, created_by, created_on, last_modified, body) VALUES (?, ?, ?, ?, ?)',
			array($issue_id, self::$_username, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $body)
		);
		$comment_id = self::db()->insert_id();
		
		// Send notification email
		self::issue_notify_email(
			$issue_id,
			'New Comment',
			'A new comment has been added by ' . array_safe(self::get_user_options(), self::$_username, self::$_username) . ":\n" .
			"\n" .
			$body
		);
		
		return $comment_id;
	}
	
	public static function issue_comment_get($comment_id) {
		return self::db()->getrow(
			'SELECT * FROM job_task_notes WHERE job_task_note_id=?',
			array($comment_id)
		);
	}
	
	public static function issue_comments_get($issue_id) {
		return self::db()->execute(
			'SELECT * FROM job_task_notes WHERE job_task_id=? ORDER BY created_on ASC',
			array($issue_id)
		);
	}
	
	public static function issue_notify_email($issue_id, $subject, $body) {
		
		$row = self::db()->getrow(
			'SELECT job_tasks.description, job_tasks.job_task_number, job_tasks.employee, jobs.project_manager, jobs.job_title ' .
				'FROM job_tasks ' .
				'LEFT JOIN jobs ON jobs.job_id=job_tasks.job_id ' .
				'WHERE job_tasks.job_task_id=?',
			array($issue_id)
		);
		if ($row) {
			
			$body =
				'Task:  ' . $row['description'] . "\n" .
				'Job:   ' . $row['job_title'] . "\n" .
				'URL:   http://192.168.0.12/caps/browse/' . $issue_id . "\n" .
				'------------------------------------------------------------------------------' . "\n" .
				"\n" .
				$body;
			
			// Determine to addresses
			$to = array();
			foreach (self::get_users() as $user) {
				if ($user['email']) {
					if ($user['full_name'] == $row['project_manager'] || $user['u'] == $row['employee'])
						$to[] = $user['email'];
				}
			}
			
			// Send out the email
			$subject = '[CAPS #' . $issue_id . ']: ' . $subject . ' - ' . $row['description'] . ($row['job_task_number'] ? (' (Ref #' . $row['job_task_number'] . ')') : '');
			self::_email($to, $subject, $body);
			
		}
		
		return false;
	}
	
	public static function issue_status($issue_id, $status) {
		self::db()->execute(
			'UPDATE job_tasks SET status=? WHERE job_task_id=?',
			array($status, $issue_id)
		);
		return true;
	}
	
	public static function get_issue_status_options($blank = false) {
		$options = array();
		if ($blank !== false)
			$options[''] = $blank;
		$options['Discuss'] = 'Discuss';
		$options['Define'] = 'Define';
		$options['Design'] = 'Design';
		$options['Develop'] = 'Develop';
		$options['Deploy'] = 'Deploy';
		$options['Hold'] = 'Hold';
		$options['Review'] = 'Review';
		$options['Test'] = 'Test';
		return $options;
	}
	
	public static function get_issue_priority_options($blank = false) {
		$options = array();
		if ($blank !== false)
			$options[''] = $blank;
		$options['Highest'] = 'Highest';
		$options['High'] = 'High';
		$options['Medium'] = 'Medium';
		$options['Low'] = 'Low';
		$options['Lowest'] = 'Lowest';
		return $options;
	}
	
	public static function get_issue_priority_map() {
		return array(
			2 => 'Highest',
			1 => 'High',
			0 => 'Medium',
			-1 => 'Low',
			-2 => 'Lowest'
		);
	}
	
	public static function get_issue_priority_label($priority) {
		if ($priority == 2)
			return '&#8657;';
		if ($priority == 1)
			return '&#8593;';
		if ($priority == -1)
			return '&#8595;';
		if ($priority == -2)
			return '&#8659;';
		return '';
	}
	
	public static function get_issue_chargeable_options($blank = false) {
		$options = array();
		if ($blank !== false)
			$options[''] = $blank;
		$options['T&M'] = 'Time and Materials';
		$options['F/C'] = 'Fixed Cost';
		$options['N/C'] = 'No Charge';
		return $options;
	}
	
	public static function get_project($project_id) {
		return self::db()->getrow('SELECT jobs.*, clients.client_name, clients.trading_name FROM jobs LEFT JOIN clients ON clients.client_id=jobs.client_id WHERE job_id=? OR job_number=?', array($project_id, $project_id));
	}
	
	public static function get_project_ids() {
		if (self::$_username == '')
			throw new CapsJiraException('Missing username');
		
		// Determine the projects are open or that the user is assigned to
		$results = self::db()->execute(
			'SELECT DISTINCT jobs.job_id, clients.client_name, clients.trading_name ' .
				'FROM jobs ' .
				'LEFT JOIN clients ON clients.client_id=jobs.client_id ' .
				'LEFT JOIN job_tasks ON job_tasks.job_id=jobs.job_id ' .
				'WHERE jobs.status=? OR (job_tasks.employee = ? AND (job_tasks.completed IS NULL OR job_tasks.completed >= ?)) ' .
				'ORDER BY IF(clients.trading_name=?, clients.client_name, clients.trading_name), jobs.job_title',
			array('open', self::$_username, date('Y-m-d H:i:s', strtotime('-1 month')), '')
		);
		foreach ($results as $row)
			$ids[] = $row['job_id'];
		
		return $ids;
	}
	
	public static function project_comments($project_id, $length = 10) {
		return self::db()->execute(
			'SELECT job_task_notes.*, job_tasks.description ' .
				'FROM job_task_notes ' .
				'LEFT JOIN job_tasks ON job_tasks.job_task_id=job_task_notes.job_task_id ' .
				'WHERE job_tasks.job_id=? ORDER BY job_task_notes.created_on DESC ' .
				'LIMIT ?, ?',
			array($project_id, 0, $length)
		);
	}
	
	public static function get_user($username) {
		return array_safe(self::get_users(), $username, null);
	}
	
	public static function get_user_options($blank = false) {
		$options = array();
		if ($blank !== false)
			$options[''] = $blank;
		foreach (self::get_users() as $user)
			$options[$user['u']] = $user['first_name'] . ' ' . $user['last_name'];
		return $options;
	}
	
	public static function get_users() {
		if (count(self::$_users) == 0) {
			$results = self::db()->execute(
				'SELECT * FROM ps ORDER BY u'
			);
			foreach ($results as $row) {
				$row['full_name'] = trim($row['first_name'] . ' ' . $row['last_name']);
				self::$_users[$row['u']] = $row;
			}
			self::$_users['admin'] = array('p_id' => '10000', 'first_name' => 'CAPS', 'last_name' => 'Admin', 'u' => 'admin', 'email' => '');
		}
		return self::$_users;
	}
	
	public static function get_work_in_progress($issue_id) {
		return self::db()->getrow(
			'SELECT * FROM job_details WHERE job_task_id=? AND employee=? AND job_date=? AND end_time IS NULL AND deleted_on IS NULL',
			array($issue_id, self::$_username, date('Y-m-d'))
		);
	}
	
	public static function get_work($issue_id) {
		return self::db()->execute(
			'SELECT job_details.*, job_tasks.description AS job_task_description, job_tasks.chargeable, job_tasks.job_task_number, jobs.job_title FROM job_details '.
				'LEFT JOIN job_tasks ON job_tasks.job_task_id=job_details.job_task_id ' .
				'LEFT JOIN jobs ON jobs.job_id=job_details.job_id ' .
				'WHERE job_details.job_task_id=? AND job_details.end_time IS NOT NULL AND job_details.deleted_on IS NULL',
			array($issue_id)
		);
	}
	
	public static function create_work($issue_id, $start_time, $end_time, $comment) {
		$issue = self::get_issue($issue_id);
		
		// Create the record
		self::db()->execute(
			'INSERT INTO job_details ( job_id, job_task_id, job_date, start_time, end_time, description, employee, created_on, last_modified ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ? )',
			array($issue['job_id'], $issue['job_task_id'], date('Y-m-d', $start_time), date('H:i:s', $start_time), $end_time ? date('H:i:s', $end_time) : null, $comment, self::$_username, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'))
		);
		$work_id = self::db()->insert_id();
		
		// Update task so clients get updates
		self::db()->execute(
			'UPDATE job_tasks SET last_modified=? WHERE job_task_id=?',
			array(date('Y-m-d H:i:s'), $issue['job_task_id'])
		);
		
		// Update totals
		updateTotalTime(self::db()->connection(), $issue['job_id']);
		
		// Return the id
		return $work_id;
	}
	
	public static function start_work($issue_id) {
		$issue = self::get_issue($issue_id);
		
		// Stop all current time recording
		self::stop_all_work();
		
		// Start from the last 15 minutes
		$now = time();
		$midnight = mktime(0, 0, 0);
		$seconds = floor(($now - $midnight) / 60) * 60;
		$minutes_diff = floor($seconds / 60) % self::$_increment_major;
		$seconds -= $minutes_diff * 60;

		// If getting close to the next period then start there instead
		if ($minutes_diff >= (self::$_increment_major - 3))
			$seconds += self::$_increment_major * 60;
		
		$start_time = $midnight + $seconds;
		
		// If there are existing records within the time period then need to
		// move back 5 minutes instead of 15 and adjust the end time of the
		// existing rows.
		$results = self::db()->execute(
			'SELECT * FROM job_details WHERE employee=? AND job_date=? AND end_time>? AND deleted_on IS NULL',
			array(self::$_username, date('Y-m-d'), date('H:i:s', $start_time))
		);
		if ($results->count()) {
			$seconds = floor(($now - $midnight) / 60) * 60;
			$minutes_diff = floor($seconds / 60) % self::$_increment_minor;
			$seconds -= $minutes_diff * 60;
			$start_time = $midnight + $seconds;
			
			// Update existing records
			$results = self::db()->execute(
				'SELECT * FROM job_details WHERE employee=? AND job_date=? AND end_time>? AND deleted_on IS NULL',
				array(self::$_username, date('Y-m-d'), date('H:i:s', $start_time))
			);
			foreach ($results as $row) {
				self::db()->execute(
					'UPDATE job_details SET end_time=?, last_modified=? WHERE job_details_id=?',
					array(date('H:i:s', $start_time), date('Y-m-d H:i:s'), $row['job_details_id'])
				);
			}
			
			// Update any records that have the same start and end date.
			// If task is done within minor window then the start and end
			// could be the same.
			$results = self::db()->execute(
				'SELECT * FROM job_details WHERE employee=? AND job_date=? AND start_time=end_time AND deleted_on IS NULL',
				array(self::$_username, date('Y-m-d'))
			);
			foreach ($results as $row) {
				self::db()->execute(
					'UPDATE job_details SET end_time=?, last_modified=? WHERE job_details_id=?',
					array(date('H:i:s', strtotime($row['end_time']) + self::$_increment_minor * 60), date('Y-m-d H:i:s'), $row['job_details_id'])
				);
			};
			
		}
		
		// Start a new record
		return self::create_work($issue['job_task_id'], $start_time, null, '');
	}
	
	public static function stop_all_work() {
		$results = self::db()->execute(
			'SELECT job_task_id FROM job_details WHERE employee=? AND end_time IS NULL AND job_task_id > ? AND deleted_on IS NULL',
			array(self::$_username, 0)
		);
		foreach ($results as $row)
			self::stop_work($row['job_task_id']);
		return null;
	}
	
	public static function stop_work($issue_id = 0) {
		
		// Update last modified time on the issue so client receives updates
		self::db()->execute(
			'UPDATE job_tasks SET last_modified=? WHERE job_task_id=?',
			array(date('Y-m-d H:i:s'), $issue_id)
		);
		
		// Stop existing record
		$row = self::get_work_in_progress($issue_id);
		if ($row) {
			
			// Stop at the end of this 15 minutes window
			$now = time();
			$midnight = mktime(0, 0, 0);
			$seconds = floor(($now - $midnight) / 60) * 60;
			$minutes_diff = floor($seconds / 60) % self::$_increment_major;
			if ($minutes_diff > 0) {
				$seconds += (self::$_increment_major - $minutes_diff) * 60;
			
				// If close to the start of this period then end in the previous one
				if ($minutes_diff <= 3)
					$seconds -= self::$_increment_major * 60;
				
			}
			
			$end_time = $midnight + $seconds;
			
			// Update existing record
			self::db()->execute(
				'UPDATE job_details SET end_time=?, last_modified=? WHERE job_details_id=?',
				array(date('H:i:s', $end_time), date('Y-m-d H:i:s'), $row['job_details_id'])
			);
			return $row['job_details_id'];
		}
		
		return null;
	}
	
}

// Set up CAPS
$db = new CapsMySQL($hostName, $userName, $password, $database);
CapsApi::init($db);