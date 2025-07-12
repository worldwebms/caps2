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
//define date
$today = date("d/m/Y" ,time());
//include a globals file for db connection
include_once("includes/globals.php");
//establish a persistent connection and get all required data.
// select valuations for  valuer
$db = mysql_pconnect ($hostName, $userName, $password);
mysql_select_db($database);

//begin general display of information
$query = "SELECT client_name, trading_name, agreement_number, website_url, status FROM clients WHERE client_id = '$client_id'";
$result = mysql_db_query ($database, $query);
$row = mysql_fetch_object($result);
$client_name = $row->client_name;
$trading_name = $row->trading_name;
$agreement_number = $row->agreement_number;
$website_url = $row->website_url;
$status = $row->status;

$query1 = "SELECT * FROM contacts WHERE contact_id = '$contact_id'";
$result1 = mysql_db_query ($database, $query1);
$row1 = mysql_fetch_object($result1);
$client_id = $row1->client_id;
$mytitle = $row1->title;
$first_name = $row1->first_name;
$last_name = $row1->last_name;
$position = $row1->position;
$mobile = $row1->mobile;
$phone = $row1->phone;
$fax = $row1->fax;
$email = $row1->email;
$client_id = $row1->client_id;
$client_id = $row1->client_id;
$newsletter = $row1->newsletter;
$xmas_letter = $row1->xmas_letter;
if ($newsletter == "yes") {$is_newsletter = "checked";}
if ($xmas_letter == "yes") {$is_xmas_letter = "checked";}
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
    <td class="text">Contact Information Update: Details</td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
    <td valign="top" class="text"> 
		<table width="700" border="0" cellspacing="0" cellpadding="0">
		<form name="add" action="contact.php" method="post">
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
          	<td>&nbsp;</td>
          	<td><img src="images/spacer.gif" width="10" height="11"></td>
          	<td><img src="images/spacer.gif" width="40" height="17"></td>
			</tr>
			<?php
			//echo "<form name=\"add\" action=\"contact.php\" method=\"post\">";
			//output a static empty form for new contacts
			echo "<input name=\"client_id\" type=\"hidden\" value=\"$client_id\">";
			echo "<input name=\"contact_id\" type=\"hidden\" value=\"$contact_id\">";
			echo "<tr><td><select name=\"mytitle\" size=\"1\" class=\"smallblack\"><option selected>Mr</option><option>Ms</option><option>Mrs</option><option>Dr</option><option>Prof</option></select></td>";
			echo "<td><input name=\"first_name\" type=\"text\" class=\"smallblack\" size=\"8\" value=\"$first_name\"></td>";
			echo "<td><input name=\"last_name\" type=\"text\" class=\"smallblack\" size=\"8\" value=\"$last_name\"></td>";
			echo "<td><input name=\"position\" type=\"text\" class=\"smallblack\" size=\"14\" value=\"$position\"></td>";
			echo "<td><input name=\"mobile\" type=\"text\" class=\"smallblack\" size=\"12\" value=\"$mobile\"></td>";
			echo "<td><input name=\"phone\" type=\"text\" class=\"smallblack\" size=\"12\" value=\"$phone\"></td>";
    		echo "<td><input name=\"fax\" type=\"text\" class=\"smallblack\" size=\"12\" value=\"$fax\"></td>";
			echo "<td>&nbsp;</td>";
			echo "<td><input name=\"email\" type=\"text\" class=\"smallblack\" size=\"25\" value=\"$email\"></td>";
			echo "<td>&nbsp;</td>";
			echo "<td align=right valign=top><input name=\"update\" type=\"submit\" value=\"Update\" class=\"smallbluebutton\">&nbsp;<input name=\"delete\" type=\"submit\" value=\"Delete\" class=\"smallbluebutton\"></td></tr>";
			echo "<tr>";
			echo "<td colspan=\"13\" class='text'>Subscribe to:<img src='images/spacer.gif' width='28' height='11'>";
			echo "Online Newsletter&nbsp;<input type='checkbox' name='newsletter' $is_newsletter>";
			echo "<img src='images/spacer.gif' width='28' height='11'>";
			echo "Christmas Newsletter&nbsp;<input type='checkbox' name='xmas_letter' $is_xmas_letter></td></tr>";
			//echo "</form>";
			?>
			</form>	  
      		</table>
      <p><u><br><img src="images/spacer.gif" width="39" height="10"></u></p></td>
  </tr>
</table>
</body>
</html>
