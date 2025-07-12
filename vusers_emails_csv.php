#!/usr/bin/php4 -f
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


//get all contacts and list in array
$db = mysql_connect ($hostName, $userName, $password);
mysql_select_db($database);
//define query
$query = "SELECT clients.client_id, vusers.v_username, vusers.domain FROM clients, vusers WHERE clients.client_id=vusers.client_id and clients.status != 'o'";
//send query to mysql
$result = mysql_query($query, $db);
	
//initiate file class
//$PATH="/vhosts/caps/includes/email_list.csv";
//new file name
$mytime = date("d-m-Y");
$myfile = $mytime."_client_emails.csv";
$sourcePath = $templateDir."/email_list.csv";
$destinationPath = $invoiceDir."/".$myfile;
//echo $new_location;
$TheFile = new file_manager($sourcePath);  //Creates Object
$data = ""; //set data to nothing

//loop through the query and write a data file	
while($row = mysql_fetch_object($result)) {
	//add email to display list
	$myemail = $row->v_username."@".$row->domain;
	//echo $myemail."\n";
	//define data to be written onto file 
	$data .= "$myemail\r\n";
}

//copy the data file to destination
$TheFile->write($data); //Write it now
//copy('/vhosts/caps/includes/email_list.csv', '/vhosts/caps/invoices/test.csv') or die('couldnt');
$ok = $TheFile->copyto($destinationPath);
if ($ok == 0) {
	echo "file could not be copied";
} else {
	//verify the file and send to chris
	if (file_exists($destinationPath)) {
		echo "file created";
	} else {
		echo "file failed";
	}
}

?>