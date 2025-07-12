<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* add_client template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 12/5/2003
* Client: WorldWeb, Domain: database intranet
* This template consists of 1 sections: HTML form directed to itself
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
include_once("includes/DateField.php");
//define date
$today = date("d/m/Y" ,time());

include_once 'includes/CapsApi.php';

$managers = array();
$results = $db->execute('SELECT p_id, first_name, last_name FROM ps WHERE r IN ( ?, ? )', array('a', 'm'));
foreach ($results as $row)
	$managers[$row['p_id']] = $row['first_name'] . ' ' . $row['last_name'];

//event -> if submit event insert client record to the database than continue
if (isset($add)) {
	//declare variables not comming from form
	$client_id = "";
	$old_log = "";
	//format next contact
	$next_contact = FromNextContact($next_contact);
	//verify check boxes for access control
	if ($general_control == "on") {
		$general_control = "yes";
	} else {$general_control = "no";}
	if ($contact_control == "on") {
		$contact_control = "yes";
	} else {$contact_control = "no";}
	if ($email_control == "on") {
		$email_control = "yes";
	} else {$email_control = "no";}
	if ($domain_control == "on") {
		$domain_control = "yes";
	} else {$domain_control = "no";}
	if ($job_control == "on") {
		$job_control = "yes";
	} else {$job_control = "no";}
	if ($postal_copy != "Yes" ) {
		$postal_copy = "No";
	}
	
	// Auto assign agreement number
	if( $agreement_number == "" ) {
		$agreement_number = $db->getone('SELECT MAX(agreement_number) AS next_number FROM clients WHERE agreement_number>=? AND agreement_number<?', array(20000, 98000));
		if ($agreement_number == 0)
			$agreement_number = 20000;
		else
			$agreement_number += 1;
	}
	
	$data = array(
		'client_name' => $client_name,
		'trading_name' => $trading_name,
		'agreement_number' => $agreement_number,
		'rep_id' => $rep_id,
		'next_contact' => $next_contact,
		'address_1' => $address_1,
		'address_2' => $address_2,
		'suburb' => $suburb,
		'post_code' => $post_code,
		'state' => $state,
		'postal_copy' => $postal_copy,
		'postal_address_1' => $postal_address_1,
		'postal_address_2' => $postal_address_2,
		'postal_suburb' => $postal_suburb,
		'postal_post_code' => $postal_post_code,
		'postal_state' => $postal_state,
		'phone1' => $phone1,
		'phone2' => $phone2,
		'fax' => $fax,
		'email' => $email,
		'website_url' => $website_url,
		'old_log' => $old_log,
		'abn' => $abn_number,
		'general_control' => $general_control,
		'contact_control' => $contact_control,
		'email_control' => $email_control,
		'domain_control' => $domain_control,
		'job_control' => $job_control,
		'origin' => $origin,
		'status' => $status
	);
	
	$db->execute(
		'INSERT INTO clients (' . implode(', ', array_keys($data)) . ') VALUES (' . trim(str_repeat(', ?', count($data)), ', ') . ')',
		$data
	);

	//and redirect to index1.php with a new client_id
	$new_client_id = $db->insert_id();
	add_to_audit_history( $db, "client", $new_client_id, "created" );
	//close db
	//redirect to index1.php
	echo "<script language=\"JavaScript\">document.location.replace('index1.php?client_id=$new_client_id');</script>";
//end insert routine
}

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
<?php include_once("includes/top.php"); ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="1%"><img src="images/spacer.gif" width="8" height="27"></td>
    <td class="clienttitle">&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td class="text"><u>Add New Client: General Information and Logs<br>
      <img src="images/spacer.gif" width="39" height="10"> </u></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td align="left" valign="top" class="text">
      <table width="600" border="0" align="left" cellpadding="3" cellspacing="3">
        <form action="add_client.php" method="post">
          <tr bgcolor="#0070A6">
            <td width="100" valign="top" class="text">Client Name:</td>
            <td width="200" valign="top" class="text"> <input tabIndex="1" name="client_name" type="text" class="smallblue" size="30" maxlength="60">
            </td>
            <td width="100" valign="top" class="text">Client Number:</td>
            <td valign="top" class="text"> <input tabIndex="2" name="agreement_number" type="text" class="smallblue" size="5"><br>(leave empty for auto-generated)
            </td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">Trading Name:</td>
            <td><input tabIndex="1" name="trading_name" type="text" class="smallblue" size="30" maxlength="60"></td>
            <td class="text">Next Contact:</td>
            <td><?php
            
            $date_field = new DateField( "next_contact", strtotime( "+7 days" ) );
            echo $date_field->getHTML();
            
            ?></td>
            <!-- <td> <input tabIndex="2" name="next_contact" type="text" class="smallblue" size="10" value="<?= date( "d-M-Y", strtotime( "+7 days" ) ) ?>"> -->
            </td>
          </tr>
          <tr><td height="10" class="text"> </td></tr>
          <tr>
            <td class="text" colspan="2">&nbsp;</td>
            <td bgcolor="#0070A6" class="text" colspan="2"><label id="postal_copy"><input tabIndex="4" id="postal_copy" type="checkbox" name="postal_copy" value="Yes" onchange="e_onPostalCopyChange(this)" checked> Same as street address</label></td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">Address 1:</td>
            <td> <input tabIndex="3" name="address_1" type="text" class="smallblue" size="25" maxlength="60" onchange="e_onAddressChange(this)"></td>
            <td class="text">Postal Address 1:</td>
            <td> <input tabIndex="4" name="postal_address_1" type="text" class="smallblue" size="25" maxlength="60"></td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">Address 2:</td>
            <td> <input tabIndex="3" name="address_2" type="text" class="smallblue" size="25" maxlength="60" onchange="e_onAddressChange(this)"></td>
            <td class="text">Postal Address 2:</td>
            <td> <input tabIndex="4" name="postal_address_2" type="text" class="smallblue" size="25" maxlength="60"></td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">Suburb:</td>
            <td> <input tabIndex="3" name="suburb" type="text" class="smallblue" size="15" onchange="e_onAddressChange(this)">
            </td>
            <td class="text">Postal Suburb:</td>
            <td> <input tabIndex="4" name="postal_suburb" type="text" class="smallblue" size="15">
            </td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">Post Code:</td>
            <td> <input tabIndex="3" name="post_code" type="text" class="smallblue" size="4" onchange="e_onAddressChange(this)"></td>
            <td class="text">Postal Post Code:</td>
            <td> <input tabIndex="4" name="postal_post_code" type="text" class="smallblue" size="4"></td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">State:</td>
            <td> <select tabIndex="3" name="state" size="1" class="smallblue" onchange="e_onAddressChange(this)">
                <option selected>SA</option>
                <option>ACT</option>
                <option>NSW</option>
                <option>NT</option>
                <option>QLD</option>
                <option>TAS</option>
                <option>VIC</option>
                <option>WA</option>
            </select></td>
            <td class="text">Postal State:</td>
            <td> <select tabIndex="4" name="postal_state" size="1" class="smallblue">
                <option selected>SA</option>
                <option>ACT</option>
                <option>NSW</option>
                <option>NT</option>
                <option>QLD</option>
                <option>TAS</option>
                <option>VIC</option>
                <option>WA</option>
              </select></td>
          </tr>
          <tr>
            <td height="20" class="text" colspan="4">&nbsp;</td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">Phone1:</td>
            <td> <input tabIndex="5" name="phone1" type="text" class="smallblue" size="12" maxlength="16"></td>
            <td class="text">1300 Number:</td>
            <td> <input tabIndex="6" name="phone2" type="text" class="smallblue" size="12" maxlength="16"></td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">Fax:</td>
            <td> <input tabIndex="5" name="fax" type="text" class="smallblue" size="12" maxlength="12"></td>
            <td class="text">Email:</td>
            <td> <input tabIndex="6" name="email" type="text" class="smallblue" size="20" maxlength="50"></td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">Website URL:</td>
            <td> <input tabIndex="5" name="website_url" type="text" class="smallblue" size="20" maxlength="50"></td>
            <td class="text">ABN Number:</td>
            <td> <input tabIndex="6" name="abn_number" type="text" class="smallblue" size="20" maxlength="50">
            </td>
          </tr>
          <tr>
            <td height="20" class="text" colspan="4">&nbsp;</td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">Status:</td>
            <td> <select tabIndex="5" name="status" size="1" class="smallblue">
