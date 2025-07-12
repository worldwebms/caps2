<?php

include_once( 'includes/config.php' );

//establish a connection to mysql
$db = mysql_connect($hostName, $userName, $password);
mysql_select_db($database);
//define query and select users 
//$query = "SELECT * FROM ps WHERE u='$uid' AND p='$pwd';";
$uid = $_POST['uid'];
$pwd = $_POST['pwd'];
$query = "SELECT * FROM ps WHERE u='$uid' AND p='$pwd';";
$result = mysql_query($query, $db);
$row = mysql_fetch_object($result);
$first_name = $row->first_name;
$last_name = $row->last_name;
$pid = $row->p_id;
$fullname = "$first_name $last_name";
$company = $row->company;
$r = $row->r;
$num=mysql_num_rows($result);

//check if there are any record matching
if ($num == 1) {
	if ($r == "c") {
		//check to see if it is musicorp user
		if ($company == "musicorp") {
			$page = "musicorp/email.php?client_id=562";
		}
	} else {
		$page = "welcome.php";
	}
	//begin session and register username
	session_start();
	session_register("pid");
	session_register("uid");
	session_register("fullname");
	session_register("r");
	header("Location: $page");
} else {
	//direct user to login page again and display an error message
	//$message = "Your user name or password did not match, please try again";
$message = 'error';
	$page = "index.php?message=$message";
	header("Location: $page");
}

?>
