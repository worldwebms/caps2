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

//include required file for file manipulation
include_once("includes/file_manager.php");
include_once( "includes/config.php" );

$userName = "capsuser";
$hostName = "localhost";
$password = "ww4ims";
$database = "caps";

//get all contacts and list in array
$db = mysql_connect ($hostName, $userName, $password);
mysql_select_db($database);
//define query
$query = "SELECT clients.client_id, contacts.email FROM clients, contacts WHERE clients.client_id=contacts.client_id and clients.status = 'a'";
//send query to mysql
$result = mysql_query($query, $db);
	
//initiate file class
$PATH="includes/email_list.csv";
//new file name
$mytime = date("d-m-Y");
$myfile = $mytime."_client_emails.csv";
$new_location = "/vhosts/192.168.0.12/html/caps/invoices/".$myfile;
$TheFile = new file_manager($PATH);  //Creates Object
	
$data = ""; //set data to nothing

//loop through the query and write a data file	
while($row = mysql_fetch_object($result)) {
	//add email to display list
	$myemail = $row->email;
	echo $myemail."<br>";
	//define data to be written onto file 
	$data .= "$myemail\r\n";
}
/*
$query1 = "select email from clients where status != 'o'";
$result1 = mysql_query($query1, $db);

//loop through the query and write a data file	
while($row1 = mysql_fetch_object($result1)) {
	//add email to display list
	$myemail = $row1->email;
	//define data to be written onto file 
	$data .= "$myemail\r\n";
}
*/

$fp = fopen($new_location,'w');
$ok = fwrite($fp,$data);
fclose($fp);

if (file_exists($new_location)) {
	echo "file created";
} else {
	echo "file failed";
}

?>
