<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* contact template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 12/5/2003
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
//include a globals file for db connection
include_once("includes/globals.php");
//define date
$today = date("d/m/Y" ,time());

//establish a persistent connection and get all required data.
// select valuations for  valuer
$db = mysql_pconnect ($hostName, $userName, $password);
mysql_select_db($database);

//event -> if update all records event update the database than continue
if (isset($update_all)) {
	//loop from 1 to i-1 and update
	for ($i=1;$i<$counter;$i++) {
		$str = "contact_id$i";
		$str1 = "mytitle$i";
		$str2 = "first_name$i";
		$str3 = "last_name$i";
		$str4 = "position$i";
		$str5 = "mobile$i";
		$str6 = "phone$i";
		$str7 = "fax$i";
		$str10 = $$str;
		$str11 = $$str1;
		$str12 = $$str2;
		$str13 = $$str3;
		$str14 = $$str4;
		$str15 = $$str5;
		$str16 = $$str6;
		$str17 = $$str7;
		$query = "UPDATE contacts SET title='$str11', first_name='$str12', last_name='$str13', position='$str14', mobile='$str15', phone='$str16', fax='$str17' WHERE contact_id = '$str10' AND client_id = '$client_id'";
		$result = mysql_query ($query, $db);
	//end loop
	}
//end update routine
}

//event -> if update single record event update the database than continue
if (isset($update)) {
	if ($newsletter == "on") {$newsletter = "yes";}
	if ($xmas_letter == "on") {$xmas_letter = "yes";}
	$query = "UPDATE contacts SET title='$mytitle', first_name='$first_name', last_name='$last_name', position='$position', mobile='$mobile', phone='$phone', fax='$fax', email='$email', newsletter='$newsletter', xmas_letter='$xmas_letter' WHERE contact_id = '$contact_id' AND client_id = '$client_id'";
	$result = mysql_query ($query, $db);
//end update routine
}

//event -> if update event update the database than continue
if (isset($add)) {
	$contact_id = "";
	if ($newsletter == "on") {$newsletter = "yes";}
	if ($xmas_letter == "on") {$xmas_letter = "yes";}
	$query = "INSERT INTO contacts VALUES ('$contact_id', '$client_id', '$mytitle', '$first_name', '$last_name', '$position', '$mobile', '$phone', '$fax', '$email', '$newsletter', '$xmas_letter')";
	$result = mysql_query ($query, $db);
	//check for errors
	$err = mysql_error();
	if ($err) {
		$message = "There is an error updating: $err";
	} else {
		$message = "Contact details added";
	}
//end add routine
}

//event -> if delete event delete the record than continue
if (isset($delete)) {
	$query = "DELETE FROM contacts WHERE contact_id = '$contact_id' AND client_id = '$client_id'";
	$result = mysql_query ($query, $db);
//end delete routine
}

//begin general display of information
$query = "SELECT client_name, trading_name, agreement_number, website_url, status FROM clients WHERE client_id = '$client_id'";
$result = mysql_db_query ($database, $query);
$row = mysql_fetch_object($result);
$client_name = $row->client_name;
$trading_name = $row->trading_name;
$agreement_number = $row->agreement_number;
$website_url = $row->website_url;
$status = $row->status;

