<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* attach template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 8/8/2003
* Client: WorldWeb, Domain: database intranet
* This template consists of 1 section: HTML form for adding attachments
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
//establish a persistent connection and get all required data.
// select valuations for  valuer
$db = mysql_pconnect ($hostName, $userName, $password);
mysql_select_db($database);

//grab the new job_id from db and add it here
$query = "SELECT max(job_id) AS job_id FROM jobs WHERE client_id = '$client_id'";
$result = mysql_query ($query, $db);
$row = mysql_fetch_object($result);
$job_id = $row->job_id;

//select some details for this job
$query1 = "SELECT job_number FROM jobs WHERE job_id = '$job_id'";
$result1 = mysql_query ($query1, $db);
//get job number for the job
$row1 = mysql_fetch_object($result1);
$job_number = $row1->job_number;

//begin general display of information
$query2 = "SELECT client_name, trading_name, agreement_number, website_url, status FROM clients WHERE client_id = '$client_id'";
$result2 = mysql_query ($query2, $db);
$row2 = mysql_fetch_object($result2);
$client_name = $row2->client_name;
$trading_name = $row2->trading_name;
$agreement_number = $row2->agreement_number;
$website_url = $row2->website_url;
$status = $row2->status;

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
    <td colspan="2" class="text"><u>Detail: Job Number <?php echo $job_number; ?></u></td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
    <td colspan="2" valign="middle" class="text"><img src="images/spacer.gif" width="39" height="10"><br> 
	Add Attachments For This Job:<br>
		<form action="upload_attachments.php" method="post" enctype="multipart/form-data">
		<input name="job_id" type="hidden" value="<?php echo $job_id; ?>"><br>
		<input name="client_id" type="hidden" value="<?php echo $client_id; ?>"><br>
		<input name="direct_to" type="hidden" value="job_control.php"><br>
		File: <input name="userfile[]" type="file" class="black">&nbsp;Description: <input name="description[]" type="text" size="30" class="black"><br>
		File: <input name="userfile[]" type="file" class="black">&nbsp;Description: <input name="description[]" type="text" size="30" class="black"><br>
		File: <input name="userfile[]" type="file" class="black">&nbsp;Description: <input name="description[]" type="text" size="30" class="black"><br>
		<input class="smallbluebutton" type="submit" name="upload" value="Upload Attachments / Commit">
		</form>
      <p></p>
      <p><br><img src="images/spacer.gif" width="39" height="10"></p></td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
    <td valign="top" class="text"> 
      <p><img src="images/spacer.gif" width="233" height="14"></p>
      </td>
    <td width="76%" valign="top" class="text"><p>&nbsp;</p></td>
  </tr>
</table>
</body>
</html>
