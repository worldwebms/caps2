<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* email template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 12/1/2004
* Client: WorldWeb, Domain: database intranet
* This template consists of 3 sections: Navigation, SQL Queries & formatting, HTML output
* Important files included in this template are: header.inc (head section and javascript library),
* globals.php (connection string),
* Last Modified: 21/9/2005 By Aviv Efrat
* Please remember to backup template before modifying
*
*********************************************************************************************************
*/
//begin session
session_start();
//include a globals file for db connection
include_once("includes/globals.php");
//include a function.php file
include_once("includes/functions.php");
//define date
$today = date("d/m/Y" ,time());

//establish a persistent connection and get all required data.
// select valuations for  valuer
$db = mysql_pconnect ($hostName, $userName, $password);
mysql_select_db($database);

//event -> if update event update the database than continue
if ($event == "delete_vuser") {
	?>
	<script type="text/javascript">
	var agree=confirm('Are you sure you wish to delete this email user?\n If yes click OK else click Cancel');
	if (agree)
	{
		document.location.replace('email.php?event=deleteme&vuser_id=<?php echo $vuser_id; ?>&client_id=<?php echo $client_id; ?>');
	}
	else
	{
		//keep going bad luck
	}
	</script>
	<?php
}

if ($event == "deleteme") {
	//update vusers table
	$query = "UPDATE vusers SET status = 'd' WHERE vuser_id = '$vuser_id'";
	$result = mysql_query($query, $db);
	//and update all aliases for this vuser
	$query1 = "UPDATE valiases SET status = 'd' WHERE vuser_id = '$vuser_id'";
	$result1 = mysql_query($result1);
	//and update forwarding for this vuser
	$query2 = "UPDATE forwarding SET status = 'd' WHERE vuser_id = '$vuser_id'";
	$result2 = mysql_query($query2);
	//delete the email_filters account we no longer need it
	$query3 = "DELETE FROM email_filters WHERE vuser_id = '$vuser_id'";
	$result3 = mysql_query($query2);
}

//event->update_users update vusers table
if (isset($update_users)) {
	$mycount = count($vuser_id);
	//loop from 0 to mycount and update
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
		$query1 = "UPDATE vusers SET v_username='$v_username[$i]', v_pwd='$v_pwd[$i]', domain='$domain[$i]', mail_server='$mail_server', status='$mystatus' WHERE vuser_id='$vuser_id[$i]'";
		$result1 = mysql_query($query1, $db);
		$err1 = mysql_error($db);
		if ($err1) {echo "$v_username[$i], $err<br>";}

	}
//end event
}

//event->add_vuser when user clicks add vuser button
if (isset($add_vuser)) {
	//before we add the alias we need to test if this email address is valid
	//and does not exist on our server already
	$testresult = testAccount($v_username, $domain, $domain);
	if ($testresult == "yes") {
		$query = "INSERT INTO vusers VALUES ('$vuser_id', '$client_id', '$v_username', '$v_pwd', '$domain', '$mail_server', 'n')";
		$result = mysql_query($query, $db);
		$query1 = "SELECT MAX(vuser_id) AS new_vuser_id FROM vusers WHERE client_id = '$client_id'";
		$result1 = mysql_query($query1, $db);
		$row1 = mysql_fetch_object($result1);
		$query2 = "INSERT INTO mail_filters (mail_filters_id, vuser_id, client_id) VALUES ('$mail_filters_id', '$row1->new_vuser_id', '$client_id')";
		$result2 = mysql_query($query2, $db);
		$query3 = "INSERT INTO forwarding (forward_id, vuser_id, client_id) VALUES ('$forward_id', '$row1->new_user_id', '$client_id')";
		$result3 = mysql_query($query3, $db);
	} else {
		?>
		<script type="text/javascript">
		var agree=alert('This email address already exists on our mail server!\n Click OK to select another one');
		if (agree)
		{
			document.location.replace('email.php?client_id=<?php echo $client_id; ?>');
		}
		</script>
		<?php
	}
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
		document.location.replace('email.php?client_id=<?php echo $client_id; ?>');
	}
	</script>
	<?php
}

