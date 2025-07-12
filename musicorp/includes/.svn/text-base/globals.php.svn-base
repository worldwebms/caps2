<?php
$userName = "capsuser";
$hostName = "localhost";
$password = "ww4ims";
$database = "caps";

//mail types arrays
$image_types = array("JPG", "JPEG", "GIF", "PNG", "BMP", "PSD", "TGA", "TIFF", "TIF", "WMV");
$office_types = array("TXT", "DOC", "PDF", "CSV", "XSL", "XLS", "XLSX", "XLXM", "XLSB", "XLTX", "XLAX", "XLTM", "PPT", "PUB", "PPS", "RTF", "EPS", "DAT", "FDF", "DOT", "DOTM", "DOTX", "DOCM", "DOCX");
$web_types = array("HTM", "HTML");
$utility_types = array("ZIP", "EML", "RAR", "WAV");

//define directory for csv files
$invoiceDir = "/vhosts/192.168.0.12/html/caps/invoices";
$templateDir = "/vhosts/192.168.0.12/html/caps/includes";


//mail host name
$mailHostName = "202.191.100.11";
$clientMailServer = "mail.musicorp.com.au";
$logEmail = "david@musicorp.com.au";

//------------------------------functions for this script-------------------------------------------------
function testAccount($valias, $domain) {
	global $isOK;
	$isOK = "no";
	$db = mysql_pconnect ("localhost", "capsuser", "ww4ims");
	mysql_select_db("caps");
	//query to see if user already exists in db
	$query = "SELECT v_username, domain FROM vusers WHERE v_username = '$valias' AND domain = '$domain'";
	$result = mysql_query($query, $db);
	$numrows = mysql_num_rows($result);
	if ($numrows == 0) {$test1 = "yes";}
	//and test the valiases table
	$query1 = "SELECT valias, domain FROM valiases WHERE valias = '$valias' AND domain = '$domain'";
	$result1 = mysql_query($query1, $db);
	$numrows1 = mysql_num_rows($result1);
	if ($numrows1 == 0) {$test2 = "yes";}
	if ($test1 == "yes" & $test2 == "yes") {$isOK = "yes";}
	return $isOK;
}

function testAliasAccount($valias, $domain) {
	global $isOK;
	$isOK = "no";
	$db = mysql_pconnect ("localhost", "capsuser", "ww4ims");
	mysql_select_db("caps");
	//query to see if user already exists in db
	$query = "SELECT v_username, domain FROM vusers WHERE v_username = '$valias' AND domain = '$domain'";
	$result = mysql_query($query, $db);
	$numrows = mysql_num_rows($result);
	if ($numrows == 0) {$test1 = "yes";}
	//and test the valiases table
	$query1 = "SELECT valias, domain FROM valiases WHERE valias = '$valias' AND domain = '$domain'";
	$result1 = mysql_query($query1, $db);
	$numrows1 = mysql_num_rows($result1);
	if ($numrows1 == 0) {$test2 = "yes";}
	if ($test1 == "yes" & $test2 == "yes") {$isOK = "yes";}
	return $isOK;
}
?>
