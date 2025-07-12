<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* closed_jobs template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 1/9/2003
* Client: WorldWeb, Domain: database intranet
* This template consists of 2 section: sql open jobs query and html output
* Important files included in this template are: header.inc (head section and javascript library),
* globals.php (connection string),
* Last Modified: 12/1/2004 By Aviv Efrat
* Please remember to backup template before modifying
*
*********************************************************************************************************
*/

ini_set('display_errors', '1');
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
//establish a persistent connection and get all required data.
// select valuations for  valuer
$db = mysql_connect ($hostName, $userName, $password);
mysql_select_db($database);
//select all closed jobs for specified period

//first make the dates real date object
$start_date = FromNextContact($C1);
//do the same for end date
$end_date = FromNextContact($C2);

//query db for all closed jobs between dates inclusive
$query = "SELECT jobs.job_id, jobs.client_id, jobs.job_number, jobs.job_title, jobs.employee, jobs.order_date, jobs.due_date, jobs.total_hours, jobs.external_hours, jobs.billable_hours, clients.client_name FROM jobs, clients WHERE jobs.client_id = clients.client_id AND jobs.status = 'closed' AND jobs.closing_date >= '$start_date' AND jobs.closing_date <= '$end_date' ORDER BY client_name, job_id";

// Ensure hour totals are correct
$result = mysql_query ($query, $db);
while ($row = mysql_fetch_assoc($result))
	updateTotalTime($db, $row['job_id']);

//send query to db
$result = mysql_query ($query, $db);
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
      <p class="subheading">Closed Jobs</p>
            
      <table border="0" cellspacing="1" cellpadding="1">
        <tr>
          <td width="65" class="text">Job Number</td>
          <td width="200" class="text">Client</td>
          <td width="70" class="text">Order Date</td>
          <td width="70" class="text">Due Date</td>
          <td width="50" class="text">Act.Hrs</td>
          <td width="50" class="text">Adj.Hrs</td>
          <td width="50" class="text">Bill.Hrs</td>
          <td width="80" class="text">Employee</td>
		  <td class="text">Job Title</td>
        </tr>
		<?php
		while ($row=mysql_fetch_object($result)) {
			$job_id = $row->job_id;
			$client_id = $row->client_id;
			$job_number = $row->job_number;
			$job_title = $row->job_title;
			$employee = $row->employee;
			$next_contact = $row->order_date;
			//format due date
			$order_date = ToNextContact($next_contact);
			$next_contact = $row->due_date;
			//format due date
			$due_date = ToNextContact($next_contact);
			$total_hours = $row->total_hours;
			$external_hours = $row->external_hours;
			$billable_hours = $row->billable_hours;
			$client_name = $row->client_name;
			//display results
			echo "<tr valign=\"middle\" bgcolor=\"#0070A6\">";
			echo "<td class=\"text\"><a href=\"job_control_detail.php?client_id=$client_id&job_id=$job_id\"><font color='#ffffff'>$job_number</font></a></td>";
			echo "<td class=\"text\">$client_name</td>";
			echo "<td class=\"text\">$order_date</td>";
			echo "<td class=\"text\">$due_date</td>";
			echo "<td class=\"text\">$total_hours</td>";
			echo "<td class=\"text\">$external_hours</td>";
			echo "<td class=\"text\">$billable_hours</td>";
			echo "<td class=\"text\">$employee</td>";
			echo "<td class=\"text\">$job_title</td></tr>";
		}
		?>
      </table>
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
