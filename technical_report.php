<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* technical_report template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 12/8/2003
* Client: WorldWeb, Domain: database intranet
* This template consists of 2 section: sql technical implementation query and html output
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
//include functions file
include_once("includes/functions.php");
//establish a connection and get all required data.
// select valuations for  valuer
$db = mysql_connect ($hostName, $userName, $password);
mysql_select_db($database);
//select all open jobs

//event->customertech report when user request it
if (isset($customertech)) {
	//check parameters for checkboxes
	//declare sql
	$query = "SELECT DISTINCT clients.client_name, technical.client_id, ";
	//begin constructing sql for main query
	$j = count($tech);
	for ($i=0;$i<$j;$i++) {
		if ($tech[$i]) {$query .= "technical." . $tech[$i] . ", ";}
	//end for loop
	}
	//if the char is a comma delete it and continue constructing sql
	if (substr($query, -2)==", ") {$query = substr($query, 0, -2);}
	$query .= " FROM clients, technical WHERE clients.client_id = technical.client_id ";
	//run the loop again and add conditions
	for ($i=0;$i<$j;$i++) {
		if ($tech[$i]) {$query .= "AND technical." . $tech[$i] . "='yes' ";}
	//end for loop
	}
	//complete the query
	$query .= "ORDER BY client_name ASC";
} else {$message = "You did not specify a query";}

//send query to db if exists
if (!empty($query)) {$result = mysql_query ($query, $db);}
?>
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
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td width="1%"><img src="images/spacer.gif" width="8" height="27"></td>
    <td colspan="2" class="clienttitle">Reports</td>
  </tr>
  <tr> 
    <td background="images/horizontal_line.gif">&nbsp;</td>
    <td colspan="2" background="images/horizontal_line.gif" class="text">&nbsp;</td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
    <td colspan="2" valign="top" class="text"> 
      <p class="subheading">Clients Technical Implementations</p>
            
      <table width="400" border="0" cellspacing="1" cellpadding="1">
        <tr> 
          <td width="100" class="text">Client</td>
          <td class="text">Implementaions</td>
        </tr>
		<?php
		if ($result) {
			while ($row=mysql_fetch_object($result)) {
				$implementations = "";
				$client_id = $row->client_id;
				$client_name = $row->client_name;
				$wwedit = $row->wwedit;
				$avatar = $row->avatar;
				$php = $row->php;
				$mysql = $row->mysql;
				$psql = $row->psql;
				$webreport = $row->webreport;
				$coldfusion = $row->coldfusion;
				$cronjob = $row->cronjob;
				//format results
				if ($wwedit == "yes") {$implementations = "WWedit ";} else {$implementations = $implementations;}
				if ($avatar == "yes") {$implementations .= "Avatar ";} else {$implementations = $implementations;}
				if ($php == "yes") {$implementations .= "PHP ";} else {$implementations = $implementations;}
				if ($mysql == "yes") {$implementations .= "Mysql ";} else {$implementations = $implementations;}
				if ($psql == "yes") {$implementations .= "PGsql ";} else {$implementations = $implementations;}
				if ($webreport == "yes") {$implementations .= "Webreport ";} else {$implementations = $implementations;}
				if ($coldfusion == "yes") {$implementations .= "Cold Fusion ";} else {$implementations = $implementations;}
				if ($cronjob == "yes") {$implementations .= "Cron Jobs";} else {$implementations = $implementations;}
				//display results
				if ($implementations != "") {
					echo "<tr valign=\"middle\" bgcolor=\"#0070A6\">";
					echo "<td class=\"text\">$client_name</font></td>";
					echo "<td class=\"text\">$implementations</td></tr>";
				}
			}
		//end if result
		} else {echo "Your query did not return any result<br>$message";}
		//echo "<tr><td colspan=2>$query</td></tr>";
		?>
      </table>
      <p><u><br>
        <img src="images/spacer.gif" width="39" height="10"> </u></p></td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
    <td valign="top" class="text"> 
      <p><img src="images/spacer.gif" width="233" height="14"></p>
      </td>
    <td width="76%" valign="top" class="text">
<p> <br>
      </p>
      </td>
  </tr>
</table>
</body>
</html>
