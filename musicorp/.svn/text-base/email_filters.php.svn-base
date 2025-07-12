<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* email_filters template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 18/2/2004
* Client: WorldWeb, Domain: database intranet
* This template consists of 3 sections: Navigation, SQL Queries & formatting, HTML output
* Important files included in this template are: header.inc (head section and javascript library),
* globals.php (connection string),
* Last Modified: 25/3/2004 By Aviv Efrat
* Please remember to backup template before modifying
*
*********************************************************************************************************
*/
//begin session
session_start();
//include a globals file for db connection
include_once("includes/globals.php");
//define date
$today = date("d/m/Y" ,time());

//function to be used on page-----------------------------------------------------------------------------
function checkType($val, $typestring) {
global $is_same;
$is_same = "no";
//explode the string into an array
$typestring = explode(",", $typestring);
//loop through the array and compare
	foreach ($typestring as $myvalue) {
		if ($val == $myvalue) {
			$is_same = "yes";
		}
	}
return $is_same;
}

function formatAttachmentSize($max_attachment_size) {
global $myattachment_size;
	switch ($max_attachment_size) {
		case "100":
			$myattachment_size = "<option selected value=\"100\">100kb</option>";
			break;
		case "512":
			$myattachment_size = "<option selected value=\"512\">500kb</option>";
			break;
		case "1024":
			$myattachment_size = "<option selected value=\"1024\">1000kb</option>";
			break;
		case "1536":
			$myattachment_size = "<option selected value=\"1536\">1500kb</option>";
			break;
		case "2048":
			$myattachment_size = "<option selected value=\"2048\">2000kb</option>";
			break;
		case "2560":
			$myattachment_size = "<option selected value=\"2560\">2500kb</option>";
			break;
		case "3072":
			$myattachment_size = "<option selected value=\"3072\">3 MB</option>";
			break;
		case "5120":
			$myattachment_size = "<option selected value=\"5120\">5 MB</option>";
			break;
		case "10240":
			$myattachment_size = "<option selected value=\"10240\">10 MB</option>";
			break;
		 case "20480":
            $myattachment_size = "<option selected value=\"20480\">20 MB</option>";
            break;
	}
return $myattachment_size;
}

//establish a persistent connection and get all required data.
// select valuations for  valuer
$db = mysql_pconnect ($hostName, $userName, $password);
mysql_select_db($database);

