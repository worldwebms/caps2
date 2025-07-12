<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* functions file by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 20/6/2003
* Client: WorldWeb Intranet:
* This template consists of common php functions to streamline code and reduce repetition throughout the site
* Last Modified: 20/6/2003 By Aviv Efrat
* Please remember to backup template before modifying
*
*********************************************************************************************************
*/

//function to add $sign and commas to float number------------------------------------------------------
function dollarFormat($num){
	global $mynum;
	//get rid of dot and 2 last digits
	list($first, $second) = explode('.', $num);
	//add commas after 3 digits
	$mynum = number_format($first);
	//add dollor sign
	$dollar = "$";
	$mynum = $dollar . $mynum;
	return $mynum;
}

//function to convert the month to numeric
function convert_month($M) {
global $month;
	switch ($M) {
		case "Jan":
			$month = "01";
			break;
		case "Feb":
			$month = "02";
			break;
		case "Mar":
			$month = "03";
			break;
		case "Apr":
			$month = "04";
			break;
		case "May":
			$month = "05";
			break;
		case "Jun":
			$month = "06";
			break;
		case "Jul":
			$month = "07";
			break;
		case "Aug":
			$month = "08";
			break;
		case "Sep":
			$month = "09";
			break;
		case "Oct":
			$month = "10";
			break;
		case "Nov":
			$month = "11";
			break;
		case "Dec":
			$month = "12";
			break;
	}
return $month;
}

//function to convert the month to numeric
function get_month($M) {
global $month;
	switch ($M) {
		case "01":
			$month = "Jan";
			break;
		case "02":
			$month = "Feb";
			break;
		case "03":
			$month = "Mar";
			break;
		case "04":
			$month = "Apr";
			break;
		case "05":
			$month = "May";
			break;
		case "06":
			$month = "Jun";
			break;
		case "07":
			$month = "Jul";
			break;
		case "08":
			$month = "Aug";
			break;
		case "09":
			$month = "Sep";
			break;
		case "10":
			$month = "Oct";
			break;
		case "11":
			$month = "Nov";
			break;
		case "12":
			$month = "Dec";
			break;
	}
return $month;
}

//function to format the next contact date to a meaningful format
function ToNextContact($mydate, $format = "d-M-Y") {
global $next_contact;
	list($Y,$M, $D) = split('-',$mydate);
	$mydate = mktime(0,0,0,$M,$D,$Y);
	return date($format, $mydate);
}

//function to establish a real date from the values returned so
//we can update the db or insert new records
function FromNextContact($mydate) {
global $next_contact;
	list($D,$M, $Y) = split('-',$mydate);
	//format the month using a function
	$M = convert_month($M);
	$next_contact = "$Y-$M-$D";
return $next_contact;
}

function getTime($start_time, $end_time) {
	global $mytime;
	if ($end_time == null) {
		$timediff = 0;
	} else {
		list($h1, $m1, $s1) = split(':',$start_time);
		list($h2, $m2, $s2) = split(':',$end_time);
		//	convert to seconds the time don't worry about second on the timestamp
		$sts = ($h1 * 60 * 60) + ($m1 * 60);
		$ets = ($h2 * 60 * 60) + ($m2 * 60);
		$timediff = $ets - $sts;
	}
	//get number of minutes left
	$myminutes = ($timediff / 60);
	//get number of hours in myminutes
	$myhours = $myminutes / 60;
	//make the decimal point with 2
	$newtime = number_format($myhours,2);
	$hours = substr($newtime, 0, -3);
	$minutes = substr($newtime, -3);
	//get minutes in real time
	$minutes = substr('00' . round($minutes * 60), -2);
	$mytime = "$hours:$minutes";
	//retrun the timediff
	return $mytime;
}

