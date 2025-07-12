<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* job_time_log template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 8/8/2003
* Client: WorldWeb, Domain: database intranet
* This template consists of 2 section: sql time log query and HTML output with a form directed to itself
* Important files included in this template are: header.inc (head section and javascript library),
* globals.php (connection string),
* Last Modified: 12/1/2004 By Aviv Efrat
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
require_once 'includes/CapsApi.php';
//establish a persistent connection and get all required data.
// select valuations for  valuer
$db = mysql_pconnect ($hostName, $userName, $password);
mysql_select_db($database);

ini_set('display_errors', '1');

// PDF generation class
class JobTaskReportPDF {
	
	const TEXT_COLOUR = '#000000';

	const HEAD_COLOUR = '#366092';

	const SUB_HEAD_COLOUR = '#366092';
	
	const LINE_COLOUR = '#cccccc';

	const SIZE_H1 = 16;
	const SIZE_H2 = 11;
	const SIZE_TEXT = 8;

	const FONT_FACE = 'opensans';
	const FONT_NORMAL = '';
	const FONT_BOLD = 'b';
	const FONT_ITALIC = 'i';
	const FONT_UNDERLINE = 'u';
	const FONT_FACE_HEADING = 'lato';

	const ALIGN_LEFT = 'L';
	const ALIGN_CENTER = 'C';
	const ALIGN_RIGHT = 'R';
	const ALIGN_JUSTIFY = 'J';
	
	const LINE_SPACING = 5;

	// Establish a top left hand corner for the text.  All other measurements
	// are relative to this.
	const MARGIN_LEFT = 14;
	const MARGIN_TOP = 15;
	const MARGIN_TOP_HEADER = 30;

	const WIDTH = 182;
	
	const LABEL_LEFT = 14;
	const LABEL_WIDTH = 50;
	
	const FIELD_LEFT = 65;
	const FIELD_WIDTH = 127;
	const FIELD_SPACING = 2;
	
	const CLIENT_LEFT = 14;
	const CLIENT_WIDTH = 49;
	
	const TASK_LEFT = 65;
	const TASK_WIDTH = 85;
	
	const ASSIGN_LEFT = 151;
	const ASSIGN_WIDTH = 15;
	
	const PHASE_LEFT = 166;
	const PHASE_WIDTH = 14;
	
	const DATE_LEFT = 180;
	const DATE_WIDTH = 15;
	
	const CELL_SPACING = 1;
	
	private $_conn;
	private $_fpdf;
	
	public function generate($sql) {
		
		// Create template
		$capspdf = new CapsPDF();
		$capspdf->add_font('lato', '', dirname(__FILE__) . '/pdf/fonts/Lato.ttf', dirname(__FILE__) . '/pdf/fonts/Lato.afm');
		$capspdf->add_font('opensans', '', dirname(__FILE__) . '/pdf/fonts/OpenSans.ttf', dirname(__FILE__) . '/pdf/fonts/OpenSans.afm');
		$capspdf->set_import_file(dirname(__FILE__).'/pdf/letterhead.pdf');
		$tpl = $capspdf->import_page_template(1);
		$capspdf->add_page();
		$capspdf->use_page_template($tpl);
		$this->_fpdf = $capspdf->get_fpdf();
		
		// Add header
		$heading = 'Task Summary - ' . date('F j, Y');
		$top = self::MARGIN_TOP_HEADER;
		list($top, $left) = $this->_text_at($top, self::MARGIN_LEFT, self::WIDTH, $heading, self::SIZE_H1);
		$top += self::LINE_SPACING;
		
		// Add the header
		$top = $this->_task_header($top);
		
		// Add each customer
		$prev_client = '';
		$client_suffix = '';
		$prev_job = '';
		$results = mysql_query($sql);
		$page = 1;
		while ($row = mysql_fetch_object($results)) {
			
			// If the client has changed then start new row
			$new_top = $top;
			if ($row->client_name != $prev_client || $client_suffix != '') {
				$top = $this->_line_at($new_top, self::CLIENT_LEFT - self::CELL_SPACING);
				list($new_top) = $this->_text_at($top, self::CLIENT_LEFT, self::CLIENT_WIDTH, $row->client_name . ($row->client_name == $prev_client ? $client_suffix : ''));
				$new_top += self::CELL_SPACING;
				$client_suffix = '';
				
			// If client stays same add sub line
			} else {
				$top = $this->_line_at($top, self::TASK_LEFT - self::CELL_SPACING) + self::CELL_SPACING;
				
			}
			
			// Add the task
			$new_top = max($new_top, $this->_task_at(
				$top,
				$row->description . ($row->job_task_number ? (' (Ref #' . $row->job_task_number . ')') : ''),
				ucwords($row->employee),
				$row->status,
				$row->due_date ? date('d M y', strtotime($row->due_date)) : ''
			));
			
			// Remember previous
			$prev_client = $row->client_name;
			$top = $new_top;
			
			// Start new page if not enough room
			if ($new_top > 260) {
				$page++;
				$capspdf->add_page();
				$this->_text_at(self::MARGIN_TOP - 10, self::MARGIN_LEFT, self::WIDTH, 'WorldWeb Management Services Pty Ltd', self::SIZE_TEXT, self::HEAD_COLOUR);
				$this->_text_at(self::MARGIN_TOP - 10, self::MARGIN_LEFT, self::WIDTH, $heading . ' - page ' . $page, self::SIZE_TEXT, self::HEAD_COLOUR, self::FONT_NORMAL, self::ALIGN_RIGHT);
				$top = $this->_task_header(self::MARGIN_TOP);
				$client_suffix = ' (cont.)';
			}
		
		}
		
		$this->_line_at($top);
		
		return $capspdf;
	}
	