$query1 = "SELECT * FROM contacts WHERE client_id = '$client_id'";
$result1 = mysql_db_query ($database, $query1);
$num_rows1 = mysql_num_rows($result1);

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
    <td class="text"><u>Contact Information</u></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td valign="top" class="text">
	<table width="600" border="0" cellspacing="0" cellpadding="0">
        <tr class="text">
          <td class="smalltext">Title</td>
          <td class="smalltext">First Name</td>
          <td class="smalltext">Last Name</td>
          <td class="smalltext">Position</td>
          <td class="smalltext" colspan="2">Mobile Ph</td>
          <td class="smalltext" colspan="2">Direct Ph</td>
          <td class="smalltext">Direct Fax</td>
          <td><img src="images/spacer.gif" width="10" height="11"></td>
          <td class="smalltext">Email <img src="images/spacer.gif" width="59" height="8"></td>
          <td class="smalltext"><img src="images/spacer.gif" width="10" height="11"></td>
          <td></td>
        </tr>
		<?php
			echo "<form action=\"contact.php\" method=\"post\">";
			echo "<input name=\"client_id\" type=\"hidden\" value=\"$client_id\">";
			$i = 1;
			//insert a table to display result
			while($row1 = mysql_fetch_object($result1)) {
				$contact_id = $row1->contact_id;
				$client_id = $row1->client_id;
				$mytitle = $row1->title;
				$f_name = $row1->first_name;
				$l_name = $row1->last_name;
				$email = $row1->email;
				//prepare title
				$thetitle = "<select name=\"mytitle$i\" size=\"1\" class=\"smallblack\">";
				$thetitle .= "<option selected>$mytitle</option>";
				$thetitle .= "<option>Mr</option>";
				$thetitle .= "<option>Ms</option>";
				$thetitle .= "<option>Mrs</option>";
				$thetitle .= "<option>Dr</option>";
            	$thetitle .= "<option>Prof</option>";
            	$thetitle .= "</select>";
				//begin dynamic loop with form for every contact
				echo "<tr><td>$thetitle</td>";
				echo "<td><input name=\"first_name$i\" type=\"text\" class=\"smallblack\" size=\"8\" maxsize=\"15\" value=\"$f_name\"></td>";
				echo "<td><input name=\"last_name$i\" type=\"text\" class=\"smallblack\" size=\"8\" maxsize=\"15\" value=\"$l_name\"></td>";
				echo "<td><input name=\"position$i\" type=\"text\" class=\"smallblack\" size=\"14\" value=\"$row1->position\"></td>";
				echo "<td><input name=\"mobile$i\" type=\"text\" class=\"smallblack\" size=\"12\" value=\"$row1->mobile\"></td>";
				echo "<td class='smalltext'><a href=\"sip:".str_replace(' ', '', $row1->mobile)."\"><font color=\"#B9E9FF\">(call)</font></a>&nbsp;&nbsp;</td>";
				echo "<td><input name=\"phone$i\" type=\"text\" class=\"smallblack\" size=\"12\" value=\"$row1->phone\"></td>";
				echo "<td class='smalltext'><a href=\"sip:".str_replace(' ', '', $row1->phone)."\"><font color=\"#B9E9FF\">(call)</font></a>&nbsp;&nbsp;</td>";
    		echo "<td><input name=\"fax$i\" type=\"text\" class=\"smallblack\" size=\"12\" value=\"$row1->fax\"></td>";
				echo "<td>&nbsp;</td>";
				echo "<td class='smalltext'><a href='mailto:$email'><font color='#FFFFFF'>$email</font></a></td>";
				echo "<td>&nbsp;</td>";
				echo "<td class='smalltext'><a href='update_contact.php?contact_id=$contact_id&client_id=$client_id'><font color='#FFFFFF'>more...</font></a></td></tr>";
				echo "<input name=\"contact_id$i\" type=\"hidden\" value=\"$contact_id\">";
				$i++;
			}
			mysql_free_result($result1);
			echo "<input name=\"counter\" type=\"hidden\" value=\"$i\">";
			echo "<tr><td colspan='11'>&nbsp;</td></tr>";
			echo "<tr><td colspan='8' height='50'></td><td class='text'><input type='submit' name='update_all' value='Update Contact Details' class='smallbluebutton'></td><td colspan='2'></td></tr></form>";
			?>
			</table>
			<p class="subheading">Add New Contact</p>
			<table width="700" border="0" cellspacing="0" cellpadding="0">
			<tr class="text">
          	<td class="smalltext">Title</td>
          	<td class="smalltext">First Name</td>
          	<td class="smalltext">Last Name</td>
          	<td class="smalltext">Position</td>
          	<td class="smalltext">Mobile Ph</td>
          	<td class="smalltext">Direct Ph</td>
          	<td class="smalltext">Direct Fax</td>
          	<td><img src="images/spacer.gif" width="10" height="11"></td>
          	<td class="smalltext">Email <img src="images/spacer.gif" width="59" height="8"></td>
          	<td><img src="images/spacer.gif" width="10" height="11"></td>
          	<td><img src="images/spacer.gif" width="10" height="11"></td>
          	<td>&nbsp;</td>
          	<td><img src="images/spacer.gif" width="10" height="11"></td>
          	<td><img src="images/spacer.gif" width="40" height="17"></td>
			</tr>
			<?php
			echo "<form name=\"add\" action=\"contact.php\" method=\"post\">";
			//output a static empty form for new contacts
			echo "<input name=\"client_id\" type=\"hidden\" value=\"$client_id\">";
			echo "<tr><td><select name=\"mytitle\" size=\"1\" class=\"smallblack\"><option selected>Mr</option><option>Ms</option><option>Mrs</option><option>Dr</option><option>Prof</option></select></td>";
			echo "<td><input name=\"first_name\" type=\"text\" class=\"smallblack\" size=\"8\" maxsize=\"15\"></td>";
			echo "<td><input name=\"last_name\" type=\"text\" class=\"smallblack\" size=\"8\" maxsize=\"15\"></td>";
			echo "<td><input name=\"position\" type=\"text\" class=\"smallblack\" size=\"14\"></td>";
			echo "<td><input name=\"mobile\" type=\"text\" class=\"smallblack\" size=\"12\"></td>";
			echo "<td><input name=\"phone\" type=\"text\" class=\"smallblack\" size=\"12\"></td>";
    		echo "<td><input name=\"fax\" type=\"text\" class=\"smallblack\" size=\"12\"></td>";
			echo "<td>&nbsp;</td>";
			echo "<td><input name=\"email\" type=\"text\" class=\"smallblack\" size=\"25\"></td>";
			echo "<td>&nbsp;</td>";
			echo "<td>&nbsp;</td>";
			echo "<td align=right><input name=\"add\" type=\"submit\" value=\"Add\" class=\"smallbluebutton\"></td></tr>";
			echo "<tr>";
			echo "<td colspan=\"13\" class='text'>Subscribe to:<img src='images/spacer.gif' width='28' height='11'>";
			echo "Online Newsletter&nbsp;<input type='checkbox' name='newsletter' checked>";
			echo "<img src='images/spacer.gif' width='28' height='11'>";
			echo "Christmas Newsletter&nbsp;<input type='checkbox' name='xmas_letter' checked></td></tr>";
			echo "</form>";
			?>
      </table>
	  <p><?php echo "<br><span class=\"text\">$message</span>"; ?></p>
      <p><u><br><img src="images/spacer.gif" width="39" height="10"></u></p></td>
  </tr>
</table>
</body>
</html>