//event->load_filters will load matrix of email filters with the relevant check boxes---------------------------
if ($event == "load_filters") {
	//set media filter types to nothing
	$myimage_types = "";
	$myoffice_types = "";
	$myweb_types = "";
	$myutility_types = "";
	//reset arrays
	reset($image_types);
	reset($office_types);
	reset($web_types);
	reset($utility_types);
	//define query and select all mail filter data for user
	$query = "SELECT * FROM mail_filters WHERE vuser_id = '$vuser_id'";
	$result = mysql_query($query, $db);
	$numrows = mysql_num_rows($result);
	if ($numrows == 0) {
		$existing_record = "no";
		$max_attachment_size = "<option selected></option>";
		//format auto_responder to have checked on the no option
		$myautoresponder1 = "";
		$myautoresponder2 = "checked";
		$responder_message = "";
		//loop through the email type arrays and add empty check boxes
		foreach($image_types as $val)
		{
			$myimage_types .= "<input name=\"image_type[]\" type=\"checkbox\" value=\"$val\">&nbsp;$val<br>";
		}
		foreach($office_types as $val)
		{
			$myoffice_types .= "<input name=\"office_type[]\" type=\"checkbox\" value=\"$val\">&nbsp;$val<br>";
		}
		foreach($web_types as $val)
		{
			$myweb_types .= "<input name=\"web_type[]\" type=\"checkbox\" value=\"$val\">&nbsp;$val<br>";
		}
		foreach($utility_types as $val)
		{
			$myutility_types .= "<input name=\"utility_type[]\" type=\"checkbox\" value=\"$val\">&nbsp;$val<br>";
		}
	} else {
		$existing_record = "yes";
		//get result and allocate variables
		$row = mysql_fetch_object($result);
		$mail_filters_id = $row->mail_filters_id;
		$max_attachment_size = $row->attachment_size;
		//format entry
		$max_attachment_size = formatAttachmentSize($max_attachment_size);
		$auto_responder = $row->auto_responder;
		if ($auto_responder == "yes") {
		 	$myautoresponder1 = "checked";
			$myautoresponder2 = "";
		} else {
			$myautoresponder1 = "";
			$myautoresponder2 = "checked";
		}
		$responder_message = $row->responder_message;
		//define filtering list
		$image_types_list = $row->image_types;
		$office_types_list = $row->office_types;
		$web_types_list = $row->web_types;
		$utility_types_list = $row->utility_types;
		//check if type is in variable for each mail type array
		foreach($image_types as $val)
		{
			$myres = checkType($val, $image_types_list);
			if ($myres == "yes") {
				$myimage_types .= "<input name=\"image_type[]\" type=\"checkbox\" value=\"$val\" checked>&nbsp;$val<br>";
			} else {
				$myimage_types .= "<input name=\"image_type[]\" type=\"checkbox\" value=\"$val\">&nbsp;$val<br>";
			}
		}
		foreach($office_types as $val)
		{
			$myres = checkType($val, $office_types_list);
			if ($myres == "yes") {
				$myoffice_types .= "<input name=\"office_type[]\" type=\"checkbox\" value=\"$val\" checked>&nbsp;$val<br>";
			} else {
				$myoffice_types .= "<input name=\"office_type[]\" type=\"checkbox\" value=\"$val\">&nbsp;$val<br>";
			}
		}
		foreach($web_types as $val)
		{
			$myres = checkType($val, $web_types_list);
			if ($myres == "yes") {
				$myweb_types .= "<input name=\"web_type[]\" type=\"checkbox\" value=\"$val\" checked>&nbsp;$val<br>";
			} else {
				$myweb_types .= "<input name=\"web_type[]\" type=\"checkbox\" value=\"$val\">&nbsp;$val<br>";
			}
		}
		foreach($utility_types as $val)
		{
			$myres = checkType($val, $utility_types_list);
			if ($myres == "yes") {
				$myutility_types .= "<input name=\"utility_type[]\" type=\"checkbox\" value=\"$val\" checked>&nbsp;$val<br>";
			} else {
				$myutility_types .= "<input name=\"utility_type[]\" type=\"checkbox\" value=\"$val\">&nbsp;$val<br>";
			}
		}
	//end else
	}
//end event
}

//event->update_filters when user clicks update filters button
if (isset($update_filters)) {
	
	//set lists to nothing
	$image_types_list = "";
	$office_types_list = "";
	$web_types_list = "";
	$utility_types_list = "";

	//first step is to loop through the arrays and create strings to be inserted to db
	$image_type_count = count($image_type);
	for ($i=0; $i<$image_type_count; $i++) {
		$image_types_list .= "$image_type[$i],";
	}
	//get rid of last comma
	$image_types_list = substr($image_types_list, 0, -1);
	
	$office_type_count = count($office_type);
	for ($i=0; $i<$office_type_count; $i++) {
		$office_types_list .= "$office_type[$i],";
	}
	//get rid of last comma
	$office_types_list = substr($office_types_list, 0, -1);
	
	$web_type_count = count($web_type);
	for ($i=0; $i<$web_type_count; $i++) {
		$web_types_list .= "$web_type[$i],";
	}
	//get rid of last comma
	$web_types_list = substr($web_types_list, 0, -1);
	
	$utility_type_count = count($utility_type);
	for ($i=0; $i<$utility_type_count; $i++) {
		$utility_types_list .= "$utility_type[$i],";
	}
	//get rid of last comma
	$utility_types_list = substr($utility_types_list, 0, -1);

	//if there is no existing record we need to create one
	if ($existing_record == "no") {
		$mail_filters_id = "";
		$query = "INSERT INTO mail_filters (mail_filters_id, vuser_id, client_id, attachment_size, auto_responder, responder_message, image_types, office_types, web_types, utility_types) ";
		$query .= "VALUES ('$mail_filters_id','$vuser_id','$client_id','$attachment_size','$auto_responder','$responder_message','$image_types_list','$office_types_list','$web_types_list','$utility_types_list')";
		$result = mysql_query($query, $db);
		$err = mysql_error($db);
		if ($err) {echo $err;}
	} else {
		$query = "UPDATE mail_filters SET attachment_size='$attachment_size',auto_responder='$auto_responder',responder_message='$responder_message',";
		$query .= "image_types='$image_types_list',office_types='$office_types_list',web_types='$web_types_list',utility_types='$utility_types_list' WHERE mail_filters_id='$mail_filters_id'";
		$result = mysql_query($query, $db);
		$err = mysql_error($db);
		if ($err) {echo $err;}
		//now we must update the flag of the vuser to u (update)
		$query1 = "UPDATE vusers SET status='u' WHERE vuser_id='$vuser_id' AND status != 'n'";
		$result1 = mysql_query($query1, $db);
		$err1 = mysql_error($db);
		if ($err1) {echo $err1;}
	}
	
	//if all is well lets go back to email.php
	echo "<script language=\"JavaScript\">document.location.replace('email.php?client_id=$client_id');</script>";
}

