<?php

	function addContactLog( $time, $client_id, $user_id, $user_name, $category, $message ) {
		global $db;
		global $keyword_map;
		$message = trim( $message );

		if( $category == "g" ) {
			foreach( $keyword_map as $keyword_cat => $keywords ) {
				foreach( $keywords as $keyword ) {
					if( strpos( $message, $keyword ) !== false ) {
						$category = $keyword_cat;
						break 2;
					}
				}
			}
		}
		
		if( $category == "g" ) {
			switch( $user_name ) {
				case "Brian Cassingea":
				case "Brian Cassingena":
				case "Peveni Rajapakse":
				case "Chris Weddle":
					$category = "m";
					break;
			}
		}
		
		echo "adding: " . $time . " - " . $user_id . " - " . $user_name . " - " . $category . " - " . nl2br( $message ) . "<br><br>";
		mysql_query(
			"INSERT INTO contact_log ( timestamp, client_id, p_id, name, category, comment ) VALUES ( " .
			"\"" . $time . "\", " .
			$client_id . ", " .
			$user_id . ", " .
			"\"" . trim( $user_name ) . "\", " .
			"\"" . trim( $category ) . "\", " .
			"\"" . str_replace( "\"", "\\\"", $message ) . "\" )", $db );
	}

	//include a globals file for db connection
	session_start();
	include_once("includes/globals.php");

	//establish a persistent connection and get all required data.
	$db = mysql_connect ($hostName, $userName, $password);
	mysql_select_db($database);
	
	// What keywords determine what category
	$keyword_map = array(
		"b" => array( "$", "cheque", "payment", "invoice" ),
		"m" => array( "spoke", "phone", "Phone", "Spoke", "Rang", "rang" ) );
	
	// Clear the table
	mysql_query('DELETE FROM contact_log');
	
	// Get a list of contact logs
	$dir = opendir( $logDir );
	while( $file = readdir( $dir ) ) {
		if( preg_match( "/^(\d+)log.txt$/", $file, $matches ) ) {
			
			// Get the log details
			$client_id = $matches[1];
			$lines = file( $logDir . "/" . $file );
			
			echo "=========================================================<br>";
			echo "proessing log for " . $client_id . "<br><br>";
			
			// Process the log file
			$content = "";
			foreach( $lines as $line ) {
				$line = $line;

				// If this is a new entry
				if( preg_match( "/^entry:\s+(\d+)\/(\d+)\/(\d+)\s+(\d+):(\d+)\s+by:\s*(.+)/i", $line, $matches ) ) {
					
					// If there was a previous entry
					if( $content != "" ) {
						addContactLog( $time, $client_id, $user_id, $user_name, $category, $content );
					}
					
					// Get the new entry details
					$time = date( "Y-m-d H:i:s", mktime( $matches[4], $matches[5], 0, $matches[2], $matches[1], $matches[3] ) );
					$content = "";
					$user_id = 0;
					$user_name = trim( $matches[6] );
					$category = "g";
					$name = explode( " ", trim( preg_replace( "/\s+/", " ", $user_name ) ) );
					$results = mysql_query( "SELECT p_id, r FROM ps WHERE first_name=\"" . $name[0] . "\" AND last_name=\"" . $name[1] . "\"", $db );
					while( $row = mysql_fetch_assoc( $results ) ) {
						$user_id = $row["p_id"];
						switch( $row["r"] ) {
							case "m":
							case "b":
								$category = $row["r"];
								break;
						}
					}
					
				} else {
					$content .= $line;
				}
				
			}
			
			// If there was a final entry
			if( $content != "" ) {
				addContactLog( $time, $client_id, $user_id, $user_name, $category, $content );
			}
			
		}
	}

	// Close database connection
	mysql_close($db);

?>
