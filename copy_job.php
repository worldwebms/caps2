<?php
session_start();

//include a globals file for db connection
include_once("includes/globals.php");
require_once ("includes/CapsMySQL.php");
try{
	// open persistent connection
	//$db = mysql_connect ($hostName, $userName, $password);
	//mysql_select_db($database);
	$db = new CapsMySQL($hostName, $userName, $password, $database);
	
	$client_id = $_POST['client_id'];
	$job_id = $_POST['job_id'];


	// Copy Jobs
	$new_job_id = false;
	$row = $db->getrow('SELECT * FROM jobs WHERE job_id=?', array($job_id));
	if ($row) {
		unset($row['job_id']);
		$row['client_id'] = $client_id;
		$db->execute(
				'INSERT INTO jobs (`' . trim(implode('`, `', array_keys($row)), ' ,') . '`) VALUES (' . trim(str_repeat('?, ', count($row)), ', ') . ')',
				array_values($row)
		);
		$new_job_id = $db->insert_id();
	}
	
	
	// Copy Job Assignment
	$job_assignment_rows = $db->getarray('SELECT * FROM job_assignments WHERE job_id=?', array($job_id));
	foreach($job_assignment_rows as $job_assignment_row) {
		$new_job_assignment_id = false;
		$old_job_assignment_id = $job_assignment_row['job_assign_id'];
			
		if ($job_assignment_row) {
			unset($job_assignment_row['job_assign_id']);
			$job_assignment_row['job_id'] = $new_job_id;
			$db->execute(
					'INSERT INTO job_assignments (`' . trim(implode('`, `', array_keys($job_assignment_row)), ' ,') . '`) VALUES (' . trim(str_repeat('?, ', count($job_assignment_row)), ', ') . ')',
					array_values($job_assignment_row)
			);
			$new_job_assignment_id = $db->insert_id();
		}
	}
	
	
	// Copy Job Tasks
	$job_task_rows = $db->getarray('SELECT * FROM job_tasks WHERE job_id=?', array($job_id));
	$task_ids = array();
		foreach($job_task_rows as $job_task_row) {
			
			$new_job_task_id = false;
			$old_job_task_id = $job_task_row['job_task_id'];
			if ($job_task_row) {
				unset($job_task_row['job_task_id']);
				$job_task_row['job_id'] = $new_job_id;
				$db->execute(
						'INSERT INTO job_tasks (`' . trim(implode('`, `', array_keys($job_task_row)), ' ,') . '`) VALUES (' . trim(str_repeat('?, ', count($job_task_row)), ', ') . ')',
						array_values($job_task_row)
				);
				$new_job_task_id = $db->insert_id();
				$task_ids[$old_job_task_id] = $new_job_task_id;
				// Copy Job Task Notes
				$job_task_note_rows = $db->getarray('SELECT * FROM job_task_notes WHERE job_task_id=?', array($old_job_task_id));
				foreach($job_task_note_rows as $job_task_note_row) {		
					$new_job_task_note_id = false;
					$old_job_task_note_id = $job_task_note_row['job_task_note_id'];
							
					if ($job_task_note_row) {
						unset($job_task_note_row['job_task_note_id']);
						$job_task_note_row['job_task_id'] = $new_job_task_id;
						$db->execute(
								'INSERT INTO job_task_notes (`' . trim(implode('`, `', array_keys($job_task_note_row)), ' ,') . '`) VALUES (' . trim(str_repeat('?, ', count($job_task_note_row)), ', ') . ')',
								array_values($job_task_note_row)
						);
						$new_job_task_note_id = $db->insert_id();
					}
				}
			}

	}
	// Copy Job Details
	$job_detail_rows = $db->getarray('SELECT * FROM job_details WHERE job_id=?', array($job_id));
	foreach($job_detail_rows as $job_detail_row) {
		$new_job_detail_id = false;
		$old_job_detail_id = $job_detail_row['job_details_id'];
			
		if ($job_detail_row) {
			unset($job_detail_row['job_details_id']);
			if($task_ids[$job_detail_row['job_task_id']]) {
				$job_detail_row['job_task_id'] = $task_ids[$job_detail_row['job_task_id']];
			}
			$job_detail_row['job_id'] = $new_job_id;
			$db->execute(
					'INSERT INTO job_details (`' . trim(implode('`, `', array_keys($job_detail_row)), ' ,') . '`) VALUES (' . trim(str_repeat('?, ', count($job_detail_row)), ', ') . ')',
					array_values($job_detail_row)
			);
			$new_job_detail_id = $db->insert_id();
		}
	}
	echo "New job $new_job_id is created.";

} catch(Exception $e) {
	echo $e->getMessage();
}

//refresh the page back to job_control_detail.php
//$page = "job_control_detail.php?client_id=$client_id&job_id=$job_id";
//echo "<SCRIPT LANGUAGE='JavaScript'>document.location.replace('$page');</SCRIPT>";
?>

