<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* welcome template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 12/5/2003
* Client: WorldWeb, Domain: database intranet
* This template consists of 1 section: HTML form directed to index1.php
* Important files included in this template are: header.inc (head section and javascript library),
* globals.php (connection string),
* Last Modified: 12/1/2004 By Aviv Efrat
* Please remember to backup template before modifying
*
*********************************************************************************************************
*/
//begin session
session_start();
//include a globals file for db connection
include_once("includes/globals.php");
//define date
$today = date("d/m/Y" ,time());
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
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
<?php include_once("includes/top.php"); ?>
<div style="padding-left:8px">
	<p class="clienttitle"><img src="images/welcome.gif" width="206" height="33"></p>

<?php

	$db = mysql_connect( $hostName, $userName, $password);
	mysql_select_db($database);

	$week_end = date('Y-m-d', date('w') == 0 ? time() : strtotime('+' . (7 - date('w')) . ' days'));

	$fields = array();
	$fields['start'] = isset($_GET['date']) ? strtotime($_GET['date']) : strtotime('-7 days', strtotime($week_end));
	$fields['end'] = strtotime('+7 days -1 minute', $fields['start']);
	$fields['staff'] = $pid;
	$fields['extended'] = '1';

	$url = 'http://192.168.0.17/api/json/custom.worldweb.calendar/calendar/get_schedules';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, count($fields));
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$response = json_decode(curl_exec($ch));

?>

	<p class="subheading">Calendar</p>
	<table class="admin_list text" border="0" cellspacing="1" cellpadding="2" width="850">
		<thead>
			<tr>
				<td width="65">Date</td>
				<td width="65" align="center">Time</td>
				<td width="65">Job Number</td>
				<td>Job Title &nbsp; Tasks</td>
				<td>Client</td>
				<td width="65" align="center">Status</td>
			</tr>
		</thead>
		<tbody>
<?php

	$prev_date = '';
	foreach ($response as $item) {
		$start = strtotime($item->start);
		$date = date('D, j M', $start);

		$results = mysql_query('SELECT job_id, job_number, job_title, client_id FROM jobs WHERE job_id=' . intval($item->job));
		$job = mysql_fetch_assoc($results);
		$results = mysql_query('SELECT job_task_id, job_task_number, description, completed, status FROM job_tasks WHERE job_task_id=' . intval($item->task));
		$task = mysql_fetch_assoc($results);
		$results = mysql_query('SELECT client_name FROM clients WHERE client_id=' . intval($job ? $job['client_id'] : 0));
		$client = mysql_fetch_assoc($results);

		$job_title = $job ? $job['job_title'] : $item->title;
		$is_today = date('Y-m-d', $start) == date('Y-m-d');
		$is_completed = ($task ? $task['completed'] : null) != null;
		$color = $is_completed ? '#999' : (date('Y-m-d', $start) == date('Y-m-d') ? '#fff' : (date('Y-m-d', $start) < date('Y-m-d') ? '#ccc' : '#fff'));


?>
			<tr class="odd" style="color:<?= $color ?>">
				<td><?= $date == $prev_date ? '' : $date ?></td>
				<td align="center"><?= date('h:i A', $start) ?></td>
				<td><?= $job ? ('<a href="job_control_detail.php?job_id=' . $job['job_id'] . '" style="color:' . $color . '">' . htmlspecialchars($job['job_number']) . '</a>') : '' ?></td>
				<td><?= $job ? ('<a href="job_control_detail.php?job_id=' . $job['job_id'] . '" style="color:' . $color . '">' . htmlspecialchars($job['job_title']) . '</a>') : htmlspecialchars($item->title) ?></td>
				<td><?= $client ? htmlspecialchars($client['client_name']) : '' ?></td>
				<td><br></td>
			</tr>
<?php

		if ($task) {

?>
			<tr class="odd" style="color:<?= $color ?>">
				<td><br></td>
				<td><br></td>
				<td><?= $task['job_task_number'] ? ('<a href="job_task_detail.php?job_task_id=' . $task['job_task_id'] . '" style="color:' . $color . '">&nbsp;&nbsp;&nbsp;' . htmlspecialchars($task['job_task_number']) . '</a>') : '' ?></td>
				<td colspan="2"<?= $is_completed ? 'style="text-decoration: line-through;"' : '' ?>><a href="job_task_detail.php?job_task_id=<?= $task['job_task_id'] ?>" style="color:<?= $color ?>">&gt; <?= htmlspecialchars($task['description'] . ($task['status'] ? (' (' . $task['status'] . ')') : '')) ?></a></td>
				<td align="center" class="<?= $is_completed ? 'completed' : strtolower($task['status']) ?>"><?= $is_completed ? 'Completed' : $task['status'] ?></td>
			</tr>
<?php

		}

		$prev_date = $date;
	
	}

