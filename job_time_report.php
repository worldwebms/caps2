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

// Get request details
$date_start = array_safe($_REQUEST, 'date_start', '');
$date_end = array_safe($_REQUEST, 'date_end', '');
$time_start = array_safe($_REQUEST, 'time_start', '');
$time_end = array_safe($_REQUEST, 'time_end', '');
$weekends = array_safe($_REQUEST, 'weekends', '') == 'Yes';
if ($date_start == '') {
	$weekday = date('w');
	if ($weekday == 0)
		$weekday = 7;
	$date_start = date('Y-m-d', $weekday == 1 ? time() : strtotime('-' . ($weekday - 1) . ' days'));
}
if ($date_end == '')
	$date_end = date('Y-m-d', strtotime('+6 days', strtotime($date_start)));
if ($time_start == '')
	$time_start = '00:00';
if ($time_end == '')
	$time_end = '24:00';
$date_start = date('Y-m-d', strtotime($date_start));
$date_end = date('Y-m-d', strtotime($date_end));

// Determine username
$employees = false;
$employee = $uid;
$results = mysql_query("SELECT r FROM ps WHERE u='" . $uid . "'");
$row = mysql_fetch_assoc($results);
if ($row && $row['r'] == 'a') {
	$employee = array_safe($_REQUEST, 'employee', $employee);
	$employees = '';
	$employees_ex = '';
	$results = mysql_query("SELECT DISTINCT jd.employee, ps.first_name FROM job_details AS jd LEFT JOIN ps ON ps.u=jd.employee ORDER BY first_name, employee");
	while ($row = mysql_fetch_assoc($results)) {
		if ($row['first_name'] == '')
			$employees_ex .= '<option value="' . $row['employee'] . '">' . $row['employee'] . '</option>';
		else
			$employees .= '<option value="' . $row['employee'] . '">' . $row['first_name'] . '</option>';
	}
}

?>

<html>
<head>
<title>CAPS | WorldWeb Management Services</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="includes/caps_styles.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="includes/time.js"></script>
<?php
//include javascript library
include_once("includes/worldweb.js");
?>
</head>

<body bgcolor="#006699" leftmargin="0" topmargin="0">
<?php
include_once("includes/top.php");
include_once("includes/DateField.php");
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">

	<tr><td width="1%" height="27"><br></td><td class="clienttitle">Time Sheets</td></tr>
	<tr><td colspan="2" background="images/horizontal_line.gif"><br></td></tr>
	<tr><td><br></td><td>

		<p class="subheading">Search Criteria</p>
	
		<form method="get" action="job_time_report.php">
			<table cellspacing="0" cellpadding="2" border="0"><tbody>
				<tr class="text" valign="top">
				<?php if ($employees) { ?>
					<td>Employee:</td>
					<td>
						<select name="employee" class="black">
							<?= str_replace('"' . $employee . '"', '"' . $employee . '" selected', $employees ) ?>
							<optgroup label="Ex..."><?= str_replace('"' . $employee . '"', '"' . $employee . '" selected', $employees_ex) ?></optgroup>
						</select>
					</td>
					<td>&nbsp; &nbsp;</td>
				<?php } ?>
					<td>Date Range:</td>
					<td>
						<? $field = new DateField('date_start', $date_start); echo $field->getHTML(); ?> to
						<? $field = new DateField('date_end', $date_end); echo $field->getHTML(); ?><br>
						<label><input type="checkbox" name="weekends" value="Yes"<?= $weekends ? " checked" : "" ?>> weekends only</label>
					</td>
					<td>&nbsp; &nbsp;</td>
					<td>Time Range:</td>
					<td>
						<input type="text" id="time_start" name="time_start" value="<?= $time_start ?>" size="4" class="smallblue" onclick="openTimeControl('time_start')"> to
						<input type="text" id="time_end" name="time_end" value="<?= $time_end ?>" size="4" class="smallblue" onclick="openTimeControl('time_end')">
					</td>
					<td>&nbsp; &nbsp;</td>
					<td><input type="submit" value="Search" class="smallbluebutton"></td>
				</tr>
			</tbody></table>
		</form>
		<br>
		
		<p class="subheading">Time Sheet</p>
		
		<table class="admin_list text" border="0" cellspacing="1" cellpadding="4">
			<thead>
				<tr>
					<td>Date</td>
					<td>Start</td>
					<td>End</td>
					<td>Time</td>
					<td>&nbsp;&nbsp;</td>
					<td>Job&nbsp;#</td>
					<td>Client&nbsp;Name</td>
					<td>Job&nbsp;Title</td>
					<td>Task&nbsp;#</td>
					<td>Task&nbsp;Title</td>
					<td>Comments</td>
				</tr>
			</thead>
			<tbody>
