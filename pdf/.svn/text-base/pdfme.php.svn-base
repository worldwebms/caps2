<?php
/**************************************WORLDWEB MANAGEMENT SERVICES**************************************
*
* report_to_pdf.php template by WorldWeb Management Services ABN: 99 189 634 462
* Created on: 28/8/2003
* Client: CHC Australia-Intranet, Domain: chc intranet
* This template uses the php-pdf class to generate a pdf version of the users report, based upon any
* filters that they have selected
* Important files included in this template are: class.ezpdf.php, pdf class,
* Last Modified: 28/8/2003 By Sam Silvester
* Please remember to backup template before modifying
*
*********************************************************************************************************
*/
session_start();

//assign the result array to variable data
$data = $result_array;
//begin producting the pdf doc
//include the pdf class
include_once('class.ezpdf.php');
//create new instance of the pdf class

//change the orientation of the page if used for daily travel form
if ($pdftitle == 'Travel Claim Report') {
	$pdf =& new Cezpdf($paper='a4',$orientation='landscape');
} else {
	$pdf =& new Cezpdf();
}

// select a font
//$pdf->selectFont('./fonts/Helvetica');

//create the pdf table
$pdf->ezTable($data,'',$pdftitle);

//check for valid output
if ($d){
  $pdfcode = $pdf->output(1);
  $pdfcode = str_replace("\n","\n<br>",htmlspecialchars($pdfcode));
  echo '<html><body>';
  echo trim($pdfcode);
  echo '</body></html>';
  $completed = "yes";
} else {
  $pdf->stream();
}

if ($completed == "yes") {
//unregister the variables
session_unregister("result_array");
session_unregister("pdftitle");
}
?>