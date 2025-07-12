<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* standard_reports template by WorldWeb Management Services ABN: 99 189 634 462
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
//define date
$today = date("d/m/Y" ,time());
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
    
    
      <p class="subheading">Job Control Reports</p>
      <table width="471" border="0" cellspacing="0" cellpadding="0">
        <form name="openjobs" action="open_jobs.php" method="post">
          <tr class="text"> 
            <td width="257" height="36" class="text">List all open jobs </td>
            <td width="82" class="text">&nbsp;</td>
            <td width="132" class="text"> <input type="submit" name="openjobs1" value="Generate Report" class="smallbluebutton"></td>
          </tr>
          <tr class="text"> 
            <td height="48" class="text">List all open jobs with an estimated 
              completion date date set within x number of days </td>
            <td class="text"><select name="days" class="black">
                <option selected value="NULL"></option>
                <option value="1">1 day</option>
                <option value="2">2 days</option>
                <option value="3">3 days</option>
                <option value="4">4 days</option>
                <option value="5">5 days</option>
                <option value="6">6 days</option>
                <option value="7">7 days</option>
                <option value="8">8 days</option>
                <option value="9">9 days</option>
                <option value="10">10 days</option>
                <option value="11">11 days</option>
                <option value="12">12 days</option>
                <option value="13">13 days</option>
                <option value="14">14 days</option>
              </select></td>
            <td class="text"> <input type="submit" name="openjobs2" value="Generate Report" class="smallbluebutton"></td>
          </tr>
					<tr class="text">
						<td height="36">List all jobs where time has not been assigned</td>
						<td><br></td>
						<td><input type="button" value="Generate Report" onclick="window.location='unassigned_jobs.php'" class="smallbluebutton"></td>
					</tr>
					<tr class="text">
						<td height="36">View time sheets</td>
						<td><br></td>
						<td><input type="button" value="Generate Report" onclick="window.location='job_time_report.php'" class="smallbluebutton"></td>
					</tr>
					<tr class="text">
						<td height="36">View all open tasks</td>
						<td><br></td>
						<td><input type="button" value="Generate Report" onclick="window.location='job_task_report.php'" class="smallbluebutton"></td>
					</tr>
					<tr class="text">
						<td height="36">View completed tasks</td>
						<td><br></td>
						<td><input type="button" value="Generate Report" onclick="window.location='job_task_complete.php'" class="smallbluebutton"></td>
					</tr>
		
        </form>
      </table>
      
      
      <p class="subheading">Accounting Reports</p>
      <table width="471" border="0" cellspacing="0" cellpadding="0">
        <form name="customercsv" action="customer_csv.php" method="post">
          <tr class="text"> 
            <td width="257" height="36" class="text">Invoice List <br>
              Export CSV file of all cusotmers with status &quot;a&quot;<br> </td>
            <td width="82" class="text">&nbsp;</td>
            <td width="132" class="text"> <input type="submit" name="customerlist" value="Generate Report" class="smallbluebutton"></td>
          </tr>
        </form>
      </table>
      <table width="471" border="0" cellspacing="0" cellpadding="0">
        <form name="closedjobs" action="closed_jobs.php" method="post">
          <tr class="text"> 
            <td width="150" height="36" class="text">Closed Jobs<br> <br>
              Begining of period<br> <?php
              
              include_once( "includes/DateField.php" );
              $date_field = new DateField( "C1", strtotime( "-1 month", mktime( 0, 0, 0, date( "m" ), 1 ) ) );
              echo $date_field->getHTML();
              
              ?></td>
            <td width="201" class="text"><br> <br>
              End of period<br> <?php
              
              include_once( "includes/DateField.php" );
              $date_field = new DateField( "C2", strtotime( "yesterday" ) );
              echo $date_field->getHTML();
              
              ?></td>
            <td width="132" valign="bottom" class="text"> <input type="submit" name="submit" value="Generate Report" class="smallbluebutton"> 
            </td>
          </tr>
        </form>
      </table>
      
      
      <p class="subheading">Management Reports </p>
      <table width="471" border="0" cellspacing="0" cellpadding="0">
        <form name="openstaff" action="open_staff_jobs.php" method="post">
          <tr class="text"> 
            <td width="257" height="36" class="text">List all open jobs for all 
              staff </td>
            <td width="82" class="text">&nbsp;</td>
            <td width="132" class="text"> <input type="submit" name="openjobs1" value="Generate Report" class="smallbluebutton"></td>
          </tr>
          <tr class="text"> 
            <td height="48" class="text">List all open jobs for all staff with 
              an estimated completion date date set within x number of days </td>
            <td class="text"><select name="days" class="black">
                <option selected value="NULL"></option>
                <option value="1">1 day</option>
                <option value="2">2 days</option>
                <option value="3">3 days</option>
                <option value="4">4 days</option>
                <option value="5">5 days</option>
                <option value="6">6 days</option>
                <option value="7">7 days</option>
                <option value="8">8 days</option>
                <option value="9">9 days</option>
                <option value="10">10 days</option>
                <option value="11">11 days</option>
                <option value="12">12 days</option>
                <option value="13">13 days</option>
                <option value="14">14 days</option>
              </select></td>
            <td class="text"> <input type="submit" name="openjobs2" value="Generate Report" class="smallbluebutton"></td>
          </tr>
          <tr class="text"> 
            <td height="48" class="text">List all open jobs for all staff where 
              current date exceeds estimated completion date</td>
            <td class="text">&nbsp;</td>
            <td class="text"> <input type="submit" name="openjobs3" value="Generate Report" class="smallbluebutton"></td>
          </tr>
        </form>
      </table>
      
      
      <p class="subheading">Technical Reports</p>
      <table width="471" border="0" cellspacing="0" cellpadding="0">
        <form name="technical" action="technical_report.php" method="post">
          <tr class="text"> 
            <td height="20" colspan="2" valign="middle" class="text">List all 
              customers with the following technical implementations</td>
          </tr>
          <tr class="text"> 
            <td height="60" valign="middle" class="text"> 
              <input name="tech[]" type="checkbox" value="php"> PHP
			<input name="tech[]" type="checkbox" value="mysql"> Mysql
			<input name="tech[]" type="checkbox" value="psql"> Pgsql 
			<input name="tech[]" type="checkbox" value="wwedit">
              WWEdit<br>
              <br>
			<input name="tech[]" type="checkbox" value="avatar"> Avatar
			<input name="tech[]" type="checkbox" value="webreport"> Webreport
			<input name="tech[]" type="checkbox" value="coldfusion"> Cols fusion 
			<input name="tech[]" type="checkbox" value="cronjob"> Cron</td>
            <td width="132" class="text"> <input type="submit" name="customertech" value="Generate Report" class="smallbluebutton"></td>
          </tr>
        </form>
      </table>
      
      
      <p class="subheading">Email Lists</p>
      <table width="500" border="0" cellspacing="0" cellpadding="0" class="text">
        <form action="customer_emails_csv.php" method="post">
          <tr>
            <td width="170">Status: 
			<input name="mystatus[]" type="checkbox" value="a"> a 
			<input name="mystatus[]" type="checkbox" value="c"> c 
			<input name="mystatus[]" type="checkbox" value="o"> o 
			<input name="mystatus[]" type="checkbox" value="q"> q 
            </td>
            <td width="230" align="right">Newsleter Style: 
              <select name="newsletter" size="1" class="smallblue">
                <option selected value="all">All</option>
                <option value="online">Online Newsletter</option>
                <option value="christmas">Christmas Newsletter</option>
              </select>
            </td>
            <td width="100" align="right"> 
              <input type="submit" name="submit" value="Generate List" class="smallbluebutton">
            </td>
          </tr>
        </form>
      </table>
      
      <p class="subheading">Postal Lists</p>
      <table width="500" border="0" cellspacing="0" cellpadding="0" class="text">
        <form action="customer_emails_csv.php" method="post">
          <input type="hidden" name="postal" value="true">
          <tr>
            <td width="170">Status: 
			<input name="mystatus[]" type="checkbox" value="a"> a 
			<input name="mystatus[]" type="checkbox" value="c"> c 
			<input name="mystatus[]" type="checkbox" value="o"> o 
			<input name="mystatus[]" type="checkbox" value="q"> q 
            </td>
            <td width="230" align="right">Newsleter Style: 
              <select name="newsletter" size="1" class="smallblue">
                <option selected value="all">All</option>
                <option value="online">Online Newsletter</option>
                <option value="christmas">Christmas Newsletter</option>
              </select>
            </td>
            <td width="100" align="right"> 
              <input type="submit" name="submit" value="Generate List" class="smallbluebutton">
            </td>
          </tr>
        </form>
      </table>
      
  </tr>
  <tr> 
    <td>&nbsp;</td>
    <td valign="top" class="text"> <p><img src="images/spacer.gif" width="233" height="14"></p></td>
    <td width="76%" valign="top" class="text"> <p> <br>
      </p></td>
  </tr>
</table>
</body>
</html>
