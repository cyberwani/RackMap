<?php
//============================================================+
// File name   : tce_select_manuf_popup.php
// Begin       : 2009-05-18
// Last Update : 2011-11-17
//
// Description : Select Manufacturers
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com
//               www.tecnick.com
//               info@tecnick.com
//
// License:
//    Copyright (C) 2004-2013 Nicola Asuni - Tecnick.com
//    Tecnick.com has granted the right for this file to be used for free only as a part of the RackMap software.
//    The code contained in this file can not be used for other purposes without explicit permission from Tecnick.com
//============================================================+

/**
 * Select Manufacturers
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2009-05-18
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_OBJECTS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_select_manufacturer'];
$enable_calendar = true;
require_once('../code/tce_page_header_popup.php');

require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_objects.php');

// set default values
$order_field='mnf_name';
if(!isset($orderdir)) {$orderdir=0;}
if(!isset($searchterms)) {$searchterms='';}
if(!isset($cid)) {$cid='';} // ID of the calling form field

echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_select">'.K_NEWLINE;
echo '<input type="hidden" name="cid" id="cid" value="'.$cid.'" />'.K_NEWLINE;
//echo '<div style="text-align:left;">';
echo '<div class="row">';
echo '<input type="text" name="searchterms" id="searchterms" value="'.htmlspecialchars($searchterms, ENT_COMPAT, $l['a_meta_charset']).'" size="20" maxlength="255" title="'.$l['w_search_keywords'].'" />';
F_submit_button('search', $l['w_search'], $l['w_search']);
echo '</div>'.K_NEWLINE;

// build a search query
if (strlen($searchterms) > 0) {
	$searchterms = trim($searchterms);
	if (preg_match("/^([0-9A-F]{2})[\:\-]([0-9A-F]{2})[\:\-]([0-9A-F]{2})/i", $searchterms, $matches) > 0) {
		// MAC address
		$mac = strtoupper($matches[1].$matches[2].$matches[3]);
		$sql = 'SELECT mnf_id, mnf_name FROM '.K_TABLE_MANUFACTURES.', '.K_TABLE_MANUFACTURES_MAC.' WHERE mnf_id=mac_mnf_id AND mac_mac=\''.$mac.'\' ORDER BY mnf_name ASC';
	} else {
		$wherequery = '';
		$terms = preg_split("/[\s]+/i", $searchterms); // Get all the words into an array
		foreach ($terms as $word) {
			$word = F_escape_sql($word);
			$wherequery .= ' AND (mnf_name LIKE \'%'.$word.'%\')';

		}
		$wherequery = substr($wherequery, 5);
		$sql = 'SELECT * FROM '.K_TABLE_MANUFACTURES.' WHERE '.$wherequery.' ORDER BY mnf_name ASC';
	}
} else {
	$sql = 'SELECT mnf_id, mnf_name FROM '.K_TABLE_MANUFACTURES.' ORDER BY mnf_name ASC';
}
if ($r = F_db_query($sql, $db)) {
	echo '<ul>'.K_NEWLINE;
	while ($m = F_db_fetch_array($r)) {
		// on click the manufacturer ID will be returned on the calling form field
		$jsaction = 'javascript:window.opener.document.getElementById(\''.$cid.'\').value='.$m['mnf_id'].';';
		$jsaction .= 'window.opener.document.getElementById(\''.$cid.'\').onchange();';
		$jsaction .= 'window.close();';
		echo '<li><a href="#" onclick="'.$jsaction.'" title="['.$l['w_select'].']">'.htmlspecialchars($m['mnf_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</a></li>'.K_NEWLINE;
	}
	echo '</ul>'.K_NEWLINE;
} else {
	F_display_db_error();
}

echo '</form>'.K_NEWLINE;

require_once(dirname(__FILE__).'/tce_page_footer_popup.php');

//============================================================+
// END OF FILE
//============================================================+