//event->add_alias ------------------------when user clicks add new alias button--------------------------------
if (isset($add_alias)) {
	//before we add the alias we need to test if this email address is valid
	//and does not exist on our server already
	$testresult = testAliasAccount($valias, $domain, $userDomain);
	if ($testresult == true) {
		//add alias to existing user
		$valias_id = "";
		$addalias_query = "INSERT INTO valiases VALUES ('$valias_id','$vuser_id','$client_id','$valias','$domain','n')";
		$addalias_result = mysql_query($addalias_query, $db);
		//we also need to set the flag of the vuser_id to u (updated)
		$vuser_query = "UPDATE vusers SET status = 'u' WHERE vuser_id = '$vuser_id'";
		$vuser_result = mysql_query($vuser_query, $db);
		//if all is well lets go back to email.php
		echo "<script language=\"JavaScript\">document.location.replace('email.php?client_id=$client_id');</script>";
	} else {
		?>
		<script type="text/javascript">
		var agree=alert('This email address already exists on our mail server!\n Or you may try to use a domain which is not the same as your user account\n Click OK to select another one');
		if (agree)
		{
			document.location.replace('email_filters.php?event=load_filters&client_id=<?php echo $client_id; ?>&vuser_id=<?php echo $vuser_id; ?>');
		}
		</script>
		<?php
	}
}

//event->update_aliases ------------------------when user clicks add update aliases button--------------------------------
if (isset($update_aliases)) {
	$mycount = count($valias_id);
	//loop from 0 to mycount and update
	for ($i=0;$i<$mycount;$i++) {
		//we need to compare existing record to the updated one to see if it has been updated or not.
		//if it does not require update keep the status flag as c
		$query = "SELECT * FROM valiases WHERE valias_id = '$valias_id[$i]'";
		$result = mysql_query($query, $db);
		//compare records
		$myupdate = "no";	//set the myupdate flag to no as default
		while ($row=mysql_fetch_object($result)) {
			if ($row->valias != $valias[$i]) {$myupdate = "yes";}
			if ($row->domain != $domain[$i]) {$myupdate = "yes";}
			//if user has been added but not updated on the commercial server we need to maintain a status of n
			$current_status = $row->status;
			if ($current_status == "n" || $current_status == "u") {$myupdate = "keep_status";}
		}
		
		/*
		if myupdate is set to yes update record and set status flag to u
		otherwise we may as well update but keep status flag as c
		*/
		
		if ($myupdate == "yes") {
			$mystatus = "u";
		} elseif ($myupdate == "keep_status") {
			$mystatus = $current_status;
		} else {
			$mystatus = "c";
		}
		$query1 = "UPDATE valiases SET valias='$valias[$i]', domain='$domain[$i]', status='$mystatus' WHERE valias_id='$valias_id[$i]'";
		$result1 = mysql_query($query1, $db);
		$err1 = mysql_error($db);
		if ($err1) {echo "$valias[$i], $err1<br>";}

	}
	
	//if all is well lets go back to email.php
	echo "<script language=\"JavaScript\">document.location.replace('email.php?client_id=$client_id');</script>";
}