<?php

	$status = "c";
	foreach( $status_options as $value => $text ) {
		echo "              <option value=\"" . $value . "\"" . ( $value == $status ? " selected" : "" ) . ">" . $text . "\n";
	}

?>              </select> </td>
            <td class="text">Origin:</td>
            <td> <input tabIndex="6" name="origin" type="text" class="smallblue" size="20" maxlength="50"></td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">Account Manager:</td>
            <td> <select name="rep_id" tabIndex="6" class="smallblue" size="1">
<?php

	foreach( $managers as $key => $value ) {
		echo "              <option value=\"" . $key . "\"" . ( $key == $pid ? " selected" : "" ) . ">" . $value . "\r\n";
	}

?>
            </select></td>
          </tr>
		  <tr>
            <td height="20" class="text">&nbsp;</td>
            <td>&nbsp;</td>
            <td class="text">&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
		  <tr bgcolor="#0070A6">
            <td colspan="4" class="text">
              <label for="general_control">&nbsp;General Control:&nbsp;<input id="general_control" tabIndex="7" name="general_control" type="checkbox" checked></label>
			  <label for="contact_control">&nbsp;Contact Control:&nbsp;<input id="contact_control" tabIndex="7" name="contact_control" type="checkbox" checked></label>
			  <label for="email_control">&nbsp;Email Control:&nbsp;<input id="email_control" tabIndex="7" name="email_control" type="checkbox"></label>
			  <label for="domain_control">&nbsp;Domain Control:&nbsp;<input id="domain_control" tabIndex="7" name="domain_control" type="checkbox"></label>
			  <label for="job_control">&nbsp;Job Control:&nbsp;<input id="job_control" tabIndex="7" name="job_control" type="checkbox"></label>
            </td>
          </tr>
          <tr bgcolor="#0070A6">
            <td height="50" colspan="4" class="text"> <input tabIndex="8" name="add" type="submit" value="Add Client" class="smallbluebutton">
            </td>
          </tr>
        </form>
        <?php include( "includes/client.js" ) ?>
      </table>
      <p>&nbsp;</p>
    </td>
  </tr>
</table>
</body>
</html>