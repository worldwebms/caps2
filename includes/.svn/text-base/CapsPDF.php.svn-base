<?php

	define('FPDF_FONTPATH', dirname(__FILE__).'/../pdf/fpdf/font/');

	require_once(dirname(__FILE__).'/../pdf/fpdf/pdf_rotation.php');
	require_once(dirname(__FILE__).'/../pdf/fpdf/fpdi_protection.php');

	/**
	 *	PDF utility wrapper class.
	 *	This class wraps a PDF creation functionality, currently implemented using the
	 *	FPDF library with several chained add-on classes.
	 *
	 *	Current chain:
	 *	FPDF -> FPDF_TPL -> FPDI -> FPDI_Protection
	 */
	class CapsPDF {
		private $pdf;
		
		const PAGE_PORTRAIT = 'P';
		const PAGE_LANDSCAPE = 'L';
		
		const SIZE_A3 = 'A3';
		const SIZE_A4 = 'A4';
		const SIZE_A5 = 'A5';
		const SIZE_LETTER = 'Letter';
		const SIZE_LEGAL = 'Legal';

		private $_orientation = CapsPDF::PAGE_PORTRAIT;
		private $_page_size = CapsPDF::SIZE_A4;
		
		public static $pdf_class = 'FPDI_Protection';
		
	/**
	 *	Create a new PDF document.
	 */
		public function __construct($orientation = CapsPDF::PAGE_PORTRAIT, $page_size = CapsPDF::SIZE_A4)
		{
			error_log('orientation level 1: '.$orientation);
			error_log('size level 1: '.$page_size);

			$class = self::$pdf_class;
			$this->pdf = new $class($orientation, 'mm', $page_size);
			
			// Save it in a private variable so we can also pass it to the addPage method when creating new pages.
			$this->_orientation = $orientation;
			$this->_page_size = $page_size;
		}

		
		/**
		 *	Set the current document to be used for importing page templates.
		 *	This method should be called before import_page_template() or
		 *	get_import_xx() to set a file from which page templates or metadata
		 *	can then be imported.
		 */
		public function set_import_file($filename) {
			return $this->pdf->setSourceFile($filename);
		}
		
	/**
	 * Returns true indicating that the supplied PDF file has an xref
	 * dictionary, false otherwise.
	 *
	 * @param string $file The name of the PDF file.
	 * @return boolean True if the file has an XREF dictionary, false otherwise.
	 */
		static function has_xref_dictionary($file) {
			$filedata = @fopen($file,"rb");
			fseek($filedata, -50, SEEK_END);
		$data = fread($filedata, 50);
		if (!preg_match('/startxref\s*(\d+)\s*%%EOF\s*$/', $data, $matches)) {
			return false;
		}
			
			return true;
		}

	/**
	 *	Import a page template from the import file.
	 *	This method gets a page from the file defined by set_import_file()
	 *	and loads it into the current document to be used as a page template.
	 *	@param integer The page number in the import document to import.
	 *	@return integer The ID allocated to the imported page template.
	 */
		public function import_page_template($page_no)
		{
			return $this->pdf->ImportPage($page_no);
		}

	/**
	 *	Add a page to the this document.
	 *	This method creates a new page in the document and sets it as the
	 *	working page. Note that you must create pages in the document
	 *	sequentially, so only call this method when you are finished working
	 *	on the previous page.
	 */
		public function add_page() {
			//error_log("Add Page Orientation: ".$this->_orientation);
			//return $this->pdf->addPage($this->_orientation);
			return $this->pdf->addPage();
		}

		/**
		 *	Use a page template on the current page.
		 *	This method gets a page template loaded with import_page_template()
		 *	and duplicates it onto the current working page.
		 *	@param integer ID of the template to use.
		 */
		public function use_page_template($tpl_idx, $offset_x=null, $offset_y=null, $page_width=0, $page_height=0) {
			return $this->pdf->useTemplate($tpl_idx, $offset_x, $offset_y, $page_width, $page_height);
		}
		
		
		/**
		 * Returns the size of the supplied template.
		 *
		 * @param integer $tpl_idx A template id returned by import_page_template
		 * @return array An array containing height and width for the page.
		 */
		public function get_page_template_size($tpl_idx) {
			$dims = $this->pdf->getTemplateSize($tpl_idx);
			return array($dims['h'], $dims['w'], 'h' => $dims['h'], 'w' => $dims['w']);
		}

	/**
	 *	Make an educated guess whether this template is portrait or landscape.
	 *	This function uses the fpdf_tpl getTemplateSize() method, which may or may not
	 *	accurately report the page size, or perhaps the size of the objects contained
	 *	in the template - not sure which.
	 *	@param integer Template ID to check.
	 *	@return string 'L' for landscape or 'P' for portrait.
	 */
		public function get_page_template_orientation($tpl_idx) {
			list($height, $width) = $this->get_page_template_size($tpl_idx);
			if ($height > $width) {
				return CapsPDF::PAGE_PORTRAIT;
			} else {
				return CapsPDF::PAGE_LANDSCAPE;
			}
		}
		
		

	/**
	 *	Get the number of pages contained in this document so far.
	 *	@return integer The page count.
	 */
		public function get_page_count()
		{
			return $this->pdf->PageNo();
		}

	/**
	 *	Get the PDF version of this document.
	 *	@return string The PDF version.
	 */
		public function get_pdf_version()
		{
			return $this->pdf->current_parser->pdfVersion;
		}

	/**
	 *	Set the author meta-data.
	 *	@param string The author.
	 */
		public function set_author($author)
		{
			return $this->pdf->SetAuthor($author);
		}

	/**
	 *	Set the keywords meta-data.
	 *	@param string The keywords.
	 */
		public function set_keywords($keywords)
		{
			return $this->pdf->SetKeywords($keywords);
		}

	/**
	 *	Set the title meta-data.
	 *	@param string The title.
	 */
		public function set_title($title)
		{
			return $this->pdf->SetTitle($title);
		}

	/**
	 *	Set the creator meta-data.
	 *	@param string The creator.
	 */
		public function set_creator($creator)
		{
			return $this->pdf->SetCreator($creator);
		}

	/**
	 *	Set the subject meta-data.
	 *	@param string The subject.
	 */
		public function set_subject($subject)
		{
			return $this->pdf->SetSubject($subject);
		}

	/**
	 *	Set the current font and font settings to be used.
	 *	You can use either an in-built font identifier(Arial, Courier, Times,
	 *	Symbol, ZapfDingbats, or a name defined by add_font().
	 *	@param string Font family.
	 *	@param string Font style(any combination of B I and U).
	 *	@param integer Size in points.
	 */
		public function set_font($family, $style = '', $size = 0)
		{
			return $this->pdf->SetFont($family, $style, $size);
		}
		
	/**
	 *	Set the current protection level for the document.
	 * 	@param array $permissions permissions is an array with values taken from the following list:
	 *		copy, print, modify, annot-forms
	 *		If a value is present it means that the permission is granted
	 *	@param string $user_pass If a user password is set, user will be prompted before document is opened
	 *	@param string $owner_pass If an owner password is set, document can be opened in privilege mode with no
	 *		restriction if that password is entered
	 */
		public function set_protection($permissions,$user_pass='',$owner_pass=null)
		{
			return $this->pdf->SetProtection($permissions, $user_pass, $owner_pass);
		}

	/**
	 * Returns the internal FPDF class.
	 * @return FPDI_Protection The internal FPDF instance.
	 */
		public function get_fpdf()
		{
			return $this->pdf;
		}
		
	/**
	 *	Output the PDF document to a file.
	 *	@param string Filename to create or overwrite with the PDF document.
	 */
		public function output_file($filename)
		{
			error_log('outputing file');
			return $this->pdf->output($filename, 'F');
		}

	/**
	 *	Output the PDF document to the screen.
	 */
		public function output($filename='')
		{
			return $this->pdf->output($filename);
		}
		
		/**
		 * Returns the PDF content as a string.  Useful for sending as an attachment
		 * without creating a temporary file.
		 *
		 * @return string A string containing PDF data.
		 */
		public function output_string() {
			return $this->pdf->output('', 'S');
		}

	/**
	 *	Get all the text content from the specified template ID.
	 *	@param integer ID of the template(returned from import_page_template
	 *	@return string Concatenated text from the given template.
	 *	@throws if the template ID specified is not valid.
	 */
		public function get_template_text($tplidx = 1)
		{
			if (!array_key_exists($tplidx, $this->pdf->tpls))
				throw new Exception("Page template with ID '{$tplidx}' does not exist!");

			$buffer = $this->pdf->tpls[$tplidx]['buffer'];

			$buffer = str_replace(")Tj", ")Tj", $buffer);
			$buffer = str_replace(")TJ", ")TJ", $buffer);
			$buffer = str_replace(")]Tj", ")]Tj", $buffer);
			$buffer = str_replace(")]TJ", ")]TJ", $buffer);

			$lines = array();

			$bits = explode("\nET\nEMC\n", $buffer);

			foreach ($bits as $bit)
			{
				$line = '';
				$matches = array();
				preg_match_all("/\\((.*[^\\\\])\\)/U", $bit, $matches);

				foreach ($matches[1] as $match)
				{
					$line .= $match;
				}

				if (strlen(trim($line)) > 0)
					$lines[] = $line;
			}

			$content = implode("\n", $lines);

			// Replace all /2xx ascii specifiers
			preg_match_all("/\\\\(2[0-9]{2})/", $content, $matches);

			foreach ($matches[1] as $match)
			{
				$content = str_replace("\\{$match}", chr(intval($match, 8)), $content);
			}

			$rep = array(
				"\\(" => "(",
				"\\)" => ")",
				"\\\\" => "\\"
			);

			$content = str_replace(array_keys($rep), array_values($rep), $content);

			return $content;
		}
		
	/**
	 * 	Adds the font to the pdf and generates the required files if necessary.
	 *	@param
	 *	of generated files.
	 */
		public function add_font($family, $style, $fontfile = false, $afmfile = false) {
			
			// Determine the filename to use
			$filename = strtolower(preg_replace('/[^a-z0-9]/i', '', $family) . $style);
			
			// If there is no font file, then generate one
			if (!file_exists(FPDF_FONTPATH . $filename . '.php')) {
				
				// If there are components missing
				if ($fontfile === false || $afmfile === false || $fontfile !== false && !file_exists($fontfile) || $afmfile !== false && !file_exists($afmfile))
					throw new Exception('Missing font or afm file when adding font family: ' . $family . ' with style ' . $style);
					
				// Make the font file dynamically
				require_once FPDF_FONTPATH . 'makefont/makefont.php';
				
				// Make a copy of the font files in a temporary directory
				$dirname = tempnam('/tmp', 'caps-font-');
				if (file_exists($dirname))
					unlink($dirname);
				mkdir($dirname);
				$fonttemp = $dirname . '/' . $filename . '.ttf';
				$afmtemp = $dirname . '/' . $filename . '.afm';
				copy($fontfile, $fonttemp);
				copy($afmfile, $afmtemp);
				
				// Generate the files
				$dir = getcwd();
				chdir(FPDF_FONTPATH);
				ob_start();
				MakeFont($fonttemp, $afmtemp);
				ob_end_clean();
				chdir($dir);
				
				// Clean up the files
				unlink($fonttemp);
				unlink($afmtemp);
				rmdir($dirname);
				
			}
			
			// Add the font to the PDF
			$this->pdf->AddFont($family, $style, $filename . '.php');
			
		}
		
	}