//event->delete_alias -------------------------when user clicked delete alias link----------------------------------
if ($event == "delete_valias") {
	//generate javascript alert and ask user if they realy want to do that
	?>
	<script type="text/javascript">
	var agree=confirm('Are you sure you wish to delete this alias?\n If yes click OK else click Cancel');
	if (agree)
	{
		document.location.replace('email_filters.php?event=deleteme&valias_id=<?php echo $valias_id; ?>&client_id=<?php echo $client_id; ?>&vuser_id=<?php echo $vuser_id; ?>');
	}
	else
	{
		document.location.replace('email.php?client_id=<?php echo $client_id; ?>');
	}
	</script>
	<?php
}

if ($event == "deleteme") {
	//delete from valiases table
	$delalias_query = "UPDATE valiases SET status = 'd' WHERE valias_id = '$valias_id'";
	$delalias_result = mysql_query($delalias_query, $db);
	//and update record on vusers table set status to u
	$vuser_query = "UPDATE vusers SET status = 'u' WHERE vuser_id = '$vuser_id'";
	$vuser_result = mysql_query($vuser_query, $db);
	//if all is well lets go back to email.php
	echo "<script language=\"JavaScript\">document.location.replace('email.php?client_id=$client_id');</script>";
}

//event->update_forwarding-------------------when user change the forwarding status----------------------------------
//event->update_forwarding-------------------when user change the forwarding status----------------------------------
if (isset($update_forwarding)) {
	$myError = false;
	//if there is no existing record we must insert one otherwise update one
	if ($_POST['existing_forward'] == "no") {
		
		$query = "INSERT INTO forwarding (forward_id,vuser_id,client_id,forwarding,forward_to,keep_mail,status) ";
		$query .= "VALUES ('',".$_POST['vuser_id'].",".$_POST['client_id'].",'".$_POST['forwarding']."','".$_POST['forward_to']."','".$_POST['keep_mail']."','n')";
		$result = mysql_query($query, $db);
		$err = mysql_error($db);
		if ($err) {echo $err; $myError=true;}
		
	} else {
		//set the status according to previous setting
		if ($_POST['is_activated'] == "yes") {
			if ($_POST['forwarding'] == "no") {
				$forward_status = "d";
			} else {
				$forward_status = "u";
			}
		} else {
			if ($forwarding == "yes") {$forward_status = "n";}
		}
		$query = "UPDATE forwarding SET forwarding='".$_POST['forwarding']."', forward_to='".$_POST['forward_to']."', keep_mail='".$_POST['keep_mail']."', status='".$forward_status."' WHERE forward_id=".$_POST['forward_id']."";
		$result = mysql_query($query, $db);
		$err = mysql_error($db);
		if ($err) {echo $err; $myError=true;}
		
	}
	
	//now we must update the flag of the vuser to u (update)
	if ($myError == false) {
		$query1 = "UPDATE vusers SET status='u' WHERE vuser_id=".$vuser_id." AND status != 'n'";
		$result1 = mysql_query($query1, $db);
		$err1 = mysql_error($db);
		if ($err1) {echo $err1;}
	}
	//if all is well lets go back to email.php
	echo "<script language=\"JavaScript\">document.location.replace('email.php?client_id=$client_id');</script>";
}

//begin general display of information
$query = "SELECT client_name, trading_name, agreement_number, website_url, status FROM clients WHERE client_id = '$client_id'";
$result = mysql_query($query, $db);
$row = mysql_fetch_object($result);
$client_name = $row->client_name;
$trading_name = $row->trading_name;
$agreement_number = $row->agreement_number;
$website_url = $row->website_url;
$status = $row->status;

