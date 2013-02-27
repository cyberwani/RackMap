<?php
//============================================================+
// File name   : tce_edit_attributes.php
// Begin       : 2011-10-31
// Last Update : 2011-11-01
//
// Description : Edit attribute types for objects.
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
 * Edit attribute types for objects.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-10-31
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_ATTRIBUTES;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_attribute_editor'];
require_once('../code/tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');

//$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
//$userlevel = intval($_SESSION['session_user_level']);

// define legal attribute types
$attribute_types = array('bool'=>'bool', 'int'=>'int', 'float'=>'float', 'string'=>'string', 'text'=>'text', 'date'=>'date', 'datetime'=>'datetime', 'password'=>'password');

if (isset($_REQUEST['atb_id'])) {
	$atb_id = intval($_REQUEST['atb_id']);
} else {
	$atb_id = 0;
}
if (isset($_REQUEST['atb_name'])) {
	$atb_name = $_REQUEST['atb_name'];
} else {
	$atb_name = '';
}
if (isset($_REQUEST['atb_description'])) {
	$atb_description = $_REQUEST['atb_description'];
} else {
	$atb_description = '';
}
if (isset($_REQUEST['atb_default'])) {
	$atb_default = $_REQUEST['atb_default'];
} else {
	$atb_default = '';
}
if (isset($_REQUEST['atb_type'])) {
	$atb_type = $_REQUEST['atb_type'];
	if (!in_array($atb_type, $attribute_types)) {
		// default value
		$atb_type = 'string';
	}
} else {
	$atb_type = 'string';
}

switch($menu_mode) { // process submitted data

	case 'delete':{
		F_stripslashes_formfields(); // ask confirmation
		F_print_error('WARNING', $l['m_delete_confirm']);
		echo '<div class="confirmbox">'.K_NEWLINE;
		echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_delete">'.K_NEWLINE;
		echo '<div>'.K_NEWLINE;
		echo '<input type="hidden" name="atb_id" id="atb_id" value="'.$atb_id.'" />'.K_NEWLINE;
		echo '<input type="hidden" name="atb_name" id="atb_name" value="'.stripslashes($atb_name).'" />'.K_NEWLINE;
		F_submit_button('forcedelete', $l['w_delete'], $l['h_delete']);
		F_submit_button('cancel', $l['w_cancel'], $l['h_cancel']);
		echo '</div>'.K_NEWLINE;
		echo '</form>'.K_NEWLINE;
		echo '</div>'.K_NEWLINE;
		break;
	}

	case 'forcedelete':{
		F_stripslashes_formfields(); // Delete specified user
		if($forcedelete == $l['w_delete']) { //check if delete button has been pushed (redundant check)
			$sql = 'DELETE FROM '.K_TABLE_ATTRIBUTE_TYPES.' WHERE atb_id='.$atb_id.'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$atb_id=FALSE;
				F_print_error('MESSAGE', '['.stripslashes($atb_name).'] '.$l['m_deleted']);
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
		if($formstatus = F_check_form_fields()) {
			// check if name is unique
			if(!F_check_unique(K_TABLE_ATTRIBUTE_TYPES, 'atb_name=\''.F_escape_sql($atb_name).'\'', 'atb_id', $atb_id)) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			$sql = 'UPDATE '.K_TABLE_ATTRIBUTE_TYPES.' SET
				atb_name=\''.F_escape_sql($atb_name).'\',
				atb_description='.F_empty_to_null($atb_description).',
				atb_type=\''.F_escape_sql($atb_type).'\',
				atb_default=\''.F_escape_sql($atb_default).'\'
				WHERE atb_id='.$atb_id.'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				F_print_error('MESSAGE', $atb_name.': '.$l['m_updated']);
			}
		}
		break;
	}

	case 'add':{ // Add
		if($formstatus = F_check_form_fields()) { // check submitted form fields
			// check if name is unique
			if(!F_check_unique(K_TABLE_ATTRIBUTE_TYPES, 'atb_name=\''.F_escape_sql($atb_name).'\'')) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			$sql = 'INSERT INTO '.K_TABLE_ATTRIBUTE_TYPES.' (
				atb_name,
				atb_description,
				atb_type,
				atb_default
				) VALUES (
				\''.F_escape_sql($atb_name).'\',
				'.F_empty_to_null($atb_description).',
				\''.F_escape_sql($atb_type).'\',
				\''.F_escape_sql($atb_default).'\'
				)';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$atb_id = F_db_insert_id($db, K_TABLE_ATTRIBUTE_TYPES, 'atb_id');
			}
		}
		break;
	}

	case 'clear':{ // Clear form fields
		$atb_name = '';
		$atb_description = '';
		$atb_type = '';
		$atb_default = '';
		break;
	}

	default :{
		break;
	}

} //end of switch

// --- Initialize variables
if($formstatus) {
	if ($menu_mode != 'clear') {
		if(!isset($atb_id) OR empty($atb_id)) {
			$atb_id = 0;
			$atb_name = '';
			$atb_description = '';
			$atb_type = '';
			$atb_default = '';
		} else {
			$sql = 'SELECT * FROM '.K_TABLE_ATTRIBUTE_TYPES.' WHERE atb_id='.$atb_id.' LIMIT 1';
			if($r = F_db_query($sql, $db)) {
				if($m = F_db_fetch_array($r)) {
					$atb_id = $m['atb_id'];
					$atb_name = $m['atb_name'];
					$atb_description = $m['atb_description'];
					$atb_type = $m['atb_type'];
					$atb_default = $m['atb_default'];
				} else {
					$atb_name = '';
					$atb_description = '';
					$atb_type = '';
					$atb_default = '';
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
echo '<label for="atb_id">'.$l['w_attribute'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="atb_id" id="atb_id" size="0" onchange="document.getElementById(\'form_editor\').submit()">'.K_NEWLINE;
echo '<option value="0" style="background-color:#009900;color:white;"';
if ($atb_id == 0) {
	echo ' selected="selected"';
}
echo '>+</option>'.K_NEWLINE;
$sql = 'SELECT * FROM '.K_TABLE_ATTRIBUTE_TYPES.' WHERE 1 ORDER BY atb_name ASC';
if($r = F_db_query($sql, $db)) {
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['atb_id'].'"';
		if($m['atb_id'] == $atb_id) {
			echo ' selected="selected"';
		}
		echo '>'.htmlspecialchars($m['atb_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
	}
} else {
	echo '</select></span></div>'.K_NEWLINE;
	F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormNoscriptSelect('selectrecord');

echo '<div class="row"><hr /></div>'.K_NEWLINE;

echo getFormRowTextInput('atb_name', $l['w_name'], $l['h_attribute_name'], '', $atb_name, '', 255, false, false, false, '');
echo getFormRowSelectBox('atb_type', $l['w_type'], $l['h_attribute_type'], '', $atb_type, $attribute_types, '');
echo getFormRowTextInput('atb_default', $l['w_default'], $l['h_attribute_default'], '', $atb_default, '', 255, false, false, false, '');
echo getFormRowTextBox('atb_description', $l['w_description'], $l['h_attribute_description'], $atb_description, false, '');

echo '<div class="row">'.K_NEWLINE;

// show buttons by case
if (isset($atb_id) AND ($atb_id > 0)) {
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
echo '<input type="hidden" name="ff_required" id="ff_required" value="atb_name" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="'.htmlspecialchars($l['w_name'], ENT_COMPAT, $l['a_meta_charset']).'" />'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_attribute_editor'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
