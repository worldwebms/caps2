<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* index1 template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 9/5/2003
* Client: WorldWeb, Domain: database intranet
* This template consists of 3 sections: Navigation, SQL Queries & formatting, HTML output
* Important files included in this template are: header.inc (head section and javascript library),
* globals.php (connection string), file_manager.php (file manipulation class).
* Last Modified: 12/1/2004 By Aviv Efrat
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
//include a file manager file
include_once("includes/file_manager.php");
//include a php function library file
include_once("includes/functions.php");
//define date
$today = date("d/m/Y" ,time());
$mytime = date("d/m/Y H:i" ,time());

//establish a persistent connection to mysql.
$db = mysql_pconnect ($hostName, $userName, $password);
mysql_select_db($database);

//general sql if users clicked the general link
$sql = "SELECT client_id, client_name, trading_name, agreement_number, rep_id, next_contact, address_1, address_2, suburb, post_code, state, postal_address_1, postal_address_2, postal_suburb, postal_state, postal_post_code, phone1, fax, email, old_log, website_url, status FROM clients WHERE client_id = '$client_id'";

//event -> ---------------------------------update event--------------------------------------------------
if (isset($update_date)) {
	//set mydateerror to nothing
	$mydateerror = "";
	//format next contact
	$next_contact = FromNextContact($next_contact);
	//check if the date is valid
	list($Y,$M, $D) = split('-',$next_contact);
	if (!is_numeric($Y)) {$mydateerror = $mydateerror . "year is incorrect $Y";}
	if (!is_numeric($M)) {$mydateerror = $mydateerror . "Month is incorrect a $M";}
	if (!is_numeric($D)) {$mydateerror = $mydateerror . "Date is incorrect $D";}
	if ($D < 1 | $D > 31) {$mydateerror = $mydateerror . "Date is incorrect $D";}
	if (!empty($mydateerror)) {
		echo "<script language=\"JavaScript\">alert('The month you entered is not valid');</script>";
	} else {
		$query1 = "UPDATE clients SET next_contact = '$next_contact' WHERE client_id = '$client_id'";
		$result1 = mysql_query($query1, $db);
		//check for errors
		$err1 = mysql_error();
		if ($err1) {$message = "There is an error updating: $err1";}
	}
	//generate general sql
	$sql = "SELECT client_id, client_name, trading_name, agreement_number, rep_id, next_contact, address_1, address_2, suburb, post_code, state, postal_address_1, postal_address_2, postal_suburb, postal_state, postal_post_code, phone1, fax, email, old_log, website_url, status FROM clients WHERE client_id = '$client_id'";
}

//event -> ---------------------------------update log event----------------------------------------------
if ($action_type == "add_to_log") {
	//prepare data and add entry to log.
	$data = "";
	$data .= "Entry: $mytime by: $fullname\n";
	//run a strip slashes routine
	$new_content = stripslashes($new_content);
	$data .= "$new_content\n";

	//define log file
	$log_file = $client_id . "log.txt";
	$filetouse = $logDir."/$log_file";

	if (file_exists($filetouse)) {
		//initiate file class
		$TheFile = new file_manager($filetouse);  //Creates Object
		$TheFile->append($data);
	} else {
		//create the log and add first entry
		$PATH="includes/log.txt";
		$new_location = $filetouse;
		//initiate class
		$TheFile = new file_manager($PATH);  //Creates Object
		$TheFile->write($data); //Write it now
		$TheFile->copyto($filetouse);
	}
	//write a message
	$message .= "<br>Log updated successfuly";
	//generate general sql
	$sql = "SELECT client_id, client_name, trading_name, agreement_number, rep_id, next_contact, address_1, address_2, suburb, post_code, state, postal_address_1, postal_address_2, postal_suburb, postal_state, postal_post_code, phone1, fax, email, old_log, website_url, status FROM clients WHERE client_id = '$client_id'";
	//set action type to nothing
	$action_type = "";
}

//event -> ---------------------------------update log event----------------------------------------------
if ($action_type == "rewrite_log") {
	//fix log content
	$log_content = stripslashes($log_content);
	//define log file
	$log_file = $client_id . "log.txt";
	$filetouse = "$logDir/$log_file";
	$PATH = $filetouse;
	if (file_exists($filetouse)) {
		//delete file
		unlink($filetouse);
		//write it again
		$MYPATH="includes/log.txt";
		$TheFile = new file_manager($MYPATH);
		$TheFile->write($log_content); //Write it now
		$TheFile->copyto($filetouse);
	}
	//write a message
	$message .= "<br>Log updated successfuly";
	//generate general sql
	$sql = "SELECT client_id, client_name, trading_name, agreement_number, rep_id, next_contact, address_1, address_2, suburb, post_code, state, postal_address_1, postal_address_2, postal_suburb, postal_state, postal_post_code, phone1, fax, email, old_log, website_url, status FROM clients WHERE client_id = '$client_id'";
	//set the action to nothing
	$action_type = ""; 
}