//event->update_global we are going to product a file and send it to web_server for processing
if ($event == "update_global") {
	//include required file for file manipulation
	include_once("includes/file_manager.php");
	$mytime = date("d-m-Y");
	$myfile = "email_config_$mytime" . ".csv";
	$sourcePath = $templateDir."/email_list.csv";
	$destinationPath = $invoiceDir."/".$myfile;
	//test template file
	if (!($fp = fopen($sourcePath, "r")))  
   		exit("Unable to open the input file, $myfile.");
	//initiate file manager class
	$TheFile = new file_manager($sourcePath);  //Creates Object
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
		//make sure the vuserdelete flag is set to no
		$vuserdelete = "no";
		//list variables and format to suit
		$vuser_id = $row->vuser_id;
		$v_username = $row->v_username;
		$v_pwd = $row->v_pwd;
		$domain = $row->domain;
		$mail_server = $row->mail_server;
		$status = $row->status;
		$attachment_size = $row->attachment_size;
		$auto_responder = $row->auto_responder;
		$responder_message = htmlentities(str_replace("\r\n", '\r\n', str_replace(',', '.', $row->responder_message)),ENT_QUOTES);
		$image_types = str_replace(",", "|", $row->image_types) . "|";
		if ($image_types == "|") {$image_types = "";}
		$office_types = str_replace(",", "|", $row->office_types) . "|";
		if ($office_types == "|") {$office_types = "";}
		$web_types = str_replace(",", "|", $row->web_types) . "|";
		if ($web_types == "|") {$web_types = "";}
		$multimedia_types = str_replace(",", "|", $row->multimedia_types) . "|";
		if ($multimedia_types == "|") {$multimedia_types = "";}
		$utility_types = str_replace(",", "|", $row->utility_types);
		//concat all types to a string
		$myfiletypes = $image_types . $office_types . $web_types . $multimedia_types . $utility_types;
				
		//define logic of file according to the status and add/update/delete vusers
		if ($status == "n") {
			//use USRADD command
			$data .= "\"USRADD\",\"$mail_server\",\"$domain\",\"$v_username\",\"$v_pwd\",\"$attachment_size\",\"$myfiletypes\"\n";
		} elseif ($status == "u") {
			//use USRUPDATE command
			$data .= "\"USRUPDATE\",\"$mail_server\",\"$domain\",\"$v_username\",\"$v_pwd\",\"$attachment_size\",\"$myfiletypes\",\"$auto_responder\",\"$responder_message\"\n";
		} else {
			//we must delete the user but we need to do it after deleting all its aliases so we will do it later
			//instead we are going to set a flag for this user to be delted
			$vuserdelete = "yes";
		}
		
		//now check all aliases for this user and update records
		$alias_query = "SELECT valias_id, valias, domain, status FROM valiases WHERE vuser_id = '$vuser_id' AND status != 'c'";
		$alias_result = mysql_query($alias_query, $db);
		$alias_numrows = mysql_num_rows($alias_result);
		//if there are records list them otherwise skip this user
		if ($alias_numrows > 0) {
			while ($alias_row = mysql_fetch_object($alias_result)) {
				$v_email = $alias_row->valias . "@" . $alias_row->domain;
				//define logic of file according to the status and add/update/delete valiases
				if ($alias_row->status == "n") {
					//use ALIASADD command
					$data .= "\"ALIASADD\",\"$mail_server\",\"$domain\",\"$v_username\",\"$v_email\"\n";
				} elseif ($alias_row->status == "u") {
					//use ALIASUPDATE command
					$data .= "\"ALIASUPDATE\",\"$mail_server\",\"$domain\",\"$v_username\",\"$v_email\"\n";
				} else {
					//use ALIASDELE command
					$data .= "\"ALIASDELE\",\"$mail_server\",\"$domain\",\"$v_username\",\"$v_email\"\n";
				}
			}
		} else {
			//skip vuser
		}
		
		//no if the vuserdelete flag is set to yes we are going to delete the user
		if ($vuserdelete == "yes") {
			//use USRDELE command
			$data .= "\"USRDELE\",\"$mail_server\",\"$domain\",\"$v_username\"\n";
		}
		
		//now select the forwarding status for this account if exist
		$forward_query = "SELECT forward_id, forwarding, forward_to, keep_mail, status FROM forwarding WHERE vuser_id = '$vuser_id' AND status != 'c'";
		$forward_result = mysql_query($forward_query, $db);
		$forward_numrows = mysql_num_rows($forward_result);
		if ($forward_numrows > 0) {
			//add forward instructions according to status
			$forward_row = mysql_fetch_object($forward_result);
			$keep_mail = $forward_row->keep_mail;
			if ($keep_mail == "yes") {
				$mykeep_mail = "Y";
			} else {
				$mykeep_mail = "N";
			}
			if ($forward_row->status == "n" & $forward_row->forwarding == "yes") {
				//use FORWARDADD command
				$data .= "\"FORWARDADD\",\"$mail_server\",\"$domain\",\"$v_username\",\"$forward_row->forward_to\",\"$mykeep_mail\"\n";
			} elseif ($forward_row->status == "u" & $forward_row->forwarding == "yes") {
				//use FORWARDUPDATE command
				$data .= "\"FORWARDUPDATE\",\"$mail_server\",\"$domain\",\"$v_username\",\"$forward_row->forward_to\",\"$mykeep_mail\"\n";
			} else {
				//use FORWARDDELE command
				$data .= "\"FORWARDDEL\",\"$mail_server\",\"$domain\",\"$v_username\",\"$forward_row->forward_to\"\n";
			}
		}
					
	}
	
	mysql_free_result($result);
	//copy the data file to destination
	$TheFile->write($data); //Write it now
	$ok = $TheFile->copyto($destinationPath);
	if ($ok == 0) {
		echo "failed writing to data file";
	} else {
	
		//verify the file and send to chris
		if (file_exists($destinationPath)) {
			//update database and make all users status of c
			//users marked for deletion may be deleted.
			$query1 = "DELETE FROM vusers WHERE status = 'd' AND client_id = '$client_id'";
			$query2 = "UPDATE vusers SET status = 'c' WHERE status != 'c' AND client_id = '$client_id'";
			$query3 = "DELETE FROM valiases WHERE status = 'd' AND client_id = '$client_id'";
			$query4 = "UPDATE valiases SET status = 'c' WHERE status != 'c' AND client_id = '$client_id'";
			$query5 = "DELETE FROM forwarding WHERE status = 'd' AND client_id = '$client_id'";
			$query6 = "UPDATE forwarding SET status = 'c' WHERE status != 'c' AND client_id = '$client_id'";
			$result1 = mysql_query($query1, $db);
			$result2 = mysql_query($query2, $db);
			$result3 = mysql_query($query3, $db);
			$result4 = mysql_query($query4, $db);
			$result5 = mysql_query($query5, $db);
			$result6 = mysql_query($query6, $db);
		}	

		//send the file to mail server for processing
		$scp_command = "scp -q -P 222 " . $destinationPath . " root@" . $clientMailServer . ":/tmp";
		
		//execute the command
		$scp_result = exec($scp_command,$scp_output,$scp_retval);
		
		//ssh the mail server and execute the script
		$ssh_command = "ssh -p 222 root@" . $mailHostName . " '/usr/sbin/vmail_manager.php' '/tmp/$myfile' '$clientMailServer' '$logEmail'";
		//and execute it
		$ssh_result = exec($ssh_command,$ssh_output,$ssh_retval);
		
	}
}

