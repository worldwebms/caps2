<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* myclient template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 12/5/2003
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
require_once("includes/CapsApi.php");
//define date
$today = date("d/m/Y" ,time());

//event->update-----------------------------when user clicks update button------------------------------
if (isset($update)) {
	//format next contact
	$next_contact = FromNextContact($next_contact);
	//update client access controls
	$general_control = checkControl($general_control);
	$contact_control = checkControl($contact_control);
	$email_control = checkControl($email_control);
	$domain_control = checkControl($domain_control);
	$job_control = checkControl($job_control);
	$postal_copy = ( $postal_copy != "Yes" ? "No" : $postal_copy );
	
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
	
	$sql = '';
	foreach ($data as $k => $v)
		$sql .= ($sql == '' ? '' : ', ') . $k . '=?';
	$data[] = $client_id;
	$db->execute(
		'UPDATE clients SET ' . $sql . ' WHERE client_id=?',
		$data
	);
	
	//redirect back to index1.php
	echo "<script language=\"JavaScript\">document.location.replace('index1.php?client_id=$client_id');</script>";

//end update routine
} elseif (isset($delete)) {
	//redirect page to delete_client.php
	echo "<script language=\"JavaScript\">document.location.replace('delete_client.php?client_id=$client_id');</script>";
//end delete routine
}

/*
finally get all the details again and display on the screen.
If window is called without a client_id number than display
blank screen ready for insertion.
*/
//if there is a client_id generate appropriate sql
$client_row = $db->getrow(
	'SELECT * FROM clients WHERE client_id=?',
	array($client_id)
);
if ($client_row) {
	$client_name = $client_row['client_name'];
	$trading_name = $client_row['trading_name'];
	$agreement_number = $client_row['agreement_number'];
	$rep_id = $client_row['rep_id'];
	$next_contact = $client_row['next_contact'];
	//format next contact
	$next_contact = ToNextContact($next_contact);
	$address_1 = $client_row['address_1'];
	$address_2 = $client_row['address_2'];
	$suburb = $client_row['suburb'];
	$post_code = $client_row['post_code'];
	$state = $client_row['state'];
	$postal_copy = $client_row['postal_copy'];
	$postal_address_1 = $client_row['postal_address_1'];
	$postal_address_2 = $client_row['postal_address_2'];
	$postal_suburb = $client_row['postal_suburb'];
	$postal_post_code = $client_row['postal_post_code'];
	$postal_state = $client_row['postal_state'];
	$phone1 = $client_row['phone1'];
	$phone2 = $client_row['phone2'];
	$fax = $client_row['fax'];
	$email = $client_row['email'];
	$website_url = $client_row['website_url'];
	$old_log = $client_row['old_log'];
	$abn = $client_row['abn'];
	$general_control = checkControl($client_row['general_control']);
	$contact_control = checkControl($client_row['contact_control']);
	$email_control = checkControl($client_row['email_control']);
	$domain_control = checkControl($client_row['domain_control']);
	$job_control = checkControl($client_row['job_control']);
	$origin = $client_row['origin'];
	$status = $client_row['status'];
	//format control switches
}
//make sure if rep_id is empty to default to the current user
if (empty($rep_id)) {$rep_id = $pid;}
//make sure if next_contact is empty to give it default 01-Jan-2003
if ($next_contact == "01-Jan-1970") {$next_contact = date( "d-M-Y", strtotime( "+7 days" ) );}

