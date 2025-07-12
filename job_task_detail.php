<?php

//begin session
session_start();
//define title
$title = "WorldWeb Internal Database";
//define date
$today = date("d/m/Y" ,time());
//include a globals file for db connection
require_once 'includes/globals.php';
//include functions file
require_once 'includes/functions.php';
require_once 'includes/CapsApi.php';

CapsApi::set_user($uid);
$job_task = CapsApi::get_issue($job_task_id);
if ($job_task == false) {
	
	$project = CapsApi::get_project($job_id);
	if ($project) {
		$job_task_id = false;
		$job_task = array(
			'job_task_id' => false,
			'job_id' => $project['job_id']
		);
	} else {
		header('Location: welcome.php');
		return;
	}
}
$job_task_id = $job_task['job_task_id'];

$job = CapsApi::get_project($job_task['job_id']);
$job_id = $job['job_id'];
$client_id = $job['client_id'];
$job_number = $job['job_number'];
$job_title = $job['job_title'];
$project_manager = $job['project_manager'];

$all_staff = CapsApi::get_user_options();

$redirect = false;
switch (array_safe($_POST, 'action', '')) {
	
	case 'attach':
		if ($job_task_id) {
			$file = array_safe($_FILES, 'file', array());
			$tmp_name = array_safe($file, 'tmp_name', '');
			if (is_uploaded_file($tmp_name))
				CapsApi::issue_attachment_create($job_task_id, $tmp_name, array_safe($file, 'name', ''), '');
		}
		$redirect = true;
		break;
		
	case 'attachdelete':
		$attachment_id = array_safe($_POST, 'attachment', '');
		if ($attachment_id && $job_task_id) {
			CapsApi::issue_attachment_delete($job_task_id, $attachment_id);
		}
		$redirect = true;
		break;
		
	case 'comment':
		$body = trim(array_safe($_POST, 'comment', ''));
		if ($body && $job_task_id) {
			CapsApi::issue_comment_create($job_task_id, $body);
		}
		$redirect = true;
		break;
	
	case 'update':
		$details = array();
		if ($job_task_id == false)
			$details['job_id'] = $job_task['job_id'];
		$fields = array(
			'description', 'code', 'due_date', 'employee', 'status', 'priority',
			'chargeable', 'estimate', 'quote',
			'cr_description', 'cr_requester', 'cr_page_affected',
			'cr_reason', 'cr_impact_page', 'cr_impact_functionality', 'cr_impact_site', 'cr_impact_third_party', 'cr_impact_cost'
		);
		foreach ($fields as $name)
			$details[$name] = trim(array_safe($_POST, $name, ''));
		$job_task = CapsApi::issue_update($job_task_id, $details);
		$redirect = 'job';
		break;
		
}

if ($redirect) {
	if ($redirect == 'job')
		header('Location: job_control_detail.php?job_id=' . $job_task['job_id']);
	else
		header('Location: job_task_detail.php?job_task_id=' . $job_task['job_task_id']);
	exit();
}

