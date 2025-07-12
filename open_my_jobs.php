<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* open_jobs template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 11/8/2003
* Client: WorldWeb, Domain: database intranet
* This template consists of 2 section: sql open jobs query and html output
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
      <p class="subheading">All Open Jobs</p>
<?php

	include( "includes/my_jobs.inc" );

?>
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
