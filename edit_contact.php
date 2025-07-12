<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* edit_contact template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 6/12/2002
* Client: WorldWeb, Domain: database intranet
* This template consists of 3 sections: Navigation, SQL Queries & formatting, HTML form directed to itself
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
//include a globals file for db connection
include_once("includes/globals.php");

//establish a persistent connection to mysql
$db = mysql_pconnect ($hostName, $userName, $password);
mysql_select_db($database);

//event -> if update event update the database than continue
if (isset($update)) {
	$query = "UPDATE contacts SET title='$mytitle', first_name='$first_name', last_name='$last_name', position='$position', mobile='$mobile', phone='$phone', fax='$fax', email='$email', primary_contact='$primary_contact'  WHERE contact_id = '$contact_id' AND client_id = '$client_id'";
	$result = mysql_query ($query, $db);
	//check for errors
	$err = mysql_error();
	if ($err) {
		$message = "There is an error updating: $err";
	} else {
		$message = "Contact details updated";
	}
	//select all the details again
	$query1 = "SELECT * FROM contacts WHERE client_id = '$client_id' AND contact_id = '$contact_id'";
	$result1 = mysql_query ($query1, $db);
	$row1 = mysql_fetch_object($result1);
	$mytitle = $row1->title;
	$first_name = $row1->first_name;
	$last_name = $row1->last_name;
	$position = $row1->position;
	$mobile = $row1->mobile;
	$phone = $row1->phone;
	$fax = $row1->fax;
	$email = $row1->email;
	$primary_contact = $row1->primary_contact;
//end update routine
}

//event -> if delete event delete the contact from database than continue
if (isset($delete)) {
	$query = "DELETE FROM contacts WHERE contact_id = '$contact_id' AND client_id = '$client_id'";
	$result = mysql_query ($query, $db);
	//check for errors
	$err = mysql_error();
	if ($err) {
		$message = "There is an error deleting: $err";
	} else {
		$message = "Contact details deleted";
	}
//end delete routine
}

//select all contact data for client
$query = "SELECT * FROM contacts WHERE client_id = '$client_id' AND contact_id = '$contact_id'";
$result = mysql_query ($query, $db);
$row = mysql_fetch_object($result);
$mytitle = $row->title;
$first_name = $row->first_name;
$last_name = $row->last_name;
$position = $row->position;
$mobile = $row->mobile;
$phone = $row->phone;
$fax = $row->fax;
$email = $row->email;
$primary_contact = $row->primary_contact;

//close connection
mysql_close($db);

//include a header file
include_once("includes/header.inc");
?>

<body leftmargin="0" rightmargin="0" marginwidth="0" topmargin="0" background="images/bg.jpg">
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td height="100%" align="center" valign="middle">
	<p class="smallwhite">Edit relevant details and click update button.</p>
	  <table width="450" border="0" align="center" cellpadding="0" cellspacing="0">
	  <form action="edit_contact.php" method="post">
	  <input name="contact_id" type="hidden" value="<?php echo $contact_id; ?>">
	  <input name="client_id" type="hidden" value="<?php echo $client_id; ?>">
        <tr>
		  <td width="150" class="smallwhite">Title:</td>
          <td width="300"><input name="mytitle" type="text" class="form" value="<?php echo $mytitle; ?>" size="3"></td>
        </tr>
		<tr> 
		  <td class="smallwhite">First Name:</td> 
		  <td><input name="first_name" type="text" class="form" value="<?php echo $first_name; ?>" size="15"></td>
		</tr>
		<tr>
		  <td class="smallwhite">Last Name:</td>
          <td><input name="last_name" type="text" class="form" value="<?php echo $last_name; ?>" size="15"></td>
		</tr>
		<tr>
		  <td class="smallwhite">position:</td>
          <td><input name="position" type="text" class="form" value="<?php echo $position; ?>" size="15"></td>
		</tr>
		<tr>
		  <td class="smallwhite">Mobile:</td>
          <td><input name="mobile" type="text" class="form" value="<?php echo $mobile; ?>" size="15"></td>
		</tr>
		<tr>
		  <td class="smallwhite">Phone:</td>
          <td><input name="phone" type="text" class="form" value="<?php echo $phone; ?>" size="15"></td>
		</tr>
		<tr>
		  <td class="smallwhite">Fax:</td>
		  <td><input name="fax" type="text" class="form" value="<?php echo $fax; ?>" size="15"></td>
		</tr>
		<tr>
		  <td class="smallwhite">Email:</td>
		  <td><input name="email" type="text" class="form" value="<?php echo $email; ?>" size="40"></td>
		</tr>
		<tr>
		  <td class="smallwhite">Primary Contact?</td>
		  <td><input name="primary_contact" type="text" class="form" value="<?php echo $primary_contact; ?>" size="15"></td>
        </tr>
		<tr><td colspan="2" height="40"><input name="update" type="submit" value="Update Contact" class="form">&nbsp;<input name="delete" type="submit" value="Delete Contact" class="form"></td></tr>
		<tr><td colspan="2" height="40" class="smallwhite"><?php echo $message; ?></tr>
		</form>
      </table> </td>
  </tr>
</table>
</body>
</html>