//and get all aliases for this vuser
$query1 = "SELECT * FROM valiases WHERE vuser_id = '$vuser_id' AND status != 'd'";
$result1 = mysql_query($query1);

//list all domains for client in a drop down
$mydomains = "";
$query2 = "SELECT domain_name FROM domains WHERE client_id = '$client_id'";
$result2 = mysql_query($query2, $db);
while ($row2=mysql_fetch_object($result2)) {
	$mydomains .= "<option>$row2->domain_name</option>";
}

//get the user name, password and domain
$query3 = "SELECT v_username, v_pwd, domain FROM vusers WHERE vuser_id = '$vuser_id'";
$result3 = mysql_query($query3, $db);
$row3 = mysql_fetch_object($result3);

//get the forwarding status for user
$query4 = "SELECT forward_id, forwarding, forward_to, keep_mail FROM forwarding WHERE vuser_id='$vuser_id'";
$result4 = mysql_query($query4, $db);
$num_rows4 = mysql_num_rows($result4);
if ($num_rows4 > 0) {
	$existing_forward = "yes";
	$row4 = mysql_fetch_object($result4);
	$forward_id = $row4->forward_id;		
	//format forwarding to have checked on the no option
	if ($row4->forwarding == "yes") {
		$myforwarding1 = "checked";
		$myforwarding2 = "";
		$is_activated = "yes";
	} else {
		$myforwarding1 = "";
		$myforwarding2 = "checked";
		$is_activated = "no";
	}
	$forward_to = $row4->forward_to;
	//format keep mail to have checked on the no option
	if ($row4->keep_mail == "yes") {
		$mykeep_mail1 = "checked";
		$mykeep_mail2 = "";
	} else {
		$mykeep_mail1 = "";
		$mykeep_mail2 = "checked";
	}
} else {
	$forward_id = "";
	$existing_forward = "no";
	$myforwarding1 = "";
	$myforwarding2 = "checked";
	$forward_to = "";
	$is_activated = "no";
	$mykeep_mail1 = "";
	$mykeep_mail2 = "checked";
}

//close connection
mysql_close($db);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>CAPS | WorldWeb Management Services</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../includes/caps_styles.css" rel="stylesheet" type="text/css">
<?php
//include javascript library
include_once("../includes/worldweb.js");
?>
<script language="JavaScript">
<!--
//validate update aliases to make sure domain is the same as the main user
function domainCheck() {

	var mycounter = this.document.updatevalias.mycounter.value;
	
	for (i=0;i<mycounter;i++) {
		mydomain = this.document.updatevalias.elements['domain[]'][i];
		mydomainvalue = mydomain.options[mydomain.selectedIndex].text;
		//test to see if same
		if (document.updatevalias.userDomain.value != mydomainvalue) {
			alert("Alias must have the same domain as the user it belongs to");
			return false;
		}
	}
	
}

//validate add aliases to make sure domain is the same as the main user
function formCheck(item) {

	var mydomain = item.options[item.selectedIndex].text;

	if (document.addalias.userDomain.value != mydomain) {
		alert("Alias must have the same domain as the user it belongs to");
		return false;
	}
	
}
-->
</script>
</head>

