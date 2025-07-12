<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* contact_list template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 19/5/2003
* Client: WorldWeb, Domain: database intranet
* This template consists of 2 section: sql query listing contacts HTML output directed to index1.php
* Important files included in this template are: header.inc (head section and javascript library),
* globals.php (connection string),
* Last Modified: 12/1/2004 By Aviv Efrat
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
$db = mysql_connect ($hostName, $userName, $password);
mysql_select_db($database);

//assign appropriate query if both names found
$query = "SELECT client_id, client_name, trading_name, agreement_number FROM clients WHERE (" . (is_numeric($mysearch) ? "agreement_number = '$mysearch' OR " : "") . "client_name LIKE '%$mysearch%' OR trading_name LIKE '%$mysearch%') AND status != 'd' ORDER BY client_name ASC";
//send query to mysql
$result = mysql_query($query, $db);
//check if there are any records
$num_rows = mysql_num_rows($result);
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
    <td width="99%" class="clienttitle">&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td class="clienttitle">
	<table width="600" border="0" cellspacing="1" cellpadding="0">
        <tr class="text"> 
          <td width="200" class="text">Client Name:</td>
          <td width="200" class="text">Trading Name:</td>
		  <td width="200" class="text">Ref:</td>
        </tr>
        <?php
		if ($num_rows > 0) {
		  while($row = mysql_fetch_object($result)) {
			$client_id = $row->client_id;
			$agreement_number = $row->agreement_number;
			$client_name = $row->client_name;
			$trading_name = $row->trading_name;
			if (empty($trading_name)) {$trading_name = "N/A";}
			if (empty($agreement_number) | $agreement_number == 0) {$agreement_number = "N/A";}
			//display contact list
			echo "<tr><td bgcolor='#0070A6' class='text'><a href=\"index1.php?client_id=$client_id&event=FindClient\"><font color='#B9E9FF'>$client_name</font></a></td>";
			echo "<td bgcolor='#0070A6' class='text'>$trading_name</td>";
			echo "<td bgcolor='#0070A6' class='text'>$agreement_number</td></tr>";
			//end loop
		  }
		//end if result
		} else {
		  	echo "<tr><td colspan=3 class=text>There are no clients with a similar name to the string you have typed</td></tr>";
		}
		  
		?>
      </table>
	</td>
  </tr>
</table>
</body>
</html>