?>
			<tr>
        <td colspan="3"><a href="?date=<?= date('Y-m-d', strtotime('-1 week', $fields['start'])) ?>" style="color:#ffffff;">&lt; previous</a></td>
				<td colspan="3" style="text-align:right;"><a href="?date=<?= date('Y-m-d', strtotime('+1 week', $fields['start'])) ?>" style="color:#ffffff;">next &gt;</a></td>
			</tr>
		</tbody>
	</table>
	<br>

<?php

	if ($week_end < date('Y-m-d', strtotime('+5 days')))
		$week_end = date('Y-m-d', strtotime('+5 days'));

	$results = mysql_query(
		"SELECT jt.job_task_id, jt.job_task_number, jt.description, jt.due_date, jt.status, j.job_id, j.job_number, j.job_title, c.client_id, c.client_name " .
			"FROM job_tasks AS jt " .
			"INNER JOIN jobs AS j ON j.job_id=jt.job_id AND jt.employee='" . $uid . "' AND jt.completed IS NULL AND jt.deleted_on IS NULL AND jt.due_date IS NOT NULL AND jt.due_date<='" . $week_end . "' " .
			"INNER JOIN clients AS c ON c.client_id=j.client_id " .
			"ORDER BY jt.due_date, c.client_name, j.job_number, jt.description"
	);
	if (mysql_num_rows($results) > 0) {

?>
	<p class="subheading">Tasks Due Soon</p>
	<table class="admin_list text" border="0" cellspacing="1" cellpadding="2" width="850">
		<thead>
			<tr>
				<td width="65">Job Number</td>
				<td>Job Title &nbsp; Tasks</td>
				<td>Client</td>
				<td width="65" align="center">Phase</td>
				<td width="65" align="center">Due&nbsp;Date</td>
				<td width="50" align="center"></td>
			</tr>
		</thead>
		<tbody>
<?php
	$job_number = '';
	while ($row = mysql_fetch_object($results)) {
		if ($job_number != $row->job_number) {
			$job_number = $row->job_number;
?>
			<tr class="odd heading">
				<td><a href="job_control_detail.php?job_id=<?= $row->job_id ?>&amp;client_id=<?= $row->client_id ?>" style="color:#ffffff"><?= $row->job_number ?></a></td>
				<td><a href="job_control_detail.php?job_id=<?= $row->job_id ?>&amp;client_id=<?= $row->client_id ?>" style="color:#ffffff"><?= htmlspecialchars($row->job_title) ?></a></td>
				<td><?= htmlspecialchars($row->client_name) ?></td>
				<td><br></td>
				<td><br></td>
				<td><br></td>
			</tr>
<?php
		}
?>
			<tr class="odd">
				<td><?= $row->job_task_number ? ('&nbsp;&nbsp;&nbsp;' . htmlspecialchars($row->job_task_number)) : '' ?></td>
				<td colspan="2"><a href="job_task_detail.php?job_task_id=<?= $row->job_task_id ?>" style="color:#ffffff">&gt <?= htmlspecialchars($row->description) ?></a></td>
				<td align="center" class="<?= strtolower($row->status) ?>"><?= htmlspecialchars($row->status) ?></td>
				<td align="center"><?= $row->due_date ? date('d-M-Y', strtotime($row->due_date)) : '' ?></td>
				<td align="center"><a href="job_control_detail.php?job_id=<?= $row->job_id ?>&amp;start_task=<?= $row->job_task_id ?>" style="color:#ffffff">[start]</a></td>
			</tr>
<?php
	}
?>
		</tbody>
	</table>
	<div style="height:2em;"></div>

<?php
	}

