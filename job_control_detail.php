<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* job_control_details template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 4/7/2003
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

include 'includes/CapsApi.php';
CapsApi::set_user($uid);

//break today into some useful variables to be inserted into date text box later
list($myd, $mym, $myy) = explode("/", $today);
//convert the month to characters
$mym = get_month($mym);

//establish a persistent connection and get all required data.
// select valuations for  valuer
$db = mysql_pconnect ($hostName, $userName, $password);
mysql_select_db($database);

//define empty array to hold staff list
$employees = '<option value=""></option>';
$all_staff = array();
$staff_list = array();
//define variable to hold drop down for staff
$mystaff = "";
//select all existing staff and add to staff list array
$query = "SELECT first_name, last_name, u, assign_job FROM ps ORDER BY first_name";
$result = mysql_query ($query, $db);
while ($row=mysql_fetch_object($result)) {
	$fname = $row->first_name;
	$lname = $row->last_name;
	$staffname = $fname . " " . $lname;
	$staff = $row->u;
	if ($row->assign_job == 'Yes') {
		$mystaff .= "<option>$staffname</option>\n";
	//add entry to array
		array_push ($staff_list, $staff);
	}
	$all_staff[$row->u] = $fname;
	$employees .= '<option value="' . $staff . '">' . $fname . '</option>';
}
//find out how many staff memebers there are
$staff_count = count($staff_list);

// Add ex exployees
$result = mysql_query('SELECT DISTINCT employee FROM job_tasks ORDER BY employee');
$employees .= '<optgroup label="Ex...">';
while ($row = mysql_fetch_object($result)) {
	if ($row->employee == '')
		continue;
	if (array_safe($all_staff, $row->employee, false) === false)
		$employees .= '<option value="' . $row->employee . '">' . $row->employee . '</option>';
}
$employees .= '</optgroup>';


//event->update_stage--------------when user changes job stage and updates--------------------------------
$redirect = false;
if (isset($update_stage)) {
	//define an array of field entry and stages for internat referencing
	$stages = array('Job Specifications' => 'specs', 'Graphics / Multimedia' => 'graphics', 'Basic HTML' => 'html', 'Advanced HTML / Scripting' => 'scripting', 'Database Development' => 'db', 'Domain & Hosting' => 'hosting', 'Programming' => 'programming', 'Server / OS / Hardware' => 'server', 'Job Review' => 'review');
	//loop through the stages array and find the correct entry according to the key.
	foreach($stages as $lable => $field) {
		if ($lable == $job_stage) {$updatedField = $field;}
	}
	//find out who is the employee for this job
	$query = "SELECT $updatedField FROM job_assignments WHERE job_id='$job_id'";
	$result = mysql_query($query, $db);
	$row = mysql_fetch_object($result);
	$newEmployee = $row->$updatedField;
	//update the stage
	$query1 = "UPDATE jobs SET employee='$newEmployee', job_stage='$job_stage' WHERE job_id='$job_id'";
	$result1 = mysql_query ($query1, $db);
	$err1 = mysql_error($db);
	if ($err1) {die($err1);}
	$redirect = true;
}

//event->add_job_details------------when user adds a job_detail instance----------------------------------
if (isset($add_job_details)) {
	//format date and make a real object
	//format next contact
	$myjob_date = FromNextContact($myJC);
	//format start_time and end_time
	$new_start_time = checkTime($start_time);
	if ($new_start_time === 0)
		$errmsg = "start time format is incorrect";
	$new_end_time = checkTime($end_time);
	if ($new_end_time === 0)
		$errmsg .= " end time format is incorrect";
	$job_task_id = $task;
	
	$description = trim($description);

	if ($job_task_id == 0 && $description == '')
		$errmsg .= " select a task or enter a comment";
	if (empty($errmsg)) {

		// Attempt to automatically allocate the task based on the description
		/*
		$query = "SELECT job_task_id, IF(employee='$uid', 1, 0) AS is_employee FROM job_tasks WHERE job_id='$job_id' AND description LIKE '" . addslashes($description . '%') . "' ORDER BY is_employee DESC";
		$result = mysql_query ($query, $db);
		$row = mysql_fetch_assoc($result);
		$job_task_id = $row ? array_safe($row, 'job_task_id', 0) : 0;
		*/

		//declare id and insert new job details
		$created_on = date('Y-m-d H:i:s');
		$query = "INSERT INTO job_details (job_id, job_task_id, job_date, start_time, end_time, description, employee, created_on, last_modified) VALUES ('$job_id', '$job_task_id', '$myjob_date', '$new_start_time', '$new_end_time', '".addslashes($description)."','$uid', '$created_on', '$created_on')";
		$result = mysql_query ($query, $db);
		//update the total time for job on jobs table
		//calculate total hours for instance
		$thetime = getTime($new_start_time, $new_end_time);
		//calculate the total time so far and update jobs table
		updateTotalTime($db, $job_id);
		$redirect = true;
	} else {
		echo "<script>alert('$errmsg')</script>";
	}
//end event add_job_details
}

