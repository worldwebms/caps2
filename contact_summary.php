<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* contact_summary template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 12/5/2003
* Client: WorldWeb, Domain: database intranet
* This template consists of 2 sections: SQL Query & formatting, HTML form linked to index1.php
* Important files included in this template are: header.inc (head section and javascript library),
* globals.php (connection string),
* Last Modified: 12/1/2004 By Aviv Efrat
* Please remember to backup template before modifying
*
*********************************************************************************************************
*/
//begin session
session_start();

//event->FindClient--------------------will direct the header to index1.php to find client--------------
if ($event == "FindClient") {
	if ($check1 == "checked") {$check1 = "on";}
	if ($check2 == "checked") {$check2 = "on";}
	if ($check3 == "checked") {$check3 = "on";}
	echo "<SCRIPT LANGUAGE=\"JavaScript\">";
	echo "window.opener.location.replace('index1.php?client_id=id&event=FindClient');";
	echo "document.location.replace('contact_summary.php?mycheck1=$check1&mycheck2=$check2&mycheck3=$check3');";
	echo "</SCRIPT>";
}

//include a php function library file
include_once("includes/functions.php");
//include a globals file for db connection
include_once("includes/globals.php");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>CAPS | WorldWeb Management Services</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="includes/caps_styles.css" rel="stylesheet" type="text/css">
<?php
//include javascript library
include_once("includes/worldweb.js");
?>
<style type="text/css">
.completed td, .completed font {
	color: #999999 !important;
}
</style>
</head>

<body bgcolor="#006699" leftmargin="0" topmargin="0">
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="1%"><img src="images/spacer.gif" width="8" height="27"></td>
    <td class="clienttitle">Contact Summary</td>
  </tr>
  <tr>
  <form name="summary" method="get" action="contact_summary.php">
    <td>&nbsp;</td>
    <td class="text">
      <table>
        <tr><td class="text">Status:</td><td class="text">
<?php
  $selected_status = array_safe($_GET, 'status', array());
  foreach ($status_options as $k => $v) {
?>
          <label><input name="status[]" type="checkbox" value="<?= $k ?>" <?= in_array($k, $selected_status) ? ' checked' : '' ?>> <?= $v ?></label> &nbsp;
<?php } ?>
        </td></tr>
        <tr><td class="text">Display:</td><td class="text">
          <label for="group_by_day"><input id="group_by_day" name="group_by_day" type="checkbox" <?= $group_by_day ? " checked" : "" ?>> Group by date</label> &nbsp;
          <input type="submit" value="update" class="smallbluebutton">
        </td></tr>
      </table>
    </td>
	</form>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td valign="top" class="text">&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td valign="top" class="text">
<?php

	include( "includes/contact_summary.inc" );

?>
      <p>&nbsp;</p>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td valign="top" class="text">
      <p class="instruction">Note: this list will refresh every 30 seconds</p>
    </td>
  </tr>
</table>
</body>
</html>
