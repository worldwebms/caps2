<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* customer_emails_csv template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 11/12/2003
* Client: WorldWeb, Domain: database intranet
* This template consists of 2 sections: SQL Query & csv file creation and mailing function
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
//include required file for file manipulation
include_once("includes/file_manager.php");

//event->submit ---------------------------when user clicks submit button------------------------------
if (isset($submit)) {
	
	$postal = $postal == 'true';
	
	//get all contacts and list in array
	$db = mysql_connect ($hostName, $userName, $password);
	mysql_select_db($database);
	
	//define query
	$query = "SELECT ";
	if ($postal)
		$query .= 'clients.client_name, clients.address_1, clients.address_2, clients.suburb, clients.post_code, clients.state, contacts.title, contacts.first_name, contacts.last_name, contacts.email';
	else
		$query .= 'contacts.email, clients.client_name, contacts.title, contacts.first_name, contacts.last_name';
	$query .= " FROM contacts " .
		"INNER JOIN clients ON clients.client_id=contacts.client_id WHERE ";
	
	//begin defining clause
	$myclause = "( ";
	$status_list = "";
	
	//define status clause and append to clause
	$j = count($mystatus);
	for ($i=0;$i<$j;$i++) {
		if (!empty($mystatus[$i])) {
			$myclause .= "clients.status='$mystatus[$i]' OR ";
			$status_list .= "$mystatus[$i] or ";
		}
	//end for loop
	}
	//get rid of last OR and close brackets
	$myclause = substr($myclause, 0, -3) . ") ";
	$status_list = substr($status_list, 0, -3);
	
	//define newsletter clause logic
	if ($newsletter == "all") {
		//do nothing
	} elseif ($newsletter == "online") {
		$myclause .= "AND (contacts.newsletter='yes') ";
	} else {
		$myclause .= " AND (contacts.xmas_letter='yes') ";
	}
	
	//define order by clause
	if ($postal)
		$myorder = "ORDER BY " . ($postal ? 'clients.client_name, ' : '') . "contacts.email ASC";
	else
		$myorder = "AND contacts.email!='' ORDER BY " . ($postal ? 'clients.client_name, ' : '') . "contacts.email ASC";
	
	//concat query
	$myquery = $query . $myclause . $myorder;
	
	//send query to mysql
	$result = mysql_query($myquery, $db);

	//new file name
	$myfile = ($postal ? 'postal' : 'email') . '_list_' . date("d-m-Y") . '.csv';
	$new_location = $invoiceDir . '/' . $myfile;
	if (!($fp = fopen($new_location, 'w')))
		exit("Unable to open the input file, $new_location.");
	
	//define other variables
	$display_list = "";	//set  display_list to nothing

	//loop through the query and write a data file	
	while ($row = mysql_fetch_assoc($result)) {
		$data = '';
		$sep = '';
		foreach ($row as $value) {
			$data .= $sep . '"' . str_replace('"', '""', $value) . '"';
			$sep = ',';
		}
		$data .= "\r\n";
		fwrite($fp, $data);
		$display_list .= $row['email'] . '<br>';
	}

	//copy the data file to destination
	fclose($fp);
	mysql_free_result($result);
	//verify the file and send to chris
	if (file_exists($new_location)) {
		//generate link
		$mylink = "<a href=\"includes/downloader.php?file=$myfile\"><font color=\"#B9E9FF\">Click to download $myfile</font></a>";
	}
	
	$content = "Click on the link below to download email list in TSV format.<br>$mylink<br><br>";
	$content .= "Email list for customers with status $status_list and newsletter style $newsletter<br><br>$display_list";
} 
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
    <td colspan="2" valign="top" class="text"> <p class="subheading"><?= $postal ? 'Postal Lists' : 'Email Lists' ?></p>
  <tr> 
    <td>&nbsp;</td>
    <td height="100" valign="middle" class="text"> <p><?php echo $content; ?></p></td>
  </tr>
</table>
</body>
</html>
