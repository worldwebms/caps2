<?php
if (empty($uid)) {
	$page = "logout.php";
	header("Location: $page");
	die;
}

require_once( dirname(__FILE__) . '/config.php' );
require_once( dirname(__FILE__) . '/functions.php' );

//mail types arrays
$image_types = array("JPG", "JPEG", "GIF", "PNG", "BMP", "PSD", "TGA", "TIFF", "TIF");
$office_types = array("DOC", "PDF", "CSV", "XSL", "XLS", "PPT");
$web_types = array("HTM", "HTML", "PHP", "JS", "JSE", "JSP", "ASP", "PL", "XML");
//$program_types = array("VB", "VBE", "VBS", "WSF", "WSH");
$multimedia_types = array("ASF", "WMF", "MPEG", "MPG", "MP3", "MOV", "QT", "QTI", "QTS", "RAM", "SWF");
$utility_types = array("ZIP", "EML", "RAR");

//define an array of parameters for assignments
$assignments = array(
	'Job Specifications' => 'C',
	'Graphics / Multimedia' => 'G',
	'Basic HTML' => 'B',
	'Advanced HTML / Scripting' => 'A',
	'Database Development' => 'D',
	'Domain & Hosting' => 'H',
	'Programming' => 'P',
	'Server / OS / Hardware' => 'S',
	'Job Review' => 'R'
);
//define array to hold values for the fields in job_assignments table
$assign_fields = array(
	'C' => 'specs',
	'G' => 'graphics',
	'B' => 'html',
	'A' => 'scripting',
	'D' => 'db',
	'H' => 'hosting',
	'P' => 'programming',
	'S' => 'server',
	'R' => 'review'
);

//define array to hold status options
$status_options = array();
$db = mysql_connect ($hostName, $userName, $password);
mysql_select_db($database);
$result = mysql_query('SELECT * FROM client_status ORDER BY id');
while ($row = mysql_fetch_object($result))
	$status_options[$row->tag] = $row->name;
mysql_close($db);

/*
$status_options = array(
	'a' => 'Active',
	'c' => 'Contact',
	'o' => 'Old',
	'q' => 'Quoted',
	'l1' => 'Level 1',
	'l2' => 'Level 2',
	'l3' => 'Level 3',
	'l4' => 'Level 4'
);

*/