if ($event == "export_list") {
	
	//define empty array to hold all rows for data
	$result_array = array();
	
	//define query and get all users and their aliases
	$query = "SELECT vuser_id, v_username, v_pwd, domain FROM vusers WHERE mail_server = '$clientMailServer' AND client_id = '$client_id'";
	
	$result = mysql_query($query, $db);
	$err = mysql_error($db);
	if ($err) {echo $err;}

	//loop through the query and write a data file	
	while($row = mysql_fetch_object($result)) {
		//list variables and format to suit
		$vuser_id = $row->vuser_id;
			
		//define variables to insert into the array
		$user_email = $row->v_username . "@" . $row->domain;
		$user_name = $row->v_username . "@" . $row->domain;
		$user_pwd = $row->v_pwd;
		
		//write an array of values
		$myarray = array('E-MAIL'=>$user_email,'USER NAME'=>$user_name,'PASSWORD'=>$user_pwd);
		//push the newly created array into the main result_array
		array_push($result_array, $myarray);
		
		//now check all aliases for this user and update records
		$alias_query = "SELECT valias_id, valias, domain, status FROM valiases WHERE vuser_id = '$vuser_id'";
		$alias_result = mysql_query($alias_query, $db);
		$alias_numrows = mysql_num_rows($alias_result);
		//if there are records list them otherwise skip this user
		if ($alias_numrows > 0) {
			while ($alias_row = mysql_fetch_object($alias_result)) {
				
				//define variables to insert into the array
				$alias_email = $alias_row->valias . "@" . $alias_row->domain;
				
				//write an array of values
				$myarray = array('E-MAIL'=>$alias_email,'USER NAME'=>$user_name,'PASSWORD'=>$user_pwd);
				//push the newly created array into the main result_array
				array_push($result_array, $myarray);
			}
		} else {
			//skip vuser
		}	
	}	
	$pdftitle = 'User Email List';
	session_register("pdftitle");
	session_register("result_array");

	//launch pdf in a new popup window
	echo "<script language='JavaScript'>window.open('poplauncher.php','mylist','width=600,height=400,resizable=yes,menubar=yes,scrollbars=yes')</script>";
//end dopdf event
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

$mydomains = "";
$query2 = "SELECT domain_name FROM domains WHERE client_id = '$client_id'";
$result2 = mysql_query($query2, $db);
while ($row2=mysql_fetch_object($result2)) {
	$mydomains .= "<option>$row2->domain_name</option>";
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
      <p class="subheading">Users Email Accounts</p>
      <table width="550" border="0" cellspacing="0" cellpadding="0">
        <tr class="text"> 
          <td width="100" class="text">User</td>
          <td width="150" class="text">@ domain</td>
          <td width="75" class="text">Password</td>
          <td width="75" class="text">Status</td>
          <td width="10"><img src="images/spacer.gif" width="10" height="11"></td>
          <td width="140"><img src="images/spacer.gif" width="104" height="14"></td>
        </tr>
        <form action="email.php" method="post">
          <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
		  <input type="hidden" name="mail_server" value="<?php echo $clientMailServer; ?>">
          <?php
		while ($row1 = mysql_fetch_object($result1)) {
			echo "<tr valign='middle'>"; 
			echo "<td><input name=\"v_username[]\" type=\"text\" class=\"black\" value=\"$row1->v_username\" size=\"8\"></td>";
			echo "<td><select name=\"domain[]\" class=\"black\"><option selected>$row1->domain</option>$mydomains</select></td>";
			echo "<td><input name=\"v_pwd[]\" type=\"text\" class=\"black\" value=\"$row1->v_pwd\" size=\"8\"></td>";
			echo "<td align=\"center\" class=\"textcentre\">$row1->status</td>";
			echo "<td>&nbsp;</td>";
			echo "<td class=\"smalltext\"><span class=\"smallwhite\"><a href=\"email_filters.php?event=load_filters&vuser_id=$row1->vuser_id&client_id=$client_id\"><font color='#CCFFFF'>settings</font></a></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			echo "<span class=\"smallwhite\"><a href=\"email.php?event=delete_vuser&vuser_id=$row1->vuser_id&client_id=$client_id\"><font color='#CCFFFF'>delete</font></a></span></td></tr>";
			echo "<input name=\"vuser_id[]\" type=\"hidden\" value=\"$row1->vuser_id\">";
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
		  <input type="hidden" name="mail_server" value="<?php echo $clientMailServer; ?>">
          <tr valign="middle"> 
            <td><input name="v_username" type="text" class="black" size="14"></td>
            <td><select name="domain" class="black">
                <?php echo $mydomains; ?></select> </td>
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
		<tr valign="middle"> 
          <td colspan="7" class="text"><a href="email.php?event=export_list&client_id=<?php echo $client_id; ?>"><img src="images/pdf.gif" border="0"></a>&nbsp;Print mail account list</td>
        </tr>
      </table>
	  <p class="subheading">Mail Server Name</p>
      <table width="150" border="0" cellspacing="0" cellpadding="0">
        <form action="email.php" method="post">
          <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
          <tr valign="middle">
            <td>
		<select name="mail_server" size="1" class="black">
		        <option selected><?php echo $clientMailServer; ?></option>
				<option>mail.worldwebms.com</option>
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
    <td colspan="2" valign="top" class="text">
	<?php
	//print output of update mail procedure $ssh_output";
	if ($ssh_output) {
		foreach ($ssh_output as $key => $value) {
			echo "<span class=\"text\">K: ".$key." <br> ".$value."</span><br>";
		}
	}
	?>
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