function totalTime($totalSoFar, $thetime) {
	global $mytotal;
	list($h1, $m1) = split(':',$totalSoFar);
	list($h2, $m2) = split(':',$thetime);
	//convert to seconds the time don't worry about second on the timestamp
	$tot = ($h1 * 60 * 60) + ($m1 * 60);
	$nt = ($h2 * 60 * 60) + ($m2 * 60);
	//add the seconds together
	$newTotal = $tot + $nt;
	//get number of minutes left
	$myminutes = ($newTotal / 60);
	//get number of hours in myminutes
	$myhours = $myminutes / 60;
	//make the decimal point with 2
	$newtime = number_format($myhours,2);
	$hours = substr($newtime, 0, -3);
	$minutes = substr($newtime, -3);
	//get minutes in real time
	$minutes = round($minutes * 60);
	$mytotal = "$hours:$minutes";
//retrun the newtime
return $mytotal;
}

function parseHoursToMinutes($hours) {
	if (strpos($hours, ':') !== false) {
		$bits = explode(':', $hours);
		$minutes = ($bits[0] * 60) + $bits[1];
	} else {
		$minutes = floatval($hours) * 60;
	}
	return $minutes;
}

function formatHours($hours) {
	return formatMinutes($hours * 60);
}

function formatMinutes($minutes) {
	return floor($minutes / 60) . ':' . str_pad($minutes % 60, 2, '0', STR_PAD_LEFT);
}

function checkTime($mytime) {
global $newtime;
	$time_len = strlen($mytime);
	if( strpos( $mytime, ":" ) !== false ) {
		$thetime = explode( ":", $mytime );
		if( count( $thetime ) == 2 ) {
			$newtime = substr( "00" . $thetime[0], -2 ) . ":" . substr( "00" . $thetime[1], -2 );
		} else {
			$newtime = 0;
		}
	} elseif($time_len != 4 | !is_numeric($mytime)) {
		$newtime = 0;
	} else {
		//add the dots after the first two digits
		$thetime1 = substr($mytime, 0, 2);
		$thetime2 = substr($mytime, -2);
		$newtime = $thetime1 . ":" . $thetime2;
	}
return $newtime;
}

function checkControl($control) {
global $mycontrol;
	$mycontrol = "";
	if ($control == "on") {
		$mycontrol = "yes";
	} elseif ($control == "") {
		$mycontrol = "no";
	} elseif ($control == "yes") {
		$mycontrol = "checked";
	} else {
		$mycontrol = "";
	}
return $mycontrol;
}

function testAccount($valias, $domain) {
	global $isOK;
	$isOK = "no";
	$db = mysql_pconnect ("localhost", "capsuser", "ww4ims");
	mysql_select_db("caps");
	//query to see if user already exists in db
	$query = "SELECT v_username, domain FROM vusers WHERE v_username = '$valias' AND domain = '$domain'";
	$result = mysql_query($query, $db);
	$numrows = mysql_num_rows($result);
	if ($numrows == 0) {$test1 = "yes";}
	//and test the valiases table
	$query1 = "SELECT valias, domain FROM valiases WHERE valias = '$valias' AND domain = '$domain'";
	$result1 = mysql_query($query1, $db);
	$numrows1 = mysql_num_rows($result1);
	if ($numrows1 == 0) {$test2 = "yes";}
	if ($test1 == "yes" & $test2 == "yes") {$isOK = "yes";}
	return $isOK;
}

function testAliasAccount($valias, $domain, $userDomain) {
global $isOK;
$isOK = false;
	if ($domain != $userDomain) {
		//return false
	} else {
		$db = mysql_pconnect ("localhost", "capsuser", "ww4ims");
		mysql_select_db("caps");
		//query to see if user already exists in db
		$query = "SELECT v_username, domain FROM vusers WHERE v_username = '$valias' AND domain = '$domain'";
		$result = mysql_query($query, $db);
		$numrows = mysql_num_rows($result);
		if ($numrows == 0) {$test1 = "yes";}
		//and test the valiases table
		$query1 = "SELECT valias, domain FROM valiases WHERE valias = '$valias' AND domain = '$domain'";
		$result1 = mysql_query($query1, $db);
		$numrows1 = mysql_num_rows($result1);
		if ($numrows1 == 0) {$test2 = "yes";}
		if ($test1 == "yes" & $test2 == "yes") {$isOK = true;}
	}
return $isOK;
}


function secondsToTime($seconds) {
	$minutes = ceil($seconds / 60);
	$hours = floor($minutes / 60);
	$minutes = $minutes % 60;
	return $hours + ($minutes / 100);
}