// -------------------- update task details ------------------------------
if (isset($update_task)) {
	
	// If updating an existing job
	$task_id = intval($task_id);
	$description = trim(array_safe($_REQUEST, 'description', ''));
	$code = array_safe($_REQUEST, 'code', '');
	$employee = array_safe($_REQUEST, 'employee', '');
	$completed = array_safe($_REQUEST, 'completed', '');
	$closed = array_safe($_REQUEST, 'closed', '');
	$due_date = array_safe($_REQUEST, 'due_date', '');
	$due_date = $due_date ? ("'" . date('Y-m-d', strtotime($due_date)) . "'") : 'NULL';
	$chargeable = array_safe($_REQUEST, 'chargeable', '');
	$chargeable = $chargeable == 'F/C' ? 2 : ($chargeable == 'N/C' ? 0 : 1);
	$status = array_safe($_REQUEST, 'status', '');
	$status = $status ? ("'" . mysql_real_escape_string($status) . "'") : 'NULL';

	// Convert estimate to numeric value
	$estimate = parseHoursToMinutes(array_safe($_REQUEST, 'estimate', ''));

	// Convert quoted to numberic value
	$quote = parseHoursToMinutes(array_safe($_REQUEST, 'quote', ''));

	if ($task_id > 0) {

		// Flag as completed or uncompleted
		if (array_safe($_REQUEST, 'completed', false) !== false) {
			if ($_REQUEST['completed'] == 'Yes')
				mysql_query("UPDATE job_tasks SET completed=NOW(), last_modified=NOW() WHERE job_task_id=" . $task_id . " AND completed IS NULL");
			else
				mysql_query("UPDATE job_tasks SET completed=NULL, last_modified=NOW() WHERE job_task_id=" . $task_id);
			header('Content-type: application/json');
			echo '{"success":true}';
			exit();
		
		// Flag as closed or not closed
		}elseif (array_safe($_REQUEST, 'closed', false) !== false) {
			if ($_REQUEST['closed'] == 'Yes')
				mysql_query("UPDATE job_tasks SET closed=NOW(), last_modified=NOW() WHERE job_task_id=" . $task_id . " AND closed IS NULL");
			else
				mysql_query("UPDATE job_tasks SET closed=NULL, last_modified=NOW() WHERE job_task_id=" . $task_id);
			header('Content-type: application/json');
			echo '{"success":true}';
			exit();


		// Delete the task
		} elseif (array_safe($_REQUEST, 'delete', '') != '') {
			mysql_query("UPDATE job_tasks SET deleted_on=NOW(), deleted_by='" . $uid . "' WHERE job_task_id=" . $task_id);
			header('Content-type: application/json');
			echo '{"success":true}';
			exit();

		// Move the task
		} elseif (array_safe($_REQUEST, 'move', '') != '') {
			mysql_query("UPDATE job_tasks SET job_id=" . $_REQUEST['job_id'] . " WHERE job_task_id=" . $task_id);
			header('Content-type: application/json');
			echo '{"success":true}';
			exit();
			
		// Copy the task
		} elseif (array_safe($_REQUEST, 'copy', '') != '') {
			header('Content-type: application/json');
			CapsApi::issue_copy($task_id, $_REQUEST['job_id']);
			echo '{"success":true}';
			exit();

		} elseif (array_safe($_REQUEST, 'get', '') != '') {


		// Update the task
		} else {
			/*
			if($code != ''){
				//echo "UPDATE job_tasks SET cr_reason='" . mysql_real_escape_string($cr_reason) ."',cr_impact_page='" . mysql_real_escape_string($cr_impact_page) ."',cr_impact_functionality='" . mysql_real_escape_string($cr_impact_functionality) ."',cr_impact_site='" . mysql_real_escape_string($cr_impact_site) ."',cr_impact_third_party='" . mysql_real_escape_string($cr_impact_third_party) ."', cr_impact_cost='" . mysql_real_escape_string($cr_impact_cost) ."', description='" . mysql_real_escape_string($description) . "', job_task_number='" . mysql_real_escape_string($code) . "', estimate=" . $estimate . ", quote=" . $quote . ", chargeable=" . $chargeable . ", employee='" . mysql_real_escape_string($employee) . "', due_date=" . $due_date . ", status=" . $status . " WHERE job_task_id=" . $task_id;
				mysql_query("UPDATE job_tasks SET cr_description='" . mysql_real_escape_string($cr_description) ."', cr_requester='" . mysql_real_escape_string($cr_requester) ."', cr_page_affected='" . mysql_real_escape_string($cr_page_affected) ."', cr_reason='" . mysql_real_escape_string($cr_reason) ."',cr_impact_page='" . mysql_real_escape_string($cr_impact_page) ."',cr_impact_functionality='" . mysql_real_escape_string($cr_impact_functionality) ."',cr_impact_site='" . mysql_real_escape_string($cr_impact_site) ."',cr_impact_third_party='" . mysql_real_escape_string($cr_impact_third_party) ."', cr_impact_cost='" . mysql_real_escape_string($cr_impact_cost) ."', description='" . mysql_real_escape_string($description) . "', job_task_number='" . mysql_real_escape_string($code) . "', estimate=" . $estimate . ", quote=" . $quote . ", chargeable=" . $chargeable . ", employee='" . mysql_real_escape_string($employee) . "', due_date=" . $due_date . ", status=" . $status . ", last_modified=NOW() WHERE job_task_id=" . $task_id);
			}else{
				mysql_query("UPDATE job_tasks SET description='" . mysql_real_escape_string($description) . "', job_task_number='" . mysql_real_escape_string($code) . "', estimate=" . $estimate . ", quote=" . $quote . ", chargeable=" . $chargeable . ", employee='" . mysql_real_escape_string($employee) . "', due_date=" . $due_date . ", status=" . $status . ", last_modified=NOW() WHERE job_task_id=" . $task_id);
			}
			*/
			mysql_query("UPDATE job_tasks SET description='" . mysql_real_escape_string($description) . "', job_task_number='" . mysql_real_escape_string($code) . "', estimate=" . $estimate . ", quote=" . $quote . ", chargeable=" . $chargeable . ", employee='" . mysql_real_escape_string($employee) . "', due_date=" . $due_date . ", status=" . $status . ", last_modified=NOW() WHERE job_task_id=" . $task_id);
			
		}

	// Add new task
	} else {
		$query = "INSERT INTO job_tasks ( job_id, description, job_task_number, estimate, quote, chargeable, employee, due_date, status, created_by, created_on, last_modified ) VALUES ( " . $job_id . ", '" . mysql_real_escape_string($description) . "', '" . mysql_real_escape_string($code) . "', " . $estimate . ", " . $quote . ", " . $chargeable . ", '" . mysql_real_escape_string($employee) . "', " . $due_date . ", " . $status . ", '" . mysql_real_escape_string($uid) . "', NOW(), NOW() )";
		mysql_query($query);
		$task_id = mysql_insert_id();
	}

	// Get the task details
	$results = mysql_query("SELECT * FROM job_tasks WHERE job_task_id=" . $task_id);
	$task = mysql_fetch_object($results);

	// Output the task
	header('Content-type: application/json');
	echo '{';
	echo '"task_id":' . $task->job_task_id . ',';
	echo '"description":"' . addslashes($task->description) . '",';
	echo '"code":"' . addslashes($task->job_task_number) . '",';
	echo '"estimate":"' . ($task->estimate > 0 ? formatMinutes($task->estimate) : '') . '",';
	echo '"quote":"' . ($task->quote > 0 ? formatMinutes($task->quote) : '') . '",';
	echo '"employee":"' . addslashes($task->employee) . '",';
	echo '"chargeable":"' . ($task->chargeable == 2 ? 'F/C' : ($task->chargeable ? 'T&M' : 'N/C')) . '",';
	echo '"title":"Created on ' . date('d-M-Y', strtotime($task->created_on)) . ' by ' . ucwords($task->created_by) . '",';
	echo '"due_date":"' . addslashes($task->due_date ? date('d-M-Y', strtotime($task->due_date)) : '') . '",';
	echo '"status":"' . addslashes($task->status) . '",';
	echo '"completed":"' . ($task->completed === null ? '' : date('d-M-Y', strtotime($task->completed))) . '"';

	/*
	echo '"cr_reason":"' . sanitise_multiline($task->cr_reason) . '",';
	echo '"cr_impact_page":"' . sanitise_multiline($task->cr_impact_page) . '",';
	echo '"cr_impact_functionality":"' . sanitise_multiline($task->cr_impact_functionality) . '",';
	echo '"cr_impact_site":"' . sanitise_multiline($task->cr_impact_site) . '",';
	echo '"cr_impact_third_party":"' . sanitise_multiline($task->cr_impact_third_party) . '",';
	echo '"cr_impact_cost":"' . sanitise_multiline($task->cr_impact_cost) . '",';

	echo '"cr_description":"' . sanitise_multiline($task->cr_description) . '",';
	echo '"cr_requester":"' . addslashes($task->cr_requester) . '",';
	echo '"cr_page_affected":"' . addslashes($task->cr_page_affected) . '"';
	*/

	echo '}';
	exit();

}

function sanitise_multiline($input){
	return str_replace(array("\r","\n"), array('','\n'),addslashes($input));
}

