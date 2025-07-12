<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* export_csv template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 11/8/2003
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

//establish a connection to mysql
$db = mysql_connect ($hostName, $userName, $password);
mysql_select_db($database);

//get all contacts and list in array
$query = "SELECT client_name, agreement_number FROM clients WHERE status='a' ORDER BY client_name ASC";
$result = mysql_query($query, $db);

$filename = "includes/invoice_list.csv";
if (!($fp = fopen($filename, "r")))  
   exit("Unable to open the input file, $filename.");
		
//initiate file class
$PATH="includes/invoice_list.csv";
//new file name
$mytime = date("d-m-Y");
$myfile = "invoice_list_$mytime" . ".csv";
$new_location = "$invoiceDir/$myfile";
$TheFile = new file_manager($PATH);  //Creates Object
$data = ""; //set data to nothing

//loop through the query and write a data file	
while($row = mysql_fetch_object($result)) {
	//list variables and format to suit
	$client_name = $row->client_name;
	$agreement_number = $row->agreement_number;
	//define data to be written onto file 
	$data .= "$client_name,$agreement_number\r\n";	
}

//copy the data file to destination
$TheFile->write($data); //Write it now
$TheFile->copyto($new_location);
mysql_free_result($result);
//verify the file and send to chris
if (file_exists($new_location)) {
	//generate link
	$mylink = "<a href=\"includes/downloader.php?file=$myfile\"><font color=\"#B9E9FF\">Click to download $myfile</font></a>";
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
    <td colspan="2" valign="top" class="text"> 
      <p class="subheading">Invoice List</p>
  <tr> 
    <td>&nbsp;</td>
    <td height="100" valign="middle" class="text"> <p>Click on the link below 
        to download invoice list.<br>
		<?php echo $mylink; ?></p>
      </td>
  </tr>
</table>
</body>
</html>