	public function download() {
		$this->_fpdf->output(date('Y-m-d') . '.pdf', 'D');
	}
	
	protected function _task_header($top) {
		list($new_top) = $this->_text_at($top, self::CLIENT_LEFT, self::CLIENT_WIDTH, 'Client', self::SIZE_TEXT, self::HEAD_COLOUR);
		list($new_top) = $this->_text_at($top, self::TASK_LEFT, self::TASK_WIDTH, 'Task', self::SIZE_TEXT, self::HEAD_COLOUR);
		list($new_top) = $this->_text_at($top, self::ASSIGN_LEFT, self::ASSIGN_WIDTH, 'Assigned To', self::SIZE_TEXT, self::HEAD_COLOUR);
		list($new_top) = $this->_text_at($top, self::PHASE_LEFT, self::PHASE_WIDTH, 'Phase', self::SIZE_TEXT, self::HEAD_COLOUR, self::FONT_NORMAL, self::ALIGN_CENTER);
		list($new_top) = $this->_text_at($top, self::DATE_LEFT, self::DATE_WIDTH, 'Due Date', self::SIZE_TEXT, self::HEAD_COLOUR, self::FONT_NORMAL, self::ALIGN_CENTER);
		return $new_top;
	}
	
	protected function _task_at($top, $task, $employee, $phase, $due_date) {
		list($new_top_1, $new_left) = $this->_text_at($top, self::TASK_LEFT, self::TASK_WIDTH, $task);
		list($new_top_2, $new_left) = $this->_text_at($top, self::ASSIGN_LEFT, self::ASSIGN_WIDTH, $employee, self::SIZE_TEXT, self::TEXT_COLOUR, self::FONT_NORMAL, self::ALIGN_CENTER);
		list($new_top_3, $new_left) = $this->_text_at($top, self::PHASE_LEFT, self::PHASE_WIDTH, $phase, self::SIZE_TEXT, self::TEXT_COLOUR, self::FONT_NORMAL, self::ALIGN_CENTER);
		list($new_top_4, $new_left) = $this->_text_at($top, self::DATE_LEFT, self::DATE_WIDTH, $due_date, self::SIZE_TEXT, self::TEXT_COLOUR, self::FONT_NORMAL, self::ALIGN_CENTER);
		return max($new_top_1, max($new_top_2, max($new_top_3, $new_top_4))) + self::CELL_SPACING;
	}
	
	protected function _line_at($top, $left = self::MARGIN_LEFT) {
		$c = colour_hex_to_rgb(self::LINE_COLOUR);
		$this->_fpdf->SetDrawColor($c['r'], $c['g'], $c['b']);
		$this->_fpdf->Line($left, $top, self::MARGIN_LEFT + self::WIDTH + self::CELL_SPACING, $top);
		$top += self::CELL_SPACING;
		return $top;
	}

	protected function _field_at($top, $label, $text) {
		list($new_top_1, $new_left) = $this->_text_at($top, self::LABEL_LEFT, self::LABEL_WIDTH, $label);
		list($new_top_2, $new_left) = $this->_text_at($top, self::FIELD_LEFT, self::FIELD_WIDTH, $text);
		return max($new_top_1, $new_top_2) + self::FIELD_SPACING;
	}
	
