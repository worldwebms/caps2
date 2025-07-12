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

$job_id = intval($job_id);

//event->delete log entry-------------when user click the delete log button------------------------------
if ($event == "dellog") {
	$query = "UPDATE job_details SET deleted_on=NOW(), deleted_by='" . $uid . "' WHERE job_details_id=$job_details_id";
	$result = mysql_query ($query, $db);
	updateTotalTime($db, $job_id);
}

//event->update_all-------------------when user click update all button----------------------------------
if (isset($update_all)) {
	//set total time to 0
	$mynewtotal = 0;
	//loop from 1 to i-1 and update
	for ($i=1;$i<$counter;$i++) {
		$str = "job_details_id$i";
		$str1 = "job_date$i";
		$str2 = "start_time$i";
		$str3 = "end_time$i";
		$str4 = "description$i";
		$str5 = "employee$i";
		$str6 = "task$i";
		$str7 = "adjusted$i";
		$str8 = "chargeable$i";
		$str9 = "ext_description$i";
		//make a real variable
		$str10 = $$str;
		$str11 = $$str1;
		$str12 = $$str2;
		$str13 = $$str3;
		$str14 = $$str4;
		$str15 = $$str5;
		$str16 = $$str6;
		$str17 = $$str7;
		$str18 = $$str8;
		$str19 = $$str9;
		//format the job_date field
		$next_contact = $str11;
		//format next contact
		$myjob_date = FromNextContact($next_contact);

		// Convert adjustement to override
		$override = parseHoursToMinutes($str17);
		$no_charge = $str18 == '' ? 1 : null;

		//run an update query for each one
		$now = date('Y-m-d H:i:s');
		$query = "UPDATE job_details SET " .
			"job_task_id='$str16', " .
			"job_date='$myjob_date', " .
			"start_time='$str12', " .
			"end_time='$str13', " .
			"description='" . str_replace("'", "\\'", $str14) . "', " .
			"ext_description='" . str_replace("'", "\\'", $str19) . "', " .
			"employee='$str15', " .
			"override='$override', " .
			"no_charge=" . ($no_charge === null ? 'NULL' : $no_charge) . ", " .
			"last_modified='$now' " .
			"WHERE job_details_id = '$str10' AND job_id = '$job_id'";
		$result = mysql_query ($query, $db);

		//now calculate the time and add to tatal_hours
		$thetime = getTime($str12, $str13);
		//add to total
		$mynewtotal = totalTime($mynewtotal, $thetime);

	//end loop
	}
	
	// move selected items
	$move_id = intval(array_safe($_POST, 'move_job_id', 0));
	if ($move_id > 0) {
		foreach (array_safe($_POST, 'selected', array()) as $id) {
			$id = intval($id);
			if ($id > 0)
				mysql_query('UPDATE job_details SET job_id=' . $move_id . ' WHERE job_details_id=' . $id . ' AND job_id=' . $job_id);
		}
		updateTotalTime($db, $move_id);
	}
	
	//replace the : with . for total_hours
	updateTotalTime($db, $job_id);

	//return to job_control_detail.php page
	header('Location: job_time_log.php?job_id=' . $job_id . '&client_id=' . $client_id);
	exit();
}

//-------------------------------------begin general page queries and formatting-------------------------

// get all tasks
$task_options = array('0' => '');
$no_charge = array();
$query = "SELECT * FROM job_tasks WHERE job_id = '$job_id' OR job_task_id IN ( SELECT DISTINCT job_task_id FROM job_details WHERE job_id = '$job_id' ) ORDER BY job_id DESC, description, employee";
$result = mysql_query($query, $db);
while ($row = mysql_fetch_assoc($result)) {
	$task_options[$row['job_task_id']] = ($row['job_id'] != $job_id ? '- ' : '') . $row['description'] . ($row['job_task_number'] ? (' - Ref # ' . $row['job_task_number']) : '') . ($row['employee'] ? (' [' . $row['employee'] . ']') : '') . ($row['deleted_on'] ? ' [DELETED]' : '');
	if ($row['chargeable'] == 0)
		$no_charge[$row['job_task_id']] = true;
}

//select all details for this job
$query = "SELECT * FROM job_details WHERE job_id = '$job_id' AND deleted_on IS NULL ORDER BY job_date ASC, start_time ASC";
$result = mysql_query ($query, $db);

