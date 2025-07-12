<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* delete_client template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 16/2/2003
* Client: WorldWeb, Domain: database intranet
* This template consists of 3 sections: SQL Queries & formatting, HTML client form directed to itself
* Important files included in this template are: header.inc (head section and javascript library),
* globals.php (connection string),
* Last Modified: 16/2/2004 By Aviv Efrat
* Please remember to backup template before modifying
*
*********************************************************************************************************
*/
//begin session
session_start();
//define title
$title = "WorldWeb Internal Database";
//include a globals file for db connection
include_once("includes/globals.php");
//include a php function library file
include_once("includes/functions.php");
//define date
$today = date("d/m/Y" ,time());

//establish a persistent connection to mysql
$db = mysql_pconnect ($hostName, $userName, $password);
mysql_select_db($database);

$myform = "<form action=\"delete_client.php\" method=\"post\">\n";
$myform .= "<input name=\"client_id\" type=\"hidden\" value=\"$client_id\">\n";
$myform .= "Please type 'delete' in text box if you wish to delete this client:<br>";
$myform .= "<input name=\"deleteclient\" type=\"text\" class=\"black\" size=\"8\">&nbsp;";          
$myform .= "<input class=\"smallbluebutton\" type=\"submit\" name=\"delete\" value=\"Commit\"></form>";          
        
//event->delete --------------------when user confirm deletion of client--------------------------------
/*
we actually not deleting this client. Instead we are setting its status to d
clients with a status of d would not be displayed or queried.
note: some code has been modified so technical, contact and domain records remain in db
*/
if (isset($delete) & $deleteclient == "delete") {
	//delete from clients
	$query = "UPDATE clients SET status = 'd' WHERE client_id='$client_id'";
	$result = mysql_query ($query, $db);
	//check for errors
	$err = mysql_error();
	if ($err) {
		$message = "There is an error deleting: $err";
	} else {
		$message = "Client record successfuly marked as Deleted.";
	}
	//delete from contacts table
	/*
	$query1 = "DELETE FROM contacts WHERE client_id='$client_id'";
	$result1 = mysql_query ($query1, $db);
	//check for errors
	$err1 = mysql_error();
	if ($err1) {
		$message .= "<br>There is an error deleting contacts table record: $err1";
	} else {
		$message .= "<br>Contacts records successfuly Deleted.";
	}
	
	//delete from contracts table
	$query2 = "DELETE FROM technical WHERE client_id='$client_id'";
	$result2 = mysql_query ($query2, $db);
	//check for errors
	$err2 = mysql_error();
	if ($err2) {
		$message .= "<br>There is an error deleting technical table record: $err2";
	} else {
		$message .= "<br>technical record successfuly Deleted.";
	}
	*/
	//delete from vusers table
	$query1 = "UPDATE vusers SET status = 'd' WHERE client_id='$client_id'";
	$result1 = mysql_query ($query1, $db);
	//check for errors
	$err1 = mysql_error();
	if ($err1) {
		$message .= "<br>There is an error deleting vusers table record: $err1";
	} else {
		$message .= "<br>vusers record successfuly marked as Deleted.";
	}

	//delete from domain table
	/*
	$query4 = "DELETE FROM domains WHERE client_id='$client_id'";
	$result4 = mysql_query ($query4, $db);
	//check for errors
	$err4 = mysql_error();
	if ($err4) {
		$message .= "<br>There is an error deleting domains table record: $err4";
	} else {
		$message .= "<br>domains record successfuly Deleted.";
	}
	*/
	$myform = $message;
//end delete routine
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

//close connection
mysql_close($db);

//include a header file
include_once("includes/header.inc");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
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
    <td class="text"><u>Delete Client <br>
      <img src="images/spacer.gif" width="39" height="10"> </u></td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
    <td align="left" valign="top" class="text"> 
      <table width="600" border="0" align="left" cellpadding="3" cellspacing="3">
        <?php echo $myform; ?>
      </table>
      <p>&nbsp;</p>
    </td>
  </tr>
</table>
</body>
</html>