//event->update_job-----------------when user updates the job details-------------------------------------
if (isset($update_job)) {
	//format billing_hours correctly
	if ($billing_method == "hours") {
		$mybilling_hours = $billing_hours1;
	} elseif ($billing_method == "no charge") {
		$mybilling_hours = $billing_hours2;
	} else {$mybilling_hours = 0;}
	//format total hours
	$query = "UPDATE jobs SET job_title='".addslashes($job_title)."', job_details='".addslashes($job_details)."', project_manager='".addslashes($project_manager)."'";
	$query .= ", billing_method='$billing_method', billing_hours='$mybilling_hours', p_contact='$p_contact', s_contact='$s_contact', o_contact='$o_contact', api_hide=" . ($api_hide ? 1 : 0) . " WHERE job_id='$job_id'";
	$result = mysql_query ($query, $db);
	$err = mysql_error();
	if ($err)
		print('error: '.$err);
	/*
	update assignments for this job
	This is a bit clumzy code. It contains many loops and is still in testing.
	This code will be changed to a more dynamic type later on
	Please be very careful when modifying this code it is complex
	*/
	//first check if a job_assignments record is available for this job
	//if not insert one, other wise update it
	$query1 = "SELECT job_assign_id FROM job_assignments WHERE job_id='$job_id'";
	$result1 = mysql_query ($query1, $db);
	$num_rows1 = mysql_num_rows($result1);
	if ($num_rows1 == 1) {
		//a record exists than update it

		//begin building update sql statement
		$sql = "UPDATE job_assignments SET ";

		//run a loop through each field in table and update
		foreach ( $assign_fields as $asskey => $assvalue ) {
			$sql .= "$assvalue=";

			//begin looping through the assignments array to check who is assigned
			foreach ( $assignments as $key => $value ) {
				//make sure to only enter the value for the relevant field
				if ($value == $asskey) {
					//begin writing entries for the relevant field
					$sql .= "'";
					for ($i=0;$i<$staff_count;$i++) {
						$str1 = $staff_list[$i] . "_" . $value;
						$str10 = $$str1;
						if ($str10 == "on") {$sql .= "$staff_list[$i]|";}
					//end writing entries for the relevant filed
					}
					//rid of the last | if exists
					if (substr($sql, -1) == "|") {$sql = substr($sql, 0, -1);}
					//close the field entry
					$sql .= "',";
				//move to the next staff member
				}
			//end staff members loop
			}
		}
		//get rid of the last , comma
		$sql = substr($sql, 0, -1);
		//close the brackets
		$sql .= " WHERE job_assign_id='$job_assign_id'";
		$result1 = mysql_query ($sql, $db);
	//end record exist and updated begin insert new record
	} else {
		//insert a new job_assignments record
		//define an empty job_assign_id
		$job_assign_id = "";
		//begin building sql statement
		$sql = "INSERT INTO job_assignments (job_assign_id, job_id, specs, graphics, html, scripting, db, hosting, programming, server, review) VALUES ('$job_assign_id','$job_id',";

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
					if ($first_stage == "") {$first_stage = $key;}
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

		//now translate first stage to a real stage and update jobs table
		$query5 = "UPDATE jobs SET job_stage=";
		//loop through the assignments and match to first stage
		foreach ( $assignments as $key => $value ) {
			if ($first_stage == $key) {$query5 .= "'$key'";}
		}
		$query5 .= " WHERE job_id='$job_id'";
		//send query to mysql for record update
		$result5 = mysql_query ($query5, $db);
	//end insert new record to job_assignments
	}

	//find out which is the employee that is going to do this stage
	$query6 = "SELECT job_stage FROM jobs WHERE job_id='$job_id'";
	$result6 = mysql_query ($query6, $db);
	$row6 = mysql_fetch_object($result6);
	$job_stage = $row6->job_stage;

	//get the collumn to query on
	switch ($job_stage) {
		case "Job Specifications":
			$lookupcollumn = "specs";
			break;
		case "Graphics / Multimedia":
			$lookupcollumn = "graphics";
			break;
		case "Basic HTML":
			$lookupcollumn = "html";
			break;
		case "Advanced HTML / Scripting":
			$lookupcollumn = "scripting";
			break;
		case "Database Development":
			$lookupcollumn = "db";
			break;
		case "Domain & Hosting":
			$lookupcollumn = "hosting";
			break;
		case "Programming":
			$lookupcollumn = "programming";
			break;
		case "Server / OS / Hardware":
			$lookupcollumn = "server";
			break;
		case "Job Review":
			$lookupcollumn = "review";
			break;
	}

	$query7 = "SELECT $lookupcollumn FROM job_assignments WHERE job_id = '$job_id'";
	$result7 = mysql_query ($query7, $db);
	$row7 = mysql_fetch_object($result7);
	$assigned_staff = $row7->$lookupcollumn;

	//update jobs table and enter the employee
	$query8 = "UPDATE jobs SET employee='$assigned_staff' WHERE job_id = '$job_id'";
	$result8 = mysql_query ($query8, $db);
	$err8 = mysql_error();
	if ($err8) {echo $err8;}
	$redirect = true;
//end event update_job
}

//event->delete attachment----------when user clicks delete attachment------------------------------------
if ($event == "delatt") {
	//delete the attachment
	$myfile = $attachment;
	$thefile = $attachDir . "/". $myfile;
	//if image exists write uploaded else write N/A
	if (file_exists($thefile)) {unlink($thefile);}

	//delete the entry from the attachment table
	$query = "delete from attachments where file_name='$attachment' and job_id='$job_id'";
	$result = mysql_query ($query, $db);
	$redirect = true;
//end event delatt
}

//event->delete_job---------------when user clicks delete job---------------------------------------------
if ($event == "delete_job") {
	//delete the job
	$query = "DELETE FROM jobs WHERE job_id='$job_id'";
	$result = mysql_query ($query, $db);
	//go to job_control
	$page = "job_control.php?client_id=$client_id";
	echo "<script language='JavaScript'>document.location.replace('$page');</script>";
	exit();
}

// If we should redirect
if ($redirect) {
	header( "Location: job_control_detail.php?job_id=" . $_REQUEST['job_id'] );
	exit();
}

//select all details for this job
updateTotalTime($db, $job_id);
$query = "SELECT jd.*, jt.description AS job_task_description, jt.job_task_number, jt.chargeable " .
	"FROM job_details AS jd " .
	"LEFT JOIN job_tasks AS jt ON jt.job_task_id=jd.job_task_id " .
	"WHERE jd.job_id='$job_id' AND jd.deleted_on IS NULL " .
	"ORDER BY jd.job_date ASC, jd.start_time ASC";
$query1 = "SELECT * FROM jobs WHERE job_id = '$job_id' LIMIT 1";

$result = mysql_query ($query, $db);
$result1 = mysql_query ($query1, $db);

//get all details for the job
$row1 = mysql_fetch_object($result1);
$client_id = $row1->client_id;
$job_number = $row1->job_number;
$job_title = stripslashes($row1->job_title);
$job_details = stripslashes($row1->job_details);
$job_closed = $row1->status != 'open';
$employee = $row1->employee;
$job_stage = $row1->job_stage;
$job_stage = $row1->job_stage;
$project_manager = $row1->project_manager;
$order_date = $row1->order_date;
$due_date = $row1->due_date;
$est_completion = $row1->est_completion;
$total_hours = $row1->total_hours;
$billing_method = $row1->billing_method;
$billing_hours = $row1->billing_hours;
$p_contact = $row1->p_contact;
$s_contact = $row1->s_contact;
$o_contact = $row1->o_contact;
$mystatus = $row1->status;
$api_hide = $row1->api_hide;

switch ($billing_method) {
	case "open":
		$is_open = "checked";
		$is_hours = "";
		$is_fixed = "";
		$is_nocharge = "";
		$billing_hours1 = "";
		$billing_hours2 = "";
		$is_rd = "";
		break;
	case "hours":
		$is_open = "";
		$is_hours = "checked";
		$is_fixed = "";
		$is_nocharge = "";
		$billing_hours1 = $billing_hours;
		$billing_hours2 = "";
		$is_rd = "";
		break;
	case "fixed":
		$is_open = "";
		$is_hours = "";
		$is_fixed = "checked";
		$is_nocharge = "";
		$billing_hours1 = "";
		$billing_hours2 = "";
		$is_rd = "";
		break;
	case "no charge":
		$is_open = "";
		$is_hours = "";
		$is_fixed = "";
		$is_nocharge = "checked";
		$billing_hours2 = $billing_hours;
		$billing_hours1 = "";
		$is_rd = "";
		break;
	case "rd":
		$is_open = "";
		$is_hours = "";
		$is_fixed = "";
		$is_nocharge = "";
		$billing_hours1 = "";
		$billing_hours2 = "";
		$is_rd = "checked";
		break;
}