	protected function _text_at($top, $left, $width, $text, $font_size=self::SIZE_TEXT, $colour=self::TEXT_COLOUR, $style=self::FONT_NORMAL, $align=self::ALIGN_LEFT, $spacing=self::LINE_SPACING) {
		$this->_fpdf->SetXY($left, $top);
		$this->_fpdf->SetFont(self::FONT_FACE, $style, $font_size);
		$c = colour_hex_to_rgb($colour);

		$this->_fpdf->SetTextColor($c['r'], $c['g'], $c['b']);

		if ($width === false) {
			$width = $this->_fpdf->GetStringWidth($text);
		}

		$this->_fpdf->MultiCell($width+5, $spacing, $text, 0, $align);
		return array($this->_fpdf->getY(), $left+$width);
	}
	
}

// Get request details
$employee = array_safe($_REQUEST, 'employee', '');
$order_by = array_safe($_REQUEST, 'order', '');
$status = array_safe($_REQUEST, 'status', '');
$state = array_safe($_REQUEST, 'state', '');
$filter_by = array_safe($_REQUEST, 'filter', array());
$generate_pdf = array_safe($_REQUEST, 'pdf', false);
if ($generate_pdf)
	$order_by = '';
$date_start = array_safe($_REQUEST, 'date_start', '');
$date_start = $date_start ? date('Y-m-d', strtotime($date_start)) : '';

$order_by_clause = '';
switch ($order_by) {
	case 'due':
		$order_by_clause = 'jt.due_date ASC, ';
		break;
	case 'created':
		$order_by_clause = 'jt.created_on ASC, ';
		break;
	case 'priority':
		$order_by_clause = 'priority DESC, ';
		break;
}

// Generate SQL to get tasks
$sql =
	"SELECT jt.job_task_id, jt.job_task_number, jt.description, jt.cr_description, jt.employee, jt.created_on, jt.completed, jt.due_date, jt.status, jt.priority, j.job_id, j.job_number, j.job_title, c.client_id, c.client_name " .
		"FROM job_tasks AS jt " .
		"INNER JOIN jobs AS j ON j.job_id=jt.job_id " .
		"INNER JOIN clients AS c ON c.client_id=j.client_id " .
		"WHERE jt.deleted_on IS NULL " .
			($employee ? (' AND jt.employee="' . mysql_escape_string($employee) . '" ') : '') .
			($status ? (' AND jt.status="' . mysql_escape_string($status) . '" ') : '') .
			(in_array('duedate', $filter_by) ? ' AND jt.due_date IS NOT NULL ' : '') .
			($date_start ? (' AND jt.created_on>="' . mysql_escape_string($date_start) . '" ') : '') .
			($state == 'any' ? '' : ($state == 'completed' ? ' AND jt.completed IS NOT NULL ' : ' AND jt.completed IS NULL ')) .
		"ORDER BY " . ($generate_pdf ? ('c.client_name, jt.due_date ASC, jt.priority DESC, jt.description') : ($order_by_clause . "c.client_name, j.job_number, jt.priority DESC, jt.description"));

// Generate the PDF
if ($generate_pdf) {
	require dirname(__FILE__) . '/includes/CapsPDF.php';
	$taskpdf = new JobTaskReportPDF();
	$taskpdf->generate($sql);
	$taskpdf->download();
	mysql_close($db);
	exit();
}

