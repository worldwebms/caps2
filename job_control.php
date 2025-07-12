<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* job_control template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 20/6/2003
* Client: WorldWeb, Domain: database intranet
* This template consists of 3 sections: Navigation, SQL Queries & formatting, HTML output
* Important files included in this template are: header.inc (head section and javascript library),
* globals.php (connection string),
* Last Modified: 21/9/2005 By Aviv Efrat
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

//define empty array to hold staff list
$staff_list = array();
//define variable to hold drop down for staff
$mystaff = "";
//select all existing staff and add to staff list array
$query = "SELECT first_name, last_name, u FROM ps WHERE assign_job='Yes' ORDER BY first_name";
$result = mysql_query ($query, $db);
while ($row=mysql_fetch_object($result)) {
	$fname = $row->first_name;
	$lname = $row->last_name;
	$staffname = $fname . " " . $lname;
	$staff = $row->u;
	$mystaff .= "<option>$staffname</option>\n";
	//add entry to array
	array_push ($staff_list, $staff);
}
//find out how many staff memebers there are
$staff_count = count($staff_list);

//event->add_job----------------when user clicks enter new job button-------------------------------------
if (isset($_POST['add_job'])) {
	//make sure agreement number (client number) exists
	//if not don't allow job insertion
	if ($agreement_number == 0) {
		$alertmeonce = 1;
		echo "<script>alert('Client number is missing. Please update client record before entering new jobs')</script>";
	} else {
		//validate the due_date and est_completion
		//set myRCdateerror to nothing
		$myRCdateerror = "";
		//format next contact
		$mydue_date = FromNextContact($myRC);
		//check if the date is valid
		list($Y, $M, $D) = split('-',$mydue_date);
		if (!is_numeric($Y)) {$myRCdateerror = $myRCdateerror . "year is incorrect $Y";}
		if ($Y < 2003 | $Y > 2020) {$myRCdateerror = $myRCdateerror . "year is incorrect $Y";}
		if (!is_numeric($M)) {$myRCdateerror = $myRCdateerror . "Month is incorrect a $M";}
		if (!is_numeric($D)) {$myRCdateerror = $myRCdateerror . "Date is incorrect $D";}
		if ($D < 1 | $D > 31) {$myRCdateerror = $myRCdateerror . "Date is incorrect $D";}
		//if errors are returned display alert
		if (!empty($myRCdateerror)) {
			echo "<script language=\"JavaScript\">alert('The Request Completion date you entered is not valid');</script>";
		}

		//set myRCdateerror to nothing
		$myECdateerror = "";
		//format next contact
		$myest_completion = FromNextContact($myEC);
		//check if the date is valid
		list($Y,$M, $D) = split('-',$myest_completion);
		if (!is_numeric($Y)) {$myECdateerror = $myECdateerror . "year is incorrect $Y";}
		if ($Y < 2003 | $Y > 2020) {$myECdateerror = $myECdateerror . "year is incorrect $Y";}
		if (!is_numeric($M)) {$myECdateerror = $myECdateerror . "Month is incorrect a $M";}
		if (!is_numeric($D)) {$myECdateerror = $myECdateerror . "Date is incorrect $D";}
		if ($D < 1 | $D > 31) {$myECdateerror = $myECdateerror . "Date is incorrect $D";}
		//if errors are returned display alert
		if (!empty($myECdateerror)) {
			echo "<script language=\"JavaScript\">alert('The Estimated Completion date you entered is not valid');</script>";
		}

		//format billing_hours correctly
		if ($billing_method == "hours") {
			$mybilling_hours = $billing_hours1;
		} elseif ($billing_method == "no charge") {
			$mybilling_hours = $billing_hours2;
		} else {$mybilling_hours = 0;}

		//check the max job_number and increment by 1
		$query = "SELECT MAX(job_id) AS job_id FROM jobs WHERE client_id = '$client_id'";
		$result = mysql_query ($query, $db);
		$row = mysql_fetch_object($result);
		$old_job_id = $row->job_id;
		$query1 = "SELECT job_number FROM jobs WHERE job_id = '$old_job_id'";
		$result1 = mysql_query ($query1, $db);
		$row1 = mysql_fetch_object($result1);
		$old_job_num = $row1->job_number;
		//if doesnt' exist create one or increment by 1
		if (empty($old_job_num)) {
			$job_number = $agreement_number . "-1";
		} else {
			//split the old_job_num to agreement number and num
			list($an, $sn) = split('-',$old_job_num);
			$sn++;
			$job_number = $an . "-" . $sn;
		}

		//generate query and add job to db
		$new_job_id;
		$query2 = "INSERT INTO jobs (job_id, client_id, job_number, job_title, job_details, project_manager, order_date, due_date, est_completion, billing_method, billing_hours, p_contact, s_contact, o_contact, status, created_on, created_by) ";
		$query2 .= "VALUES ('$new_job_id', '$client_id', '$job_number', '".addslashes($_POST['job_title'])."', '".addslashes($_POST['job_details'])."', '".addslashes($project_manager)."', NOW(), '$mydue_date', '$myest_completion', '$billing_method', '$billing_hours', '$p_contact', '$s_contact', '$o_contact', 'open', NOW(), '$uid')";
		$result2 = mysql_query ($query2, $db);
		$err2 = mysql_error();
		if ($err2) {
			die( $err2 );
		} else {
			//keep going
		}

		/*
		begin adding assignments for this job
		first get the last job_id so we can create a relationship between the job and assignments
		*/
		$query3 = "SELECT MAX(job_id) AS job_id FROM jobs WHERE client_id = '$client_id'";
		$result3 = mysql_query ($query3, $db);
		$row3 = mysql_fetch_object($result3);
		$new_job_id = $row3->job_id;

		//define an empty job_assign_id
		$job_assign_id = "";
		//begin building sql statement
		$sql = "INSERT INTO job_assignments (job_assign_id, job_id, specs, graphics, html, scripting, db, hosting, programming, server, review) VALUES ('$job_assign_id','$new_job_id',";
		
		//define a variable to hold the first assignment which was checked
		$first_stage = "";

		//begin looping and defining values for job_assignments table
		foreach ( $assignments as $key => $value ) {
			//define value for each one of the assignment types
			$sql .= "'";
			for ($i=0;$i<$staff_count;$i++) {
				$str1 = $staff_list[$i] . "_" . $value;
				$str10 = $$str1;
				if ($str10 == "on") {
					//get the first stage if empty
					if (empty($first_stage)) {
						$first_stage = $key;
						$assign_job_to = $value;
					}
					$sql .= "$staff_list[$i]|";
				}
			//end assignment types loop
			}
			//rid of the last | if exists
			if (substr($sql, -1) == "|") {$sql = substr($sql, 0, -1);}
			//close the entry
			$sql .= "',";
		//end staff members loop
		}
		//get rid of the lase , comma
		$sql = substr($sql, 0, -1);
		//close the brackets
		$sql .= ")";
		//send sql to mysql db for insertion of record
		$result4 = mysql_query ($sql, $db);

		//find out which is the employee that is going to do this stage
		switch ($assign_job_to) {
			case "C":
				$lookupcollumn = "specs";
				break;
			case "G":
				$lookupcollumn = "graphics";
				break;
			case "B":
				$lookupcollumn = "html";
				break;
			case "A":
				$lookupcollumn = "scripting";
				break;
			case "D":
				$lookupcollumn = "db";
				break;
			case "H":
				$lookupcollumn = "hosting";
				break;
			case "P":
				$lookupcollumn = "programming";
				break;
			case "S":
				$lookupcollumn = "server";
				break;
			case "R":
				$lookupcollumn = "review";
				break;
		}

		$query5 = "SELECT $lookupcollumn FROM job_assignments WHERE job_id = '$new_job_id'";
		$result5 = mysql_query ($query5, $db);
		$row5 = mysql_fetch_object($result5);
		$assigned_staff = $row5->$lookupcollumn;

		//now translate first stage to a real stage and update jobs table with employees assigned as well
		$query6 = "UPDATE jobs SET job_stage=";
		//loop through the assignments and match to first stage
		foreach ( $assignments as $key => $value ) {
			if ($first_stage == $key) {$query6 .= "'$key'";}
		}
		$query6 .= ", employee='$assigned_staff' WHERE job_id='$new_job_id'";
		//send query to mysql for record update
		$result6 = mysql_query ($query6, $db);

		//redirect page to attach.php for addint attachments
		$mypage = "attach.php?client_id=$client_id";
		echo "<script language=\"JavaScript\">document.location.replace('$mypage');</script>";
	}
//end event add_job
}


