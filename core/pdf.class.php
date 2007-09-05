<?php
/**
* PDF class
*
* Generate PDF for pdfview
*
* @copyright	CONTREXX CMS - COMVATION AG
* @author		Comvation Development Team <info@comvation.com>
* @access		public
* @module		settings
* @modulegroup	core
* @version		1.1.0
*/

require_once ASCMS_LIBRARY_PATH.'/html2fpdf/html2fpdf.php';

class PDF extends HTML2FPDF
{
	/**
    * string $content
    * Content for insert
    */
	var $content;
	
	/**
    * string $orientation 
    * pageorientation
    */
	var $pdf_orientation;
	
	/**
    * string $unit
    * Unit-format 
    */
	var $pdf_unit;
	
	/**
    * string $format
    * Page-format
    */
	var $pdf_format;
	
	/**
    * string $pdf_creator
    * PDF author
    */
	var $pdf_autor;
	
	function PDF()
	{
		$this->pdf_orientation 	= 'P';
		$this->pdf_unit 		= 'mm';
		$this->pdf_format 		= 'A4';
		$this->pdf_autor		= 'Contrexx - Web Content Management System (WCMS)';
	}
	
	function Create()
	{

		$this->content = utf8_decode($this->_ParseHTML($this->content));
		
		$pdf = new HTML2FPDF();
		
		$pdf->DisplayPreferences('HideWindowUI');
		$pdf->AddPage();
		$pdf->WriteHTML($this->content);
		$pdf->Output();
		
	}
	
	function _ParseHTML($source){
		
		// H1
		// ----------------
		$source = str_replace('<h1>', '<div class="h1">', $source);
		$source = str_replace('</h1>', '</div>', $source);
		// H2
		// ----------------
		$source = str_replace('<h2>', '<div class="h2">', $source);
		$source = str_replace('</h2>', '</div>', $source);
		// H3
		// ----------------
		$source = str_replace('<h3>', '<div class="h3">', $source);
		$source = str_replace('</h3>', '</div>', $source);
		// H4
		// ----------------
		$source = str_replace('<h3>', '<div class="h3">', $source);
		$source = str_replace('</h3>', '</div>', $source);
		
		// body
		// ----------------
		$source = str_replace('<body>', '<body><div class="body">', $source);
		$source = str_replace('</body>', '</div></body>', $source);
		
		// p
		// ----------------
		$source = str_replace('<p>', '<div class="p">', $source);
		$source = str_replace('</p>', '</div>', $source);
		
		// image to relative path
		// ----------------
		$source = str_replace('src="/images', 'src="images', $source);
		$source = str_replace("src='/images", "src='images", $source);
		
		return $source;
	}
} 

?>