?><html>
<head>
<title>CAPS | WorldWeb Management Services</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="includes/caps_styles.css" rel="stylesheet" type="text/css">
<?php
//include javascript library
include_once("includes/worldweb.js");
?>
<style type="text/css">
#task-template { display: none; }
.copy td { color: #999; }
.admin_list .other td { opacity: 0.6; }
.completed td { color: #999; }
.change_request_fields td { position: relative; }
.change_request_fields textarea { width: 99%; height: 99%; }
</style>
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
		<td class="text" colspan="2"><u>Detail: Job Number <?php echo $job_number; ?> : <?php echo $job_title; ?></u> &nbsp; &nbsp; &nbsp; Project Manager: <?= $project_manager ?></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td valign="top" class="text" colspan="2">
		
			&gt; <a href="job_control_detail.php?job_id=<?= $job_task['job_id'] ?>" class="white"><font color="B9E9FF">Back to job details</font></a>
			&nbsp; &nbsp; &nbsp;
			&gt; <a href="job_control_detail.php?job_id=<?= $job_id ?>&start_task=<?= $job_task_id ?>" style="color:#B9E9FF;">Start recording time</a>
			
			<form method="post" action="?" enctype="multipart/form-data">
				<input type="hidden" name="job_task_id" value="<?= $job_task_id ?>">
				<?= $job_task_id == '' ? ('<input type="hidden" name="job_id" value="' . $job_id . '">') : '' ?>
				<input type="hidden" name="action" value="update">
					
				<p class="subheading">Task Details</p>
				<table width="750" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td class="text" width="120">Task Name:</td>
						<td colspan="3"><input type="text" name="description" value="<?= htmlspecialchars($job_task['description']) ?>" class="smallblue" maxlength="1000" title="Task Description" style="width:99%;"></td>
					</tr>
					<tr>
						<td class="text">Reference #:</td>
						<td>
							<input type="text" name="code" value="<?= htmlspecialchars($job_task['job_task_number']) ?>" size="20" class="smallblue" maxlength="32" title="Task Reference Number">
							<?php
								$url = CapsKayakoApi::get_ticket_url($job_task['job_task_number']);
								if ($url) {
									echo ' <a href="' . $url . '" target="_blank" class="text" style="color:#fcfcfc;">[view ticket]</a>';
								}
							?>
						</td>
						<td class="text"><?= $job_task['created_by'] ? 'Created On:' : '' ?></td>
						<td class="text"><?= $job_task['created_by'] ? (date('M j, Y', strtotime($job_task['created_on'])) . ' by ' . array_safe($all_staff, $job_task['created_by'], ucwords($job_task['created_by']))) : '' ?></td>
					</tr>
					<tr>
						<td class="text">Due Date:</td>
						<td><?php
            				include_once 'includes/DateField.php';
            				$date_field = new DateField('due_date', $job_task['due_date']);
            				echo $date_field->getHTML();
						?></td>
						<td class="text">Billing Mode:</td>
						<td>
							<select name="chargeable" class="black">
							<?php
							foreach (CapsApi::get_issue_chargeable_options() as $k => $v)
								echo '<option value="' . htmlspecialchars($k) . '"' . ($k == $job_task['chargeable'] ? ' selected' : '') . '>' . htmlspecialchars($v) . '</option>';
							?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="text">Assigned To:</td>
						<td>
							<select name="employee" class="black">
							<?php
							foreach (CapsApi::get_user_options('') as $k => $v)
								echo '<option value="' . htmlspecialchars($k) . '"' . ($k == $job_task['employee'] ? ' selected' : '') . '>' . htmlspecialchars($v) . '</option>';
							?>
							</select>
						</td>
						<td class="text" width="120">Internal Estimate:</td>
						<td><input type="text" name="estimate" value="<?= htmlspecialchars($job_task['estimate'] > 0 ? formatMinutes($job_task['estimate']) : '') ?>" size="4" class="smallblue" maxlength="8" title="Estimated Hours for Internal Use"></td>
					</tr>
					<tr>
						<td class="text">Phase:</td>
						<td>
							<select name="status" class="black">
							<?php
							foreach (CapsApi::get_issue_status_options('') as $k => $v)
								echo '<option value="' . htmlspecialchars($k) . '"' . ($k == $job_task['status'] ? ' selected' : '') . '>' . htmlspecialchars($v) . '</option>';
							?>
							</select>
						</td>
						<td class="text">External Estimate:</td>
						<td><input type="text" name="quote" value="<?= htmlspecialchars($job_task['quote'] > 0 ? formatMinutes($job_task['quote']) : '') ?>" size="4" class="smallblue" maxlength="8" title="Estimated Hours for External Use"></td>
					</tr>
					<tr>
						<td class="text">Priority:</td>
						<td>
							<select name="priority" class="black">
							<?php
							foreach (CapsApi::get_issue_priority_options() as $k => $v)
								echo '<option value="' . htmlspecialchars($k) . '"' . ($k == $job_task['priority'] ? ' selected' : '') . '>' . htmlspecialchars($v) . '</option>';
							?>
							</select>
						</td>
					</tr>
				</table>
				
				<p class="subheading">Task Description</p>
				
				<table width="1000" class="text change_request_fields" id="cr-edit">
					<tr>
						<td colspan="2">Description of Request</td>
						<td colspan="2"></td>
					</tr>
					<tr valign="top">
						<td colspan="4" style="position:relative;height:100px;"><textarea class="smallblue" name="cr_description"><?= htmlspecialchars($job_task['cr_description']) ?></textarea></td>
						<td colspan="2">
							<label style="display:inline-block;width:120px">Requester:</label> <input type="text" name="cr_requester" class="smallblue" value="<?= htmlspecialchars($job_task['cr_requester']) ?>"><br>
							<br>
							<label style="display:inline-block;width:120px">Page / Module Affected:</label> <input type="text" name="cr_page_affected" class="smallblue" value="<?= htmlspecialchars($job_task['cr_page_affected']) ?>"><br>
							<br>
							<?php
								if ($job_task_id) {
							?>
							&gt; <a href="change_request.php?task_id=<?= $job_task_id ?>&title=<?= rawurlencode('CHANGE AUTHORISATION') ?>" style="color:#fcfcfc;" target="_blank">Generate Change Request Form</a><br>
							&gt; <a href="change_request.php?task_id=<?= $job_task_id ?>&title=<?= rawurlencode('SYSTEM UPDATE AUTHORISATION') ?>" style="color:#fcfcfc;" target="_blank">Generate System Update Authorisation Form</a><br>
							<?php
								}
							?>
						</td>
					</tr>
					<tr>
						<td>Reason For Change</td>
						<td>Impact on Page</td>
						<td>Impact on Functionality</td>
						<td>Impact on Site</td>
						<td>Impact on Third Party</td>
						<td>Cost Impact Comments</td>
					</tr>
					<tr valign="top" style="height:150px;">
						<td><textarea class="smallblue" name="cr_reason"><?= htmlspecialchars($job_task['cr_reason']) ?></textarea></td>
						<td><textarea class="smallblue" name="cr_impact_page"><?= htmlspecialchars($job_task['cr_impact_page']) ?></textarea></td>
						<td><textarea class="smallblue" name="cr_impact_functionality"><?= htmlspecialchars($job_task['cr_impact_functionality']) ?></textarea></td>
						<td><textarea class="smallblue" name="cr_impact_site"><?= htmlspecialchars($job_task['cr_impact_site']) ?></textarea></td>
						<td><textarea class="smallblue" name="cr_impact_third_party"><?= htmlspecialchars($job_task['cr_impact_third_party']) ?></textarea></td>
						<td><textarea class="smallblue" name="cr_impact_cost"><?= htmlspecialchars($job_task['cr_impact_cost']) ?></textarea></td>
					</tr>
				</table>
				<input type="submit" value="<?= $job_task_id ? 'Update Details' : 'Create Task' ?>" class="smallbluebutton">
				
			</form>
			
<?php
	if ($job_task_id) {
?>
			
			<?php
				$attachments = CapsApi::issue_attachments($job_task_id);
			?>
			<p class="subheading">Attachments (<?= count($attachments) ?>)</p>
			
			<form method="post" action="?">
				<input type="hidden" name="job_task_id" value="<?= $job_task_id ?>">
				<input type="hidden" name="action" value="attachdelete">
				<input type="hidden" name="attachment" value="" id="attach-delete-id">
			<?php
				$count = 1;
				foreach ($attachments as $attachment) {
					echo '&bull; <a href="' . $attachment['url'] . '" target="_blank"><font color="fcfcfc">';
					echo htmlspecialchars($attachment['file_name']);
					echo ' - ' . date('d-M-Y', strtotime($attachment['file_date']));
					if ($attachment['created_by'])
						echo ' by ' . array_safe($all_staff, $attachment['created_by'], $attachment['created_by']);
					echo '</font></a>';
					echo ' <a href="#" style="color:#fcfcfc;" onclick="return deleteAttachment(' . $attachment['attach_id'] . ')">[delete]</a>';
					echo '<br>' . "\n";
				}
			?>
				<br>
			</form>
			<script>
			function deleteAttachment(id) {
				if (confirm('Are you sure you want to delete this attachment?')) {
					var input = document.getElementById('attach-delete-id');
					input.value = id;
					input.form.submit();
				}
				return false;
			}
			</script>
			
			<form method="post" action="?" enctype="multipart/form-data">
				<input type="hidden" name="job_task_id" value="<?= $job_task_id ?>">
				<input type="hidden" name="action" value="attach">
				<input type="file" name="file" class="black"> <input type="submit" value="Upload Attachment" class="smallbluebutton">
			</form>

			<?php
				$comments = CapsApi::issue_comments_get($job_task_id);
			?>
			<p class="subheading">Comments (<?= $comments->count() ?>)</p>
			<table width="1000" border="0" cellpadding="2" cellspacing="1" class="text admin_list">
			<?php
				$count = 1;
				foreach ($comments as $comment) {
					echo '<tr valign="top" class="' . ($count % 2 == 0 ? 'even' : 'odd') . '">';
					echo '<td width="30" class="text">' . $count++ . '.</td>';
					echo '<td class="text">';
					echo nl2br(htmlspecialchars($comment['body'])) . '</td>';
					echo '<td class="text" width="220" nowrap>';
					echo date('d-M-Y h:ia', strtotime($comment['created_on'])) . ' by ' . array_safe($all_staff, $comment['created_by'], $comment['created_by']) . '<br>';
					echo '</td>';
					echo '</tr>' . "\n";
				}
				if ($count == 1)
					echo '<tr><td>No comments</td></tr>';
			?>
			</table>
			
			<p class="subheading">Add Comment</p>
			<form method="post" action="?">
				<input type="hidden" name="job_task_id" value="<?= $job_task_id ?>">
				<input type="hidden" name="action" value="comment">
				<table width="750" border="0" cellspacing="0" cellpadding="0">
					<tr><td><textarea class="smallblue" name="comment" style="width:99%" rows="4"></textarea>
				</table>
				<input type="submit" value="Add Comment" class="smallbluebutton">
			</form>
			
			<p class="subheading">Work Log</p>
			
			<p>&gt; <a href="job_control_detail.php?job_id=<?= $job_id ?>&start_task=<?= $job_task_id ?>" style="color:#B9E9FF;">Start recording time</a></p>
			
			<?php
				$log = CapsApi::get_work($job_task_id);
				$result = $log->resource();
				$hide_task = true;
				$show_job = true;
				include 'includes/time_log.php';
			?>
			
<?php

	} // end update task

?>
		
		</td>
	</tr>
</table>

</body>
</html>