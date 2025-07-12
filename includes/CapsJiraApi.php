<?php

require_once dirname(__FILE__) . '/CapsApi.php';

abstract class CapsJiraApi {
	
	private static $_base_url;
	private static $_api_url;
	private static $_img_url;
	private static $_post_data;
	
	const GET = 'GET';
	const POST = 'POST';
	const PUT = 'PUT';
	
	const STATUS_OPEN = 'Open';
	const STATUS_IN_PROGRESS = 'In Progress';
	const STATUS_REOPENED = 'Reopened';
	const STATUS_RESOLVED = 'Resolved';
	const STATUS_CLOSED = 'Closed';
	const STATUS_DONE = 'Done';
	const STATUS_TO_DO = 'To Do';
	
	const TRANSITION_START = 'Start Progress';
	const TRANSITION_STOP = 'Stop Progress';
	const TRANSITION_RESOLVE = 'Resolve Issue';
	const TRANSITION_CLOSE = 'Close Issue';
	
	const RESOLUTION_DEFINE = 'Define';
	const RESOLUTION_DESIGN = 'Design';
	const RESOLUTION_DEVELOP = 'Develop';
	const RESOLUTION_DEPLOY = 'Deploy';
	const RESOLUTION_DONE = 'Done';
	const RESOLUTION_HOLD = 'Hold';
	const RESOLUTION_REVIEW = 'Review';
	const RESOLUTION_TEST = 'Test';
	
	public static function _dispatch($method, $path) {
		switch ($path) {
			case 'auth/1/session':
			case 'auth/latest/session':
				return self::auth_latest_session();
			case 'filter/favourite':
				return array();
			case 'issue':
				if ($method == self::POST)
					return self::issue_create();
				break;
			case 'issue/createmeta':
				return self::issue_create_meta();
			case 'issuetype':
				return self::issue_type();
			case 'priority':
				return self::priority();
			case 'project':
				return self::projects();
			case 'resolution':
				return self::resolution();
			case 'search':
				return self::search();
			case 'serverInfo':
				return self::server_info();
			case 'status':
				return self::status();
			case 'worklog':
				return self::worklogs();
		}
		
		if (preg_match('|^issue/(\d+)/?(.+)?|', $path, $matches)) {
			
			$issue = self::issue($matches[1]);
			$extra = array_safe($matches, 2, '');
			switch ($extra) {
				
				case 'attachments':
					if ($method == self::POST)
						return self::issue_attachment($issue);
					break;
					
				case 'comment':
					if ($method == self::POST)
						return self::issue_comment($issue);
					return $issue['fields']['comment'];
					
				case 'transitions':
					if ($method == self::POST)
						return self::issue_transition($issue);
					return array(
						'expand' => 'transitions',
						'transitions' => $issue['transitions']
					);
					
				case 'worklog':
					if ($method == self::POST) {
						$comment = self::_post_var('comment');
						$start_time = strtotime(self::_post_var('started'));
						if ($start_time) {
							$start_time = strtotime(date('Y-m-d H:i:00', $start_time));
							$end_time = $start_time + (self::_string_to_minutes(self::_post_var('timeSpent')) * 60);
							if ($end_time > $start_time) {
								$work_id = CapsApi::create_work($issue['id'], $start_time, $end_time, $comment);
								debug('work is: ' . $work_id);
								if ($work_id)
									return new CapsJiraResponse(201);
							}
						}
						return new CapsJiraResponse(400);
					}
					return $issue['fields']['worklog'];
					
				default:
					if ($extra)
						throw new CapsJiraException('Unsupported issue command: ' . $extra);
					
			}
			
			// Only get is supported
			if ($method == self::PUT)
				return self::issue_update($issue['id']);
			
			return $issue;
		}
		
		if (preg_match('|^project/(.+)|', $path, $matches))
			return self::project($matches[1]);
		
		return null;
	}

	private static function _timestamp_to_iso8601($timestamp,$utc=true){
		$datestr = date('Y-m-d\TH:i:s.000O',$timestamp);
		$pos = strrpos($datestr, "+");
		if ($pos === FALSE) {
			$pos = strrpos($datestr, "-");
		}
		if ($pos !== FALSE) {
			if (strlen($datestr) == $pos + 5) {
				$datestr = substr($datestr, 0, $pos + 3) . ':' . substr($datestr, -2);
			}
		}
		if($utc){
			$pattern = '/'.
			'([0-9]{4})-'.	// centuries & years CCYY-
			'([0-9]{2})-'.	// months MM-
			'([0-9]{2})'.	// days DD
			'T'.			// separator T
			'([0-9]{2}):'.	// hours hh:
			'([0-9]{2}):'.	// minutes mm:
			'([0-9]{2})(\.[0-9]*)?'. // seconds ss.ss...
			'(Z|[+\-][0-9]{2}:?[0-9]{2})?'. // Z to indicate UTC, -/+HH:MM:SS.SS... for local tz's
			'/';
	
			if(preg_match($pattern,$datestr,$regs)){
				return sprintf('%04d-%02d-%02dT%02d:%02d:%02dZ',$regs[1],$regs[2],$regs[3],$regs[4],$regs[5],$regs[6]);
			}
			return false;
		} else {
			return $datestr;
		}
	}
	
	private static function _authenticate() {
		$user = CapsApi::auth(self::_username(), array_safe($_SERVER, 'PHP_AUTH_PW', ''));
		if ($user == false)
			throw new CapsJiraException('Username or password incorrect', 401);
		return $user;
	}
	
	private static function _date($date) {
		if ($date !== null && !is_numeric($date))
			$date = self::_timestamp_to_iso8601(strtotime($date), false);
		return $date;
	}
	
	private static function _username() {
		return array_safe($_SERVER, 'PHP_AUTH_USER', '');
	}
	
	private static function _get_var($name, $default = null) {
		return array_safe($_GET, $name, $default);
	}
	
	private static function _post_var($name, $default = null) {
		return array_safe(self::$_post_data, $name, $default);
	}
	
