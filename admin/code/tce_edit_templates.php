<?php
//============================================================+
// File name   : tce_edit_templates.php
// Begin       : 2011-10-31
// Last Update : 2011-11-10
//
// Description : Edit configuration templates.
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
 * Edit configuration templates.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-11-10
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_TEMPLATES;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_templates_editor'];
require_once('../code/tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');

//$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
//$userlevel = intval($_SESSION['session_user_level']);

if (isset($_REQUEST['tmp_id'])) {
	$tmp_id = intval($_REQUEST['tmp_id']);
} else {
	$tmp_id = 0;
}
if (isset($_REQUEST['tmp_name'])) {
	$tmp_name = preg_replace('/[^A-Z0-9]+/', '', strtoupper($_REQUEST['tmp_name']));
} else {
	$tmp_name = '';
}
if (isset($_REQUEST['tmp_template'])) {
	$tmp_template = $_REQUEST['tmp_template'];
} else {
	$tmp_template = '';
}

switch($menu_mode) { // process submitted data

	case 'delete':{
		F_stripslashes_formfields(); // ask confirmation
		F_print_error('WARNING', $l['m_delete_confirm']);
		echo '<div class="confirmbox">'.K_NEWLINE;
		echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_delete">'.K_NEWLINE;
		echo '<div>'.K_NEWLINE;
		echo '<input type="hidden" name="tmp_id" id="tmp_id" value="'.$tmp_id.'" />'.K_NEWLINE;
		echo '<input type="hidden" name="tmp_name" id="tmp_name" value="'.stripslashes($tmp_name).'" />'.K_NEWLINE;
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
			$sql = 'DELETE FROM '.K_TABLE_TEMPLATES.' WHERE tmp_id='.$tmp_id.'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$tmp_id=FALSE;
				F_print_error('MESSAGE', '['.stripslashes($tmp_name).'] '.$l['m_deleted']);
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
			if(!F_check_unique(K_TABLE_TEMPLATES, 'tmp_name=\''.F_escape_sql($tmp_name).'\'', 'tmp_id', $tmp_id)) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			$sql = 'UPDATE '.K_TABLE_TEMPLATES.' SET
				tmp_name=\''.F_escape_sql($tmp_name).'\',
				tmp_template=\''.F_escape_sql($tmp_template).'\'
				WHERE tmp_id='.$tmp_id.'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				F_print_error('MESSAGE', $tmp_name.': '.$l['m_updated']);
			}
		}
		break;
	}

	case 'add':{ // Add
		if($formstatus = F_check_form_fields()) { // check submitted form fields
			// check if name is unique
			if(!F_check_unique(K_TABLE_TEMPLATES, 'tmp_name=\''.F_escape_sql($tmp_name).'\'')) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE;
				F_stripslashes_formfields();
				break;
			}
			$sql = 'INSERT INTO '.K_TABLE_TEMPLATES.' (
				tmp_name,
				tmp_template
				) VALUES (
				\''.F_escape_sql($tmp_name).'\',
				\''.F_escape_sql($tmp_template).'\'
				)';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$tmp_id = F_db_insert_id($db, K_TABLE_TEMPLATES, 'tmp_id');
			}
		}
		break;
	}

	case 'clear':{ // Clear form fields
		$tmp_name = '';
		$tmp_template = '';
		break;
	}

	default :{
		break;
	}

} //end of switch

// --- Initialize variables
if($formstatus) {
	if ($menu_mode != 'clear') {
		if(!isset($tmp_id) OR empty($tmp_id)) {
			$tmp_id = 0;
			$tmp_name = '';
			$tmp_template = '';
		} else {
			$sql = 'SELECT * FROM '.K_TABLE_TEMPLATES.' WHERE tmp_id='.$tmp_id.' LIMIT 1';
			if($r = F_db_query($sql, $db)) {
				if($m = F_db_fetch_array($r)) {
					$tmp_id = $m['tmp_id'];
					$tmp_name = $m['tmp_name'];
					$tmp_template = $m['tmp_template'];
				} else {
					$tmp_name = '';
					$tmp_template = '';
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
echo '<label for="tmp_id">'.$l['w_template'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="tmp_id" id="tmp_id" size="0" onchange="document.getElementById(\'form_editor\').submit()">'.K_NEWLINE;
echo '<option value="0" style="background-color:#009900;color:white;"';
if ($tmp_id == 0) {
	echo ' selected="selected"';
}
echo '>+</option>'.K_NEWLINE;
$sql = 'SELECT * FROM '.K_TABLE_TEMPLATES.' WHERE 1 ORDER BY tmp_name ASC';
if($r = F_db_query($sql, $db)) {
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['tmp_id'].'"';
		if($m['tmp_id'] == $tmp_id) {
			echo ' selected="selected"';
		}
		echo '>'.htmlspecialchars($m['tmp_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
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

echo getFormRowTextInput('tmp_name', $l['w_name'], $l['h_attribute_name'], '', $tmp_name, '', 255, false, false, false, '');
echo getFormRowTextBox('tmp_template', $l['w_template'], $l['h_config_template'], $tmp_template, false, '');

echo '<div class="row">'.K_NEWLINE;

// show buttons by case
if (isset($tmp_id) AND ($tmp_id > 0)) {
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
echo '<input type="hidden" name="ff_required" id="ff_required" value="tmp_name" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="'.htmlspecialchars($l['w_name'], ENT_COMPAT, $l['a_meta_charset']).'" />'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_templates_editor'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
