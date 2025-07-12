<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* contact_list template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 19/5/2003
* Client: WorldWeb, Domain: database intranet
* This template consists of 2 section: sql query listing contacts HTML output directed to index1.php
* Important files included in this template are: header.inc (head section and javascript library),
* globals.php (connection string),
* Last Modified: 12/1/2004 By Aviv Efrat
* Please remember to backup template before modifying
*
*********************************************************************************************************
*/
//begin session
session_start();
//include a globals file for db connection
include_once("includes/globals.php");
//define date
$today = date("d/m/Y" ,time());

//establish a persistent connection and get all required data.
$db = mysql_connect ($hostName, $userName, $password);
mysql_select_db($database);

//assign appropriate query if both names found
$query = "SELECT client_id, client_name, trading_name, agreement_number FROM clients WHERE (" . (is_numeric($mysearch) ? "agreement_number = '$mysearch' OR " : "") . "client_name LIKE '%$mysearch%' OR trading_name LIKE '%$mysearch%') AND status != 'd' ORDER BY client_name ASC";
//send query to mysql
$clients = mysql_query($query, $db);

// find any jobs with matching values
$close_date = date('Y-m-d', strtotime('-3 months'));
$query = "SELECT j.job_id, j.job_number, j.job_title, j.status, c.client_name " .
	"FROM jobs AS j " .
	"LEFT JOIN clients AS c ON c.client_id=j.client_id " .
	"WHERE ( j.job_title LIKE '%$mysearch%' OR j.job_number LIKE '$mysearch%' ) AND ( j.status='open' OR ( j.status='closed' AND j.closing_date >= '$close_date' ) ) " .
	"ORDER BY status ASC, order_date DESC";
$jobs = mysql_query($query, $db);

// find any tasks with matching values
$query = "SELECT jt.job_task_id, jt.description, jt.job_task_number, c.client_name, jt.completed, jt.status, j.job_title " .
	"FROM job_tasks AS jt " .
	"LEFT JOIN jobs AS j ON j.job_id=jt.job_id " .
	"LEFT JOIN clients AS c ON c.client_id=j.client_id " .
	"WHERE ( jt.description LIKE '%$mysearch%' OR jt.job_task_number LIKE '%$mysearch%' ) AND ( jt.completed IS NULL OR jt.completed >= '$close_date' ) AND jt.deleted_on IS NULL " .
	"ORDER BY completed ASC, last_modified DESC";
$tasks = mysql_query($query, $db);

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
</head>

<body bgcolor="#006699" leftmargin="0" topmargin="0">
<?php include_once("includes/top.php"); ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td width="1%"><img src="images/spacer.gif" width="8" height="27"></td>
    <td class="clienttitle">&nbsp;</td>
  </tr>
  <tr valign="top">
    <td>&nbsp;</td>
    <td class="clienttitle">
	  <table width="800" border="0" cellspacing="1" cellpadding="1">
        <tr class="text"> 
          <td class="text">Client Name:</td>
          <td width="200" class="text">Trading Name:</td>
		  <td width="200" class="text">Ref:</td>
        </tr>
        <?php
		if (mysql_num_rows($clients) > 0) {
		  while ($row = mysql_fetch_object($clients)) {
			$client_id = $row->client_id;
			$agreement_number = $row->agreement_number;
			$client_name = $row->client_name;
			$trading_name = $row->trading_name;
			if (empty($trading_name)) {$trading_name = "N/A";}
			if (empty($agreement_number) | $agreement_number == 0) {$agreement_number = "N/A";}
			//display contact list
			echo "<tr><td bgcolor='#0070A6' class='text'><a href=\"index1.php?client_id=$client_id&event=FindClient\"><font color='#B9E9FF'>$client_name</font></a></td>";
			echo "<td bgcolor='#0070A6' class='text'>$trading_name</td>";
			echo "<td bgcolor='#0070A6' class='text'>$agreement_number</td></tr>";
			//end loop
		  }
		//end if result
		} else {
		  	echo "<tr><td bgcolor='#0070A6' colspan=3 class=text>There are no clients with a similar name to the string you have typed</td></tr>";
		}
		  
		?>
      </table>
      
      <br>
	  <table width="100%" border="0" cellspacing="1" cellpadding="2" class="admin_list text">
	    <tr class="text">
	      <td>Task Reference</td>
	      <td>Task Title</td>
	      <td>Job Title</td>
	      <td>Client</td>
	      <td align="center">Phase</td>
	    </tr>
	    <?php
		if (mysql_num_rows($tasks) > 0) {
		  while ($row = mysql_fetch_object($tasks)) {
            $job_task_id = $row->job_task_id;
            $description = $row->description;
            $reference = $row->job_task_number;
            $client_name = $row->client_name;
            $completed = $row->completed;
            $status = $completed ? 'Completed' : $row->status;
            $job_title = $row->job_title;
			echo "<tr class='" . ($completed ? 'completed' : '') . "'>";
			echo "<td bgcolor='#0070A6'><a href=\"job_task_detail.php?job_task_id=$job_task_id\"><font color='#B9E9FF'>$reference</font></a></td>";
			echo "<td bgcolor='#0070A6'><a href=\"job_task_detail.php?job_task_id=$job_task_id\"><font color='#B9E9FF'>$description</font></a></td>";
			echo "<td bgcolor='#0070A6'>$job_title</td>";
			echo "<td bgcolor='#0070A6'>$client_name</td>";
			echo "<td bgcolor='#0070A6' align='center' class='status " . strtolower($status) . "'>" . $status . "</td>";
			echo "</tr>";
			//end loop
		  }
		//end if result
		} else {
		  	echo "<tr><td bgcolor='#0070A6' colspan=5 class=text>There are no tasks with a similar name to the string you have typed</td></tr>";
		}
		  
		?>
	  </table>
      
      <br>
	  <table width="800" border="0" cellspacing="1" cellpadding="2" class="admin_list text">
	    <tr class="text">
	      <td>Job Number</td>
	      <td>Job Title</td>
	      <td>Client</td>
	      <td>Status</td>
	    </tr>
	    <?php
		if (mysql_num_rows($jobs) > 0) {
		  while ($row = mysql_fetch_object($jobs)) {
            $job_id = $row->job_id;
            $job_number = $row->job_number;
            $job_title = $row->job_title;
            $client_name = $row->client_name;
            $status = $row->status;
			echo "<tr class='" . ($status == 'closed' ? 'completed' : '') . "'>";
			echo "<td bgcolor='#0070A6'><a href=\"job_control_detail.php?job_id=$job_id\"><font color='#B9E9FF'>$job_number</font></a></td>";
			echo "<td bgcolor='#0070A6'><a href=\"job_control_detail.php?job_id=$job_id\"><font color='#B9E9FF'>$job_title</font></a></td>";
			echo "<td bgcolor='#0070A6'>$client_name</td>";
			echo "<td bgcolor='#0070A6'>$status</td>";
			echo "</tr>";
			//end loop
		  }
		//end if result
		} else {
		  	echo "<tr><td bgcolor='#0070A6' colspan=4 class=text>There are no jobs with a similar name to the string you have typed</td></tr>";
		}
		  
		?>
	  </table>
	</td>
  </tr>
</table>
</body>
</html>
