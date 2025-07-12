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
<script type="text/javascript" src="includes/jquery.js"></script>
<script type="text/javascript">

	var Tasks = {
		close: function(el){
				if(confirm('Are you sure you want to close this task')){
				var row = $(el).parents('.task');
				var task = {
					'update_task': 'true',
					'task_id': row.find( 'input[name="task_id"]' ).val(),
					'closed': row.find( 'input[name="closed"]:checked' ).val() == 'Yes' ? 'Yes' : 'No'
				};
				row[ task.closed == 'Yes' ? 'addClass' : 'removeClass' ]( 'closed' );
				jQuery.post(
					'job_control_detail.php',
					task
				);
				return true;


			}
		}
	};


</script>
<style>
	.admin_list .closed td{
		color:#999999;
	}

</style>


</head>

<body bgcolor="#006699" leftmargin="0" topmargin="0">
<?php 
include_once("includes/top.php"); 
include_once("includes/DateField.php");
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">

	<tr><td width="1%" height="27"><br></td><td class="clienttitle">All Completed Tasks</td></tr>
	<tr><td colspan="2" background="images/horizontal_line.gif"><br></td></tr>
	<tr><td><br></td><td>

		<p class="subheading">Search Criteria</p>
		<form method="get" action="job_task_complete.php">
			<table cellspacing="0" cellpadding="2" border="0"><tbody><tr class="text">
				<td>Employee:</td><td><select name="employee" class="black">
					<option value="">- all -</option>
<?php
	// Get request details
	$date_start = array_safe($_REQUEST, 'date_start', '');
	$date_end = array_safe($_REQUEST, 'date_end', '');
	if ($date_start == '') {

		$date_start = date('Y-m-d', time());
	}
	if ($date_end == '')
		$date_end = date('Y-m-d', strtotime('+1 days', strtotime($date_start)));

	$date_start = date('Y-m-d', strtotime($date_start));
	$date_end = date('Y-m-d', strtotime($date_end));


	$employee = array_safe($_REQUEST, 'employee', '');
	$results = mysql_query('SELECT u FROM ps ORDER BY u');
	while ($row = mysql_fetch_object($results))
		echo '<option value="' . $row->u . '"' . ($row->u == $employee ? ' selected' : '') . '>' . $row->u . '</option>';
?>
				</select></td>
				<td> </td>
				<td>Date Range:</td>
				<td>
					<? $field = new DateField('date_start', $date_start); echo $field->getHTML(); ?> to
					<? $field = new DateField('date_end', $date_end); echo $field->getHTML(); ?>
				</td>
<?php
	$order_by = array_safe($_REQUEST, 'order', '');
?>
				
				<td> </td>
				<td><input class="smallbluebutton" type="submit" value="Search"></td>
			</tr></tbody></table>
<?php

?>
		</form>

		<p class="subheading">Open Tasks</p>

		<table class="admin_list text" border="0" cellspacing="1" cellpadding="2">
			<thead>
				<tr>
					<td>Closed</td>
					<td>Completed&nbsp;Date</td>
					<td width="65">Job Number</td>
					<td>Job Title &amp; Tasks</td>
					<td>Client</td>
					<td>Assigned&nbsp;To</td>
					<td>Due&nbsp;Date</td>
					
				</tr>
			</thead>
			<tbody>
<?php

	$job_number = '';
	$sql = 
		"SELECT jt.job_task_id, jt.job_task_number, jt.description, jt.employee, jt.due_date, jt.completed, jt.closed, jt.completed, j.job_id, j.job_number, j.job_title, c.client_id, c.client_name " .
			"FROM job_tasks AS jt " .
			"INNER JOIN jobs AS j ON j.job_id=jt.job_id AND jt.completed IS NOT NULL " . ($employee ? (' AND jt.employee="' . $employee . '" ') : '') .
			"INNER JOIN clients AS c ON c.client_id=j.client_id " .
			"WHERE jt.completed>='" . mysql_real_escape_string($date_start) . "' " .
			"AND jt.completed<='" . mysql_real_escape_string($date_end) . "' " .
			"AND jt.deleted_on IS NULL " .
			"ORDER BY jt.completed DESC, c.client_name, j.job_number, jt.description";
	$results = mysql_query($sql);
	while ($row = mysql_fetch_object($results)) {
		if ($job_number != $row->job_number) {
?>
				<tr class="odd heading">
					<td><br></td>
					<td><br></td>
					<td><a href="job_control_detail.php?job_id=<?= $row->job_id ?>" style="color:#ffffff"><?= $row->job_number ?></a></td>
					<td><a href="job_control_detail.php?job_id=<?= $row->job_id ?>" style="color:#ffffff"><?= htmlspecialchars($row->job_title) ?></a></td>
					<td><?= htmlspecialchars($row->client_name) ?></td>
					<td><br></td>
					<td><br></td>
					
				</tr>
<?php
		}
?>
				<tr class="odd task <?= $row->closed ? 'closed':'' ?>">
					<td>
						<input type="hidden" name="task_id" value="<?= $row->job_task_id ?>">
						<input type="checkbox" name="closed" value="Yes" onchange="Tasks.close(this)"<?= $row->closed != null ? ' checked' : '' ?>>

					</td>
					<td><?= $row->completed ? date('d-M-Y', strtotime($row->completed)) : '' ?></td>
					<td><?= $row->job_task_number ? ('&nbsp;&nbsp;&gt;&nbsp;' . htmlspecialchars($row->job_task_number)) : '<br>' ?></td>
					<td colspan="2">&gt; <?= htmlspecialchars($row->description) ?></td>
					<td><?= $row->employee ? $row->employee : '<em style="color:#999999">n/a</em>' ?></td>
					<td><?= $row->due_date ? date('d-M-Y', strtotime($row->due_date)) : '' ?></td>
					
				</tr>
<?php
		$job_number = $row->job_number;
	}
	
?>
			</tbody>
		</table>
	
	</td></tr>

</table>

<?php
	include 'footer.php';
	mysql_close($db);
?>
</body>
</html>