	public static function auth_latest_session() {
		$user = self::_authenticate();
		return array(
			'self' => self::_self(),
			'name' => $user['u'],
			'loginInfo' => array(
				'loginCount' => 90,
				'previousLoginTime' => '2014-07-13T00:06:48.252+0930'
			),
				
			// session appears to be required by IntelliJ
			'session' => array(
				'name' => $user['u'],
				'value' => ''
			)
		);
	}
	
	private static function _get_field($name) {
		switch ($name) {
			case 'assignee':
				return array('required' => false, 'schema' => self::_get_schema('assignee'), 'name' => 'Assignee', 'autoCompleteUrl' => self::$_api_url . 'user/assignable/search?issueKey=JC-1&username=', 'operations' => array('set'));
			case 'attachment':
				return array('required' => false, 'schema' => self::_get_schema('attachment'), 'name' => 'Attachment', 'operations' => array());
			case 'comment':
				return array('required' => false, 'schema' => self::_get_schema('comment'), 'name' => 'Comment', 'operations' => array('add', 'edit', 'remove'));
			case 'components':
				return array('required' => false, 'schema' => self::_get_schema('components'), 'name' => 'Component/s', 'operations' => array('add', 'set', 'remove'), 'allowedValues' => array());
			case 'customfield_10100':
				return array('required' => false, 'schema' => self::_get_schema('customfield_10100'), 'name' => 'Reference #', 'operations' => array('set'));
			case 'description':
				return array('required' => false, 'schema' => self::_get_schema('description'), 'name' => 'Description', 'operations' => array('set'));
			case 'duedate':
				return array('required' => false, 'schema' => self::_get_schema('duedate'), 'name' => 'Due Date', 'operations' => array('set'));
			case 'environment':
				return array('required' => false, 'schema' => self::_get_schema('environment'), 'name' => 'Environment', 'operations' => array('set'));
			case 'fixVersions':
				return array('required' => false, 'schema' => self::_get_schema('fixVersions'), 'name' => 'Fix Version/s', 'operations' => array('set', 'add', 'remove'), 'allowedValues' => array());
			case 'issuetype':
				return array('required' => true, 'schema' => self::_get_schema('issuetype'), 'name' => 'Issue Type', 'operations' => array(), 'allowedValues' => self::issue_type());
			case 'labels':
				return array('required' => false, 'schema' => self::_get_schema('labels'), 'name' => 'Labels', 'autoCompleteUrl' => self::$_api_url . 'labels/suggest?query=', 'operations' => array('add', 'set', 'remove'));
			case 'priority':
				return array('required' => false, 'schema' => self::_get_schema('priority'), 'name' => 'Priority', 'operations' => array('set'), 'allowedValues' => self::priority());
			case 'reporter':
				return array('required' => true, 'schema' => self::_get_schema('reporter'), 'name' => 'Reporter', 'autoCompleteUrl' => self::$_api_url . 'user/search?username=', 'operations' => array('set'));
			case 'resolution':
				return array('required' => true, 'schema' => self::_get_schema('resolution'), 'name' => 'Resolution', 'operations' => array('set'), 'allowedValues' => self::resolution());
			case 'summary':
				return array('required' => true, 'schema' => self::_get_schema('summary'), 'name' => 'Summary', 'operations' => array('set'));
			case 'timetracking':
				return array('required' => false, 'schema' => self::_get_schema('timetracking'), 'name' => 'Time Tracking', 'operations' => array('set', 'edit'));
			case 'versions':
				return array('required' => false, 'schema' => self::_get_schema('versions'), 'name' => 'Affects Version/s', 'operations' => array('set', 'add', 'remove'), 'allowedValues' => array());
			case 'worklog':
				return array('required' => false, 'schema' => self::_get_schema('worklog'), 'name' => 'Work Log', 'operations' => array('add'));
		}
		throw new CapsJiraException('Unknown field: ' . $name);
	}
	
	private static function _get_fields($names) {
		$fields = array();
		foreach ($names as $name)
			$fields[$name] = self::_get_field($name);
		return $fields;
	}
	
	private static function _get_group() {
		return array(
			'type' => 'group',
			'value' => 'all'
		);
	}
	