//select all existing contacts
$query3 = "SELECT first_name, last_name FROM contacts WHERE client_id = '$client_id'";
$result3 = mysql_query ($query3, $db);
//add values to a variable to be used in drop downs
$mycontacts = "";
while ($row3=mysql_fetch_object($result3)) {
	$mycontacts = $mycontacts . "<option>$row3->first_name $row3->last_name</option>\n";
}

//begin general display of information
$query4 = "SELECT client_name, trading_name, agreement_number, website_url, status FROM clients WHERE client_id = '$client_id'";
$result4 = mysql_query ($query4, $db);
$row4 = mysql_fetch_object($result4);
$client_name = $row4->client_name;
$trading_name = $row4->trading_name;
$agreement_number = $row4->agreement_number;
$website_url = $row4->website_url;
$status = $row4->status;

$query5 = "SELECT attach_id, file_name, DATE_FORMAT(file_date, '%d-%m-%Y') AS file_date, description FROM attachments WHERE job_id='$job_id'";
$result5 = mysql_query ($query5, $db);

$query6 = "SELECT * FROM job_assignments WHERE job_id = '$job_id'";
$result6 = mysql_query ($query6, $db);
$row6 = mysql_fetch_object($result6);
$job_assign_id = $row6->job_assign_id;
$C = $row6->specs;
$G = $row6->graphics;
$B = $row6->html;
$A = $row6->scripting;
$D = $row6->db;
$H = $row6->hosting;
$P = $row6->programming;
$S = $row6->server;
$R = $row6->review;

//write a drop down for jobstages
$jobstages = "";
if ($C) {$jobstages .= "<option value='Job Specifications'>Job Specifications ($C)</option>";}
if ($G) {$jobstages .= "<option value='Graphics / Multimedia'>Graphics / Multimedia ($G)</option>";}
if ($B) {$jobstages .= "<option value='Basic HTML'>Basic HTML ($B)</option>";}
if ($A) {$jobstages .= "<option value='Advanced HTML / Scripting'>Advanced HTML / Scripting ($A)</option>";}
if ($D) {$jobstages .= "<option value='Database Development'>Database Development ($D)</option>";}
if ($H) {$jobstages .= "<option value='Domain & Hosting'>Domain & Hosting ($H)</option>";}
if ($P) {$jobstages .= "<option value='Programming'>Programming ($P)</option>";}
if ($S) {$jobstages .= "<option value='Server / OS / Hardware'>Server / OS / Hardware ($S)</option>";}
if ($R) {$jobstages .= "<option value='Job Review'>Job Review ($R)</option>";}

// Get job tasks totals
$task_totals = array();
$query = 'SELECT job_details.job_task_id, SUM(TIME_TO_SEC(job_details.end_time) - TIME_TO_SEC(job_details.start_time)) AS seconds ' .
	'FROM job_details ' .
	'LEFT JOIN job_tasks ON job_tasks.job_task_id=job_details.job_task_id ' .
	'WHERE job_details.job_task_id > 0 AND ( job_details.job_id=' . $job_id . ' OR job_tasks.job_id=' . $job_id . ' ) AND job_details.deleted_on IS NULL ' .
	'GROUP BY job_details.job_task_id';
$results = mysql_query($query);
while ($row = mysql_fetch_assoc($results))
	$task_totals[$row['job_task_id']] = $row['seconds'] / 60;

$task_groups = array(
	'my' => array(),
	'other' => array()
);

// My Tasks
$query = 'SELECT * FROM job_tasks WHERE job_id=' . $job_id . ' AND employee=\'' . mysql_escape_string($uid) . '\' AND deleted_on IS NULL AND completed IS NULL ORDER BY employee, description';
$results = mysql_query($query);
while ($row = mysql_fetch_assoc($results)) {
	$row['total'] = array_safe($task_totals, $row['job_task_id'], 0);
	$task_groups['my'][] = $row;
}
$query = 'SELECT * FROM job_tasks WHERE job_id=' . $job_id . ' AND employee=\'' . mysql_escape_string($uid) . '\' AND deleted_on IS NULL AND completed IS NOT NULL ORDER BY completed DESC, description';
$results = mysql_query($query);
while ($row = mysql_fetch_assoc($results)) {
	$row['total'] = array_safe($task_totals, $row['job_task_id'], 0);
	$task_groups['my'][] = $row;
}

// Other Tasks
$query = 'SELECT * FROM job_tasks WHERE job_id=' . $job_id . ' AND employee!=\'' . mysql_escape_string($uid) . '\' AND deleted_on IS NULL AND completed IS NULL ORDER BY employee, description';
$results = mysql_query($query);
while ($row = mysql_fetch_assoc($results)) {
	$row['total'] = array_safe($task_totals, $row['job_task_id'], 0);
	$task_groups['other'][] = $row;
}
$results = mysql_query('SELECT * FROM job_tasks WHERE job_id=' . $job_id . ' AND employee!=\'' . mysql_escape_string($uid) . '\' AND deleted_on IS NULL AND completed IS NOT NULL ORDER BY completed DESC, description');
while ($row = mysql_fetch_assoc($results)) {
	$row['total'] = array_safe($task_totals, $row['job_task_id'], 0);
	$task_groups['other'][] = $row;
}

$task_groups['other'][] = array(
	'job_task_id' => -1,
	'description' => '',
	'employee' => '',
	'completed' => null,
	'created_on' => '',
	'cr_impact_cost' => '',
	'job_task_number' => '',
	'total' => 0,
	'estimate' => '',
	'quote' => '',
	'chargeable' => 0,
	'status' => '',
	'due_date' => null
);

// Get list of open jobs
$open_jobs = array();
$query = "SELECT job_id, job_number, job_title FROM jobs WHERE client_id=" . $client_id . " AND status='Open' ORDER BY job_id DESC";
$results = mysql_query($query);
while ($row = mysql_fetch_assoc($results))
	$open_jobs[$row['job_id']] = $row['job_number'] . ' - ' . $row['job_title'];

// Get list of active clients
$active_clients = array();
$query = "SELECT client_name, client_id FROM clients WHERE status='a' ORDER BY client_name";
$results = mysql_query($query);
while ($row = mysql_fetch_assoc($results))
	$active_clients[$row['client_id']] = $row['client_name'];


