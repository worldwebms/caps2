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

include_once( 'includes/config.php' );

//get all contacts and list in array
$db = mysql_connect ($hostName, $userName, $password);
mysql_select_db($database);
//define query
$query = "select clients.client_id, clients.client_name, clients.trading_name, clients.phone1, contacts.title, contacts.first_name, contacts.last_name, contacts.mobile, contacts.phone from clients, contacts where clients.status='a' and clients.client_id=contacts.client_id order by client_name ASC, first_name ASC;";
//send query to mysql
$result = mysql_query($query, $db);

$data = "\"Client Name\",\"Trading Name\",\"General Phone\",\"Contact Name\",\"Mobile\",\" Contact Phone\"\n";

//loop through the query and write a data file	
while($row = mysql_fetch_object($result)) {
	//add email to display list
	$name = $row->title." ".$row->first_name." ".$row->last_name;
	//echo $row->client_name." | ".$row->trading_name." | ".$row->title." ".$row->first_name." ".$row->last_name." | ".$row->mobile." | ".$row->phone."<br>";
	$data .= "\"".$row->client_name."\",\"".$row->trading_name."\",\"".$row->phone1."\",\"".$name."\",\"".$row->mobile."\",\"".$row->phone."\"\n";
	//define data to be written onto file 
	//$data .= "$myemail\r\n";
}

$destinationFile = date("d-m-Y", time())."_caps_client_contacts.csv";
$fname = "/vhosts/caps/invoices/" . $destinationFile;
$fp = fopen($fname,'w');
$ok = fwrite($fp,$data);
fclose($fp);

echo "file written";

?>
