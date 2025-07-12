<?php
session_start();

//include a globals file for db connection
include_once("includes/globals.php");

// open persistent connection
$db = mysql_pconnect ($hostName, $userName, $password);
mysql_select_db($database);

//define array of images
$number_of_files = array_key_exists('userfile', $_FILES) ? count($_FILES['userfile']['size']) : 0;

//main script please tread carefully
if (!ereg("/$", $attachDir))
     $attachDir = $attachDir."/";
//foreach ($files['name'] as $key=>$name) {
for($i=0;$i<=$number_of_files;$i++) {
 	if (!$_FILES['userfile']['size'][$i] == 0) {
    	//define name
		$tempfile = $_FILES['userfile']['tmp_name'][$i];
		$uploadfile = $_FILES['userfile']['name'][$i];
		//replace spaces with underscore
		$uploadfile = str_replace(" ", "_", $uploadfile);
		$location = $attachDir.$uploadfile;
		copy($tempfile, $location) or die('could not copy $location');
		//delete the tempfile
    	unlink($tempfile);
		//add entry to attachments table
		$attach_id = "";
		$query = "insert into attachments (job_id, file_name, file_date, description) VALUES ('$job_id', '$uploadfile', NOW(), '$description[$i]')";
		$result = mysql_query ($query, $db);
  	}
}

//close connection
mysql_close($db);

//refresh the page back to relevant job
if ($direct_to == "job_control.php") {
	$page = "job_control.php?client_id=$client_id";
} else {
	$page = "job_control_detail.php?client_id=$client_id&job_id=$job_id";
}
echo "<SCRIPT LANGUAGE=\"JavaScript\">document.location.replace('$page');</SCRIPT>";