?><html>
<head>
<title>CAPS | WorldWeb Management Services</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="includes/caps_styles.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="includes/time.js"></script>
<?php
//include javascript library
include_once("includes/worldweb.js");
?>
<style>
.completed td, .completed td a { color: #999 !important; }
.admin_list tr.completed td.status { background-color: #0070a6; }
.admin_list .missing { float: right; background-color: #cc9900; width: 1.2em; text-align: center; border-radius: .6em; color: #fff !important; }
</style>
</head>

<body bgcolor="#006699" leftmargin="0" topmargin="0">
<?php
include_once("includes/top.php");
include_once("includes/DateField.php");
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">

	<tr><td width="1%" height="27"><br></td><td class="clienttitle">All Open Tasks</td></tr>
	<tr><td colspan="2" background="images/horizontal_line.gif"><br></td></tr>
	<tr><td><br></td><td>

		<p class="subheading">Search Criteria</p>
		<form method="get" action="job_task_report.php">
			<table cellspacing="0" cellpadding="2" border="0"><tbody><tr class="text">
				<td>Employee:</td><td><select name="employee" class="black">
					<option value="">- all -</option>
<?php
	$results = mysql_query('SELECT u FROM ps ORDER BY u');
	while ($row = mysql_fetch_object($results))
		echo '<option value="' . $row->u . '"' . ($row->u == $employee ? ' selected' : '') . '>' . $row->u . '</option>';
?>
				</select></td>
				<td> </td>
				<td>Phase:</td><td><select name="status" class="black">
					<option value=""<?= $status == '' ? ' selected' : '' ?>>- all -</option>
<?php
	$results = mysql_query('SELECT DISTINCT status FROM job_tasks ORDER BY status');
	while ($row = mysql_fetch_object($results)) {
		if ($row->status)
			echo '<option value="' . $row->status . '"' . ($row->status == $status ? ' selected' : '') . '>' . $row->status . '</option>';
	}
?>
				</select></td>
				<td> </td>
				<td>Status:</td><td><select name="state" class="black">
					<option value="">Open</option>
					<option value="completed"<?= $state == 'completed' ? ' selected' : ''?>>Completed</option>
					<option value="any"<?= $state == 'any' ? ' selected' : ''?>>Any</option>
				</select></td>
				<td> </td>
				<td>Order by:</td><td><select name="order" class="black">
					<option value="">Alphabetic</option>
					<option value="priority"<?= $order_by == 'priority' ? ' priority' : ''?>>Priority</option>
					<option value="created"<?= $order_by == 'created' ? ' selected' : ''?>>Created On</option>
					<option value="due"<?= $order_by == 'due' ? ' selected' : ''?>>Due Date</option>
				</select></td>
				<td> </td>
				<td>Created since:</td>
				<td>
					<? $field = new DateField('date_start', $date_start); echo $field->getHTML(); ?>
				</td>
				<td> </td>
				<td>Must Have:</td><td><label><input type="checkbox" name="filter[]" value="duedate"<?= in_array('duedate', $filter_by) ? ' checked' : '' ?>>Due Date</label></td>
				<td> </td>
				<td><input class="smallbluebutton" type="submit" value="Search"></td>
				<td> &nbsp; &nbsp; </td>
				<td><input class="smallbluebutton" name="pdf" type="submit" value="Generate PDF"></td>
			</tr></tbody></table>
<?php

?>
		</form>

		<p class="subheading">Open Tasks</p>
		
		<table class="admin_list text" border="0" cellspacing="1" cellpadding="2">
			<thead>
				<tr>
					<td width="65">Job Number</td>
					<td>&nbsp;</td>
					<td>Job Title &amp; Tasks</td>
					<td>Client</td>
					<td></td>
					<td width="65" align="center">Phase</td>
					<td width="65" align="center">Assigned&nbsp;To</td>
					<td width="65" align="center">Created&nbsp;On</td>
					<td width="65" align="center">Due&nbsp;Date</td>
					</tr>
			</thead>
			<tbody>
<?php

	$job_number = '';
	$results = mysql_query($sql);
	while ($row = mysql_fetch_object($results)) {
		if ($job_number != $row->job_number) {
?>
				<tr class="odd heading">
					<td><a href="job_control_detail.php?job_id=<?= $row->job_id ?>" style="color:#ffffff"><?= $row->job_number ?></a></td>
					<td></td>
					<td><a href="job_control_detail.php?job_id=<?= $row->job_id ?>" style="color:#ffffff"><?= htmlspecialchars($row->job_title) ?></a></td>
					<td><?= htmlspecialchars($row->client_name) ?></td>
					<td><br></td>
					<td><br></td>
					<td><br></td>
					<td><br></td>
					<td><br></td>
				</tr>
<?php
		}
?>
				<tr class="odd<?= $row->completed ? ' completed' : '' ?>">
					<td>
						<?= $row->job_task_number ? ('&nbsp;&nbsp;&gt;&nbsp;' . htmlspecialchars($row->job_task_number)) : '<br>' ?>
					</td>
					<td><?= CapsApi::get_issue_priority_label($row->priority) ?></td>
					<td colspan="2"><a href="job_task_detail.php?job_task_id=<?= $row->job_task_id ?>" style="color:#ffffff">&gt; <?= htmlspecialchars($row->description) ?></a></td>
					<td><?= $row->cr_description == '' ? '<span class="missing" title="Missing job description">!</span>' : '' ?></td>
					<td align="center" class="status <?= strtolower($row->status) ?>"><?= htmlspecialchars($row->status) ?></td>
					<td align="center"><?= $row->employee ? $row->employee : '<em style="color:#999999">n/a</em>' ?></td>
					<td align="center"><?= $row->created_on ? date('d-M-Y', strtotime($row->created_on)) : '' ?></td>
					<td align="center"><?= $row->due_date ? date('d-M-Y', strtotime($row->due_date)) : '' ?></td>
					</tr>
<?php
		$job_number = $row->job_number;
	}
	
?>
			</tbody>
		</table>
	
	</td></tr>

</table>

<?php
	include 'footer.php';
	mysql_close($db);
?>
</body>
</html>