//select some details for this job
$query1 = "SELECT job_number, job_title FROM jobs WHERE job_id = '$job_id' LIMIT 1";
$result1 = mysql_query ($query1, $db);
//get job number for the job
$row1 = mysql_fetch_object($result1);
$job_number = $row1->job_number;
$job_title = $row1->job_title;

// Summarise all staff members
$staff_names = array();
$staff_options = '<option value=""></option>';
$result2 = mysql_query ('SELECT u, first_name, last_name FROM ps ORDER BY first_name');
while ($row = mysql_fetch_object($result2)) {
	$staff_options .= '<option value="' . $row->u . '">' . $row->first_name . '</option>';
	$staff_names[$row->u] = true;
}
$staff_options .= '<optgroup label="Ex..">';
$result2 = mysql_query ('SELECT DISTINCT employee FROM job_details ORDER BY employee');
while ($row = mysql_fetch_object($result2)) {
  if ($row->employee && !array_key_exists($row->employee, $staff_names))
		$staff_options .= '<option value="' . $row->employee . '">' . $row->employee . '</option>';
}
$staff_options .= '</optgroup>';

//begin general display of information
$query2 = "SELECT client_name, trading_name, agreement_number, website_url, status FROM clients WHERE client_id = '$client_id'";
$result2 = mysql_query ($query2, $db);
$row2 = mysql_fetch_object($result2);
$client_name = $row2->client_name;
$trading_name = $row2->trading_name;
$agreement_number = $row2->agreement_number;
$website_url = $row2->website_url;
$status = $row2->status;

