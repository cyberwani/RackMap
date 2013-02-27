<?php
//============================================================+
// File name   : tce_pdf_data.php
// Begin       : 2011-12-13
// Last Update : 2011-12-20
//
// Description : Functions to export RacmMap data to PDF document.
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Fubra Limited
//               Manor Coach House
//               Church Hill
//               Aldershot
//               Hampshire
//               GU12 4RQ
//               http://www.rackmap.net
//               support@rackmap.net
//
// License:
//    Copyright (C) 2011-2011 Fubra Limited
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
//    See LICENSE.TXT file for more information.
//============================================================+

/**
 * @file
 * Functions to export RacmMap data to PDF document.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-12-13
 */

/**
 * Create a PDF ducument containing selected Rackmap data.
 * @param $data (array) Array of data to export.
 * @param $filter (array) Array containing data filtering info.
 * @return (string) PDF data.
 */
function getDataPDF($data, $filter=array()) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/config/tce_pdf.php');
	require_once('tce_rmtcpdf.php');
	
	// create new PDF document
	$pdf = new RMTCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor(PDF_AUTHOR);
	$pdf->SetTitle('RackMap Data Report');
	$pdf->SetSubject('RackMap Data Report');
	$pdf->SetKeywords('RackMap, report, datacenter, suite, rack, asset');

	// set default header data
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

	// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	//set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	//set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	//set some language-dependent strings
	$pdf->setLanguageArray($l);

	// ---------------------------------------------------------

	// set default font subsetting mode
	$pdf->setFontSubsetting(true);

	// Set font
	$pdf->SetFont('helvetica', '', 10, '', true);
	
	// print cover page and data filtering information
	$pdf->printCoverPage($data['filter']);

	// print data
	$pdf->printData($data['asset']);
	
	// print user groups
	$pdf->printGroups($data['groups']);

	// add a new page for TOC
	$pdf->addTOCPage();

	// write the TOC title
	$pdf->SetFont('times', 'B', 18);
	$pdf->MultiCell(0, 0, $l['t_index'], 0, 'C', 0, 1, '', '', true, 0);
	$pdf->Ln();

	$pdf->SetFont('helvetica', '', 10);

	// add a simple Table Of Content at first page
	// (check the example n. 59 for the HTML version)
	$pdf->addTOC(2, 'courier', '.', 'INDEX', 'B', array(128,0,0));

	// end of TOC page
	$pdf->endTOCPage();


	// ---------------------------------------------------------

	// Close and return PDF document data
	return $pdf->Output('doc.pdf', 'S');
	
}

//============================================================+
// END OF FILE
//============================================================+
