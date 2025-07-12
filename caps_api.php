<?php
session_start();

require_once( dirname(__FILE__) . '/includes/config.php' );
require_once( dirname(__FILE__) . '/includes/functions.php' );

$db = mysql_connect($hostName, $userName, $password);
mysql_select_db("caps");

$action = $_GET['action'];

switch ($action)
{
	case 'fetch_users':
		$query = "SELECT CONCAT(`ps`.`first_name`, ' ', `ps`.`last_name`) AS `name`, `ps`.`p_id` as `id` FROM `ps` WHERE `assign_job` = 'Yes' ORDER BY `ps`.`first_name` ASC";
		break;
	case 'search_clients':
		$term = $_GET['term'];
		$query = "SELECT IF(trading_name != '', CONCAT(`clients`.`client_name`, ' aka ', `clients`.`trading_name`), client_name) AS `label`, `clients`.`client_name` AS `value`, `clients`.`client_id` AS `id` FROM `clients` WHERE `status`!='d' AND (`client_name` LIKE '%$term%' OR trading_name LIKE '%$term%')";
		break;
	case 'fetch_jobs':
		$client_id = $_GET['client_id'];
		$query = "SELECT CONCAT(jobs.job_number, ' - ', `jobs`.`job_title`) AS `label`, CONCAT('job_', `jobs`.`job_id`) AS `id` FROM `jobs` WHERE `client_id` = $client_id AND status='open' ORDER BY job_number DESC";
		break;
	case 'fetch_tasks':
		$job_id = $_GET['job_id'];
		$query = "SELECT CONCAT(IF(completed IS NULL, '', 'COMPLETED - '), IF(job_task_number != '', CONCAT(job_task_number, ' - '), ''), `job_tasks`.`description`, IF(employee != '', CONCAT(' (', employee, ')'), '')) AS `label`, CONCAT('task_', `job_tasks`.`job_task_id`) AS `id` FROM `job_tasks` WHERE `job_tasks`.`job_id` = $job_id AND `job_tasks`.deleted_on IS NULL ORDER BY job_task_number, IF(completed IS NULL, 0, 1), description";
		break;
	case 'task_job':
		$task_id = $_GET['task_id'];
		$query = "SELECT
			`jobs`.`job_title` AS `label`,
			`jobs`.`job_id` AS `id`,
			`job_tasks`.`description` AS `description`,
			`job_tasks`.`employee` AS `employee`,
			`job_tasks`.`completed` AS `completed`,
			`clients`.`client_name` AS `client_name`
			FROM `jobs`
			JOIN `job_tasks`
				ON `jobs`.`job_id` = `job_tasks`.`job_id`
				AND `job_tasks`.`job_task_id` = $task_id
			JOIN `clients`
				ON `jobs`.`client_id` = `clients`.`client_id`";
		break;
	case 'get_staff':
		$staff_id = $_GET['staff'];
		$query = "SELECT CONCAT(`ps`.`first_name`, ' ', `ps`.`last_name`) AS `name`, `ps`.`p_id` AS `id` FROM `ps` WHERE `p_id` = $staff_id";
		break;
}

$results = mysql_query($query);

$result_list = array();
while ($row = mysql_fetch_object($results))
{
	$result = array();
	
	foreach($row as $key=>$value)
	{
		$result[$key] = $value;
	}

	$result_list[] = $result;
}

echo json_encode($result_list);
