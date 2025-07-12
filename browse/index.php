<?php

session_start();
include_once("../includes/globals.php");

$db = mysql_connect( $hostName, $userName, $password);
mysql_select_db($database);

if (preg_match('|browse/(\d+)|', $_SERVER['REQUEST_URI'], $matches)) {
	$results = mysql_query('SELECT job_task_id FROM job_tasks WHERE job_task_id=' . intval($matches[1]));
	$row = mysql_fetch_assoc($results);
} else {
	$row = false;
}
if ($row) {
	header('Location: ../job_task_detail.php?job_task_id=' . $row['job_task_id']);
} else {
	echo 'Unknown task';
}