	public static function issue($issue_id) {
		self::_authenticate();
		
		$expand = explode(',', self::_get_var('expand'));
		
		$issue = CapsApi::get_issue($issue_id);
		if (!$issue)
			throw new CapsJiraException('Unable to find issue', 404);
		$issue_id = $issue['job_task_id'];

		$row = CapsApi::get_work_in_progress($issue_id);
		$status = $row ? self::STATUS_IN_PROGRESS : ($issue['completed'] ? self::STATUS_DONE : self::STATUS_OPEN);
		
		$resolution = null;
		if ($status == self::STATUS_DONE)
			$resolution = self::_get_resolution(self::RESOLUTION_DONE);
		elseif ($issue['status'] == CapsApi::STATUS_HOLD)
			$resolution = self::_get_resolution(self::RESOLUTION_HOLD);
		elseif ($issue['status'] == CapsApi::STATUS_REVIEW)
			$resolution = self::_get_resolution(self::RESOLUTION_REVIEW);
		
		$time_spent = 0;
		$worklogs = self::_get_worklogs($issue_id);
		foreach ($worklogs['worklogs'] as $log)
			$time_spent += $log['timeSpentSeconds'];
		
		$time_remaining = $issue['estimate'] * 60;
		
		$output = array(
			'expand' => 'renderedFields,names,schema,transitions,operations,editmeta,changelog',
			'id' => strval($issue_id),
			'self' => self::$_api_url . 'issue/' . $issue_id,
			'key' => strval($issue_id),
			'fields' => array(
				'summary' => $issue['summary'],
				'progress' => array(
					'progress' => $time_spent,
					'total' => $time_spent,
					'percent' => $time_spent ? 100 : 0
				),
				'timetracking' => array(
					'remainingEstimate' => self::_seconds_to_string($time_remaining),
					'timeSpent' => self::_seconds_to_string($time_spent),
					'remainingEstimateSeconds' => $time_remaining,
					'timeSpentSeconds' => $time_spent
				),
				'issuetype' => self::_get_issue_type(),
				'timespent' => $time_spent == 0 ? null : $time_spent,
				'reporter' => self::_get_user($issue['created_by']),
				'created' => self::_date($issue['created_on']),
				'updated' => self::_date($issue['last_modified']),
				'priority' => self::_get_priority(),
				'description' => $issue['details'],
				'issuelinks' => array(),
				'subtasks' => array(),
				'status' => self::_get_status($status),
				'labels' => array(),
				'workratio' => -1,
				'project' => self::_get_project($issue['job_id']),
				'environment' => null,
				'lastViewed' => self::_date('2014-01-01 00:00:00'),
				'aggregateprogress' => array(
					'progress' => $time_spent,
					'total' => $time_spent,
					'percent' => $time_spent ? 100 : 0
				),
				'components' => array(),
				'comment' => self::_get_comments($issue_id),
				'timeoriginalestimate' => null,
				'votes' => array(
					'self' => self::$_api_url . 'issue/' . $issue_id . 'votes',
					'votes' => 0,
					'hasVoted' => false
				),
				'resolution' => $resolution,
				'fixVersions' => array(),
				'resolutiondate' => null,
				'creator' => self::_get_user($issue['created_by']),
				'aggregatetimeoriginalestimate' => null,
				'duedate' => $issue['due_date'],
				'watches' => array(
					'self' => self::$_api_url . 'issue/' . $issue_id . '/watchers',
					'watchCount' => 1,
					'isWatching' => true
				),
				'worklog' => $worklogs,
				'assignee' => self::_get_user($issue['employee']),
				'attachment' => self::_get_attachments($issue_id),
				'aggregatetimeestimate' => null,
				'versions' => array(),
				'timeestimate' => null,
				'aggregatetimespent' => $time_spent,
				'customfield_10100' => $issue['job_task_number']
			),
			'names' => array(
				'summary' => 'Summary',
				'progress' => 'Progress',
				'timetracking' => 'Time Tracking',
				'issuetype' => 'Issue Type',
				'timespent' => 'Time Spent',
				'reporter' => 'Reporter',
				'created' => 'Created',
				'updated' => 'Updated',
				'priority' => 'Priority',
				'description' => 'Description',
				'issuelinks' => 'Linked Issues',
				'subtasks' => 'Sub-Tasks',
				'status' => 'Status',
				'labels' => 'Labels',
				'workratio' => 'Work Ratio',
				'project' => 'Project',
				'environment' => 'Environment',
				'lastViewed' => 'Last Viewed',
				'aggregateprogress' => 'Sum Progress',
				'components' => 'Component/s',
				'comment' => 'Comment',
				'timeoriginalesimtate' => 'Original Estimate',
				'votes' => 'Votes',
				'resolution' => 'Resolution',
				'fixVersions' => 'Fix Version/s',
				'resolutiondate' => 'Resolved',
				'creator' => 'Creator',
				'aggregatetimeoriginalestimate' => 'Sum Original Estimate',
				'duedate' => 'Due Date',
				'watches' => 'Watchers',
				'worklog' => 'Log Work',
				'assignee' => 'Assignee',
				'attachment' => 'Attachment',
				'aggregatetimeestimate' => 'Sum Remaining Estimate',
				'versions' => 'Affects Version/s',
				'timeestimate' => 'Remaining Estimate',
				'aggregatetimespent' => 'Sum Time Spent',
				'customfield_10100' => 'Reference #',
			),
			'schema' => self::_get_schemas(array(
				'summary',
				'progress',
				'timetracking',
				'issuetype',
				'timespent',
				'reporter',
				'created',
				'updated',
				'priority',
				'description',
				'issuelinks',
				'subtasks',
				'status',
				'labels',
				'workratio',
				'project',
				'environment',
				'lastViewed',
				'aggregateprogress',
				'components',
				'comment',
				'timeoriginalestimate',
				'votes',
				'resolution',
				'fixVersions',
				'resolutiondate',
				'creator',
				'aggregatetimeoriginalestimate',
				'duedate',
				'watches',
				'worklog',
				'assignee',
				'attachment',
				'aggregatetimeestimate',
				'versions',
				'timeestimate',
				'aggregatetimespent',
				'customfield_10100'
			)),
			'transitions' => array(
				self::_get_transition($status == self::STATUS_IN_PROGRESS ? self::TRANSITION_STOP : self::TRANSITION_START),
				self::_get_transition(self::TRANSITION_RESOLVE),
				self::_get_transition(self::TRANSITION_CLOSE)
			),
				
			// This determines what fields are editable in the interface
			'editmeta' => array(
				'fields' => self::_get_fields(array(
					'summary',
					'timetracking',
// 					'issuetype',
// 					'labels',
					'assignee',
// 					'fixVersions',
					'attachment',
					'reporter',
// 					'versions',
// 					'environment',
// 					'priority',
					'description',
					'duedate',
// 					'components',
					'comment',
					'customfield_10100'
				))
			)
		);
		
		return $output;
	}
	
	private static function _convert_attachment($row) {
		return array(
			'self' => self::$_api_url . 'attachments/' . $row['attach_id'],
			'id' => $row['attach_id'],
			'filename' => $row['file_name'],
			'author' => self::_get_user($row['created_by']),
			'created' => self::_date($row['file_date']),
			'size' => filesize($row['filepath']),
			'mimeType' => mime_content_type($row['filepath']),
			'properties' => new stdClass(),
			'content' => $row['url']
		);
	}
	
	private static function _get_attachments($issue_id) {
		$output = array();
		$attachments = CapsApi::issue_attachments($issue_id);
		foreach ($attachments as $row)
			$output[] = self::_convert_attachment($row);
		return $output;
	}
	
