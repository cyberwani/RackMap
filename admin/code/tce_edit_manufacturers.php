<?php
//============================================================+
// File name   : tce_edit_manufacturers.php
// Begin       : 2011-10-31
// Last Update : 2011-11-03
//
// Description : Edit Manufacturers.
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
 * Edit Manufacturers.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-10-31
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_MANUFACTURERS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_manufacturer_editor'];
require_once('../code/tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');

//$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
//$userlevel = intval($_SESSION['session_user_level']);

if (isset($_REQUEST['mnf_id'])) {
	$mnf_id = intval($_REQUEST['mnf_id']);
} else {
	$mnf_id = 0;
}
if (isset($_REQUEST['mnf_name'])) {
	$mnf_name = $_REQUEST['mnf_name'];
} else {
	$mnf_name = '';
}
if (isset($_REQUEST['mnf_url'])) {
	$mnf_url = $_REQUEST['mnf_url'];
} else {
	$mnf_url = '';
}
if (isset($_REQUEST['mnf_description'])) {
	$mnf_description = $_REQUEST['mnf_description'];
} else {
	$mnf_description = '';
}
// mac addresses
$macs = array();
if (isset($_REQUEST['max_macs'])) {
	$max_macs = min(intval($_REQUEST['max_macs']), 100);
	for ($i = 0; $i <= $max_macs; ++$i) {
		if (!empty($_REQUEST['mac_'.$i])) {
			$macs[] = $_REQUEST['mac_'.$i];
		}
	}
}

switch($menu_mode) { // process submitted data

	case 'delete':{
		F_stripslashes_formfields(); // ask confirmation
		F_print_error('WARNING', $l['m_delete_confirm']);
		echo '<div class="confirmbox">'.K_NEWLINE;
		echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_delete">'.K_NEWLINE;
		echo '<div>'.K_NEWLINE;
		echo '<input type="hidden" name="mnf_id" id="mnf_id" value="'.$mnf_id.'" />'.K_NEWLINE;
		echo '<input type="hidden" name="mnf_name" id="mnf_name" value="'.stripslashes($mnf_name).'" />'.K_NEWLINE;
		F_submit_button('forcedelete', $l['w_delete'], $l['h_delete']);
		F_submit_button('cancel', $l['w_cancel'], $l['h_cancel']);
		echo '</div>'.K_NEWLINE;
		echo '</form>'.K_NEWLINE;
		echo '</div>'.K_NEWLINE;
		break;
	}

	case 'forcedelete':{
		F_stripslashes_formfields(); // Delete specified user
		if ($forcedelete == $l['w_delete']) { //check if delete button has been pushed (redundant check)
			$sql = 'DELETE FROM '.K_TABLE_MANUFACTURES.' WHERE mnf_id='.$mnf_id.'';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$mnf_id=FALSE;
				F_print_error('MESSAGE', '['.stripslashes($mnf_name).'] '.$l['m_deleted']);
			}
		}
		break;
	}

	case 'update':{ // Update
		// check if the confirmation chekbox has been selected
		if (!isset($_REQUEST['confirmupdate']) OR ($_REQUEST['confirmupdate'] != 1)) {
			F_print_error('WARNING', $l['m_form_missing_fields'].': '.$l['w_confirm'].' &rarr; '.$l['w_update']);
			F_stripslashes_formfields();
			break;
		}
		if ($formstatus = F_check_form_fields()) {
			// check if name is unique
			if (!F_check_unique(K_TABLE_MANUFACTURES, 'mnf_name=\''.F_escape_sql($mnf_name).'\'', 'mnf_id', $mnf_id)) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			$sql = 'UPDATE '.K_TABLE_MANUFACTURES.' SET
				mnf_name=\''.F_escape_sql($mnf_name).'\',
				mnf_url='.F_empty_to_null($mnf_url).',
				mnf_description='.F_empty_to_null($mnf_description).'
				WHERE mnf_id='.$mnf_id.'';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				F_print_error('MESSAGE', $mnf_name.': '.$l['m_updated']);
			}
			// add mac prefixes
			$sql = 'DELETE FROM '.K_TABLE_MANUFACTURES_MAC.' WHERE mac_mnf_id='.$mnf_id.'';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			}
			foreach ($macs as $k => $v) {
				$sql = 'INSERT INTO '.K_TABLE_MANUFACTURES_MAC.' (
					mac_mnf_id,
					mac_mac
					) VALUES (
					'.$mnf_id.',
					\''.F_escape_sql($v).'\'
					)';
				if (!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
				}
			}
		}
		break;
	}

	case 'add':{ // Add
		if ($formstatus = F_check_form_fields()) { // check submitted form fields
			// check if name is unique
			if (!F_check_unique(K_TABLE_MANUFACTURES, 'mnf_name=\''.F_escape_sql($mnf_name).'\'')) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			$sql = 'INSERT INTO '.K_TABLE_MANUFACTURES.' (
				mnf_name,
				mnf_url,
				mnf_description
				) VALUES (
				\''.F_escape_sql($mnf_name).'\',
				'.F_empty_to_null($mnf_url).',
				'.F_empty_to_null($mnf_description).'
				)';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$mnf_id = F_db_insert_id($db, K_TABLE_MANUFACTURES, 'mnf_id');
			}
			// add mac prefixes
			foreach ($macs as $k => $v) {
				$sql = 'INSERT INTO '.K_TABLE_MANUFACTURES_MAC.' (
					mac_mnf_id,
					mac_mac
					) VALUES (
					'.$mnf_id.',
					\''.F_escape_sql($v).'\'
					)';
				if (!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
				}
			}
		}
		break;
	}

	case 'clear':{ // Clear form fields
		$mnf_name = '';
		$mnf_url = '';
		$mnf_description = '';
		break;
	}

	default :{
		break;
	}

} //end of switch