<body bgcolor="#006699" leftmargin="0" topmargin="0">
<?php 
include_once("includes/top.php"); 
//include_once("includes/client_top.php");
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td>&nbsp;</td>
    <td colspan="2" class="text"><br><u>Email Settings</u><br>
	<p class="subheading">Mail Aliases For <?php echo "$row3->v_username@$row3->domain"; ?></p>
	  <table width="400" border="0" cellspacing="0" cellpadding="0">
	  	<form name="updatevalias" action="email_filters.php" method="post" onSubmit="return domainCheck()">
      	  <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
		  <input type="hidden" name="vuser_id" value="<?php echo $vuser_id; ?>">
		  <input type="hidden" name="userDomain" value="<?php echo $row3->domain; ?>">
          <tr> 
            <td width="100" class="text">Alias</td>
            <td width="150" class="text">@ domain</td>
			<td width="50" class="textcentre">Status</td>
            <td width="100" class="textcentre">Actions</td>
          </tr>
		  <?php
		  //loop through the result1 and display list of aliases
		  $counter = 0;
		  while ($row1 = mysql_fetch_object($result1)) {
		  	echo "<tr valign=\"top\">";
		   	echo "<td><input name=\"valias[]\" type=\"text\" class=\"black\" value=\"$row1->valias\" size=\"8\"></td>";
            echo "<td><select name=\"domain[]\" class=\"black\"><option selected>$row1->domain</option>$mydomains</select></td>";
			echo "<td class=\"textcentre\">$row1->status</td>";
			echo "<td class=\"textcentre\"><span class=\"smallwhite\"><a href=\"email_filters.php?event=delete_valias&valias_id=$row1->valias_id&client_id=$client_id&vuser_id=$vuser_id\"><font color='#CCFFFF'>delete</font></a></span></td></tr>";
			echo "<input name=\"valias_id[]\" type=\"hidden\" value=\"$row1->valias_id\">";
			echo "<input name=\"vuser_id\" type=\"hidden\" value=\"$row1->vuser_id\">";
			$counter++;
		  }
		  echo "<input name=\"mycounter\" type=\"hidden\" value=\"$counter\">";
		  ?>
		  <tr>
            <td colspan="4" height="30" valign="middle"><input type="submit" name="update_aliases" value="Update Aliases" class="smallbluebutton"></td>
          </tr>
		  </form>
		  <tr>
            <td colspan="4">&nbsp;</td>
          </tr>
		  <form name="addalias" action="email_filters.php" method="post" onSubmit="return formCheck(this.domain)">
		  <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
		  <input type="hidden" name="vuser_id" value="<?php echo $vuser_id; ?>">
		  <input type="hidden" name="userDomain" value="<?php echo $row3->domain; ?>">
          <tr> 
            <td width="100" class="text">Alias</td>
            <td width="150" class="text">@ domain</td>
			<td width="50" class="textcentre">Status</td>
			<td width="100">&nbsp;</td>
          </tr>
		  <tr valign="top"> 
            <td><input name="valias" type="text" class="black" size="8"></td>
            <td><select name="domain" class="black"><?php echo $mydomains; ?></select></td>
			<td class="textcentre">n</td>
			<td>&nbsp;</td>
          </tr>
		  <tr>
            <td colspan="4" height="30" valign="middle"><input type="submit" name="add_alias" value="Add Alias" class="smallbluebutton"></td>
          </tr>
		  </form>
		</table>
	  <p class="subheading">Mail Forwarding For <?php echo "$row3->v_username@$row3->domain"; ?></p>
	  <table>
	  	<form action="email_filters.php" method="post">
		<input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
		<input type="hidden" name="vuser_id" value="<?php echo $vuser_id; ?>">
		<input type="hidden" name="forward_id" value="<?php echo $forward_id; ?>">
		<input type="hidden" name="existing_forward" value="<?php echo $existing_forward; ?>">
		<input type="hidden" name="is_activated" value="<?php echo $is_activated; ?>">
	  	<tr height="30" valign="top">
          <td width="100" class="text">Forwarding:</td>
          <td colspan="4" class="text">
            <input name="forwarding" type="radio" value="yes" <?php echo $myforwarding1; ?>>
             on 
            <input name="forwarding" type="radio" value="no" <?php echo $myforwarding2; ?>>
             off
          </td>
        </tr>
		<tr height="30" valign="top">
          <td class="text">Keep copy on server </td>
          <td colspan="4" class="text"><input name="keep_mail" type="radio" value="yes" <?php echo $mykeep_mail1; ?>> yes
          <input name="keep_mail" type="radio" value="no" <?php echo $mykeep_mail2; ?>> no
		  </td>
        </tr>
        <tr height="30" valign="top">
          <td class="text">Forward To:</td>
          <td colspan="4" class="text"><input name="forward_to" type="text" class="black" size="30" value="<?php echo $forward_to; ?>"></td>
        </tr>
        <tr height="30" valign="top">
          <td colspan="5" class="text"><input type="submit" name="update_forwarding" value="Update Email Forwarding" class="smallbluebutton"></td>
        </tr>
		</form>
	  </table>
      <p class="subheading">Mail Filters For <?php echo "$row3->v_username@$row3->domain"; ?></p>
	  <table width="400" border="0" cellspacing="0" cellpadding="0">
	  	<form action="email_filters.php" method="post">
      	  <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
		  <input type="hidden" name="vuser_id" value="<?php echo $vuser_id; ?>">
		  <input type="hidden" name="existing_record" value="<?php echo $existing_record; ?>">
		  <input type="hidden" name="mail_filters_id" value="<?php echo $mail_filters_id; ?>">
		  <tr>
            <td colspan="4">&nbsp;</td>
          </tr>
          <tr height="40" valign="top"> 
            <td width="100" height="40" class="text">Attachment Size:</td>
            <td width="400" height="40" colspan="4" class="text">
		  <select name="attachment_size" size="1" class="black">
		  	<?php echo $max_attachment_size; ?>
			    <option value="100">100kb</option>
				<option value="512">500kb</option>
			    <option value="1024">1 MB</option>
			    <option value="1536">1.5 MB</option>
			    <option value="2048">2 MB</option>
			    <option value="2560">2.5 MB</option>
			    <option value="3072">3 MB</option>
				<option value="4096">4 MB</option>
				<option value="5120">5 MB</option>
				<option value="10240">10 MB</option>
				<option value="20480">20 MB</option>
		  </select>
		  </td>
          </tr>
          <tr height="30" valign="top">
            <td width="100" class="text">Autoresponder:</td>
            <td colspan="4" class="text">
            <input name="auto_responder" type="radio" value="yes" <?php echo $myautoresponder1; ?>>
               on 
              <input name="auto_responder" type="radio" value="no" <?php echo $myautoresponder2; ?>>
               off
          </td>
          </tr>
		  <tr height="20" valign="top">
            <td colspan="5" class="instruction">Use the autoresponder to send an automatic reply in response to 
              any email sent to your account.</td>
          </tr>
		  <tr height="40" valign="top">
            <td width="100" height="40" class="text">Autoresponder Message:</td>
            <td height="40" colspan="4" class="text">
            <textarea name="responder_message" cols="30" rows="3" class="black"><?php echo $responder_message; ?></textarea><br>
          </td>
          </tr>
		  <tr height="30" valign="middle"> 
            <td height="30" class="text">Image Types</td>
            <td height="30" class="text">Offic Doc Types</td>
            <td height="30" class="text">Web Doc Types</td>
            <td height="30" class="text">Utility Types</td>
          </tr>
		  <tr valign="top"> 
            <td class="text"><?php echo $myimage_types; ?></td>
            <td class="text"><?php echo $myoffice_types; ?></td>
            <td class="text"><?php echo $myweb_types; ?></td>
            <td class="text"><?php echo $myutility_types; ?></td>
          </tr>
          <tr align="left" valign="middle"> 
            <td colspan="5" height="30">
			<input type="submit" name="update_filters" value="Add/Update Filters" class="smallbluebutton"></td>
          </tr>
		  </form>
          <tr>
            <td height="30" colspan="5" class="text"><p><a href="email.php?client_id=<?php echo $client_id; ?>"><font color="B9E9FF">To go back to email page, click here</font></a></p></td>
          </tr>
      </table>
      <img src="../images/spacer.gif" width="39" height="10">
	 </td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
    <td valign="top" class="text"> 
      <p><img src="../images/spacer.gif" width="233" height="14"></p>
      </td>
    <td width="76%" valign="top" class="text"></td>
  </tr>
</table>
</body>
</html>