	public static function issue_attachment($issue) {
		
		$file = array_safe($_FILES, 'file', array());
		$tmp_name = array_safe($file, 'tmp_name', '');
		$filename = array_safe($file, 'name', '');
		if (file_exists($tmp_name) && is_uploaded_file($tmp_name)) {
			CapsApi::issue_attachment_create($issue['id'], $tmp_name, $filename, '');
			return self::_get_attachments($issue['id']);
		}
		
		return new CapsJiraResponse(403);
	}
	
	private static function _convert_comment($comment) {
		return array(
			'self' => self::$_api_url . 'issue/' . $comment['job_task_id'] . '/comment/' . $comment['job_task_note_id'],
			'id' => strval($comment['job_task_note_id']),
			'author' => self::_get_user($comment['created_by']),
			'body' => $comment['body'],
			'updateAuthor' => self::_get_user($comment['created_by']),
			'created' => self::_date($comment['created_on']),
			'updated' => self::_date($comment['last_modified']),
			'visibility' => self::_get_group()
		);
	}
	
	public static function issue_comment_get($comment_id) {
		$comment = CapsApi::issue_comment_get($comment_id);
		if ($comment)
			return self::_convert_comment($comment);
		return new CapsJiraResponse(404);
	}
	
	public static function issue_comment($issue) {
		
		$body = self::_post_var('body', '');
		if ($body) {
			$id = CapsApi::issue_comment_create($issue['id'], $body);
			if ($id)
				return new CapsJiraResponse(201, self::issue_comment_get($id));
		}
		
		return new CapsJiraResponse(400);
	}
	
	private static function _get_comments($issue_id) {
		$output = array();
		foreach (CapsApi::issue_comments_get($issue_id) as $row)
			$output[] = self::_convert_comment($row);
		return array(
			'startAt' => 0,
			'maxResults' => count($output),
			'total' => count($output),
			'comments' => $output
		);
	}
	
	private static function _convert_issue_post() {
		
		$details = array();
		foreach (self::_post_var('fields', array()) as $name => $value) {
			switch ($name) {
				case 'summary':
					$details['description'] = CapsApi::parse_issue_summary($value);
					break;
				case 'project':
					$project = self::_get_project($value['key']);
					if ($project)
						$details['job_id'] = $project['id'];
					break;
				case 'description':
					$bits = CapsApi::parse_issue_description($value);
					foreach ($bits as $k => $v)
						$details[$k] = $v;
					break;
				case 'assignee':
					$details['employee'] = $value['name'];
					break;
				case 'duedate':
					$details['due_date'] = $value;
					break;
				case 'timetracking':
					$details['estimate'] = formatMinutes(self::_string_to_minutes($value['originalEstimate']));
				case 'customfield_10100':
					$details['job_task_number'] = $value;
					break;
			}
		}
		
		return $details;
	}
	
	public static function issue_create() {
		$details = self::_convert_issue_post();
		if (array_safe($details, 'job_id', 0) > 0) {
			$issue = CapsApi::issue_update(0, $details);
			if ($issue)
				return self::issue($issue['job_task_id']);
		}
	}
	
	public static function issue_create_meta() {
		$project_id = self::_get_var('projectKeys');
		$project = self::_get_project($project_id);
		return array(
			'expand' => 'projects',
			'projects' => array(
				$project
			)
		);
	}
	
	private static function _has_transition($issue, $name) {
		foreach ($issue['transitions'] as $issue) {
			if ($issue['name'] == $name)
				return true;
		}
		return false;
	}
	
	public static function issue_transition($issue) {
		if ($issue == false)
			return new CapsJiraResponse(404);
		
		// Detect what transition to go to
		$transition = self::_get_transition_by_id(array_safe(self::_post_var('transition', array()), 'id', null));
		switch (array_safe($transition, 'name', '')) {
			
			case self::TRANSITION_CLOSE:
				if (CapsApi::issue_complete($issue['id']))
					return new CapsJiraResponse(204);
				break;
			
			case self::TRANSITION_START:
				$work_id = CapsApi::start_work($issue['id']);
				return new CapsJiraResponse(204);
				
			case self::TRANSITION_STOP:
				$work_id = CapsApi::stop_work($issue['id']);
				return new CapsJiraResponse(204);
				
			case self::TRANSITION_RESOLVE:
				$fields = self::_post_var('fields', array());
				$resolution = self::_get_resolution_by_id(array_safe(array_safe($fields, 'resolution', array()), 'id', 0));
				if ($resolution) {
					$status = '';
					switch ($resolution['name']) {
						case self::RESOLUTION_DEFINE:
							$status = 'Define';
							break;
						case self::RESOLUTION_DEPLOY:
							$status = 'Deploy';
							break;
						case self::RESOLUTION_DESIGN:
							$status = 'Define';
							break;
						case self::RESOLUTION_DEVELOP:
							$status = 'Develop';
							break;
						case self::RESOLUTION_DONE:
							$status = self::STATUS_DONE;
							break;
						case self::RESOLUTION_HOLD:
							$status = 'Hold';
							break;
						case self::RESOLUTION_REVIEW:
							$status = 'Review';
							break;
						case self::RESOLUTION_TEST:
							$status = 'Test';
							break;
					}
					
					// Mark as completed
					if ($status == self::STATUS_DONE) {
						if (CapsApi::issue_complete($issue['id']))
							return new CapsJiraResponse(204);
						
					// Update the status
					} elseif ($status) {
						if (CapsApi::issue_status($issue['id'], $status))
							return new CapsJiraResponse(204);
						
					}
					
				}
				debug('Unknown resolution: ');
				debug(self::$_post_data);
				break;
				
			default:
				debug('Unknown transition: ');
				debug($transition['id'] . ' - ' . $transition['name']);
				debug(self::$_post_data);
				
		}
		
		return new CapsJiraResponse(404);
	}
	
	private static function _get_issue_type($name = 'Task') {
		foreach (self::issue_type() as $v) {
			if ($v['name'] == $name)
				return $v;
		}
		return null;
	}
	
