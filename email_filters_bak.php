<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* email_filters template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 5/2/2004
* Client: WorldWeb, Domain: database intranet
* This template consists of 3 sections: Navigation, SQL Queries & formatting, HTML output
* Important files included in this template are: header.inc (head section and javascript library),
* globals.php (connection string),
* Last Modified: 5/2/2004 By Aviv Efrat
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
		case "512":
			$myattachment_size = "<option selected value=\"512\">512kb</option>";
			break;
		case "1024":
			$myattachment_size = "<option selected value=\"1024\">1mb</option>";
			break;
		case "1536":
			$myattachment_size = "<option selected value=\"1536\">1.5mb</option>";
			break;
		case "2048":
			$myattachment_size = "<option selected value=\"2048\">2mb</option>";
			break;
		case "2560":
			$myattachment_size = "<option selected value=\"2560\">2.5mb</option>";
			break;
		case "3072":
			$myattachment_size = "<option selected value=\"3072\">3mb</option>";
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
	$mymultimedia_types = "";
	$myutility_types = "";
	//reset arrays
	reset($image_types);
	reset($office_types);
	reset($web_types);
	reset($multimedia_types);
	reset($utility_types);
	//define query and select all mail filter data for user
	$query = "SELECT vusers.v_username, vusers.v_pwd, vusers.v_alias, vusers.domain, mail_filters.* FROM vusers, mail_filters WHERE vusers.vuser_id = '$vuser_id' AND vusers.vuser_id = mail_filters.vuser_id";
	$result = mysql_query($query, $db);
	$numrows = mysql_num_rows($result);
	if ($numrows == 0) {
		$existing_record = "no";
		$query1 = "SELECT v_username, v_pwd, v_alias, domain FROM vusers WHERE vuser_id = '$vuser_id'";
		$result1 = mysql_query($query1, $db);
		$row1 = mysql_fetch_object($result1);
		$myv_username = $row1->v_username;
		$myv_pwd = $row1->v_pwd;
		$myv_alias = $row1->v_alias;
		$myv_domain = $row1->domain;
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
		foreach($multimedia_types as $val)
		{
			$mymultimedia_types .= "<input name=\"multimedia_type[]\" type=\"checkbox\" value=\"$val\">&nbsp;$val<br>";
		}
		foreach($utility_types as $val)
		{
			$myutility_types .= "<input name=\"utility_type[]\" type=\"checkbox\" value=\"$val\">&nbsp;$val<br>";
		}
	} else {
		$existing_record = "yes";
		//get result and allocate variables
		$row = mysql_fetch_object($result);
		$myv_username = $row->v_username;
		$myv_pwd = $row->v_pwd;
		$myv_alias = $row->v_alias;
		$myv_domain = $row->domain;
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
		$image_types_list = $row->image_types;
		$office_types_list = $row->office_types;
		$web_types_list = $row->web_types;
		$multimedia_types_list = $row->multimedia_types;
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
		foreach($multimedia_types as $val)
		{
			$myres = checkType($val, $multimedia_types_list);
			if ($myres == "yes") {
				$mymultimedia_types .= "<input name=\"multimedia_type[]\" type=\"checkbox\" value=\"$val\" checked>&nbsp;$val<br>";
			} else {
				$mymultimedia_types .= "<input name=\"multimedia_type[]\" type=\"checkbox\" value=\"$val\">&nbsp;$val<br>";
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
	$multimedia_types_list = "";
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
	
	$multimedia_type_count = count($multimedia_type);
	for ($i=0; $i<$multimedia_type_count; $i++) {
		$multimedia_types_list .= "$multimedia_type[$i],";
	}
	//get rid of last comma
	$multimedia_types_list = substr($multimedia_types_list, 0, -1);
	
	$utility_type_count = count($utility_type);
	for ($i=0; $i<$utility_type_count; $i++) {
		$utility_types_list .= "$utility_type[$i],";
	}
	//get rid of last comma
	$utility_types_list = substr($utility_types_list, 0, -1);

	//if there is no existing record we need to create one
	if ($existing_record == "no") {
		$mail_filters_id = "";
		$query = "INSERT INTO mail_filters VALUES ('$mail_filters_id','$vuser_id','$client_id','$attachment_size','$auto_responder','$responder_message','$image_types_list','$office_types_list','$web_types_list','$multimedia_types_list','$utility_types_list')";
		$result = mysql_query($query, $db);
		$err = mysql_error($db);
		if ($err) {echo $err;}
	} else {
		$query = "UPDATE mail_filters SET vuser_id='$vuser_id',client_id='$client_id',attachment_size='$attachment_size',auto_responder='$auto_responder',responder_message='$responder_message',";
		$query .= "image_types='$image_types_list',office_types='$office_types_list',web_types='$web_types_list',multimedia_types='$multimedia_types_list',utility_types='$utility_types_list' WHERE mail_filters_id='$mail_filters_id'";
		$result = mysql_query($query, $db);
		$err = mysql_error($db);
		if ($err) {echo $err;}
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

<body bgcolor="#006699" leftmargin="0" topmargin="0">
<?php 
include_once("includes/top.php"); 
include_once("includes/client_top.php");
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <?php include_once("includes/admin_links.php"); ?>
  <tr> 
    <td>&nbsp;</td>
    <td colspan="2" valign="top" class="text"><u>Email Settings</u><br>
      <p class="subheading">Mail Filters</p>
	  <table width="500" border="0" cellspacing="0" cellpadding="0">
	  	<form action="email_filters.php" method="post">
      	  <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
		  <input type="hidden" name="vuser_id" value="<?php echo $vuser_id; ?>">
		  <input type="hidden" name="existing_record" value="<?php echo $existing_record; ?>">
		  <input type="hidden" name="mail_filters_id" value="<?php echo $mail_filters_id; ?>">
          <tr> 
            <td width="100" class="text">Alias</td>
            <td width="200" colspan="2" class="text">@ domain</td>
            <td width="100" class="text">User</td>
            <td width="100" class="text">Pass</td>
          </tr>
		  <tr valign="top"> 
            <td width="100" class="text" bgcolor='#0070A6'><?php echo $myv_alias; ?></td>
            <td width="200" colspan="2" class="text" bgcolor='#0070A6'><?php echo $myv_domain; ?></td>
            <td width="100" class="text" bgcolor='#0070A6'><?php echo $myv_username; ?></td>
            <td width="100" class="text" bgcolor='#0070A6'><?php echo $myv_pwd; ?></td>
          </tr>
		  <tr>
            <td colspan="5">&nbsp;</td>
          </tr>
          <tr height="40" valign="top"> 
            <td width="100" class="text">Attachment Size:</td>
            <td width="400" colspan="4" class="text">
		  <select name="attachment_size" size="1" class="black">
		  	<?php echo $max_attachment_size; ?>
			    <option value="512">512kb</option>
			    <option value="1024">1mb</option>
			    <option value="1024">1mb</option>
			    <option value="1536">1.5mb</option>
			    <option value="2048">2mb</option>
			    <option value="2560">2.5mb</option>
			    <option value="3072">3mb</option>
		  </select>
		  </td>
          </tr>
          <tr height="40" valign="top">
            <td width="100" class="text">Autoresponder:</td>
            <td colspan="4" class="text">
            <input name="auto_responder" type="radio" value="yes" <?php echo $myautoresponder1; ?>>
               on 
              <input name="auto_responder" type="radio" value="no" <?php echo $myautoresponder2; ?>>
               off
          </td>
          </tr>
		  <tr height="40" valign="top">
            <td width="100" class="text">Autoresponder Message:</td>
            <td colspan="4" class="text">
            <textarea name="responder_message" cols="30" rows="3" class="black"><?php echo $responder_message; ?></textarea>
          </td>
          </tr>
		  <tr height="30" valign="top"> 
            <td class="text">Image Types</td>
            <td class="text">Offic Doc Types</td>
            <td class="text">Web Doc Types</td>
            <td class="text">Multimedia Types</td>
            <td class="text">Utility Types</td>
          </tr>
		  <tr valign="top"> 
            <td class="text"><?php echo $myimage_types; ?></td>
            <td class="text"><?php echo $myoffice_types; ?></td>
            <td class="text"><?php echo $myweb_types; ?></td>
            <td class="text"><?php echo $mymultimedia_types; ?></td>
            <td class="text"><?php echo $myutility_types; ?></td>
          </tr>
          <tr align="left" valign="middle"> 
            <td colspan="5" height="30">
			<input type="submit" name="update_filters" value="Add/Update Filters" class="smallbluebutton"></td>
          </tr>
		  </form>
          <tr>
            <td height="30" colspan="5" class="text"><a href="email.php?client_id=<?php echo $client_id; ?>"><font color="B9E9FF">To go back to email page, click here</font></a></td>
          </tr>
      </table>
      <img src="images/spacer.gif" width="39" height="10">
	 </td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
    <td valign="top" class="text"> 
      <p><img src="images/spacer.gif" width="233" height="14"></p>
      </td>
    <td width="76%" valign="top" class="text"></td>
  </tr>
</table>
</body>
</html>
