<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* change_completion_date template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 26/7/2003
* Client: WorldWeb, Domain: database intranet
* This template consists of 3 sections: Navigation, SQL Queries & formatting, HTML output
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

//make sure email is not being sent without update
$sendemail = "no";
$myerrors = "";

//event->update------------------when user clicks change completion date----------------------------------
if (isset($update)) {
	//format next contact
	$due_date = FromNextContact($myD);
	//update mysql db
	$query = "UPDATE jobs SET due_date='$due_date', change_reason='$change_reason' WHERE job_id='$job_id'";
	$result = mysql_query ($query, $db);
	//define a flag for email
	$sendemail = "yes";

	//if sendemail flag is set to yes send the email to all relevant contacts
	if ($sendemail == "yes") {
		//construct an array of emails
		$myemails = array("");
		array_push ($myemails, "chris$worldweb.net.au");
		//if ($p_email) {array_push ($myemails, $p_email);}
		//if ($s_email) {array_push ($myemails, $s_email);}
		//if ($o_email) {array_push ($myemails, $o_email);}
		if ($myemail1) {array_push ($myemails, $myemail1);}
		if ($myemail2) {array_push ($myemails, $myemail2);}
		if ($myemail3) {array_push ($myemails, $myemail3);}
		//for each email in the array send the email
		$num = count($myemails);
		for ($j=1;$j<$num;$j++) {
			// get email from array
			$mycontact = $myemails[$j];
			//who are you mailing to?
			$mailto = "$mycontact";

			//summarise form content and package it into variable 
			$form_summary = "
			date = $today
			The estimated completion date for the following job: $job_number
			undertaken by WorldWeb Management has been changed to: $due_date

			Reason for the change:
			$change_reason

			Worldweb appologises for any inconviniences.

			Kind Regards
			Worldweb Management";

			//email content
			$headers .= "From: Worldweb Web Site <support@worldwebms.com>\n";
			$subject_line = "Estimated completion change for WorldWeb job number $job_number";
			$ok =  mail("mailto", $subject_line, $form_summary, $headers);
			//test if email was sent and if not prepare an error message
			if (!$ok) {$myerrors = $myerrors . "could not send message to $mailto<br>";}
		//end sending emails
		}
	//end sendemail event
	}
	//end update return to job_detail page
	$page = "job_control_detail.php?client_id=$client_id&job_id=$job_id";
	echo "<script language=\"JavaScript\">document.location.replace('$page');</script>";
}

$query = "SELECT client_id, due_date, p_contact, s_contact, o_contact FROM jobs WHERE job_id = '$job_id'";
$result = mysql_query ($query, $db);
$row = mysql_fetch_object($result);
$client_id = $row->client_id;
$due_date = ToNextContact($row->due_date);
$p_contact = $row->p_contact;
$s_contact = $row->s_contact;
$o_contact = $row->o_contact;

//for each existing contact get their email
if ($p_contact) {list($p_fname, $p_sname) = split(' ',$p_contact);}
if ($s_contact) {list($s_fname, $s_sname) = split(' ',$s_contact);}
if ($o_contact) {list($o_fname, $o_sname) = split(' ',$o_contact);}
//get email address for the existing ones
if (!empty($p_contact)) {
	$query1 = "SELECT email FROM contacts WHERE client_id='$client_id' AND first_name='$p_fname' AND last_name='$p_sname'";
	$result1 = mysql_query ($query1, $db);
	$row1 = mysql_fetch_object($result1);
	$p_email = $row1->email;
}

if (!empty($s_contact)) {
	$query1 = "SELECT email FROM contacts WHERE client_id='$client_id' AND first_name='$s_fname' AND last_name='$s_sname'";
	$result1 = mysql_query ($query1, $db);
	$row1 = mysql_fetch_object($result1);
	$s_email = $row1->email;
}