//event -> ---------------------------------Find client event--------------------------------------------------
if ($event == "FindClient") {
	//generate appropriate sql
	$sql = "SELECT client_id, client_name, trading_name, agreement_number, rep_id, next_contact, address_1, address_2, suburb, post_code, state, postal_address_1, postal_address_2, postal_suburb, postal_state, postal_post_code, phone1, fax, email, old_log, website_url, status FROM clients WHERE client_id = '$client_id'"; 
}

//event -> ---------------------------------search event--------------------------------------------------
if (isset($mysearch)) {
	//find the type of searh
	switch ($search_type) {
		case "company":
			$sql = "SELECT client_id, client_name, trading_name, agreement_number, rep_id, next_contact, address_1, address_2, suburb, post_code, state, postal_address_1, postal_address_2, postal_suburb, postal_state, postal_post_code, phone1, fax, email, website_url, status FROM clients WHERE client_name = '$mysearch' OR trading_name = '$mysearch'";
			break;
		case "reference":
			$sql = "SELECT client_id, client_name, trading_name, agreement_number, rep_id, next_contact, address_1, address_2, suburb, post_code, state, postal_address_1, postal_address_2, postal_suburb, postal_state, postal_post_code, phone1, fax, email, website_url, status FROM clients WHERE agreement_number = '$mysearch'";
			break;
		case "domain":
			$sql = "SELECT client_id, client_name, trading_name, agreement_number, rep_id, next_contact, address_1, address_2, suburb, post_code, state, postal_address_1, postal_address_2, postal_suburb, postal_state, postal_post_code, phone1, fax, email, website_url, status FROM clients WHERE website_url LIKE '%$mysearch%'";
			break;
		case "contact":
			//send the page to new page called contact_list.php
			$page = "contact_list.php?mysearch=$mysearch";
			echo "<SCRIPT LANGUAGE=\"JavaScript\">window.location.replace('$page')</script>";
			break;
		default: 	//no default for this job
			$sql = "";
	}
//end of search event
}

if (empty($sql)) {
	//do nothing
} else {
	//get result and format output
	$result = mysql_query($sql, $db);
	$num_rows = mysql_num_rows($result);
	//if there are no matches found go to index2.php and suggest
	if ($num_rows == 0) {
		//chop my search to first 5 characters
		$mysearch = substr($mysearch, 0, 5);
		$page = "client_list.php?mysearch=$mysearch";
		echo "<SCRIPT LANGUAGE=\"JavaScript\">window.location.replace('$page')</script>";
	} else {
		$row_result = mysql_fetch_object($result);
		//extract the data
		$client_id = $row_result->client_id;
		$client_name = $row_result->client_name;
		$trading_name = $row_result->trading_name;
		$agreement_number = $row_result->agreement_number;
		$rep_id = $row_result->rep_id;
		//format next contact
		$next_contact = ToNextContact($row_result->next_contact);
		list($mydate,$mymonth, $myyear) = split('-',$next_contact);
		$address_1 = $row_result->address_1;
		$address_2 = $row_result->address_2;
		$suburb = $row_result->suburb;
		$post_code = $row_result->post_code;
		$state = $row_result->state;
		$postal_address_1 = $row_result->postal_address_1;
		$postal_address_2 = $row_result->postal_address_2;
		$postal_suburb = $row_result->postal_suburb;
		$postal_post_code = $row_result->postal_post_code;
		$postal_state = $row_result->postal_state;
		$phone1 = $row_result->phone1;
		$fax = $row_result->fax;
		$email = $row_result->email;
		$old_log = $row_result->old_log;
		$website_url = $row_result->website_url;
		$status = $row_result->status;
	}
//end if no sql
}

//display log
//define log file
$log_file = $client_id . "log.txt";
$mylog = "$logDir/$log_file";
if (file_exists($mylog)) {
	//read it and display
	$mydata = file($mylog);
	$log_content = implode("", $mydata);
	$mydata = "";
}
if (empty($log_content)) {$log_content = "log is not available";}
//if there is no result delete old log_content
if (empty($client_id)) {$log_content = "";}