	public static function issue_type() {
		self::_authenticate();
		return array(
			array(
				'self' => self::$_api_url . 'issuetype/3',
				'id' => '3',
				'description' => 'A task that needs to be done.',
				'iconUrl' => self::$_img_url . 'task.png',
				'name' => 'Task',
				'subtask' => false
			),
			/*
			array(
				'self' => self::$_api_url . 'issuetype/5',
				'id' => '5',
				'description' => 'The sub-task of the issue',
				'iconUrl' => self::$_img_url . 'subtask_alternate.png',
				'name' => 'Sub-task',
				'subtask' => true
			),
			array(
				'self' => self::$_api_url . 'issuetype/2',
				'id' => '2',
				'description' => 'A new feature of the product, which has yet to be developed.',
				'iconUrl' => self::$_img_url . 'newfeature.png',
				'name' => 'New Feature',
				'subtask' => false
			)
			*/
		);
	}
	
	public static function issue_update($issue_id) {
		$details = self::_convert_issue_post();
		$issue = CapsApi::issue_update($issue_id, $details);
		if ($issue)
			return self::issue($issue_id);
	}
	
	private static function _get_priority($name = 'Major') {
		foreach (self::priority() as $v) {
			if ($v['name'] == $name)
				return $v;
		}
		return null;
	}
	
	public static function priority() {
		self::_authenticate();
		return array(
			array(
				'self' => self::$_api_url . 'priority/1',
				'statusColor' => '#cc0000',
				'description' => 'Not applicable.',
				'iconUrl' => self::$_img_url . 'minor.png',
				'name' => 'N/A',
				'id' => '3'
			)
		);

	}
	
	private static function _get_project($project_id) {
		$project = CapsApi::get_project($project_id);
		return array(
			'self' => self::$_api_url . 'project/' . $project['job_id'],
			'id' => $project['job_id'],
			'key' => $project['job_number'],
			'name' => ($project['trading_name'] ? $project['trading_name'] : $project['client_name']) . ' - ' . $project['job_title'],
			'description' => '',
			'lead' => self::_get_user('admin'),
			'components' => array(),
			'issueTypes' => self::issue_type(),
			'assigneeType' => 'UNASSIGNED',
			'versions' => array(),
			'roles' => array(
				'Users' => self::$_api_url . 'project/' . $project['job_id'] . '/role/10000'
			),
			'avatarUrls' => array(
				'16x16' => self::$_img_url . 'avatar-16x16.png',
				'24x24' => self::$_img_url . 'avatar-24x24.png',
				'32x32' => self::$_img_url . 'avatar-32x32.png',
				'48x48' => self::$_img_url . 'avatar-48x48.png'
			)
		);
	}
	
	public static function project($project_id) {
		return self::_get_project($project_id);
	}
	
	public static function projects() {
		self::_authenticate();
		$output = array();
		$ids = CapsApi::get_project_ids(self::_username());
		foreach ($ids as $project_id)
			$output[] = self::_get_project($project_id);
		return $output;
	}
	
	private static function _get_resolution($name = '') {
		if ($name) {
			foreach (self::resolution() as $v) {
				if ($v['name'] == $name)
					return $v;
			}
		}
		return null;
	}
	
	private static function _get_resolution_by_id($id) {
		foreach (self::resolution() as $v) {
			if ($v['id'] == $id)
				return $v;
		}
		return null;
	}
	
	public static function resolution() {
		self::_authenticate();
		
		return array(
			
			array(
				'self' => self::$_api_url . 'resolution/1',
				'id' => '1',
				'description' => 'Approval is required from the client to deploy.',
				'name' => self::RESOLUTION_REVIEW
			),
			array(
				'self' => self::$_api_url . 'resolution/2',
				'id' => '2',
				'description' => 'Task description and estimates needs to be completed.',
				'name' => self::RESOLUTION_DEFINE
			),
			array(
				'self' => self::$_api_url . 'resolution/3',
				'id' => '3',
				'description' => 'Graphical and style guides need to be completed.',
				'name' => self::RESOLUTION_DESIGN
			),
			array(
				'self' => self::$_api_url . 'resolution/4',
				'id' => '4',
				'description' => 'Programming tasks need to be completed.',
				'name' => self::RESOLUTION_DEVELOP
			),
			array(
				'self' => self::$_api_url . 'resolution/5',
				'id' => '5',
				'description' => 'Approval has been granted to deploy the update.',
				'name' => self::RESOLUTION_DEPLOY
			),
			array(
				'self' => self::$_api_url . 'resolution/6',
				'id' => '6',
				'description' => 'Testing is required before approval can be granted.',
				'name' => self::RESOLUTION_TEST
			),
			array(
				'self' => self::$_api_url . 'resolution/7',
				'id' => '7',
				'description' => 'Work is on hold due to customer delay or budget concern.',
				'name' => self::RESOLUTION_HOLD,
			),
			array(
				'self' => self::$_api_url . 'resolution/10000',
				'id' => '10000',
				'description' => '',
				'name' => self::RESOLUTION_DONE
			)
		);
		
		return array(
			array(
				'self' => self::$_api_url . 'resolution/1',
				'id' => '1',
				'description' => 'A fix for this issue is checked into the tree and tested.',
				'name' => 'Fixed'
			),
			array(
				'self' => self::$_api_url . 'resolution/2',
				'id' => '2',
				'description' => 'The problem described is an issue which will never be fixed.',
				'name' => 'Won\'t Fix'
			),
			array(
				'self' => self::$_api_url . 'resolution/3',
				'id' => '3',
				'description' => 'The problem is a duplicate of an existing issue.',
				'name' => 'Duplicate'
			),
			array(
				'self' => self::$_api_url . 'resolution/4',
				'id' => '4',
				'description' => 'The problem is not completely described.',
				'name' => 'Incomplete'
			),
			array(
				'self' => self::$_api_url . 'resolution/5',
				'id' => '5',
				'description' => 'All attempts at reproducing this issue failed, or not enough information was available to reproduce the issue. Reading the code produces no clues as to why this behavior would occur. If more information appears later, please reopen the issue.',
				'name' => 'Cannot Reproduce'
			),
			array(
				'self' => self::$_api_url . 'resolution/10000',
				'id' => '10000',
				'description' => '',
				'name' => 'Done'
			)
		);
	}
	
