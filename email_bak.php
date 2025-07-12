<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* email template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 12/1/2004
* Client: WorldWeb, Domain: database intranet
* This template consists of 3 sections: Navigation, SQL Queries & formatting, HTML output
* Important files included in this template are: header.inc (head section and javascript library),
* globals.php (connection string),
* Last Modified: 16/2/2004 By Aviv Efrat
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

//establish a persistent connection and get all required data.
// select valuations for  valuer
$db = mysql_pconnect ($hostName, $userName, $password);
mysql_select_db($database);

//event -> if update event update the database than continue
if ($event == "delete_vuser") {
	//update technical table
	$query = "UPDATE vusers SET status = 'd' WHERE vuser_id = '$vuser_id'";
	$result = mysql_query($query, $db);
}

//event->update_users update vusers table
if (isset($update_users)) {
	$mycount = count($vuser_id);
	//loop from 1 to i-1 and update
	for ($i=0;$i<$mycount;$i++) {
		//we need to compare existing record to the updated one to see if it has been updated or not.
		//if it does not require update keep the status flag as c
		$query = "SELECT * FROM vusers WHERE vuser_id = '$vuser_id[$i]'";
		$result = mysql_query($query, $db);
		//compare records
		$myupdate = "no";	//set the myupdate flag to no as default
		while ($row=mysql_fetch_object($result)) {
			if ($row->v_username != $v_username[$i]) {$myupdate = "yes";}
			if ($row->v_pwd != $v_pwd[$i]) {$myupdate = "yes";}
			if ($row->v_alias != $v_alias[$i]) {$myupdate = "yes";}
			if ($row->domain != $domain[$i]) {$myupdate = "yes";}
			if ($row->mail_server != $mail_server[$i]) {$myupdate = "yes";}
			//if user has been added but not updated on the commercial server
			//we need to maintain a status of n
			$current_status = $row->status;
			if ($current_status == "n" || $current_status == "u") {$myupdate = "keep_status";}
		}
		
		//if myupdate is set to yes update record and set status flag to u
		//otherwise we may as well update but keep status flag as c
		if ($myupdate == "yes") {
			$mystatus = "u";
		} elseif ($myupdate == "keep_status") {
			$mystatus = $current_status;
		} else {
			$mystatus = "c";
		}
		$query1 = "UPDATE vusers SET v_username='$v_username[$i]', v_pwd='$v_pwd[$i]', v_alias='$v_alias[$i]', domain='$domain[$i]', mail_server='$mail_server[$i]', status='$mystatus' WHERE vuser_id='$vuser_id[$i]'";
		$result1 = mysql_query($query1, $db);
		$err = mysql_error($db);
		if ($err) {echo "$v_username[$i], $err<br>";}

	}
//end event
}

//event->add_vuser when user clicks add vuser button
if (isset($add_vuser)) {
	$query = "INSERT INTO vusers VALUES ('$vuser_id', '$client_id', '$v_username', '$v_pwd', '$v_alias', '$domain', '$mail_server', 'n')";
	$result = mysql_query($query, $db);
	$query1 = "SELECT MAX(vuser_id) AS new_vuser_id FROM vusers WHERE client_id = '$client_id'";
	$result1 = mysql_query($query1, $db);
	$row1 = mysql_fetch_object($result1);
	$query2 = "INSERT INTO mail_filters (mail_filters_id, vuser_id, client_id) VALUES ('$mail_filters_id', '$row1->new_vuser_id', '$client_id')";
	$result2 = mysql_query($query2, $db);
}

//event->delete_domain delete from domains table
if ($event == "delete_domain") {
	$query = "DELETE FROM domains WHERE domain_id='$domain_id' AND client_id='$client_id'";
	$result = mysql_query($query, $db);
}

//event->update_server when user clicks update_server update mail_server for all vusers of this client
if (isset($update_server)) {
	$query = "UPDATE vusers SET mail_server = '$mail_server' WHERE client_id='$client_id'";
	$result = mysql_query($query, $db);
	//generate javascript alert and ask user if they realy want to do that
	?>
	<script type="text/javascript">
	var agree=confirm('Are you sure you wish to update global configurations?\n If yes click OK else click Cancel');
	if (agree)
	{
		document.location.replace('email.php?event=update_global&mail_server=<?php echo $mail_server; ?>&client_id=<?php echo $client_id; ?>');
	}
	else
	{
		//keep going bad luck
	}
	</script>
	<?php
}

