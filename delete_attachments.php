<?php
session_start();

//include a globals file for db connection
include_once("includes/globals.php");

// open persistent connection
$db = mysql_connect ($hostName, $userName, $password);
mysql_select_db($database);

//delete the attachment
$myfile = $attachment;
$thefile = $attachDir . "/$myfile";
//if image exists write uploaded else write N/A
if (file_exists($thefile)) {unlink($thefile);}

//delete the entry from the attachment table
$query = "delete from attachments where file_name='$attachment' and job_id='$job_id'";
$result = mysql_query ($query, $db);

//refresh the page back to job_control_detail.php
$page = "job_control_detail.php?client_id=$client_id&job_id=$job_id";
echo "<SCRIPT LANGUAGE='JavaScript'>document.location.replace('$page');</SCRIPT>";
?>