?>
	
	<p class="subheading">My Pending Tasks</p>
	<table class="admin_list text" border="0" cellspacing="1" cellpadding="2" width="850">
		<thead>
			<tr>
				<td width="65">Job Number</td>
				<td>Job Title &amp; Tasks</td>
				<td>Client</td>
				<td width="65" align="center">Phase</td>
				<td width="65" align="center">Due&nbsp;Date</td>
				<td width="50" align="center"></td>
			</tr>
		</thead>
		<tbody>
<?php

	$job_number = '';
	$results = mysql_query(
		"SELECT jt.job_task_id, jt.description, jt.due_date, jt.status, j.job_id, j.job_number, j.job_title, c.client_id, c.client_name " .
			"FROM job_tasks AS jt " .
			"INNER JOIN jobs AS j ON j.job_id=jt.job_id AND jt.employee='" . $uid . "' AND jt.completed IS NULL AND jt.deleted_on IS NULL " .
			"INNER JOIN clients AS c ON c.client_id=j.client_id " .
			"ORDER BY c.client_name, j.job_number, jt.description");
	while ($row = mysql_fetch_object($results)) {
		if ($job_number != $row->job_number) {
?>
			<tr class="odd heading">
				<td><a href="job_control_detail.php?job_id=<?= $row->job_id ?>&amp;client_id=<?= $row->client_id ?>" style="color:#ffffff"><?= $row->job_number ?></a></td>
				<td><a href="job_control_detail.php?job_id=<?= $row->job_id ?>&amp;client_id=<?= $row->client_id ?>" style="color:#ffffff"><?= htmlspecialchars($row->job_title) ?></a></td>
				<td><?= htmlspecialchars($row->client_name) ?></td>
				<td><br></td>
				<td><br></td>
				<td><br></td>
			</tr>
<?php
		}
?>
			<tr class="odd">
				<td><br></td>
				<td colspan="2"><a href="job_task_detail.php?job_task_id=<?= $row->job_task_id ?>" style="color:#ffffff">&gt; <?= htmlspecialchars($row->description) ?></a></td>
				<td align="center" class="<?= strtolower($row->status) ?>"><?= htmlspecialchars($row->status) ?></td>
				<td align="center"><?= $row->due_date ? date('d-M-Y', strtotime($row->due_date)) : '' ?></td>
				<td align="center"><a href="job_control_detail.php?job_id=<?= $row->job_id ?>&amp;start_task=<?= $row->job_task_id ?>" style="color:#ffffff">[start]</a></td>
			</tr>
<?php
		$job_number = $row->job_number;
	}
	mysql_close($db);

?>
		</tbody>
	</table>
	
	<p class="subheading">My Open Jobs</p>
<?php

	$days = "NULL";
	include( "includes/my_jobs.inc" );

	if( $r == "a" ) {
		
?>
	<p class="subheading">Contacts This Week</p>
<?php
		$weekday = date( "w" );
		$day_start = date( "Y-m-d", ( $weekday == 1 ? time() : strtotime( ( $weekday == 0 ? "-6" : ( "-" . ( $weekday - 1 ) ) ) . " days" ) ) );
		$day_end = date( "Y-m-d", strtotime( "+6 days", strtotime( $day_start ) ) );
		include( "includes/contact_summary.inc" );

	}
	
?>
</div>
</body>
</html>