$managers = array();
if( $rep_id == "001" ) { $managers["001"] = "(unknown)"; }
$results = $db->execute('SELECT p_id, first_name, last_name FROM ps ORDER BY first_name, last_name');
foreach ($results as $row)
	$managers[$row["p_id"]] = $row["first_name"] . " " . $row["last_name"];

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
    <td class="text"><u>General Information and Logs<br>
      <img src="images/spacer.gif" width="39" height="10"> </u></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td align="left" valign="top" class="text">
      <table width="600" border="0" align="left" cellpadding="3" cellspacing="3">
        <form action="myclient.php" method="post">
          <input name="client_id" type="hidden" value="<?= htmlspecialchars($client_id) ?>">
          <tr bgcolor="#0070A6">
            <td width="100" valign="top" class="text">Client Name:</td>
            <td width="200" valign="top" class="text"> <input tabIndex="1" name="client_name" type="text" class="smallblue" value="<?= htmlspecialchars($client_name) ?>" size="30" maxlength="60">
            </td>
            <td width="100" valign="top" class="text">Client Number:</td>
            <td valign="top" class="text"> <input tabIndex="2" name="agreement_number" type="text" class="smallblue" value="<?= htmlspecialchars($agreement_number) ?>" size="5">
            </td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">Trading Name:</td>
            <td><input tabIndex="1" name="trading_name" type="text" class="smallblue" value="<?= htmlspecialchars($trading_name) ?>" size="30" maxlength="60"></td>
            <td class="text">Next Contact:</td>
            <td> <?php
            
            include_once( "includes/DateField.php" );
            $date_field = new DateField( "next_contact", $next_contact );
            echo $date_field->getHTML();
            
            ?></td>
          </tr>
          <tr><td height="10" class="text"> </td></tr>
          <tr>
            <td class="text" colspan="2">&nbsp;</td>
            <td bgcolor="#0070A6" class="text" colspan="2"><label id="postal_copy"><input tabIndex="4" id="postal_copy" type="checkbox" name="postal_copy" value="Yes" onchange="e_onPostalCopyChange(this)"<?= $postal_copy == "Yes" ? " checked" : "" ?>> Same as street address</label></td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">Address 1:</td>
            <td> <input tabIndex="3" name="address_1" type="text" class="smallblue" value="<?= htmlspecialchars($address_1) ?>" size="25" maxlength="60" onchange="e_onAddressChange(this)"></td>
            <td class="text">Postal Address 1:</td>
            <td> <input tabIndex="4" name="postal_address_1" type="text" class="smallblue" value="<?= htmlspecialchars($postal_address_1) ?>" size="25" maxlength="60"></td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">Address 2:</td>
            <td> <input tabIndex="3" name="address_2" type="text" class="smallblue" value="<?= htmlspecialchars($address_2) ?>" size="25" maxlength="60" onchange="e_onAddressChange(this)"></td>
            <td class="text">Postal Address 2:</td>
            <td> <input tabIndex="4" name="postal_address_2" type="text" class="smallblue" value="<?= htmlspecialchars($postal_address_2) ?>" size="25" maxlength="60"></td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">Suburb:</td>
            <td> <input tabIndex="3" name="suburb" type="text" class="smallblue" value="<?= htmlspecialchars($suburb) ?>" size="15" onchange="e_onAddressChange(this)">
            </td>
            <td class="text">Postal Suburb:</td>
            <td> <input tabIndex="4" name="postal_suburb" type="text" class="smallblue" value="<?= htmlspecialchars($postal_suburb) ?>" size="15">
            </td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">Post Code:</td>
            <td> <input tabIndex="3" name="post_code" type="text" class="smallblue" value="<?= htmlspecialchars($post_code) ?>" size="4" onchange="e_onAddressChange(this)"></td>
            <td class="text">Postal Post Code:</td>
            <td> <input tabIndex="4" name="postal_post_code" type="text" class="smallblue" value="<?= htmlspecialchars($postal_post_code) ?>" size="4"></td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">State:</td>
            <td> <select tabIndex="3" name="state" size="1" class="smallblue" onchange="e_onAddressChange(this)">
                <option<?= $state == 'SA' ? ' selected' : '' ?>>SA</option>
                <option<?= $state == 'ACT' ? ' selected' : '' ?>>ACT</option>
                <option<?= $state == 'NSW' ? ' selected' : '' ?>>NSW</option>
                <option<?= $state == 'NT' ? ' selected' : '' ?>>NT</option>
                <option<?= $state == 'QLD' ? ' selected' : '' ?>>QLD</option>
                <option<?= $state == 'TAS' ? ' selected' : '' ?>>TAS</option>
                <option<?= $state == 'VIC' ? ' selected' : '' ?>>VIC</option>
                <option<?= $state == 'WA' ? ' selected' : '' ?>>WA</option>
              </select></td>
            <td class="text">Postal State:</td>
            <td> <select tabIndex="4" name="postal_state" size="1" class="smallblue">
                <option<?= $postal_state == 'SA' ? ' selected' : '' ?>>SA</option>
                <option<?= $postal_state == 'ACT' ? ' selected' : '' ?>>ACT</option>
                <option<?= $postal_state == 'NSW' ? ' selected' : '' ?>>NSW</option>
                <option<?= $postal_state == 'NT' ? ' selected' : '' ?>>NT</option>
                <option<?= $postal_state == 'QLD' ? ' selected' : '' ?>>QLD</option>
                <option<?= $postal_state == 'TAS' ? ' selected' : '' ?>>TAS</option>
                <option<?= $postal_state == 'VIC' ? ' selected' : '' ?>>VIC</option>
                <option<?= $postal_state == 'WA' ? ' selected' : '' ?>>WA</option>
              </select></td>
          </tr>
          <tr>
            <td height="20" class="text">&nbsp;</td>
            <td>&nbsp;</td>
            <td class="text">&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">Phone1:</td>
            <td> <input tabIndex="5" name="phone1" type="text" class="smallblue" value="<?= htmlspecialchars($phone1) ?>" size="12" maxlength="16"></td>
            <td class="text">1300 Number:</td>
            <td> <input tabIndex="6" name="phone2" type="text" class="smallblue" value="<?= htmlspecialchars($phone2) ?>" size="12" maxlength="16"></td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">Fax:</td>
            <td> <input tabIndex="5" name="fax" type="text" class="smallblue" value="<?= htmlspecialchars($fax) ?>" size="12" maxlength="12"></td>
            <td class="text">Email:</td>
            <td> <input tabIndex="6" name="email" type="text" class="smallblue" value="<?= htmlspecialchars($email) ?>" size="20" maxlength="50"></td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">Website URL:</td>
            <td> <input tabIndex="5" name="website_url" type="text" class="smallblue" value="<?= htmlspecialchars($website_url) ?>" size="20" maxlength="50"></td>
            <td class="text">ABN Number:</td>
            <td> <input tabIndex="6" name="abn_number" type="text" class="smallblue" value="<?= htmlspecialchars($abn) ?>" size="20" maxlength="50">
            </td>
          </tr>
          <tr>
            <td height="20" class="text">&nbsp;</td>
            <td>&nbsp;</td>
            <td class="text">&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">Status:</td>
            <td> <select tabIndex="5" name="status" size="1" class="smallblue">