//close mysql connection
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
<script type="text/javascript" src="includes/jquery.js"></script>
<style type="text/css">
#task-template { display: none; }
.copy td { color: #999; }
.admin_list .other td { opacity: 0.6; }
.completed td, .completed a { color: #999 !important; }
#task-list td.status { width: 60px; }
#task-list td.estimate, #task-list td.quote, #task-lis td.chargeable { width: 50px; }
#task-list td.due_date { width: 70px; }
</style>
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
    <td class="text" colspan="2"><u>Detail: Job Number <?php echo $job_number; ?> : <?php echo $job_title; ?></u> &nbsp; &nbsp; &nbsp; Project Manager: <?= $project_manager ?></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td valign="top" class="text" colspan="2"> <p class="subheading">Stage</p>
      <table border="0" cellspacing="0" cellpadding="0">
	  <tr>
	  <form name="form1" method="post" action="">
	  <input name="job_id" type="hidden" value="<?php echo $job_id; ?>">
        
          <td width="120" class="text">Assign new stage:</td>
		  <td style="padding-right:20">
              <select name="job_stage" class="black" size="1">
			  	<option selected><?php echo $job_stage; ?></option>
			    <?php echo $jobstages; ?>
              </select>
          </td>
          <td width="60"><input type="submit" name="update_stage" value="Commit" class="smallbluebutton"></td>
       
		</form>
		  	<td>
				<span id="job-copy-open"><input type="button" onclick="$('.job-copy-fields').show();$('#job-copy-open').hide();" class="smallbluebutton" value="Copy this job to another client"></span>
			</td>
				<td class="text job-copy-fields" style="display:none;">Clients: </td>
				<td class="job-copy-fields" style="display:none;"><select name="client-id" class="smallblue" style="width:99%;">
					<option value=""></option>
					<?php foreach ($active_clients as $id => $name) { ?>
						<option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
					<?php } ?>
					</select></span>
				</td>
				<td colspan="3" class="job-copy-fields" style="display:none;"><input type="button" value="Copy" class="smallbluebutton" onclick="copyJob()"></td>
		</tr>
		<script type="text/javascript">
			function copyJob() {
				var params = {};
				params['job_id'] = Number("<?php echo $job_id; ?>");
				params['client_id'] = Number($('select[name=client-id]').val());
						console.log(params);
				jQuery.post(
						'copy_job.php',
						params,
						function( response ) {
							alert( "success " + response );
						},
						'json'
					);
			}
		</script>
      </table>
      <p class="subheading">Job Log</p>
      
      <?php if ($job_closed) { ?>
      <p><strong>This job has been closed so no additional time should be recorded against it.</strong></p>
      <p>Please check if there is another job that time should be recorded against.</p>
      <?php }?>
      
      
            <script type="text/javascript" src="includes/time.js"></script>
			<form id="job-log" action="job_control_detail.php" method="post" onsubmit="window.onbeforeunload=null;"<?php if ($job_closed) { ?> style="display:none;"<?php } ?>>
				<input name="job_id" type="hidden" value="<?php echo $job_id; ?>">
				
				<table width="750" border="0" cellspacing="0" cellpadding="1">
				  <tr class="text">
				    <td width="40">Date:</td>
				    <td width="120" nowrap><?php

            	include_once( "includes/DateField.php" );
            	$date_field = new DateField( "myJC", time() );
            	echo $date_field->getHTML();
            
                    ?></td>
                    <td width="10" rowspan="2"></td>
                    <td width="75">Task:</td>
                    <td><select name="task" class="black" style="width:100%;"></select></td>
                    <td width="10" rowspan="2"></td>
                    <td width="50"><input type="submit" name="add_job_details" value="Commit" class="smallbluebutton"></td>
                  </tr>
				  <tr class="text" valign="top">
				    <td>Time:</td>
                    <td nowrap><input id="start_time" name="start_time" type="text" class="black" style="width:50px" value="" onclick="openTimeControl('start_time')"> to <input id="end_time" name="end_time" type="text" class="black" style="width:50px" value="" onclick="openTimeControl('end_time')"></td>
				    <td>Comments:&nbsp;</td>
				    <td><textarea name="description" type="text" class="smallblue" rows="1" style="width:100%"></textarea></td>
                  </tr>
				</table>
				<br>
			</form>
			<script type="text/javascript">
			$(document).ready( function() {
				$('#job-log').submit( function() {
					var form = $('#start_time').parents('form');
					if( form.find('#start_time').val() == '' ) {
						alert("Please enter a Start Time.");
						return false;
					}
					if( form.find('#end_time').val() == '' ) {
						alert("Please enter a Finish Time.");
						return false;
					}
					if( form.find('select[name="task"]').val() == '' && form.find('textarea[name="description"]').val() == '' ) {
						alert("Please select a task or enter a comment.");
						return false;
					}
					if( form.find('textarea[name="description"]').val() == '') {
						if (confirm('It is strongly recommended you add a comment describing the work you are logging.\n\nWould you like to add a comment?')) {
							form.find('textarea[name="description"]').focus();
							return false;
						}
					}
					return true;
				} );
			} );
			</script>
			
      <?php include 'includes/time_log.php'; ?>
      
      <br>
      &gt; <a href="job_time_log.php?job_id=<?php echo $job_id; ?>&client_id=<?php echo $client_id; ?>"><font color="B9E9FF">view
            / edit the full log for this job</font></a>
      
			<div style="display:none"><textarea id="time-copy"></textarea></div>
			<script>
			$(document).ready( function() {
				if( window.clipboardData )
					$('.copyLink').show();
			} );
			function copyTime(link) {
				var row = $(link).parent().parent();
				var line = '';
				row.find( 'td.copy' ).each( function() {
					line += ( line == '' ? '' : '    ' ) + $(this).text();
				} );
				$('#time-copy').val( line );
				if( window.clipboardData ) {
					window.clipboardData.setData('text', line);
					row.addClass( 'completed' );
				}
				return false;
			}
			</script>

			<form action="job_control_detail.php" method="get" onsubmit="return Tasks.update();" id="task-list">
			
			<?php foreach ($task_groups as $group_key => $tasks) { ?>
			
				<p class="subheading"><?= $group_key == 'my' ? 'My Tasks' : 'Other Tasks' ?></p>
				<table width="1000" border="0" cellspacing="1" cellpadding="2" class="text admin_list">
					<thead>
						<tr>
							<td colspan="2">Task Description</td>
							<td>Ref&nbsp;#</td>
							<td title="Actual Hours">Act.&nbsp;Hrs.</td>
							<td title="Estimated Hours for Internal Use">Int.&nbsp;Est.</td>
							<td title="Estimated Hours for External Use">Ext.&nbsp;Est.</td>
							<td>Mode</td>
							<td>Assigned&nbsp;To</td>
							<td align="center">Phase</td>
							<td align="center">Due Date</td>
							<!-- <td align="center">Completed</td> -->
							<td><br></td>
							<td><br></td>
							<td><br></td>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($tasks as $task) { ?>
						<tr id="task-<?= $task['job_task_id'] > 0 ? $task['job_task_id'] : 'template' ?>"
							class="odd task<?= $task['completed'] != null ? ' completed' : '' ?>"
							title="Created on <?= date('d-M-Y', strtotime($task['created_on'])) ?> by <?= ucwords($task['created_by']) ?><?= $task['cr_description'] ? htmlspecialchars("\n" . $task['cr_description']) : '' ?>">
							<td colspan="2" class="detail">
								<input type="hidden" name="task_id" value="<?= $task['job_task_id'] ?>">
								<label><input type="checkbox" name="completed" value="Yes" onchange="Tasks.complete(this)"<?= ($task['completed'] != null) ? ' checked' : '' ?> <?= (false && $task['cr_impact_cost'] == '' && $task['job_task_number'] != '')? 'disabled' : '' ?>><span class="description"><?= htmlspecialchars($task['description']) ?></span></label>
							</td>
							<td class="code"><span class="ref_link" style="font-weight:bold; <?= $task['cr_impact_cost'] == ''? 'color:#F7A902;' : 'color:#00ff00;' ?>"><?= $task['job_task_number'] ?></span></td>
							<td><?= $task['total'] > 0 ? formatMinutes($task['total']) : '' ?></td>
							<td class="estimate"><?= $task['estimate'] ? formatMinutes($task['estimate']) : '<br>' ?></td>
							<td class="quote"><?= $task['quote'] ? formatMinutes($task['quote']) : '<br>' ?></td>
							<td class="chargeable"><?= $task['chargeable'] == 2 ? 'F/C' : ($task['chargeable'] ? 'T&M' : 'N/C') ?></td>
							<td class="employee"><?= htmlspecialchars($task['employee']) ?></td>
							<td class="status <?= strtolower($task['status']) ?>" align="center"><?= $task['status'] ?></td>
							<td class="due_date" align="center"><?= $task['due_date'] ? date('d-M-Y', strtotime($task['due_date'])) : '' ?></td>
							<!-- <td class="completed" align="center"><?= $task['completed'] ? date('d-M-Y', strtotime($task['completed'])) : '' ?></td> -->
							<td align="center"><a href="#" onclick="return Tasks.start(this);" style="display:block;color:#b9e9ff" class="start">start</a></td>
							<td align="center"><a href="#" onclick="return Tasks.edit(this);" style="display:block;color:#b9e9ff">edit</a></td>
							<td align="center"><a href="job_task_detail.php?job_task_id=<?= $task['job_task_id'] ?>" style="display:block;color:#b9e9ff">details</a></td>
							<td align="center"><a href="#" onclick="return Tasks.remove(this);" style="display:block;color:#b9e9ff">delete</a></td>
						</tr>
					<?php } ?>
					<?php if ($group_key == 'other') { ?>
						<tr class="task task-edit" id="task-edit">
							<td class="label">Quick&nbsp;Add:</td>
							<td style="width:90%;">
								<input type="hidden" name="task_id" value="">
								<input type="text" name="description" value="" style="width:99%;" class="smallblue" maxlength="1000" title="Task Description">
							</td>
							<td><input type="text" name="code" value="" size="6" class="smallblue" maxlength="32" title="Task Reference Number"></td>
							<td><br></td>
							<td><input type="text" name="estimate" value="" size="4" class="smallblue" maxlength="8" title="Estimated Hours for Internal Use"></td>
							<td><input type="text" name="quote" value="" size="4" class="smallblue" maxlength="8" title="Estimated Hours for External Use"></td>
							<td><select name="chargeable" class="black">
									<option value="T&M" title="Time and Materials">T&M</option>
									<option value="F/C" title="Fixed Cost">F/C</option>
									<option value="N/C" title="No Charge">N/C</option>
								</select></td>
							<td><select name="employee" class="black"><?= $employees ?></select></td>
							<td><select name="status" class="black">
								<?php foreach (CapsApi::get_issue_status_options('') as $k => $v) { ?>
									<option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($v) ?></option>
								<?php } ?>
								</select></td>
							<td class="due_date" nowrap colspan="2"><?php

								include_once( "includes/DateField.php" );
								$date_field = new DateField( "due_date", '' );
								echo $date_field->getHTML();

								?></td>
							<td colspan="3"><input type="submit" value="Save" class="smallbluebutton" onclick="return Tasks.update(this)"></td>
						</tr>
						<tr><td colspan="3">
							<input type="button" onclick="window.location='job_task_detail.php?job_id=<?= $job_id ?>'" class="smallbluebutton" value="Add New Task">
							<span id="task-move-open">&nbsp; <input type="button" onclick="return Tasks.moveDisplay()" class="smallbluebutton" value="Move or copy remaining tasks to another job"></span>
						</td></tr>
						<tr id="task-move" style="display:none;">
							<td class="label">Job:</td>
							<td><select name="task_move" class="smallblue" style="width:99%;">
								<option value=""></option>
							<?php foreach ($open_jobs as $id => $name) { ?>
								<option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
							<?php } ?>
							</select></td>
							<td colspan="3"><input type="button" value="Move" class="smallbluebutton" onclick="return Tasks.move(this)"> <input type="button" value="Copy" class="smallbluebutton" onclick="return Tasks.copy(this)"></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			<?php } ?>
			</form>
			<script type="text/javascript">

			window.onbeforeunload = function(e) {
				e = e || window.event;
				if( $('#start_time').val() != '' ) {
					var message = 'You are currently recording time in the time log.\n\nAny changes will be lost if you leave the page.';
					alert(message);
					if (e)
						e.returnValue = message;
					return message;
				}
			}
			
			var Tasks = {

				// Start the time list for the task
				start: function( el ) {

					<?php if ($job_closed) { ?>
					if (confirm('This job has been closed so no additional time should be recorded against it.\n\nAre you sure you want to start recording time?')) {
						$('#job-log').slideDown();
					} else {
						return false;
					}
					<?php } ?>
					
					var row = $(el).parents( '.task' );
					var now = new Date();
					var minutes = '00' + ( Math.floor( now.getMinutes() / 15 ) * 15 );
					var field = $('#start_time');
					if( field.val() != '' ) {
						alert('You are already recording time.  Please complete the current time log before starting a new one.');
						return false;
					}
					field.val( now.getHours() + ':' + minutes.substr( -2 ) );
					field.parents('form').find( 'select[name="task"]' ).val( row.find( 'input[name="task_id"]' ).val() );
					return false;
				},
				
				// Update the task details
				update: function( el ) {

					// If no element provided then add new task
					if( el == null )
						el = $('#task-edit .black');
					var row = $(el).parents( '.task' );
					var crRow = row.next();

					// Get request details
					var task = {
						'job_id': '<?= $job_id ?>',
						'update_task': 'true',
						'task_id': row.find( 'input[name="task_id"]' ).val(),
						'description': row.find( 'input[name="description"]' ).val(),
						'code': row.find( 'input[name="code"]' ).val(),
						'estimate': row.find( 'input[name="estimate"]' ).val(),
						'quote': row.find( 'input[name="quote"]' ).val(),
						'chargeable': row.find( 'select[name="chargeable"]' ).val(),
						'employee': row.find( 'select[name="employee"]' ).val(),
						'due_date': row.find( 'input[name="due_date"]' ).val(),
						'status': row.find( 'select[name="status"]' ).val()
					};

					// Send off the request
					jQuery.post(
						'job_control_detail.php',
						task,
						function( response ) {

							// Create the display mode row
							var newRow = $('#task-template').clone( true );
							newRow.attr( 'id', 'task-' + response.task_id );
							newRow.find( 'input[name="task_id"]').val( response.task_id );
							newRow.find( '.description' ).text( response.description );
							newRow.find( '.code' ).text( response.code );
							newRow.find( '.estimate' ).text( response.estimate );
							newRow.find( '.quote' ).text( response.quote );
							newRow.find( '.chargeable' ).text( response.chargeable );
							newRow.find( '.employee' ).text( response.employee );
							newRow.find( '.due_date' ).text( response.due_date );
							newRow.find( '.status' ).text( response.status );
							newRow.find( '.completed' ).text( response.completed ? response.completed : '' );
							newRow.attr( 'title', response.title );
							newRow.find( 'a' ).each(function() {
								$(this).attr('href', $(this).attr('href').replace('-1', response.task_id));
							});

							// If this was a new task, add the row
							if( task.task_id == '' ) {
								$('#task-template').before( newRow );
								$('#task-edit input[type="text"]').val( '' );

							// If this was an existing task, replace the row
							} else {
								row.replaceWith( newRow );
							}
						
							Tasks.refreshList();
							
						},
						'json'
					);
					
					return false;
				},
				
				// Flag a task as completed
				complete: function( el ) {
					var row = $(el).parents( '.task' );
					var task = {
						'update_task': 'true',
						'task_id': row.find( 'input[name="task_id"]' ).val(),
						'completed': row.find( 'input[name="completed"]:checked' ).val() == 'Yes' ? 'Yes' : 'No'
					};
					row[ task.completed == 'Yes' ? 'addClass' : 'removeClass' ]( 'completed' );
					row.find( '.completed' ).text( task.completed == 'Yes' ? '<?= date('d-M-Y') ?>' : '' );
					jQuery.post(
						'job_control_detail.php',
						task
					);
					return true;
				},
				
				// Edit a task
				dateCount: 1,
				edit: function( el ) {
					var t = this;

					// Get the task details
					var row = $(el).parents( '.task' );
					var task = {
						'update_task': 'true',
						'task_id': row.find( 'input[name="task_id"]' ).val(),
						'get': 'YES'
					};

					// Send off the request
					jQuery.post(
						'job_control_detail.php',
						task,
						function( task ) {
							// Convert row to editable format
							var editRow = $('#task-edit').clone(true);

							editRow.find( '.due_date input' ).attr( 'id', 'DueDateField' + t.dateCount ).val( task.due_date );
							editRow.find( '.due_date a' ).attr( 'id', 'DueDateLink' + t.dateCount );
							editRow.find( '.due_date input, .due_date a' ).each( function() {
								this.onclick = function() {
									oCaldue_date.select( this.id.replace( 'Link', 'Field' ), this.id.replace( 'Field', 'Link' ), 'dd-NNN-yyyy' );
									return false;
								};
							} );
							editRow.attr( 'id', '' );
							editRow.find( '.label' ).remove();
							editRow.find( 'input[name="task_id"]' ).val( task.task_id ).parent().attr( 'colspan', 2 );
							editRow.find( 'input[name="description"]' ).val( task.description );
							editRow.find( 'input[name="code"]' ).val( task.code );
							editRow.find( 'input[name="estimate"]' ).val( task.estimate );
							editRow.find( 'input[name="quote"]' ).val( task.quote );
							editRow.find( 'select[name="chargeable"]').val( task.chargeable );
							editRow.find( 'select[name="employee"]').val( task.employee );
							editRow.find( 'select[name="status"]').val( task.status );

							/*
							if(task['code'] != ''){
								var crRow = $( '#cr-edit' ).clone(true);
								crRow.find( 'textarea[name="cr_reason"]').html( task.cr_reason );
								crRow.find( 'textarea[name="cr_impact_page"]').html( task.cr_impact_page );
								crRow.find( 'textarea[name="cr_impact_functionality"]').html( task.cr_impact_functionality );
								crRow.find( 'textarea[name="cr_impact_site"]').html( task.cr_impact_site );
								crRow.find( 'textarea[name="cr_impact_third_party"]').html( task.cr_impact_third_party );
								crRow.find( 'textarea[name="cr_impact_cost"]').html( task.cr_impact_cost );
								crRow.find( 'textarea[name="cr_description"]').html( task.cr_description );
								crRow.find( 'input[name="cr_requester"]').val( task.cr_requester );
								crRow.find( 'input[name="cr_page_affected"]').val( task.cr_page_affected );

								crRow.css('display','table-row');
								row.replaceWith( editRow.add(crRow) );
							}else{
								row.replaceWith( editRow );
							}
							*/
							row.replaceWith( editRow );
							editRow.find( 'input[name="description"]' ).focus();

							t.dateCount++;

						},
						'json'
					);

					return false;

					/*var task = {
						'id': row.find( 'input[name="task_id"]' ).val(),
						'description': row.find( '.description' ).text(),
						'code': row.find( '.code' ).text(),
						'estimate': row.find( '.estimate' ).text(),
						'quote': row.find( '.quote' ).text(),
						'chargeable': row.find( '.chargeable' ).text(),
						'employee': row.find( '.employee' ).text(),
						'due_date': row.find( '.due_date' ).text(),
						'status': row.find( '.status' ).text(),
					};*/


				},

				// Displays the move interface
				moveDisplay: function( el ) {
					$('#task-move').show();
					$('#task-move-open').hide();
					return false;
				},

				// Performs a specific action on remaining tasks
				actionRemaining: function( action, remove ) {
					var job_id = $('#task-move select').val();
					if( job_id == '' ) {
						alert( 'Please choose the job the remaining tasks should be moved to.' );
						return false;
					}

					$('.task input[type="checkbox"]:not(:checked)').each( function() {
						var row = $(this).parents( '.task' );
						var task = {
							'update_task': 'true',
							'task_id': row.find( 'input[name="task_id"]' ).val(),
							'job_id': job_id
						};
						task[action] = 'true';
						if( task.task_id > 0 ) {
							jQuery.post(
								'job_control_detail.php',
								task
							);
							if( remove )
								row.remove();
						}
					} );

					alert('Done');
					
					Tasks.refreshList();
					return false;
				},

				// Copies the remaining tasks to another job
				copy: function() {
					return this.actionRemaining( 'copy', false );
				},

				// Moves the remaining tasks to another job
				move: function() {
					return this.actionRemaining( 'move', true );
				},
				
				// Remove a task
				remove: function( el ) {
					if (!confirm('Are you sure you want to remove this task?'))
						return false;
					var row = $(el).parents( '.task' );
					var task = {
						'update_task': 'true',
						'task_id': row.find( 'input[name="task_id"]' ).val(),
						'delete': 'true'
					};
					jQuery.post(
						'job_control_detail.php',
						task
					);
					row.remove();
					Tasks.refreshList();
					return false;
				},

				// View the task
				view: function( el ) {
					var row = $(el).parents( '.task' );
					window.location = 'job_task_detail.php?job_task_id=' + row.find('input[name="task_id"]').val();
					return false;
				},

				// Refreshes the time keeping task options
				refreshList: function() {
					var select = $('#job-log select[name="task"]');
					var existing = select.val();
					select.html('');
					var options = [ { 'value': '', 'text': '- select task -' } ];
					$('#task-list input[name="task_id"]').each(function() {
						var field = $(this);
						if (field.val())
							options.push( { 'value': field.val(), 'text': $.trim(field.parent().text()) + ' [' + field.parent().parent().find('.employee').text() + ']' } );
					});
					$.each(options, function() {
						select.append( $('<option></option>').val( this.value ).text( this.text ) );
					});
					select.val( existing );
				}
				
			};
			
			$(document).ready( function() {
				Tasks.refreshList();
			<?php if (array_safe($_REQUEST, 'start_task', '') != '') { ?>
				$('#task-<?= $_REQUEST['start_task'] ?> .start').click();
			<?php } ?>
			} );
			</script>
			
			<p class="subheading">Recent Comments</p>
			
			<table width="1000" border="0" cellspacing="1" cellpadding="2" class="text admin_list">
			<?php
				$count = 1;
				$comments = CapsApi::project_comments($job_id);
				$full_names = CapsApi::get_user_options();
				foreach ($comments as $comment) {
					echo '<tr valign="top" class="' . ($count % 2 == 0 ? 'even' : 'odd') . '">';
					echo '<td width="30" class="text">' . $count++ . '.</td>';
					echo '<td class="text">';
					echo nl2br(htmlspecialchars($comment['body'])) . '<br>';
					echo '<br>';
					echo 'in <a href="job_task_detail.php?job_task_id=' . $comment['job_task_id'] . '" style="color:#fcfcfc;">' . $comment['description'] . '</a>';
					echo '</td>';
					echo '<td class="text" width="220">';
					echo date('d-M-Y h:ia', strtotime($comment['created_on'])) . ' by ' . array_safe($full_names, $comment['created_by'], $comment['created_by']) . '<br>';
					echo '</td>';
					echo '</tr>' . "\n";
				}
				if ($count == 1)
					echo '<tr><td>No comments</td></tr>';
			?>
			</table>
			
      <p class="subheading">Details</p>
      <table width="650" border="0" cellspacing="0" cellpadding="0">
        <form action="job_control_detail.php" method="post">
          <input name="job_id" type="hidden" value="<?php echo $job_id; ?>">
          <input name="job_assign_id" type="hidden" value="<?php echo $job_assign_id; ?>">
          <tr class="text">
            <td width="155" class="text">Job Title</td>
            <td width="488" class="text"><input name="job_title" type="text" class="black" value="<?php echo $job_title; ?>" size="80">
            </td>
          </tr>
          <tr>
            <td class="text">Requested Completion Date</td>
            <td class="text"><?php echo $due_date ? date( "d-M-Y", strtotime( $due_date ) ) : ''; ?></td>
          </tr>
          <tr>
            <td class="text">Estimated Completion Date</td>
            <td class="text"><?php echo $est_completion ? date( "d-M-Y", strtotime( $est_completion ) ) : ''; ?><img src="images/spacer.gif" width="41" height="11">&gt;
              <a href="change_completion_date.php?job_id=<?php echo $job_id; ?>&job_number=<?php echo $job_number; ?>"><font color="B9E9FF">change
              estimated completion date</font></a></font></td>
          </tr>
          <tr valign="top">
            <td colspan="2" class="text"><img src="images/spacer.gif" width="10" height="11"><br>
              <table border="0" cellspacing="0" cellpadding="0" class="text"><tbody>
                <tr><td>Job Specification</td><td style="text-align:right"><a href="#" id="job_details_expand" onclick="return toggleExpand('job_details')" style="color:#b9e9ff">expand</a></td></tr>
                <tr><td colspan="2"><textarea name="job_details" cols="110" rows="10" expandrows="30" class="smallblue" id="job_details" onfocus="toggleExpand('job_details',true)"><?= htmlspecialchars( $job_details ) ?></textarea></td></tr>
              </tbody></table>
            </td>
          </tr>
          <tr valign="top">
            <td colspan="2" class="text"> <p><img src="images/spacer.gif" width="10" height="11"><br>Attachments:
              <?php
			  echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"5\">";
			  echo "<tr>";
			  while ($row5=mysql_fetch_object($result5)) {
			    $attach_id = $row5->attach_id;
			    $attachment = $row5->file_name;
			    $attach_date = $row5->file_date;
				$desc = $row5->description;
				//get the file type
				$ftype = strtolower(substr($attachment, -3));
				if ($ftype == "jpg")
					$im = "<img name='$attachment' src='images/jpg.gif' border=\"0\">";
				elseif ($ftype == "gif")
					$im = "<img name='$attachment' src='images/gif.gif' border=\"0\">";
				elseif ($ftype == "doc")
					$im = "<img name='$attachment' src='images/word.gif' border=\"0\">";
				elseif ($ftype == "xsl")
					$im = "<img name='$attachment' src='images/excel.gif' border=\"0\">";
				elseif ($ftype == "pdf")
					$im = "<img name='$attachment' src='images/pdf.gif' border=\"0\">";
				elseif ($ftype == "txt" || $ftype == "php")
					$im = "<img name='$attachment' src='images/txt.gif' border=\"0\">";
				else
					$im = "";
				//display the attachments
				echo "<td class=text width=100><a href=\"attachments/$attachment\" target=\"_blank\" style=\"color:#b9e9ff;display:block;\">$im<br>$attachment<br></a>$attach_date<br>$desc<br><a href=\"job_control_detail.php?event=delatt&client_id=$client_id&job_id=$job_id&attachment=$attachment\" onClick=\"return confirmDelete()\"><font color=\"#B9E9FF\">delete</font></a></td>";
			  }
			  echo "</tr></table>";
			  ?>
                <br>
                <br>
                <img src="images/spacer.gif" width="10" height="11"><br>
                Contacts for this job:<br>
                Primary:
                <select name="p_contact" size="1" class="black">
                  <option selected><?php echo $p_contact; ?></option>
                  <?php echo $mycontacts; ?>
                </select>
                <img src="images/spacer.gif" width="18" height="12">Secondary:
                <select name="s_contact" size="1" class="black">
                  <option selected><?php echo $s_contact; ?></option>
                  <?php echo $mycontacts; ?>
                </select>
                <img src="images/spacer.gif" width="18" height="12">Tertiary:
                <select name="o_contact" size="1" class="black">
                  <option selected><?php echo $o_contact; ?></option>
                  <?php echo $mycontacts; ?>
                </select>
                <br>
                <br>
                <img src="images/spacer.gif" width="10" height="11"><br>
                Billing and Budget Tracking:<br>
                <input type="radio" name="billing_method" value="open" <?php echo $is_open; ?>>
                Open hourly rate<br>
                <input type="radio" name="billing_method" value="hours" <?php echo $is_hours; ?>>
                Fixed number of hours, being:
                <input name="billing_hours1" type="text" class="black" size="2" value="<?php echo $billing_hours1; ?>">
                <br>
                <input type="radio" name="billing_method" value="fixed" <?php echo $is_fixed; ?>>
                Fixed price agreement <br>
                <input type="radio" name="billing_method" value="no charge" <?php echo $is_nocharge; ?>>
                No Charge, Enter budget hours
                <input name="billing_hours2" type="text" class="black" size="2" value="<?php echo $billing_hours2; ?>">
                <br>
                <input type="radio" name="billing_method" value="rd" <?php echo $is_rd; ?>>
                Research &amp; Development <img src="images/spacer.gif" width="10" height="11"><br>
              </p>
              <p>Assignments for this job:</p>
              Project Manager:
              <select name="project_manager" class="black" size="1">
                <option selected><?php echo $project_manager; ?></option>
                <?php echo $mystaff; ?> </select> <br><br>
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
				
				<br>
				API Settings:<br>
				<label><input type="checkbox" name="api_hide" value="show"<?= $api_hide ? ' checked' : '' ?>> Hide from "All Tasks" filter</label>
                 
            </td>
          </tr>
          <tr>
            <td colspan="2" class="text"><div align="left"><img src="images/spacer.gif" width="10" height="11"><br>
                <input type="submit" name="update_job" value="Update Job" class="smallbluebutton">
              </div></td>
          </tr>
        </form>
        <tr>
          <td colspan="2" class="text">&nbsp;</td>
        </tr>
      </table>
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td colspan="2" class="text"><p class="subheading">Additional Actions</p></td>
        </tr>
      </table>
      <table width="650" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td colspan="2" class="text">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="2" class="text">
            <a href="close_job.php?client_id=<?php echo $client_id; ?>&job_id=<?php echo $job_id; ?>"><font color="B9E9FF">To close this job, click here</font></a><br>
            <a href="delete_job.php?client_id=<?php echo $client_id; ?>&job_id=<?php echo $job_id; ?>"><font color="B9E9FF">To delete this job, click here</font></a>
          </td>
        </tr>
        <tr>
          <td colspan="2" class="text">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="2" class="text">Add More Attachments:
            <form action="upload_attachments.php" method="post" enctype="multipart/form-data">
              <input name="job_id" type="hidden" value="<?php echo $job_id; ?>">
              <input name="client_id" type="hidden" value="<?php echo $client_id; ?>">
              <input name="direct_to" type="hidden" value="job_control_detail.php">
              File:
              <input name="userfile[]" type="file" class="black">
              &nbsp;Description:
              <input name="description[]" type="text" size="30" class="black">
              <br>
              File:
              <input name="userfile[]" type="file" class="black">
              &nbsp;Description:
              <input name="description[]" type="text" size="30" class="black">
              <br>
              File:
              <input name="userfile[]" type="file" class="black">
              &nbsp;Description:
              <input name="description[]" type="text" size="30" class="black">
              <br>
              <input class="smallbluebutton" type="submit" name="upload" value="Upload Attachments">
            </form></td>
        </tr>
      </table>
      <p><img src="images/spacer.gif" width="233" height="14"></p>
      <p>&nbsp;</p></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td valign="top" class="text"> <p><img src="images/spacer.gif" width="233" height="14"></p></td>
    <td width="76%" valign="top" class="text"><p>&nbsp;</p></td>
  </tr>
</table>
<? include 'footer.php' ?>
</body>
</html>
