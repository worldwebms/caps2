<?php

	require_once(dirname(__FILE__) . '/includes/globals.php');

	$db = mysql_connect($hostName, $userName, $password);
	mysql_select_db($database);

	// Get date range
	$date_start = strtotime('-7 days');
	$date_end = strtotime('-1 days');
	$days = array('Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa');
	$dates = array();
	for ($date = $date_start; $date <= $date_end; $date = strtotime('+1 day', $date))
		$dates[date('Y-m-d', $date)] = $days[date('w', $date)];

	// Load all records
	$sql =
		"SELECT employee, job_date, start_time, end_time " .
			"FROM job_details " .
			"WHERE job_date>='" . mysql_real_escape_string(date('Y-m-d', $date_start)) . "' " .
				"AND job_date<='" . mysql_real_escape_string(date('Y-m-d', $date_end)) . "' " .
				"AND end_time IS NOT NULL " .
				"AND deleted_on IS NULL " .
			"ORDER BY employee, job_date";
	$summary = array();
	$results = mysql_query($sql);
	while ($row = mysql_fetch_assoc($results)) {

		// Add employee
		$employee = $row['employee'];
		if (!array_key_exists($employee, $summary)) {
			$summary[$employee] = array();
			foreach ($dates as $date => $val)
				$summary[$employee][$date] = 0;
		}

		// Summarise the time
		$diff = (strtotime($row['end_time']) - strtotime($row['start_time'])) / (60 * 60);
		$summary[$employee][$row['job_date']] += $diff;

	}

	// Output the content
	ob_start();

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Timesheet summary</title>
	<style type="text/css">
	table {
		border-top: 1px solid #cccccc;
	}
	td, th {
		font-size: 14px;
		padding: 3px 8px;
		text-align: right;
	}
	th {
		background-color: #e8e8e8;
	}
	td, th {
		border-bottom: 1px solid #cccccc;
	}
	td.left {
		text-align: left;
	}
	tr.odd td {
		background-color: #f8f8f8;
	}
	</style>
</head>
<body>

	<table cellspacing="0" cellpadding="0" border="0">
		<thead>
			<tr valign="bottom">
				<th rowspan="2"><br></th>
				<th rowspan="2">TOTAL</th>
<?php
	foreach ($dates as $date => $day)
		echo '<th>' . date('j/n', strtotime($date)) . '</th>';
?>
			</tr>
			<tr>
<?php
	foreach ($dates as $date => $day)
		echo '<th>' . $day . '</th>';
?>
			</tr>
		</thead>
		<tbody>
<?php

	$class = 'odd';
	foreach ($summary as $employee => $days) {

		// Start row
		echo '<tr class="' . $class . '">';
		echo '<td class="left">' . $employee . '</td>';

		// Calculate total
		$total = 0;
		$line = '';
		foreach ($days as $summary) {
			$line .= '<td>' . number_format($summary, 1) . '</td>';
			$total += $summary;
		}

		// End row
		echo '<td><strong>' . ceil($total) . '</strong></td>';
		echo $line;
		echo '</tr>' . "\n";

		// Change class
		$class = $class == 'odd' ? 'even' : 'odd';

	}

?>
		</tbody>
	</table>

</body>
</html>
<?php

	// Get the contents
	$content = ob_get_clean();

	// Send out the email
	$headers = 'MIME-Version: 1.0' . "\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
	
	$emails = array('chrisw@worldwebms.com','Rebecca.Tulloch@worldwebms.com', 'Rohan.Hastwell@bdo.com.au');
	foreach ($emails as $email) {
		mail($email, 'Timesheet Summary: ' . date('d-M-Y'), $content, $headers);
	}