// --- Initialize variables
if ($formstatus) {
	if ($menu_mode != 'clear') {
		if (!isset($mnf_id) OR empty($mnf_id)) {
			$mnf_id = 0;
			$mnf_name = '';
			$mnf_description = '';
			$macs = array();
			$macs[] = '';
		} else {
			$sql = 'SELECT * FROM '.K_TABLE_MANUFACTURES.' WHERE mnf_id='.$mnf_id.' LIMIT 1';
			if ($r = F_db_query($sql, $db)) {
				if ($m = F_db_fetch_array($r)) {
					$mnf_id = $m['mnf_id'];
					$mnf_name = $m['mnf_name'];
					$mnf_url = $m['mnf_url'];
					$mnf_description = $m['mnf_description'];
					// get mac prefixes
					$sqlm = 'SELECT mac_mac FROM '.K_TABLE_MANUFACTURES_MAC.' WHERE mac_mnf_id='.$mnf_id.' ORDER BY mac_mac ASC';
					if ($rm = F_db_query($sqlm, $db)) {
						$macs = array();
						while ($mm = F_db_fetch_array($rm)) {
							$macs[] = $mm['mac_mac'];
						}
						$macs[] = ''; // default value for new entry
					} else {
						F_display_db_error();
					}
				} else {
					$mnf_name = '';
					$mnf_url = '';
					$mnf_description = '';
					$macs = array();
					$macs[] = '';
				}
			} else {
				F_display_db_error();
			}
		}
	}
}

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_editor">'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="mnf_id">'.$l['w_manufacturer'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="mnf_id" id="mnf_id" size="0" onchange="document.getElementById(\'form_editor\').submit()" style="width:20em;max-width:20em;">'.K_NEWLINE;
echo '<option value="0" style="background-color:#009900;color:white;"';
if ($mnf_id == 0) {
	echo ' selected="selected"';
}
echo '>+</option>'.K_NEWLINE;
$sql = 'SELECT * FROM '.K_TABLE_MANUFACTURES.' WHERE 1 ORDER BY mnf_name ASC';
if ($r = F_db_query($sql, $db)) {
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['mnf_id'].'"';
		if ($m['mnf_id'] == $mnf_id) {
			echo ' selected="selected"';
		}
		echo '>'.htmlspecialchars($m['mnf_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
	}
} else {
	echo '</select></span></div>'.K_NEWLINE;
	F_display_db_error();
}
echo '</select>'.K_NEWLINE;

// manufacturers selection popup
$jsaction = 'selectWindow=window.open(\'tce_select_manuf_popup.php?cid=mnf_id\', \'selectWindow\', \'dependent, height=600, width=800, menubar=no, resizable=yes, scrollbars=yes, status=no, toolbar=no\');return false;';
echo '<a href="#" onclick="'.$jsaction.'" class="xmlbutton" title="'.$l['w_select'].'">...</a>';

echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('selectrecord');

echo '<div class="row"><hr /></div>'.K_NEWLINE;

echo getFormRowTextInput('mnf_name', $l['w_name'], $l['h_manufacturer_name'], '', $mnf_name, '', 255, false, false, false, '');
echo getFormRowTextInput('mnf_url', $l['w_url'], $l['h_manufacturer_url'], '', $mnf_url, '', 255, false, false, false, '');
echo getFormRowTextBox('mnf_description', $l['w_description'], $l['h_manufacturer_description'], $mnf_description, false, '');

// editor for MAC prefixes
$k = 0;
foreach ($macs as $k => $v) {
	echo getFormRowTextInput('mac_'.$k, $l['w_mac'].' '.($k + 1), $l['h_manufacturer_mac'], '', $v, '^([A-F0-9]{6})$', 6, false, false, false, '');
}
echo '<input type="hidden" name="max_macs" id="max_macs" value="'.$k.'" />'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;

// show buttons by case
if (isset($mnf_id) AND ($mnf_id > 0)) {
	echo '<span style="background-color:#999999;">';
	echo '<input type="checkbox" name="confirmupdate" id="confirmupdate" value="1" title="confirm &rarr; update" />';
	F_submit_button('update', $l['w_update'], $l['h_update']);
	echo '</span>';
	F_submit_button('delete', $l['w_delete'], $l['h_delete']);
} else {
	F_submit_button('add', $l['w_add'], $l['h_add']);
}
F_submit_button('clear', $l['w_clear'], $l['h_clear']);

// comma separated list of required fields
echo '<input type="hidden" name="ff_required" id="ff_required" value="mnf_name" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="'.htmlspecialchars($l['w_name'], ENT_COMPAT, $l['a_meta_charset']).'" />'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_manufacturer_editor'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
