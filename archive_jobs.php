<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* archive_jobs template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 16/2/2004
* Client: WorldWeb, Domain: database intranet
* This template consists of 3 sections: Navigation, SQL Queries & formatting, HTML output
* Important files included in this template are: header.inc (head section and javascript library),
* globals.php (connection string),
* Last Modified: 16/2/2004 By Aviv Efrat
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

//establish a persistent connection and get all required data.
// select valuations for  valuer
$db = mysql_pconnect ($hostName, $userName, $password);
mysql_select_db($database);

//select all existing jobs for client
$query1 = "SELECT * FROM jobs WHERE client_id = '$client_id' ORDER BY job_id DESC";
$result1 = mysql_query ($query1, $db);

// update totals for all jobs to ensure it is accurate
while ($row = mysql_fetch_assoc($result1))
	updateTotalTime($db, $row['job_id']);
$result1 = mysql_query ($query1, $db);

//select all existing contacts
$query2 = "SELECT first_name, last_name FROM contacts WHERE client_id = '$client_id'";
$result2 = mysql_query ($query2, $db);
//add values to a variable to be used in drop downs
$mycontacts = "";
while ($row2=mysql_fetch_object($result2)) {
	$mycontacts = $mycontacts . "<option>$row2->first_name $row2->last_name</option>\n";
}

//begin general display of information
$query3 = "SELECT client_name, trading_name, agreement_number, website_url, status FROM clients WHERE client_id = '$client_id'";
$result3 = mysql_query ($query3, $db);
$row3 = mysql_fetch_object($result3);
$client_name = $row3->client_name;
$trading_name = $row3->trading_name;
$agreement_number = $row3->agreement_number;
$website_url = $row3->website_url;
$status = $row3->status;

//close connection to db
mysql_close($db);
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
include_once("includes/client_top.php");
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <?php include_once("includes/admin_links.php"); ?>
  <tr>
    <td>&nbsp;</td>
    <td colspan="2" class="text"><u>Job Archive</u></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td valign="top" class="text" colspan="2">
      <p class="subheading">Job List</p>
      <table border="0" cellspacing="1" cellpadding="1">
        <tr class="text">
          <td width="70" class="text">Job Number</td>
          <td width="50" class="text">Status</td>
          <td width="10" class="text"><img src="images/spacer.gif" width="10" height="11"></td>
          <td width="350" class="text">Title</td>
					<td width="90" class="text">Project Manager</td>
          <td width="90" class="text">Due Date<br>Completion Date</td>
		  <td width="90" class="text">Job Close Date</td>
          <td width="40" class="text">Act.Hrs</td>
          <td width="40" class="text">Adj.Hrs</td>
          <td width="40" class="text">Bill.Hrs</td>
        </tr>
		<?php
		while ($row1 = mysql_fetch_object($result1)) {
			$job_id = $row1->job_id;
			$job_number = $row1->job_number;
			$job_title = $row1->job_title;
			//format due date
			$due_date = ToNextContact($row1->due_date);
			//format closing date
			if (!empty($row1->closing_date)) {
				$closing_date = ToNextContact($row1->closing_date);
			} else {$closing_date = "n/a";}
			$total_hours = $row1->total_hours;
			$external_hours = $row1->external_hours;
			$billable_hours = $row1->billable_hours;
			$status = $row1->status;
			if ($status == "open") {$mystyle = "subheading";} else {$mystyle = "text";}
			echo "<tr valign=\"middle\" bgcolor=\"#0070A6\">";
			echo "<td class=\"text\"><a href=\"job_control_detail.php?job_id=$job_id&client_id=$client_id\"><font color=\"#B9E9FF\">$job_number</font></a></td>";
			echo "<td class=\"$mystyle\"><input name=\"status\" type=\"text\" value=\"$status\" size=\"4\" class=\"$mystyle\"></td>";
			echo "<td>&nbsp;</td>";
			echo "<td class=\"text\" align=\"left\"><a href=\"job_control_detail.php?job_id=$job_id&client_id=$client_id\"><font color=\"#B9E9FF\">$job_title</font></a></td>";
			echo "<td class=\"text\">" . $row1->project_manager . "</td>";
			echo "<td class=\"text\">$due_date</td>";
			echo "<td class=\"text\">$closing_date</td>";
			echo "<td class=\"text\">$total_hours</td>";
			echo "<td class=\"text\">$external_hours</td>";
			echo "<td class=\"text\">$billable_hours</td>";
			echo "</tr>";
		}
		?>
      </table>
      <p><img src="images/spacer.gif" width="10" height="11"></p>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td valign="top" class="text">
      <p><img src="images/spacer.gif" width="233" height="14"></p>
      </td>
    <td width="76%" valign="top" class="text">
<p></p>
      </td>
  </tr>
</table>
</body>
</html>