function updateTotalTime($db, $job_id) {
	$query = 'SELECT ' .
		'SUM(TIME_TO_SEC(end_time) - TIME_TO_SEC(start_time)) AS internal, ' .
		'SUM(IF(override > 0, override * 60, TIME_TO_SEC(end_time) - TIME_TO_SEC(start_time))) AS external, ' .
		'SUM(IF(job_tasks.chargeable=0 OR job_details.no_charge=1, 0, IF(override > 0, override * 60, TIME_TO_SEC(end_time) - TIME_TO_SEC(start_time)))) AS billable ' .
		'FROM job_details ' .
		'LEFT JOIN job_tasks ON job_tasks.job_task_id=job_details.job_task_id ' .
		'WHERE job_details.job_id=' . intval($job_id) . ' AND end_time IS NOT NULL AND job_details.deleted_on IS NULL';
	$results = mysql_query($query, $db);
	$result = mysql_fetch_assoc($results);
	
	$total_hours = secondsToTime($result['internal']);
	$external_hours = secondsToTime($result['external']);
	$billable_hours = secondsToTime($result['billable']);
	
	$query = 'UPDATE jobs SET ' .
		'total_hours=' . $total_hours . ', ' .
		'external_hours=' . $external_hours . ', ' .
		'billable_hours=' . $billable_hours . ' ' .
		'WHERE job_id=' . intval($job_id);
	mysql_query($query, $db);
	
}

function array_safe($array, $key, $default = false) {
	if(!is_array($array) && !is_object($array))
		return $default;

	elseif(array_key_exists($key, $array))
		return $array[$key];

	else
		return $default;
}

function add_to_audit_history( $db, $object_type, $object_id, $action, $notes = "" ) {
	global $pid;
	mysql_query( "INSERT INTO audit_history ( object, object_id, p_id, action, notes ) VALUES ( '$object_type', '$object_id', '$pid', '$action', '$notes' )", $db );
}

function get_last_audit_history( $db, $object_type, $object_id, $action ) {
	$results = mysql_query( "SELECT * FROM audit_history WHERE object='$object_type' AND object_id='$object_id' AND action='$action' ORDER BY timestamp DESC LIMIT 1" );
	if( $results !== false && mysql_num_rows( $results ) > 0) {
		$results = mysql_fetch_assoc( $results );
		$results2 = mysql_query( "SELECT first_name, last_name FROM ps WHERE p_id='" . $results["p_id"] . "' LIMIT 1" );
		if( $results2 !== false && mysql_num_rows( $results2 ) > 0 ) {
			$results2 = mysql_fetch_assoc( $results2 );
			$results["full_name"] = $results2["first_name"] . " " . $results2["last_name"];
		} else {
			$results["full_name"] = "unknown";
		}
	} else {
		$results = false;
	}
	return $results;
}

/**
 * Converts individual RGB values into a hex string.
 *
 * @param integer $r Red value
 * @param integer $g Green value
 * @param integer $b Blue value
 * @return string The hex string representing that colour.
 */
function colour_rgb_to_hex($r, $g, $b) {
	$hex_r = dechex($r);
	if (strlen($hex_r) == 1)
		$hex_r = '0'.$hex_r;

	$hex_g = dechex($g);
	if (strlen($hex_g) == 1)
		$hex_g = '0'.$hex_g;

	$hex_b = dechex($b);
	if (strlen($hex_b) == 1)
		$hex_b = '0'.$hex_b;

	return '#'.$hex_r.$hex_g.$hex_b;
}


/**
 * Converts a hex colour string into its individual RGB components.
 *
 * @param string $hex Hex colour string
 * @return array An array conaining the RGB components.
 */
function colour_hex_to_rgb($hex) {
	// Trim off the # character if it exists.
	if (substr($hex,0,1) == '#')
		$hex = substr($hex, 1);

	switch (strlen($hex)) {
		case 6:
			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));
			break;
		case 3:
			$r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
			$g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
			$b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
			break;
		default:
			return false;
	}
	return array('r' => $r, 'g' => $g, 'b' => $b);
}

function debug($message) {
	if (!is_string($message))
		$message = var_export($message, true);
	error_log($message);
}