	private static function _get_schema($name) {
		switch ($name) {
			case 'aggregateprogress':
				return array('type' => 'progress', 'system' => 'aggregateprogress');
			case 'aggregatetimeestimate':
				return array('type' => 'number', 'system' => 'aggregatetimeestimate');
			case 'aggregatetimespent':
				return array('type' => 'number', 'system' => 'aggregatetimespent');
			case 'aggregatetimeoriginalestimate':
				return array('type' => 'number', 'system' => 'aggregatetimeoriginalestimate');
			case 'assignee':
				return array('type' => 'user', 'system' => 'assignee');
			case 'attachment':
				return array('type' => 'array', 'items' => 'attachment', 'system' => 'attachment');
			case 'comment':
				return array('type' => 'array', 'items' => 'comment', 'system' => 'comment');
			case 'components':
				return array('type' => 'array', 'items' => 'component', 'system' => 'components');
			case 'created':
				return array('type' => 'datetime', 'system' => 'created');
			case 'creator':
				return array('type' => 'user', 'system' => 'creator');
			case 'customfield_10100':
				return array('type' => 'string', 'custom' => 'com.atlassian.jira.plugin.system.customfieldtypes:textfield', 'customId' => 10100);
			case 'description':
				return array('type' => 'string', 'system' => 'description');
			case 'duedate':
				return array('type' => 'date', 'system' => 'duedate');
			case 'environment':
				return array('type' => 'string', 'system' => 'environment');
			case 'fixVersions':
				return array('type' => 'array', 'items' => 'version', 'system' => 'fixVersions');
			case 'issuelinks':
				return array('type' => 'array', 'items' => 'issuelinks', 'system' => 'issuelinks');
			case 'issuetype':
				return array('type' => 'issuetype', 'system' => 'issuetype');
			case 'labels':
				return array('type' => 'array', 'items' => 'string', 'system' => 'labels');
			case 'lastViewed':
				return array('type' => 'datetime', 'system' => 'lastViewed');
			case 'priority':
				return array('type' => 'priority', 'system' => 'priority');
			case 'progress':
				return array('type' => 'progress', 'system' => 'progress');
			case 'project':
				return array('type' => 'project', 'system' => 'project');
			case 'reporter':
				return array('type' => 'user', 'system' => 'reporter');
			case 'resolution':
				return array('type' => 'resolution', 'system' => 'resolution');
			case 'resolutiondate':
				return array('type' => 'datetime', 'system' => 'resolutiondate');
			case 'status':
				return array('type' => 'status', 'system' => 'status');
			case 'subtasks':
				return array('type' => 'array', 'items' => 'issuelinks', 'system' => 'subtasks');
			case 'summary':
				return array('type' => 'string', 'system' => 'summary');
			case 'timeestimate':
				return array('type' => 'number', 'system' => 'timeestimate');
			case 'timeoriginalestimate':
				return array('type' => 'number', 'system' => 'timeoriginalestimate');
			case 'timespent':
				return array('type' => 'number', 'system' => 'timespent');
			case 'timetracking':
				return array('type' => 'timetracking', 'system' => 'timetracking');
			case 'updated':
				return array('type' => 'datetime', 'system' => 'updated');
			case 'versions':
				return array('type' => 'array', 'items' => 'version', 'system' => 'versions');
			case 'votes':
				return array('type' => 'array', 'items' => 'votes', 'system' => 'votes');
			case 'watches':
				return array('type' => 'array', 'items' => 'watches', 'system' => 'watches');
			case 'worklog':
				return array('type' => 'array', 'items' => 'worklog', 'system' => 'worklog');
			case 'workratio':
				return array('type' => 'number', 'system' => 'workratio');
		}
		throw new CapsJiraException('Unknown schema "' . $name . '"');
	}
	
	private static function _get_schemas($names) {
		$schemas = array();
		foreach ($names as $name)
			$schemas[$name] = self::_get_schema($name);
		return $schemas;
	}
	
	private static function _search_issue($issue_id) {
		$issue = CapsApi::get_issue($issue_id);
		return array(
			'expand' => '',
			'id' => strval($issue_id),
			'self' => self::$_api_url . 'issue/' . $issue_id,
			'key' => $issue_id
		);
	}
	
	public static function search() {
		self::_authenticate();
		
		$start_at = self::_get_var('startAt', 0);
		$max_results = self::_get_var('maxResults', 1000);
		
		$output = array(
			'expand' => 'names, schema',
			'startAt' => $start_at,
			'maxResults' => $max_results,
			'total' => 0,
			'issues' => array()
		);
		
		// Determine the project id to find
		$query = self::_get_var('jql');
		if (preg_match('|project\s+in\s+\("(\d+\-\d+)"|', $query, $matches)) {
			$project = CapsApi::get_project($matches[1]);
			$project_id = array_safe($project, 'job_id', -1);
			
		// Use all products
		} else {
			$project_id = CapsApi::PROJECT_ALL;
			
		}
		
		$issue_ids = CapsApi::get_issue_ids($project_id, $start_at, $max_results);
		foreach ($issue_ids as $issue_id)
			$output['issues'][] = self::_search_issue($issue_id);
		
		$output['total'] = count($output['issues']);
		return $output;
	}
	
	public static function server_info() {
		self::_authenticate();
		return array(
			'baseUrl' => rtrim(self::$_base_url, '/'),
			'version' => '6.3-OD-08-005-WN',
			'versionNumbers' => array(6, 3, 0),
			'buildNumber' => 6328,
			'buildDate' => '2014-06-30T00:00:00.000+0930',
			'serverTime' => self::_date(date('Y-m-d H:i:s')),
			'scmInfo' => 'aae40f7178774ac0c2b0a349d29c88f14216faa1',
			'serverTitle' => 'JIRA'
		);
	}
	