$created_by = get_last_audit_history( $db, "client", $client_id, "created" ); 


//close connection
mysql_close($db);
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
<body bgcolor="#006699" leftmargin="0" topmargin="0" onLoad="scrollToBottom(document.mylog.log_content);">
<?php 
include_once("includes/top.php"); 
include_once("includes/client_top.php"); 
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <?php include_once("includes/admin_links.php"); ?>
  <tr> 
    <td>&nbsp;</td>
    <td colspan="2" class="text"><u>General Information and Logs<br>
      <img src="images/spacer.gif" width="39" height="10"> </u></td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
    <td valign="top" class="text"> 
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td colspan="3" class="text"><table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php

	if( $created_by !== false ) {

?>
              <tr class="text"><td>Added by <?= $created_by["full_name"] ?> on <?= date( "d-M-Y", strtotime( $created_by["timestamp"] ) ) ?></td></tr>
<?php

	}

?>
              <tr class="text"> 
                <td width="80%"><br>Next Contact Date:</td>
              </tr>
              <tr> 
                <td>
				<form name="nextcontact" action="index1.php" method="post">
				<input name="client_id" type="hidden" value="<?php echo $client_id; ?>">
				<?php
				
				include_once( "includes/DateField.php" );
				$date_field = new DateField( "next_contact", $next_contact );
				echo $date_field->getHTML(); 
				
				?>
                  <input type="submit" name="update_date" value="Update" class="smallbluebutton">
				  </form>
				</td>
              </tr>
            </table>
            <br> </td>
        </tr>
        <tr> 
          <td colspan="3" class="text">&nbsp;</td>
        </tr>
        <tr> 
          <td colspan="3" class="text">Phone: <a href="sip:<?php echo str_replace(" ", '', $phone1); ?>"><font color="#B9E9FF"><?php echo $phone1; ?></font></a><br>
            Fax: <?php echo $fax; ?><img src="images/spacer.gif" width="39" height="10"><br>
            Email: <a href="mailto:<?php echo $email; ?>"><font color="#CCFFFF"><?php echo $email; ?></font> 
            </a></font></td>
        </tr>
        <tr class="text"> 
          <td width="51%">&nbsp;</td>
          <td width="49%" colspan="2">&nbsp;</td>
        </tr>
        <tr class="text"> 
          <td> <p>Street Address:<br>
              <?php echo $address_1; ?><br>
              <?= empty( $address_2 ) ? "" : ( $address_2 . "<br>" ) ?>
              <?php echo $suburb; ?> <br>
              <?php echo $state; ?><br>
              <?php echo $post_code; ?> </p></td>
          <td colspan="2">Postal Address:<br>
            <?php echo $postal_address_1; ?><br>
            <?= empty( $postal_address_2 ) ? "" : ( $postal_address_2 . "<br>" ) ?>
            <?php echo $postal_suburb; ?><br>
            <?php echo $postal_state; ?><br>
            <?php echo $postal_post_code; ?> </td>
        </tr>
        <tr class="text"> 
          <td>&nbsp;</td>
          <td colspan="2">&nbsp;</td>
        </tr>
        <tr class="text"> 
          <td colspan="3"><a href="myclient.php?client_id=<?php echo $client_id; ?>"><font color="#B9E9FF">To update core client information, click here</font></a></td>
        </tr>
      </table>
      <p><img src="images/spacer.gif" width="233" height="14"></p>
      </td>
    <td width="76%" valign="top" class="text">
	<table width="100" border="0" cellspacing="0" cellpadding="0">
	<form name="mylog" method="post">
      <input name="client_id" type="hidden" value="<?php echo $client_id; ?>">
	  <input name="action_type" type="hidden" value="">
        <tr class="text"> 
          <td><span class="text">Contact Log:</span><br> </td>
          <td> <div align="right">&gt; <a href="" onclick="Onsubmit_updatelog(); return false"><font color="#B9E9FF">update existing log</font></a> </div></td>
        </tr>
        <tr> 
          <td colspan="2"><textarea name="log_content" cols="85" rows="12" class="smallblue"><?php echo $log_content; ?></textarea></td>
        </tr>
        <tr> 
          <td colspan="2"><textarea name="new_content" cols="85" rows="5" class="smallblue"></textarea></td>
        </tr>
        <tr>
          <td colspan="2" align="right"><a href="" onclick="Onsubmit_addtolog(); return false"><input type="button" name="update_log" value="Add New Entry" class="smallbluebutton"></a></td>
        </tr>
		</form>
      </table>
      <p></p>
      </td>
  </tr>
</table>
</body>
</html>
