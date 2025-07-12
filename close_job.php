<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* close_job template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 8/8/2003
* Client: WorldWeb, Domain: database intranet
* This template consists of 1 section: HTML form for closing a job and sql update query
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

//break today into some useful variables to be inserted into date text box later
list($myd, $mym, $myy) = explode("/", $today);
//convert the month to characters
$mym = get_month($mym);

//establish a persistent connection and get all required data.
// select valuations for  valuer
$db = mysql_pconnect ($hostName, $userName, $password);
mysql_select_db($database);

if (isset($closeme)) {
	if ($closejob == "close") {
		//format closing date
		//concat the text boxes values to a date object
		$next_contact = "$myCLdate-$myCLmonth-$myCLyear";
		//format next contact
		$myclose_date = FromNextContact($next_contact);
		//delete the entry from the attachment table
		$query = "UPDATE jobs SET closing_person='".mysql_escape_string($fullname)."', update_date=NOW(), closing_date='$myclose_date', status='closed' WHERE job_id='$job_id' AND client_id='$client_id'";
		$result = mysql_query ($query, $db);
	} else {
		//do nothing
	}

	//refresh the page back to job_control_detail.php
	$page = "job_control.php?client_id=$client_id";
	echo "<SCRIPT LANGUAGE=\"JavaScript\">document.location.replace('$page');</SCRIPT>";
}

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

//close connection
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
    <td class="text"><u>Detail: Job Number <?php echo $job_number; ?></u></td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
    <td valign="middle" class="text" colspan="2"><img src="images/spacer.gif" width="39" height="10"><br>
	<p class="subheading">Close Job</p> 
      <form action="close_job.php" method="post">
		<input name="job_id" type="hidden" value="<?php echo $job_id; ?>">
		<input name="client_id" type="hidden" value="<?php echo $client_id; ?>">
        <br>
        Specify close date:<br>
        <input name="myCLdate" type="text" class="smallblue" size="2" maxlength="2" value="<?php echo $myd; ?>" onKeyUp="return autoTab(this, 2, event)"> 
        <input name="myCLmonth" type="text" class="smallblue" size="3" maxlength="3" value="<?php echo $mym; ?>" onChange="javascript:this.value=titleCase(this.value);return autoTab(this, 3, event);"> 
        <input name="myCLyear" type="text" class="smallblue" size="4" maxlength="4" value="<?php echo $myy; ?>" onKeyUp="return autoTab(this, 4, event)"><br>
        Please type "close" in text box if you wish to close this job:<br>
        <input name="closejob" type="text" class="black" size="8">
        <input class="smallbluebutton" type="submit" name="closeme" value="Commit">
		</form>
      <br><img src="images/spacer.gif" width="39" height="10"></td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
    <td valign="top" class="text"><img src="images/spacer.gif" width="233" height="14"></td>
    <td width="76%" valign="top" class="text">&nbsp;</td>
  </tr>
</table>
</body>
</html>