	private static function _get_status($name = self::STATUS_OPEN) {
		foreach (self::status() as $v) {
			if ($v['name'] == $name)
				return $v;
		}
		return null;
	}
	
	public static function status() {
		self::_authenticate();
		return array(
			array(
				'self' => 'https://sonofharris.atlassian.net/rest/api/latest/status/1',
				'description' => 'The issue is open and ready for the assignee to start work on it.',
				'iconUrl' => 'https://sonofharris.atlassian.net/images/icons/statuses/open.png',
				'name' => self::STATUS_OPEN,
				'id' => '1',
				'statusCategory' => self::_get_status_category('new')
			),
			array(
				'self' => 'https://sonofharris.atlassian.net/rest/api/latest/status/3',
				'description' => 'This issue is being actively worked on at the moment by the assignee.',
				'iconUrl' => 'https://sonofharris.atlassian.net/images/icons/statuses/inprogress.png',
				'name' => self::STATUS_IN_PROGRESS,
				'id' => '3',
				'statusCategory' => self::_get_status_category('indeterminate')
			),
			array(
				'self' => 'https://sonofharris.atlassian.net/rest/api/latest/status/4',
				'description' => 'This issue was once resolved, but the resolution was deemed incorrect. From here issues are either marked assigned or resolved.',
				'iconUrl' => 'https://sonofharris.atlassian.net/images/icons/statuses/reopened.png',
				'name' => self::STATUS_REOPENED,
				'id' => '4',
				'statusCategory' => self::_get_status_category('new')
			),
			array(
				'self' => 'https://sonofharris.atlassian.net/rest/api/latest/status/5',
				'description' => 'A resolution has been taken, and it is awaiting verification by reporter. From here issues are either reopened, or are closed.',
				'iconUrl' => 'https://sonofharris.atlassian.net/images/icons/statuses/resolved.png',
				'name' => self::STATUS_RESOLVED,
				'id' => '5',
				'statusCategory' => self::_get_status_category('done')
			),
			array(
				'self' => 'https://sonofharris.atlassian.net/rest/api/latest/status/6',
				'description' => 'The issue is considered finished, the resolution is correct. Issues which are closed can be reopened.',
				'iconUrl' => 'https://sonofharris.atlassian.net/images/icons/statuses/closed.png',
				'name' => self::STATUS_CLOSED,
				'id' => '6',
				'statusCategory' => self::_get_status_category('done')
			),
			array(
				'self' => 'https://sonofharris.atlassian.net/rest/api/latest/status/10000',
				'description' => '',
				'iconUrl' => 'https://sonofharris.atlassian.net/images/icons/statuses/closed.png',
				'name' => self::STATUS_DONE,
				'id' => '10000',
				'statusCategory' => self::_get_status_category('done')
			),
			array(
				'self' => 'https://sonofharris.atlassian.net/rest/api/latest/status/10001',
				'description' => '',
				'iconUrl' => 'https://sonofharris.atlassian.net/images/icons/statuses/open.png',
				'name' => self::STATUS_TO_DO,
				'id' => '10001',
				'statusCategory' => self::_get_status_category('new')
			)
		);
	}
	
	private static function _get_status_category($key) {
		foreach (self::statuscategory() as $v) {
			if ($v['id'] == $key || $v['key'] == $key)
				return $v;
		}
		return null;
	}
	
	public static function statuscategory() {
		return array(
			array(
				'self' => self::$_api_url . 'statuscategory/2',
				'id' => 2,
				'key' => 'new',
				'colorName' => 'blue-gray',
				'name' => 'New'
			),
			array(
				'self' => self::$_api_url . 'statuscategory/3',
				'id' => 3,
				'key' => 'done',
				'colorName' => 'green',
				'name' => 'Complete'
			),
			array(
				'self' => self::$_api_url . 'statuscategory/4',
				'id' => 4,
				'key' => 'indeterminate',
				'colorName' => 'yellow',
				'name' => 'In Progress'
			)
		);
	}
	
	private static function _get_transition($name) {
		foreach (self::transitions() as $v) {
			if ($v['name'] == $name)
				return $v;
		}
		return null;
	}
	
	private static function _get_transition_by_id($id) {
		foreach (self::transitions() as $v) {
			if ($v['id'] == $id)
				return $v;
		}
		return null;
	}
	
	public static function transitions() {
		return array(
			array(
				'id' => '4',
				'name' => self::TRANSITION_START,
				'to' => self::_get_status(self::STATUS_IN_PROGRESS),
				'fields' => new stdClass()
			),
			array(
				'id' => '5',
				'name' => self::TRANSITION_RESOLVE,
				'to' => self::_get_status(self::STATUS_RESOLVED),
				'fields' => self::_get_fields(array('assignee', 'fixVersions', 'resolution', 'worklog'))
			),
			array(
				'id' => '2',
				'name' => self::TRANSITION_CLOSE,
				'to' => self::_get_status(self::STATUS_CLOSED),
				'fields' => self::_get_fields(array('assignee', 'fixVersions', 'worklog'))
			),
			array(
				'id' => '301',
				'name' => self::TRANSITION_STOP,
				'to' => self::_get_status(self::STATUS_OPEN),
				'fields' => new stdClass()
			)
		);
	}
	
	private static function _get_user($username) {
		$user = CapsApi::get_user($username);
		if ($user == false)
			return null;
		return array(
			'self' => self::$_api_url . 'user?username=' . $username,
			'name' => $user['u'],
			'emailAddress' => $user['email'],
			'avatarUrls' => array(
				'16x16' => self::$_img_url . 'avatar-16x16.png',
				'24x24' => self::$_img_url . 'avatar-24x24.png',
				'32x32' => self::$_img_url . 'avatar-32x32.png',
				'48x48' => self::$_img_url . 'avatar-48x48.png'
			),
			'displayName' => $user['first_name'] . ' ' . $user['last_name'],
			'active' => true
		);
	}
	