if (!empty($o_contact)) {
	$query1 = "SELECT email FROM contacts WHERE client_id='$client_id' AND first_name='$o_fname' AND last_name='$o_sname'";
	$result1 = mysql_query ($query1, $db);
	$row1 = mysql_fetch_object($result1);
	$o_email = $row1->email;
}

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
    <td class="text"><u>Change Estimated Completion Date: Job Number <?php echo $job_number; ?></u></td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
    <td valign="top" class="text">
      <p>&nbsp;</p>
      <table width="616" border="0" cellspacing="0" cellpadding="0">
        <form action="change_completion_date.php" method="post">
          <input name="job_id" type="hidden" value="<?php echo $job_id; ?>">
          <tr class="text"> 
            <td colspan="5">Enter reason for the change of completion date:</td>
          </tr>
          <tr class="text"> 
            <td colspan="5"><textarea name="change_reason" cols="110" rows="6" class="smallblue"></textarea></td>
          </tr>
          <tr class="text"> 
            <td width="126"><img src="images/spacer.gif" width="32" height="12"><br>
              New Completion Date:</td>
            <td colspan="4">&nbsp;</td>
          </tr>
          <tr> 
            <td colspan="5"><?php
            
              include( "includes/DateField.php" );
              $date_field = new DateField( "myD", $due_date );
              echo $date_field->getHTML();

            ?></td>
          </tr>
          <tr class="instruction"> 
            <td colspan="5"><img src="images/spacer.gif" width="32" height="12"><br>
              Note: an auto-notification will be sent to the following email addresses:</td>
          </tr>
          <tr class="instruction"> 
            <td>chris@worldweb.net.au</td>
            <td colspan="4">&nbsp;</td>
          </tr>
          <?php
		if (!empty($p_contact)) {
			echo "<tr class=\"instruction\"><td>$p_email</td><td colspan=\"4\">&nbsp;</td></tr>";
		}
		if (!empty($s_contact)) {
			echo "<tr class=\"instruction\"><td>$s_email</td><td colspan=\"4\">&nbsp;</td></tr>";
		}
		if (!empty($o_contact)) {
			echo "<tr class=\"instruction\"><td>$o_email</td><td colspan=\"4\">&nbsp;</td></tr>";
		}
		?>
          <tr> 
            <td colspan="5" class="smalltext">To send notifications to additional 
              addresses, enter them below:</td>
          </tr>
          <tr> 
            <td colspan="5"><input name="myemail1" type="text" class="black" size="20"> 
              <img src="images/spacer.gif" width="8	" height="12"> <input name="myemail2" type="text" class="black" size="20"> 
              <img src="images/spacer.gif" width="8	" height="12"> <input name="myemail3" type="text" class="black" size="20"> 
            </td>
          </tr>
          <tr> 
            <td>&nbsp;</td>
            <td width="1">&nbsp;</td>
            <td width="56">&nbsp;</td>
            <td width="22">&nbsp;</td>
            <td width="411"><div align="right"> </div></td>
          </tr>
          <tr> 
            <td colspan="5"><input type="submit" name="update" value="Commit" class="smallbluebutton"></td>
          </tr>
          <tr> 
            <td colspan="5" class="text">&gt; <a href="job_control_detail.php?job_id=<?php echo $job_id; ?>"><font color="B9E9FF">cancel 
              action and return to job control detail page</font></a></td>
          </tr>
          <tr>
            <td colspan="5" class="text">
			<?php if (!empty($myerrors)) {echo $myerrors;} else {echo "message sent";} ?>
			</td>
          </tr>
        </form>
      </table>
      <p>&nbsp;</p>
      <p><u><br>
        <img src="images/spacer.gif" width="39" height="10"> </u></p></td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
    <td valign="top" class="text"> <p><img src="images/spacer.gif" width="233" height="14"></p></td>
    <td width="76%" valign="top" class="text"> <p> <br>
      </p></td>
  </tr>
</table>
</body>
</html>