<?
		
	// Display the results
	$sql =
		"SELECT jd.job_date, jd.start_time, jd.end_time, jd.description, c.client_id, c.client_name, j.job_id, j.job_title, j.job_number, jt.job_task_id, jt.description AS job_task_description FROM job_details AS jd " .
			"LEFT JOIN job_tasks AS jt ON jt.job_task_id=jd.job_task_id " .
			"INNER JOIN jobs AS j ON j.job_id=jd.job_id " .
				"AND jd.employee='" . $employee . "' " .
				"AND job_date>='" . mysql_real_escape_string($date_start) . "' " .
				"AND job_date<='" . mysql_real_escape_string($date_end) . "' " .
				"AND jd.deleted_on IS NULL " .
				($weekends ? "AND DAYOFWEEK(job_date) IN ( 1, 7 ) " : "") .
				"AND ( " .
					"( start_time>='" . mysql_real_escape_string($time_start) . "' AND ( end_time IS NULL OR end_time<='" . mysql_real_escape_string($time_end) . "' ) ) " .
					"OR ( start_time<='" . mysql_real_escape_string($time_start) . "' AND ( end_time IS NULL OR end_time>='" . mysql_real_escape_string($time_end) . "' ) ) " .
					"OR ( start_time<='" . mysql_real_escape_string($time_end) . "' AND ( end_time IS NULL OR end_time>='" . mysql_real_escape_string($time_end) . "' ) ) " .
				") " .
			"INNER JOIN clients AS c ON c.client_id=j.client_id " .
			"ORDER BY job_date DESC, start_time DESC";

	$odd = true;
	$date = false;
	$sub_total_time = 0;
	$total_time = 0;
	$results = mysql_query($sql);
	echo mysql_error();
	while ($row = mysql_fetch_object($results)) {

		if ($date != $row->job_date) {
			if ($sub_total_time > 0) {
				echo '<tr class="odd"><td colspan="3" align="right">Subtotal:</td><td align="right">';
				echo floor( $sub_total_time / 60 ) . ':' . substr( '00' . ( $sub_total_time % 60 ), -2 );
				echo "</td><td colspan=\"7\"><br></td></tr>\n";
			}
			$date = $row->job_date;
			$sub_total_time = 0;
		}

		$start_time = date('H:i', strtotime($row->start_time));
		if ($row->end_time == null) {
			$end_time = '---';
			$time = false;
		} else {
			$end_time = date('H:i', strtotime($row->end_time));
			$time = explode(':', getTime($start_time, $end_time));
	
			$sub_total_time += ( $time[0] * 60 ) + $time[1];
			$total_time += ( $time[0] * 60 ) + $time[1];
		}
	
		echo '<tr class="' . ($odd ? 'odd' : 'even') . '">';
		echo '<td>' . date('d-M-Y', strtotime($row->job_date)) . '</td>';
		echo '<td>' . $start_time . '</td>';
		echo '<td>' . $end_time . '</td>';
		echo '<td align="right">' . ($time ? ($time[0] . ':' . $time[1]) : $time) . '</td>';
		echo '<td><br></td>';
		echo '<td><a href="job_control_detail.php?job_id=' . $row->job_id . '" style="color:#b9e9ff">' . $row->job_number . '</a></td>';
		echo '<td>' . $row->client_name . '</td>';
		echo '<td>' . $row->job_title . '</td>';
		echo '<td>' . ($row->job_task_id ? ('<a href="job_task_detail.php?job_task_id=' . $row->job_task_id . '" style="color:#b9e9ff">' . $row->job_task_id . '</a>') : '') . '</td>';
		echo '<td>' . htmlspecialchars($row->job_task_description) . '</td>';
		echo '<td>' . htmlspecialchars($row->description) . '</td>';
		echo "</tr>\n";

		$odd = !$odd;
	}

	if ($sub_total_time > 0) {
		echo '<tr class="odd"><td colspan="3" align="right">Subtotal:</td><td align="right">';
		echo floor( $sub_total_time / 60 ) . ':' . substr( '00' . ( $sub_total_time % 60 ), -2 );
		echo "</td><td colspan=\"7\"><br></td></tr>\n";
	}
	
	$total_time = floor( $total_time / 60 ) . ':' . substr( '00' . ( $total_time % 60 ), -2 );

?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="3" align="right">TOTAL:</td>
					<td align="right"><?= $total_time ?></td>
				</tr>
			</tfoot>
		</table>

	</td></tr>

</table>

<?php
	include 'footer.php';
	mysql_close($db);
?>
</body>
</html>
