<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* logout template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 12/5/2003
* Client: WorldWeb, Domain: database intranet
* This template consists of 1 section: session termination routine and page redirection to index.php
* Important files included in this template are: header.inc (head section and javascript library),
* globals.php (connection string),
* Last Modified: 19/5/2003 By Aviv Efrat
* Please remember to backup template before modifying
*
*********************************************************************************************************
*/
//begin session
session_start();
//erase session variables
session_unset();
session_destroy(); 
$page = "index.php";
header("Location: $page");
?>