<?php
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
//include pdf library
include_once("includes/CapsPDF.php");


//establish a persistent connection and get all required data.
// select valuations for  valuer
$db = mysql_pconnect ($hostName, $userName, $password);
mysql_select_db($database);

$taskpdf = new CapsTask($db);
$taskpdf->generate(array_safe($_REQUEST,'task_id'),array_safe($_REQUEST,'title', 'CHANGE AUTHORISATION'));

//close mysql connection
mysql_close($db);

class CapsTask {
	
	const TEXT_COLOUR = '#000000';

	const HEAD_COLOUR = '#366092';

	const SUB_HEAD_COLOUR = '#366092';

	const SIZE_H1 = 16;
	const SIZE_H2 = 11;
	const SIZE_TEXT = 10;

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

	// Establish a top left hand corner for the text.  All other measurements
	// are relative to this.
	const MARGIN_LEFT = 14;
	const MARGIN_TOP = 30;

	const WIDTH = 181;
	
	const LABEL_LEFT = 14;
	const LABEL_WIDTH = 50;
	
	const FIELD_LEFT = 70;
	const FIELD_WIDTH = 121;
	const FIELD_SPACING = 2;

	const LINE_SPACING = 5;

	const GAP_HEADING = 17;   // Gap between main heading and body.
	const GAP_BODY = 5;		// Gab between paragraphs in the body.

	function __construct() {}


	public function generate($task_id,$title) {

		// Get the task details
		$results = mysql_query("SELECT * FROM job_tasks WHERE job_task_id=" . $task_id);
		$task = mysql_fetch_object($results);

		$jresults = mysql_query("SELECT * FROM jobs WHERE job_id=" . $task->job_id);
		$job = mysql_fetch_object($jresults);

		$cresults = mysql_query("SELECT * FROM clients WHERE client_id=" . $job->client_id);
		$client = mysql_fetch_object($cresults);

		$found = ($task !== false);

		if ($found) {
			// Create object and import the template.
			$capspdf = new CapsPDF();
			$capspdf->add_font('lato', '', dirname(__FILE__) . '/pdf/fonts/Lato.ttf', dirname(__FILE__) . '/pdf/fonts/Lato.afm');
			$capspdf->add_font('opensans', '', dirname(__FILE__) . '/pdf/fonts/OpenSans.ttf', dirname(__FILE__) . '/pdf/fonts/OpenSans.afm');
			$capspdf->set_import_file(dirname(__FILE__).'/pdf/letterhead.pdf');
			$tpl = $capspdf->import_page_template(1);
			$capspdf->add_page();
			$capspdf->use_page_template($tpl);
			$pdf = $capspdf->get_fpdf();
			
			// Now lets start building the PDF itself.
			$top = self::MARGIN_TOP + self::GAP_HEADING;

			$top = 34;
			$this->_text_at($pdf, $top, self::MARGIN_LEFT, self::WIDTH, '#'.$task->job_task_number, self::SIZE_H1, self::TEXT_COLOUR, self::FONT_NORMAL, self::ALIGN_LEFT);
			$this->_text_at($pdf, $top, self::MARGIN_LEFT, self::WIDTH, $title, self::SIZE_H1, self::TEXT_COLOUR, self::FONT_NORMAL, self::ALIGN_RIGHT);

			$top = 50;
			$top = $this->_field_at($pdf, $top, 'Client Name', $client->client_name);
			$top = $this->_field_at($pdf, $top, 'Requestor', $task->cr_requester);
			$top = $this->_field_at($pdf, $top, 'Task Name', $task->description);
			if ($task->cr_page_affected)
				$top = $this->_field_at($pdf, $top, 'Page / Module Affected', $task->cr_page_affected, self::ALIGN_LEFT);
			$top = $this->_field_at($pdf, $top, 'Date of Request', date('F d, Y',strtotime($task->created_on)));
			$top = $this->_field_at($pdf, $top, 'Description of Request', $task->cr_description);
			
			$top = $this->_line_at($pdf, $top);
			
			$top = $this->_section_at($pdf, $top, 'Reason for Change', $task->cr_reason);
			$top = $this->_section_at($pdf, $top, 'Impact on Page', $task->cr_impact_page);
			$top = $this->_section_at($pdf, $top, 'Impact on Functionality', $task->cr_impact_functionality);
			$top = $this->_section_at($pdf, $top, 'Impact on Site', $task->cr_impact_site);
			$top = $this->_section_at($pdf, $top, 'Impact on Third Party software Integrated', $task->cr_impact_third_party);
			$top = $this->_section_at($pdf, $top, 'Cost Impact Comments', $task->cr_impact_cost);

			$total_time = ($task->quote / 60);
			$this->_text_at($pdf, $top, self::MARGIN_LEFT, self::WIDTH, 'Total Time: '.$total_time . (($total_time > 1) ?' hours':' hour'));

			$pdf_data = $capspdf->output();

		} else {
			// Product not found
			return false;
		}

		return $pdf_data;
	}

	public function check_end_of_page($top){

	}


	public function get_file_size($task_id) {
		return strlen($this->generate($task_id));
	}
	
	protected function _field_at($pdf, $top, $label, $text, $align = self::ALIGN_JUSTIFY) {
		list($new_top_1, $new_left) = $this->_text_at($pdf, $top, self::LABEL_LEFT, self::LABEL_WIDTH, $label, self::SIZE_TEXT, self::HEAD_COLOUR, self::FONT_NORMAL, self::ALIGN_LEFT);
		list($new_top_2, $new_left) = $this->_text_at($pdf, $top, self::FIELD_LEFT, self::FIELD_WIDTH, $text, self::SIZE_TEXT, self::TEXT_COLOUR, self::FONT_NORMAL, $align);
		return max($new_top_1, $new_top_2) + self::FIELD_SPACING;
	}
	
	protected function _line_at($pdf, $top) {
		$top += self::LINE_SPACING;
		$pdf->Line(self::MARGIN_LEFT, $top, self::MARGIN_LEFT+self::WIDTH, $top);
		$top += self::LINE_SPACING;
		return $top;
	}

	protected function _section_at($pdf, $top, $label, $text) {
		
		// Add heading
		list($new_top, $new_left) = $this->_text_at($pdf, $top, self::MARGIN_LEFT, self::WIDTH, $label, self::SIZE_H2, self::HEAD_COLOUR);
		$new_top += 2;
		
		// Add body
		list($new_top, $new_left) = $this->_text_at($pdf, $new_top, self::MARGIN_LEFT, self::WIDTH, $text);
		$new_top += self::GAP_BODY;
		
		// Start new page if not enough room
		if ($new_top > 260) {
			$capspdf->add_page();
			$pdf = $capspdf->get_fpdf();
			$top = self::MARGIN_TOP;
		}
		
		return $new_top;
	}

	protected function _text_at($pdf, $top, $left, $width, $text, $font_size=self::SIZE_TEXT, $colour=self::TEXT_COLOUR, $style=self::FONT_NORMAL, $align=self::ALIGN_JUSTIFY, $spacing=self::LINE_SPACING) {
		$pdf->SetXY($left, $top);
		$pdf->SetFont(self::FONT_FACE, $style, $font_size);
		$c = colour_hex_to_rgb($colour);

		$pdf->SetTextColor($c['r'], $c['g'], $c['b']);

		if ($width === false) {
			$width = $pdf->GetStringWidth($text);
		}

		$pdf->MultiCell($width+5, $spacing, $text, 0, $align);
		return array($pdf->getY(), $left+$width);


	}


}