	private static function _string_to_minutes($string) {
		$minutes = 0;
		if (preg_match('/\d+m/', $string, $matches))
			$minutes += $matches[0];
		if (preg_match('/\d+h/', $string, $matches))
			$minutes += $matches[0] * 60;
		return $minutes;
	}
	
	private static function _seconds_to_string($seconds) {
		$minutes = floor($seconds / 60);
		$hours = floor($minutes / 60);
		return $hours . 'h ' . $minutes . 'm';
	}
	
	private static function _convert_worklog($log) {
		$seconds = strtotime($log['job_date'] . ' ' . $log['end_time']) - strtotime($log['job_date'] . ' ' . $log['start_time']);
		return array(
			'self' => self::$_api_url . 'issue/' . $log['job_task_id'] . '/worklog/' . $log['job_details_id'],
			'author' => self::_get_user($log['employee']),
			'updateAuthor' => self::_get_user($log['employee']),
			'comment' => $log['description'],
			'created' => self::_date($log['created_on']),
			'updated' => self::_date($log['last_modified']),
			'started' => self::_date($log['job_date'] . ' ' . $log['start_time']),
			'timeSpent' => self::_seconds_to_string($seconds),
			'timeSpentSeconds' => $seconds,
			'id' => $log['job_details_id']
		);
	}
	
	private static function _get_worklogs($issue_id) {
		$logs = CapsApi::get_work($issue_id);
		
		$worklogs = array();
		foreach ($logs as $log)
			$worklogs[] = self::_convert_worklog($log);
		
		return array(
			'startAt' => 0,
			'maxResults' => count($worklogs),
			'total' => count($worklogs),
			'worklogs' => $worklogs
		);
	}
	
	private static function _self() {
		return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}
	
	private static function _obj_to_array($var) {
		if (is_object($var) || is_array($var)) {
			$output = array();
			foreach ($var as $k => $v)
				$output[$k] = self::_obj_to_array($v);
			return $output;
		}
		return $var;
	}
	
	public static function dispatch() {
		
		self::$_base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/caps/';
		self::$_api_url = self::$_base_url . 'rest/api/2/';
		self::$_img_url = self::$_base_url . 'images/jira/';
		
		// Fail by default
		header('HTTP/1.1 500 Internal Server Error');
		header('Status: 500 Internal Server Error');
		
		try {
			
			// Determine path
			$request = $_SERVER['REQUEST_URI'];
			$path = preg_replace('|^/caps/rest/|', '', $request);
			$path = preg_replace('|^api/latest/|', '', $path);
			$path = preg_replace('|^api/2/|', '', $path);
			if (strpos($path, '?') !== false)
				$path = substr($path, 0, strpos($path, '?'));
			
			// Get the POST data
			self::$_post_data = array();
			$method = $_SERVER['REQUEST_METHOD'];
			if ($method == self::POST || $method == self::PUT) {
				$data = file_get_contents('php://input');
				if ($data)
					self::$_post_data = self::_obj_to_array(json_decode($data));
			}
			
			// Handle the path
			$output = self::_dispatch($method, $path);
			if ($output === null)
				throw new CapsJiraException('Unhandled request: ' . $method . ' - ' . $path . ' ? ' . $_SERVER['QUERY_STRING']);
			
			// Convert pure array response to object approach with standard http code
			if (is_array($output))
				$output = new CapsJiraResponse(200, $output);
			
		} catch (CapsJiraException $e) {
			debug($e->getMessage());
			$output = new CapsJiraResponse($e->getCode() ? $e->getCode() : 500, array());
			
		}
		
		// Output HTTP status code
		switch ($output->http_code) {
			case 201:
				header('HTTP/1.1 201 Created', true);
				header('Status: 201 Created', true);
				break;
			case 202:
				header('HTTP/1.1 202 Accepted', true);
				header('Status: 202 Accepted', true);
				break;
			case 204:+
				header('HTTP/1.1 204 No Content', true);
				header('Status: 204 No Content', true);
				break;
			case 301:
				header('HTTP/1.1 301 Moved Permanently', true);
				$this->redirect_to($output['redirect']);
				exit();
			case 302:
				$this->redirect_to($output['redirect'], true);
				exit();
			case 400:
				header('HTTP/1.1 400 Bad Request', true);
				header('Status: 400 Bad Request', true);
				break;
			case 401:
				header('HTTP/1.1 401 Authorization Required', true);
				header('WWW-Authenticate: Basic realm=NoSoupForYou', true);
				break;
			case 403:
				header('HTTP/1.1 403 Forbidden', true);
				header('Status: 403 Forbidden', true);
				break;
			case 404:
				header('HTTP/1.1 404 Not Found', true);
				header('Status: 404 Not Found', true);
				break;
			case 413:
				header('HTTP/1.1 413 Request Entity Too Large', true);
				header('Status: 413 Request Entity Too Large', true);
				break;
			case 500:
				header('HTTP/1.1 500 Internal Server Error', true);
				header('Status: 500 Internal Server Error', true);
				break;
			case 501:
				header('HTTP/1.1 501 Not Implemented', true);
				header('Status: 501 Not Implemented', true);
				break;
			case 502:
				header('HTTP/1.1 502 Bad Gateway', true);
				header('Status: 502 Bad Gateway', true);
				break;
			case 503:
				header('HTTP/1.1 503 Service Unavailable', true);
				header('Status: 503 Service Unavailable', true);
				break;
			case 504:
				header('HTTP/1.1 504 Gateway Timeout', true);
				header('Status: 504 Gateway Timeout', true);
				break;
			default:
				header('HTTP/1.1 200 OK', true);
				header('Status: 200 OK', true);
		}

		// Output the content
		header('Content-type: application/json;charset=UTF-8');
		echo json_encode($output->content);

	}
	
}

class CapsJiraResponse {
	
	public $http_code;
	public $content;
	
	public function __construct($http_code, $content = array()) {
		$this->http_code = $http_code;
		$this->content = $content;
	}
	
}

class CapsJiraException extends Exception {
	
}

CapsJiraApi::dispatch();