<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* job_time_log template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 8/8/2003
* Client: WorldWeb, Domain: database intranet
* This template consists of 2 section: sql time log query and HTML output with a form directed to itself
* Important files included in this template are: header.inc (head section and javascript library),
* globals.php (connection string),
* Last Modified: 12/1/2004 By Aviv Efrat
* Please remember to backup template before modifying
*
*********************************************************************************************************
*/
//begin session
session_start();
//define title
$title = "WorldWeb Internal Database";
//define date
$today = date("d/m/Y" ,time());
//include a globals file for db connection
include_once("includes/globals.php");
//include functions file
include_once("includes/functions.php");
//establish a persistent connection and get all required data.
// select valuations for  valuer
$db = mysql_pconnect ($hostName, $userName, $password);
mysql_select_db($database);

$jobs = array();
$results = mysql_query(
	"SELECT DISTINCT j.job_id, c.client_id, c.client_name, j.job_number, j.job_title, j.due_date, j.project_manager, p.first_name, p.last_name " .
		"FROM jobs AS j " .
		"INNER JOIN job_details AS jd ON jd.job_id=j.job_id AND jd.employee='' AND jd.deleted_on IS NULL " .
		"INNER JOIN clients AS c ON c.client_id=j.client_id " .
		"LEFT JOIN ps AS p ON p.p_id=c.rep_id " .
		"ORDER BY c.client_name, j.job_number");
while ($row = mysql_fetch_object($results))
	$jobs[] = $row;

//close connection to db
mysql_close($db);
?>

<html>
<head>
<title>CAPS | WorldWeb Management Services</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="includes/caps_styles.css" rel="stylesheet" type="text/css">
<?php
//include javascript library
include_once("includes/worldweb.js");
?>
</head>

<body bgcolor="#006699" leftmargin="0" topmargin="0">
<?php 
include_once("includes/top.php"); 
?>

<p><u>List of jobs with missing employees in time sheets</u></p>

<table cellspacing="0" cellpadding="2" border="0">
	<thead class="text">
		<tr>
			<td>Job&nbsp;#</td>
			<td>Client</td>
			<td>Due&nbsp;Date</td>
			<td>Job Title</td>
			<td>Project Manager</td>
			<td>Account Manager</td>
		</tr>
	</thead>
	<tbody class="text">
<?
foreach ($jobs as $job) {
	echo '<tr>';
	echo '<td><a href="job_time_log.php?job_id=' . $job->job_id . '&amp;client_id=' . $job->client_id . '" style="color:#B9E9FF">' . $job->job_number . '</a></td>';
	echo '<td>' . $job->client_name . '</td>';
	echo '<td>' . ($job->due_date ? date('d-M-Y', strtotime($job->due_date)) : '<br>' ) . '</td>';
	echo '<td>' . $job->job_title . '</td>';
	echo '<td>' . $job->project_manager . '</td>';
	echo '<td>' . $job->first_name . ' ' . $job->last_name . '</td>';
	echo '</tr>' . "\n";
}
?>
	</tbody>
</table>
<? include 'footer.php' ?>
</body>
</html>