//select all existing jobs for last 3 months
//find out date 3 months ago
$ts = time();
$mydays = 90 * 24 * 60 * 60; //90 days
# make a timestamp from mydays
$timeperiod = date("Y-m-d", ($ts - $mydays));
$query1 = "SELECT * FROM jobs WHERE client_id = '$client_id' AND (closing_date > '$timeperiod' OR closing_date IS NULL) ORDER BY status ASC, job_id DESC";
$result1 = mysql_query ($query1, $db);

// update total times on all visible jobs to ensure they are correct
while ($row = mysql_fetch_assoc($result1))
	updateTotalTime($db, $row['job_id']);
$result1 = mysql_query ($query1, $db);

//select all existing contacts
$query2 = "SELECT first_name, last_name FROM contacts WHERE client_id = '$client_id'";
$result2 = mysql_query ($query2, $db);
//add values to a variable to be used in drop downs
$mycontacts = "";
while ($row2=mysql_fetch_object($result2)) {
	$mycontacts = $mycontacts . "<option>$row2->first_name $row2->last_name</option>\n";
}

//begin general display of information
$query3 = "SELECT client_name, trading_name, agreement_number, website_url, status FROM clients WHERE client_id = '$client_id'";
$result3 = mysql_query ($query3, $db);
$row3 = mysql_fetch_object($result3);
$client_name = $row3->client_name;
$trading_name = $row3->trading_name;
$agreement_number = $row3->agreement_number;
$website_url = $row3->website_url;
$status = $row3->status;

