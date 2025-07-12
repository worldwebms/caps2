<?php

//establish a connection and get all required data.
// select valuations for  valuer
$db = mysql_connect ($hostName, $userName, $password);
mysql_select_db($database);

//define user id for querying
$myid = strtolower(stripslashes($uid));

//select all open jobs
if ($days != "NULL") {
	// get the current unix timestamp 
	$ts = time(); 
	// figure out what is the requested number of days in seconds
	$mydays = $days * 24 * 60 * 60; 
	// make a timestamp from mydays
	$timeperiod = date("Y-m-d", ($ts + $mydays));
	//query db
	$query = "SELECT jobs.job_id, jobs.client_id, jobs.job_number, jobs.job_title, jobs.job_stage, jobs.employee, jobs.order_date, jobs.due_date, jobs.total_hours, jobs.project_manager, clients.client_name FROM jobs, clients WHERE jobs.client_id = clients.client_id AND jobs.status = 'open' AND jobs.employee LIKE '%$myid%' AND jobs.est_completion = '$timeperiod' ORDER BY client_name, job_id";
} else {
	$query = "SELECT jobs.job_id, jobs.client_id, jobs.job_number, jobs.job_title, jobs.job_stage, jobs.employee, jobs.order_date, jobs.due_date, jobs.total_hours, jobs.project_manager, clients.client_name FROM jobs, clients WHERE jobs.client_id = clients.client_id AND jobs.status = 'open' AND jobs.employee LIKE '%$myid%' ORDER BY client_name, job_id";
}
//send query to db
$result = mysql_query ($query, $db);
?>
      <table border="0" cellspacing="1" cellpadding="2" class="admin_list">
        <tr> 
          <td width="65" class="text">Job Number</td>
          <td width="120" class="text">Client</td>
          <td width="70" class="text">Order Date</td>
          <td width="70" class="text">Due Date</td>
          <td width="50" class="text">Hours</td>
          <td width="80" class="text">Stage</td>
<!--          <td width="60" class="text">Employee</td> -->
				  <td width="320" class="text">Job Title</td>
					<td class="text">Project Manager</td>
        </tr>
		<?php
		$row_odd = true;
		while ($row=mysql_fetch_object($result)) {
			$job_id = $row->job_id;
			$client_id = $row->client_id;
			$job_number = $row->job_number;
			$job_title = $row->job_title;
			$employee = str_replace( "|", ", ", $row->employee );
			$next_contact = $row->order_date;
			//format due date
			$order_date = ToNextContact($next_contact);
			$next_contact = $row->due_date;
			//format due date
			$due_date = ToNextContact($next_contact);
			$total_hours = $row->total_hours;
			$client_name = $row->client_name;
			$stage = $row->job_stage;
			//display results
			echo "<tr valign=\"middle\" class=\"" . ( $row_odd ? "odd" : "even" ) . "\">";
			echo "<td class=\"text\"><a href=\"job_control_detail.php?client_id=$client_id&job_id=$job_id\"><font color='#ffffff'>$job_number</font></a></td>";
			echo "<td class=\"text\">$client_name</td>";
			echo "<td class=\"text\">$order_date</td>";
			echo "<td class=\"text\">$due_date</td>";
			echo "<td class=\"text\">$total_hours</td>";
			echo "<td class=\"text\">$stage</td>";
//			echo "<td class=\"text\">$employee</td>";
			echo "<td class=\"text\">$job_title</td>";
			echo "<td class=\"text\">" . $row->project_manager . "</td>";
			echo "</tr>";
			$row_odd = !$row_odd;
		}
		?>
      </table>
<?php

	mysql_close( $db );

?>