//event->update_global we are going to product a file and send it to web_server for processing
if ($event == "update_global") {
	//include required file for file manipulation
	include_once("includes/file_manager.php");
	//test template file
	$filename = "includes/email_list.csv";
	if (!($fp = fopen($filename, "r")))  
   		exit("Unable to open the input file, $filename.");
	
	//new file name
	$mytime = date("d-m-Y");
	$myfile = "email_config_$mytime" . ".csv";
	$new_location = "$invoiceDir/$myfile";
	$TheFile = new file_manager($filename);  //Creates Object
	$data = ""; //set data to nothing
	
	$query = "SELECT vusers.*, mail_filters.attachment_size, mail_filters.auto_responder, mail_filters.responder_message, 
	mail_filters.image_types, mail_filters.office_types, mail_filters.web_types, mail_filters.multimedia_types, 
	mail_filters.utility_types FROM vusers, mail_filters 
	WHERE vusers.mail_server = '$mail_server' 
	AND vusers.client_id = '$client_id' 
	AND mail_filters.vuser_id = vusers.vuser_id
	AND vusers.status != 'c'";
	$result = mysql_query($query, $db);
	$err = mysql_error($db);
	if ($err) {echo $err;}

	//loop through the query and write a data file	
	while($row = mysql_fetch_object($result)) {
		//list variables and format to suit
		$vuser_id = $row->vuser_id;
		$v_username = $row->v_username;
		$v_pwd = $row->v_pwd;
		$v_alias = $row->v_alias;
		$domain = $row->domain;
		$mail_server = $row->mail_server;
		$status = $row->status;
		$attachment_size = $row->attachment_size;
		$auto_responder = $row->auto_responder;
		$responder_message = $row->responder_message;
		$image_types = str_replace(",", "|", $row->image_types) . "|";
		if ($image_types == "|") {$image_types = "";}
		$offic_types = str_replace(",", "|", $row->offic_types) . "|";
		if ($offic_types == "|") {$offic_types = "";}
		$web_types = str_replace(",", "|", $row->web_types) . "|";
		if ($web_types == "|") {$web_types = "";}
		$multimedia_types = str_replace(",", "|", $row->multimedia_types) . "|";
		if ($multimedia_types == "|") {$multimedia_types = "";}
		$utility_types = str_replace(",", "|", $row->utility_types);
		//concat all types to a string
		$myfiletypes = $image_types . $offic_types . $web_types . $multimedia_types . $utility_types;
		//in case we use alias for email forwarding we need to define v_email
		$v_email = $v_alias . "@" . $domain;
		//define logic of file according to the status
		if ($status == "n") {
			//check if alias and user are different, if so use the ALIASADD command
			//otherwise use USRADD command
			if ($v_alias != $v_username) {
				$data .= "\"ALIASADD\",\"$mail_server\",\"$domain\",\"$v_username\",\"$v_email\"\n";
			} else {
				$data .= "\"USRADD\",\"$mail_server\",\"$domain\",\"$v_username\",\"$v_pwd\",\"$attachment_size\",\"$myfiletypes\"\n";
			}
		} elseif ($status == "u") {
			//check if alias and user are different, if so use the ALIASUPDATE command
			//otherwise use USRUPDATE command
			if ($v_alias != $v_username) {
				$data .= "\"ALIASUPDATE\",\"$mail_server\",\"$domain\",\"$v_username\",\"$v_email\"\n";
			} else {
				$data .= "\"USRUPDATE\",\"$mail_server\",\"$domain\",\"$v_username\",\"$v_pwd\",\"$attachment_size\",\"$myfiletypes\",\"$auto_responder\",\"$responder_message\"\n";
			}
		} else {
			//check if alias and user are different, if so use the ALIASDELE command
			//otherwise use USRDELE command
			if ($v_alias != $v_username) {
				$data .= "\"ALIASDELE\",\"$mail_server\",\"$domain\",\"$v_username\",\"$v_email\"\n";
			} else {
				$data .= "\"USRDELE\",\"$mail_server\",\"$domain\",\"$v_username\"\n";
			}
		}
			
	}
	
	//copy the data file to destination
	$TheFile->write($data); //Write it now
	$TheFile->copyto($new_location);
	mysql_free_result($result);
	//verify the file and send to chris
	if (file_exists($new_location)) {
		//update database and make all users status of c
		//users marked for deletion may be deleted.
		$query1 = "DELETE FROM vusers WHERE status = 'd' AND client_id = '$client_id'";
		$query2 = "UPDATE vusers SET status = 'c' WHERE status != 'c' AND client_id = '$client_id'";
		$result1 = mysql_query($query1, $db);
		$result2 = mysql_query($query2, $db);
	}

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

//select all the details again
$query1 = "SELECT * FROM vusers WHERE client_id = '$client_id' AND status != 'd'";
$result1 = mysql_query($query1, $db);

//select all the details again
$query2 = "SELECT * FROM domains WHERE client_id = '$client_id'";
$result2 = mysql_query($query2, $db);

$mydomains = "";
$query3 = "SELECT domain_name FROM domains WHERE client_id = '$client_id'";
$result3 = mysql_query($query3, $db);
while ($row3=mysql_fetch_object($result3)) {
	$mydomains .= "<option>$row3->domain_name</option>";
}

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
      <p class="subheading">Users and Virtual Email</p>
      <table width="500" border="0" cellspacing="0" cellpadding="0">
        <tr class="text"> 
          <td width="100" class="text">Alias</td>
          <td width="100" class="text">@ domain</td>
          <td width="75" class="text">User</td>
          <td width="75" class="text">Pass</td>
		  <td width="75" class="text">Status</td>
          <td width="10"><img src="images/spacer.gif" width="10" height="11"></td>
          <td width="140"><img src="images/spacer.gif" width="104" height="14"></td>
        </tr>
        <form action="email.php" method="post">
          <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
          <?php
		while ($row1 = mysql_fetch_object($result1)) {
			$vuser_id = $row1->vuser_id;
			$mail_server = $row1->mail_server;
			echo "<tr valign='middle'>"; 
			echo "<td><input name='v_alias[]' type='text' class='black' value='$row1->v_alias' size='14'></td>";
			echo "<td><select name='domain[]' class='black'><option selected>$row1->domain</option>$mydomains</select></td>";
			echo "<td><input name='v_username[]' type='text' class='black' value='$row1->v_username' size='8'></td>";
			echo "<td><input name='v_pwd[]' type='text' class='black' value='$row1->v_pwd' size='8'></td>";
			echo "<td align=\"center\" class=\"textcentre\">$row1->status</td>";
			echo "<td>&nbsp;</td>";
			echo "<td class='smalltext'><span class='smallwhite'><a href='email_filters.php?event=load_filters&vuser_id=$vuser_id&client_id=$client_id'><font color='#CCFFFF'>filter</font></a></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			echo "<span class='smallwhite'><a href='email.php?event=delete_vuser&vuser_id=$vuser_id&client_id=$client_id'><font color='#CCFFFF'>delete</font></a></span></td></tr>";
			echo "<input name=\"vuser_id[]\" type=\"hidden\" value=\"$vuser_id\">";
			echo "<input name=\"mail_server[]\" type=\"hidden\" value=\"$mail_server\">";
		}
		?>
          <tr valign="middle"> 
            <td colspan="5" align="right" height="30">
<input type="submit" name="update_users" value="Update Users" class="smallbluebutton"></td>
            <td></td>
			<td></td>
		  </tr>
        </form>
        <tr valign="middle">
          <td colspan="7" class="smalltext">&nbsp;</td>
        </tr>
        <tr valign="middle"> 
          <td colspan="7" class="smalltext">Add New:</td>
        </tr>
        <form action="email.php" method="post">
          <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
		  <input type="hidden" name="mail_server" value="<?php echo $mail_server; ?>">
          <tr valign="middle"> 
            <td><input name="v_alias" type="text" class="black" size="14"></td>
            <td><select name="domain" class="black"><?php echo $mydomains; ?></select> </td>
            <td><input name="v_username" type="text" class="black" size="8"></td>
            <td><input name="v_pwd" type="text" class="black" size="8"> </td>
			<td align="center" class="textcentre">n</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr> 
            <td height="30" colspan="5" align="right" valign="middle"> 
              <input type="submit" name="add_vuser" value="Add User" class="smallbluebutton"></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
		  </tr>
        </form>
      </table>
	  <p class="subheading">Mail Server Name</p>
      <table width="150" border="0" cellspacing="0" cellpadding="0">
        <form action="email.php" method="post">
          <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
          <tr valign="middle"> 
            <td>
		<select name="mail_server" size="1" class="black">
		<option selected><?php echo $mail_server; ?></option>
		<option>mail.worldwebms.com</option>
		<option>mail.musicorp.com.au</option>
		</select>
	  </td>
          </tr>
          <tr> 
            <td height="30" align="left" valign="middle"> 
          	<input type="submit" name="update_server" value="Update Global Mail Configuration" class="smallbluebutton">
            </td>
          </tr>
        </form>
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