//if agreement number (customer number) is not registered we must do it first.
if ($agreement_number == 0 & $alertmeonce != 1) {echo "<script>alert('Client number is missing. Please update client record before entering new jobs')</script>";}

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
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <?php include_once("includes/admin_links.php"); ?>
  <tr>
    <td>&nbsp;</td>
    <td colspan="2" class="text"><u>Job Control</u></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td valign="top" class="text" colspan="2">
      <p class="subheading">Job List</p>
      <table border="0" cellspacing="1" cellpadding="1">
        <tr class="text">
          <td width="70" class="text">Job Number</td>
          <td width="50" class="text">Status</td>
          <td width="10" class="text"><img src="images/spacer.gif" width="10" height="11"></td>
          <td class="text">Title</td>
          <td width="90" class="text">Project Manager</td>
          <td width="90" class="text">Due Date<br>Completion Date</td>
		  <td width="90" class="text">Job Close Date</td>
          <td width="40" class="text">Act.Hrs</td>
          <td width="40" class="text">Adj.Hrs</td>
          <td width="40" class="text">Bill.Hrs</td>
        </tr>
		<?php
		while ($row1 = mysql_fetch_object($result1)) {
			$job_id = $row1->job_id;
			$job_number = $row1->job_number;
			$job_title = $row1->job_title;
			//format due date
			$due_date = ToNextContact($row1->due_date);
			//format closing date
			if (!empty($row1->closing_date)) {
				$closing_date = ToNextContact($row1->closing_date);
			} else {$closing_date = "n/a";}
			$total_hours = $row1->total_hours;
			$external_hours = $row1->external_hours;
			$billable_hours = $row1->billable_hours;
			$status = $row1->status;
			if ($status == "open") {$mystyle = "subheading";} else {$mystyle = "text";}
			echo "<tr valign=\"middle\" bgcolor=\"#0070A6\">";
			echo "<td class=\"text\"><a href=\"job_control_detail.php?job_id=$job_id&client_id=$client_id\"><font color=\"#B9E9FF\">$job_number</font></a></td>";
			echo "<td class=\"$mystyle\"><input name=\"status\" type=\"text\" value=\"$status\" size=\"4\" class=\"$mystyle\"></td>";
			echo "<td>&nbsp;</td>";
			echo "<td class=\"text\" align=\"left\"><a href=\"job_control_detail.php?job_id=$job_id&client_id=$client_id\"><font color=\"#B9E9FF\">$job_title</font></a></td>";
			echo "<td class=\"text\">" . $row1->project_manager . "</td>";
			echo "<td class=\"text\">$due_date</td>";
			echo "<td class=\"text\">$closing_date</td>";
			echo "<td class=\"text\">$total_hours</font></a></td>";
			echo "<td class=\"text\">$external_hours</font></a></td>";
			echo "<td class=\"text\">$billable_hours</font></a></td>";
			echo "</tr>";
		}
		?>
		<tr><td class="text" colspan="6">&nbsp;</td></tr>
		<tr><td class="text" colspan="6"><a href="archive_jobs.php?client_id=<?php echo $client_id; ?>"><font color="B9E9FF">To view complete job archive, click here</font></a></td></tr>
      </table>
      <p><img src="images/spacer.gif" width="10" height="11"></p>
      <p class="subheading">Create New Job</p>
      <table width="643" border="0" cellspacing="0" cellpadding="0">
	  <form action="job_control.php" method="post">
	  <input name="client_id" type="hidden" value="<?php echo $client_id; ?>">
	  <input name="agreement_number" type="hidden" value="<?php echo $agreement_number; ?>">
        <tr class="text">
          <td width="155" class="text">Job Title</td>
          <td width="488" class="text"><input name="job_title" type="text" class="black" size="80">
          </td>
        </tr>
        <tr>
          <td class="text">Requested Completion Date</td>
          <td><?php
          
          	include_once( "includes/DateField.php" );
          	$date_field = new DateField( "myRC" );
          	echo $date_field->getHTML();
          
          ?>
		  </td>
        </tr>
        <tr>
          <td class="text">Estimated Completion Date</td>
          <td><?php
          
          	include_once( "includes/DateField.php" );
          	$date_field = new DateField( "myEC" );
          	echo $date_field->getHTML();
          
          ?></td>
        </tr>
        <tr valign="top">
          <td colspan="2" class="text"><img src="images/spacer.gif" width="10" height="11"><br>
            <table border="0" cellspacing="0" cellpadding="0" class="text"><tbody>
              <tr><td>Job Specification</td><td style="text-align:right"><a href="#" id="job_details_expand" onclick="return toggleExpand('job_details')" style="color:#b9e9ff">expand</a></td></tr>
              <tr><td colspan="2"><textarea name="job_details" cols="110" rows="6" expandrows="20" class="smallblue" id="job_details" onfocus="toggleExpand('job_details',true)"><?= htmlspecialchars( $job_details ) ?></textarea></td></tr>
            </tbody></table>
          </td>
        </tr>
        <tr valign="top">
          <td colspan="2" class="text"><img src="images/spacer.gif" width="10" height="11"><br>
            Contacts for this job:<br>
            Primary:
            <select name="p_contact" size="1" class="black">
			  <option selected></option>
              <?php echo $mycontacts; ?>
            </select> <img src="images/spacer.gif" width="18" height="12">Secondary:
            <select name="s_contact" size="1" class="black">
			  <option selected></option>
              <?php echo $mycontacts; ?>
            </select>
            <img src="images/spacer.gif" width="18" height="12">Tertiary:
            <select name="o_contact" size="1" class="black">
			  <option selected></option>
              <?php echo $mycontacts; ?>
            </select></td>
        </tr>
        <tr valign="top">
          <td colspan="2" class="text">
            <div align="left">
              <p><img src="images/spacer.gif" width="10" height="11"><br>
                Billing and Budget Tracking:<br>
                <input type="radio" name="billing_method" value="open">
                Open hourly rate<br>
                <input type="radio" name="billing_method" value="hours">
                Fixed number of hours, being:
                <input name="billing_hours1" type="text" class="black" size="2" value="">
                <br>
                <input type="radio" name="billing_method" value="fixed">
                Fixed price agreement
				<br>
                <input type="radio" name="billing_method" value="no charge">
                No Charge, Enter budget hours
				<input name="billing_hours2" type="text" class="black" size="2" value="">
				<br>
				<input type="radio" name="billing_method" value="rd">
                Research &amp; Development
				</p>
                <p>Assignments for this job:</p>
                  Project Manager:
                <select name="project_manager" class="black" size="1">
			    <option selected></option>
                <?php echo $mystaff; ?>
              </select>
          	  <br>
			  <?php
				//display matrix of assignments
                echo "<table border='0' cellspacing='0' cellpadding='0' class='text' id='job_assign'>";
				//write the lables row for users
				echo "<tr><th width='150'>&nbsp;</th>";
				$staff_count = count($staff_list);
				for ($i=0;$i<$staff_count;$i++) {
					$myname = ucfirst($staff_list[$i]);
					echo "<th title='" . $myname . "'>" . substr( $myname, 0, 3 ) . "</th>";
				}
				echo "</tr>";
	
				//loop through the assignments and add row of checkboxes for each user
				foreach ( $assignments as $key => $value ) {
					$theass = $key;
					$myass = $value;
					echo "<tr>";
					echo "<th><div class=\"assign\">$theass</div></th>";
					//run another loop and write a cell for each employee
					for ($i=0;$i<$staff_count;$i++) {
						//find who are the employees assigned for this assignment
						//by searching for their name inside the string saved in the database
						$myval = $$myass;
						//$isChecked = checkAssignment($myval, );
						if (eregi($staff_list[$i], $myval)){$isChecked = "checked";} else {$isChecked = "";}
						echo "<td><input name='$staff_list[$i]_$myass' type='checkbox' onclick='rebuildJobAssign()' $isChecked></td>";
					}
                	echo "</tr>";
				}
	
                echo "</table>";
                include( "includes/job_assign.js" );
                ?>
              </div></td>
        </tr>
        <tr>
          <td colspan="2" class="text"><img src="images/spacer.gif" width="10" height="11"><br>
            <input type="submit" name="add_job" value="Enter New Job" class="smallbluebutton"></td>
        </tr>
		</form>
      </table>
      <p></p>
      <p><br><img src="images/spacer.gif" width="39" height="10"></p></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td valign="top" class="text">
      <p><img src="images/spacer.gif" width="233" height="14"></p>
      </td>
    <td width="76%" valign="top" class="text">
<p></p>
      </td>
  </tr>
</table>
</body>
</html>
