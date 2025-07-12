<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* reports template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 8/8/2003
* Client: WorldWeb, Domain: database intranet
* This template consists of 1 section: sql query to determine reporting level and redirect
* Important files included in this template are: header.inc (head section and javascript library),
* globals.php (connection string),
* Last Modified: 12/1/2004 By Aviv Efrat
* Please remember to backup template before modifying
*
*********************************************************************************************************
*/
//begin session
session_start();
//find the restriction level and redirect accordingly
if ($r == "a"){
	//go to admin_reports.php
	$page = "admin_reports.php";
	header("Location: $page");
} elseif ($r == "s") {
	//go to standard_reports.php
	$page = "standard_reports.php";
	header("Location: $page");
} else {
	//go to welcome
	$page = "welcome.php";
	header("Location: $page");
}
?>