// get list of open jobs
$job_options = array();
$timeperiod = date('Y-m-d', strtotime('-90 days'));
$sql = "SELECT * FROM jobs WHERE client_id = '$client_id' AND (closing_date > '$timeperiod' OR closing_date IS NULL) ORDER BY status ASC, job_id DESC";
$results = mysql_query($sql);
while ($row = mysql_fetch_assoc($results))
	$job_options[$row['job_id']] = $row['job_title'];

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
include_once("includes/client_top.php");
?>
<style type="text/css">
form textarea { width: 99%; }
form .task select { width: 350px; }
</style>
<script type="text/javascript" language="JavaScript" src="includes/time.js"></script>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <?php include_once("includes/admin_links.php"); ?>
  <tr>
    <td>&nbsp;</td>
    <td class="text"><u>Detail: Job Number <?php echo $job_number . ': ' . $job_title; ?></u></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td valign="top" class="text">
      &gt; <a href="job_control_detail.php?client_id=<?php echo $client_id; ?>&job_id=<?php echo $job_id; ?>"><font color="B9E9FF">Back to job details</font></a><br>
      <img src="images/spacer.gif" width="39" height="10"><br>
	  Time Log For This Job:<br>
      <img src="images/spacer.gif" width="39" height="10"><br>
      <form action="job_time_log.php" method="post">
        <table width="100%" border="0" cellspacing="0" cellpadding="2">
          <input name="job_id" type="hidden" value="<?php echo $job_id; ?>">
          <input name="client_id" type="hidden" value="<?php echo $client_id; ?>">
          <tr class="text">
            <td width="1%"></td>
            <td width="1%">Date</td>
            <td width="1%">Start&nbsp;Time</td>
            <td width="1%">Finish&nbsp;Time</td>
            <td width="1%">Employee</td>
            <td width="1%">Task</td>
            <td>Employee Comment</td>
            <td>Adjusted Comment</td>
            <td width="1%">Act.Hrs.&nbsp;&nbsp;</td>
            <td width="1%">Adj.Hrs.</td>
            <td width="1%">Chrg.</td>
            <td width="1%">Actions</td>
          </tr>
          <?php
          
          include_once( "includes/DateField.php" );
          
		//display job history
		$i = 1;
		$total_actual = 0;
		$total_adjusted = 0;
		while ($row=mysql_fetch_object($result)) {
			$job_details_id = $row->job_details_id;
			$next_contact = $row->job_date;
			//format job_date
			$job_date = ToNextContact($next_contact);
			$start_time = date( "H:i", strtotime( $row->start_time ) );
			$end_time = $row->end_time ? date( "H:i", strtotime( $row->end_time ) ) : '';
			$description = $row->description;
			$ext_description = $row->ext_description;

			// Calculate hours
			$actual = $end_time ? ((strtotime($row->end_time) - strtotime($row->start_time)) / 60) : 0;
			$total_actual += $actual;
			$total_adjusted += ($row->override ? $row->override : $actual);
			
			$date_field = new DateField( "job_date" . $i, $job_date );
						
			echo "<tr class=\"text time-log\" valign=\"top\">";
			echo '<td><input type="checkbox" name="selected[]" value="' . $job_details_id . '"></td>';
			echo "<td nowrap>" . "<input name=\"job_details_id$i\" type=\"hidden\" value=\"$job_details_id\">" . $date_field->getHTML() . "</td>";
			echo "<td nowrap><input name=\"start_time$i\" id=\"start_time$i\" type=\"text\" class=\"black\" size=\"4\" value=\"$start_time\" onclick=\"openTimeControl('start_time$i')\"></td>";
			echo "<td nowrap><input name=\"end_time$i\" id=\"end_time$i\" type=\"text\" class=\"black\" size=\"4\" value=\"$end_time\" onclick=\"openTimeControl('end_time$i')\"></td>";
			echo "<td nowrap><select name=\"employee$i\" class=\"black\">";
			if (stristr($staff_options, '"' . $row->employee . '"'))
				echo str_replace('"' . $row->employee . '"', '"' . $row->employee . '" selected', $staff_options);
			else
				echo $staff_options . '<option value="' . $row->employee . '" selected>' . $row->employee . '</option>';
			echo "</select></td>";
			echo "<td nowrap class=\"task\"><select name=\"task$i\" class=\"black\">";
			foreach ($task_options as $id => $text) {
				echo '<option value="' . htmlspecialchars($id) . '"' . ($row->job_task_id == $id ? ' selected' : '') . '>' . htmlspecialchars($text) . '</option>';
			}
			echo "</select></td>";
			echo "<td><textarea name=\"description$i\" type=\"text\" class=\"smallblue\" rows=\"1\">" . htmlspecialchars($description) . "</textarea></td>";
			echo "<td><textarea name=\"ext_description$i\" type=\"text\" class=\"smallblue\" rows=\"1\">" . htmlspecialchars($ext_description) . "</textarea></td>";
			echo '<td nowrap>' . formatMinutes($actual) . '</td>';
			echo '<td nowrap><input name="adjusted' . $i . '" type="text" class="black" size="4" value="' . ($row->override ? formatMinutes($row->override) : '') . '"></td>';
			echo '<td nowrap>';
			if (array_safe($no_charge, $row->job_task_id, false))
				echo '<input name="chargeable' . $i . '" type="hidden" value="' . ($row->no_charge ? '' : 'charge') . '"><input type="checkbox" disabled title="Task is non-chargeable" checked>';
			else
				echo '<input name="chargeable' . $i . '" type="checkbox" value="charge"' . ($row->no_charge ? '' : ' checked') . '>';
			echo '</td>';
			echo "<td nowrap class=\"text\"><a href='job_time_log.php?event=dellog&job_id=$job_id&client_id=$client_id&job_details_id=$job_details_id'><font color='#CCFFFF'>delete</font></a></td>";
			echo "</tr>";
			$i++;
		}
		echo "<input name=\"counter\" type=\"hidden\" value=\"$i\">";
		?>
		  <tr class="text">
		    <td colspan="6">Move selected entries to:
		      <select name="move_job_id" class="black">
		        <option value=""></option>
		        <?php foreach ($job_options as $k => $v) {
		        	echo '<option value="' . $k . '">' . htmlspecialchars($v) . '</option>';
		        } ?>
		      </select>
		    </td>
            <td class="text" style="text-align:right;">Total:&nbsp;</td>
            <td class="text"><?= formatMinutes($total_actual) ?></td>
            <td class="text"><?= formatMinutes($total_adjusted) ?></td>
		  </tr>
          <tr height="40" align="middle">
            <td colspan="6" align="left"><input type='submit' name='update_all' value='Update Time Log' class='smallbluebutton'></td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td valign="top" class="text">
      <p><img src="images/spacer.gif" width="233" height="14"></p>
      </td>
  </tr>
</table>
<? include 'footer.php' ?>
</body>
</html>