<?php

	foreach( $status_options as $value => $text ) {
		echo "              <option value=\"" . $value . "\"" . ( $value == $status ? " selected" : "" ) . ">" . $text . "\n";
	}

?>
              </select> </td>
            <td class="text">Origin:</td>
            <td><input tabIndex="6" name="origin" type="text" class="smallblue" value="<?= htmlspecialchars($origin) ?>" size="20" maxlength="50"></td>
          </tr>
          <tr bgcolor="#0070A6">
            <td class="text">Account Manager:</td>
            <td> <select name="rep_id" tabIndex="6" class="smallblue" size="1">
<?php

	foreach( $managers as $key => $value ) {
		echo "              <option value=\"" . $key . "\"" . ( strcmp( $key, $rep_id ) == 0 ? " selected" : "" ) . ">" . $value . "\r\n";
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
            <td colspan="4" class="text">General Control:&nbsp;
              <input tabIndex="7" name="general_control" type="checkbox" <?= htmlspecialchars($general_control) ?>>
			  &nbsp;Contact Control:&nbsp;
			  <input tabIndex="7" name="contact_control" type="checkbox" <?= htmlspecialchars($contact_control) ?>>
			  &nbsp;Email Control:&nbsp;
			  <input tabIndex="7" name="email_control" type="checkbox" <?= htmlspecialchars($email_control) ?>>
			  &nbsp;Domain Control:&nbsp;
			  <input tabIndex="7" name="domain_control" type="checkbox" <?= htmlspecialchars($domain_control) ?>>
			  &nbsp;Job Control:&nbsp;
			  <input tabIndex="7" name="job_control" type="checkbox" <?= htmlspecialchars($job_control) ?>>
               </td>
          </tr>
          <tr bgcolor="#0070A6">
            <td height="50" colspan="4" class="text">
              <input tabIndex="8" name="update" type="submit" value="Update Client Details" class="smallbluebutton">
              &nbsp; <input tabIndex="8" name="delete" type="submit" value="Delete Client" class="smallbluebutton">
              &nbsp; <?php echo $message; ?> </td>
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
