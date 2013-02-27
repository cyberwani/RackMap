<?php
//============================================================+
// File name   : tce_edit_object_types.php
// Begin       : 2011-10-31
// Last Update : 2012-01-06
//
// Description : Edit object types.
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
//    Copyright (C) 2011-2012 Fubra Limited
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
 * Edit object types.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-10-31
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_OBJECT_TYPES;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_object_type_editor'];
require_once('../code/tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');

require_once('tce_functions_objects.php');

require_once('../../shared/code/htmlcolors.php');

//$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
//$userlevel = intval($_SESSION['session_user_level']);

if (isset($_REQUEST['obt_id'])) {
	$obt_id = intval($_REQUEST['obt_id']);
} else {
	$obt_id = 0;
}
if (isset($_REQUEST['obt_name'])) {
	$obt_name = $_REQUEST['obt_name'];
} else {
	$obt_name = '';
}
if (isset($_REQUEST['obt_description'])) {
	$obt_description = $_REQUEST['obt_description'];
} else {
	$obt_description = '';
}
if (isset($_REQUEST['obt_color']) AND in_array($_REQUEST['obt_color'], $webcolor)) {
	$obt_color = $_REQUEST['obt_color'];
} else {
	$obt_color = '';
}
if (isset($_REQUEST['obt_virtual'])) {
	$obt_virtual = intval($_REQUEST['obt_virtual']);
} else {
	$obt_virtual = false;
}

switch($menu_mode) { // process submitted data

	case 'delete':{
		F_stripslashes_formfields(); // ask confirmation
		F_print_error('WARNING', $l['m_delete_confirm']);
		echo '<div class="confirmbox">'.K_NEWLINE;
		echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_delete">'.K_NEWLINE;
		echo '<div>'.K_NEWLINE;
		echo '<input type="hidden" name="obt_id" id="obt_id" value="'.$obt_id.'" />'.K_NEWLINE;
		echo '<input type="hidden" name="obt_name" id="obt_name" value="'.stripslashes($obt_name).'" />'.K_NEWLINE;
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
			$sql = 'DELETE FROM '.K_TABLE_OBJECT_TYPES.' WHERE obt_id='.$obt_id.'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$obt_id=FALSE;
				F_print_error('MESSAGE', '['.stripslashes($obt_name).'] '.$l['m_deleted']);
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
			if(!F_check_unique(K_TABLE_OBJECT_TYPES, 'obt_name=\''.F_escape_sql($obt_name).'\'', 'obt_id', $obt_id)) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			$sql = 'UPDATE '.K_TABLE_OBJECT_TYPES.' SET
				obt_name=\''.F_escape_sql($obt_name).'\',
				obt_description='.F_empty_to_null($obt_description).',
				obt_color='.F_empty_to_null($obt_color).',
				obt_virtual=\''.$obt_virtual.'\'
				WHERE obt_id='.$obt_id.'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				F_print_error('MESSAGE', $obt_name.': '.$l['m_updated']);
			}
			// remove old attributes
			$old_object_attributes = F_get_object_attributes($obt_id);
			foreach ($old_object_attributes as $atb_id) {
				// delete previous groups
				$sql = 'DELETE FROM '.K_TABLE_OBJECT_ATTRIBUTES_MAP.'
					WHERE oam_obt_id='.$obt_id.' AND oam_atb_id='.$atb_id.'';
				if (!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
				}
			}
			// update attributes
			if (!empty($object_attributes)) {
				foreach ($object_attributes as $atb_id) {
					$sql = 'INSERT INTO '.K_TABLE_OBJECT_ATTRIBUTES_MAP.' (
						oam_obt_id,
						oam_atb_id
						) VALUES (
						\''.$obt_id.'\',
						\''.intval($atb_id).'\'
						)';
					if (!$r = F_db_query($sql, $db)) {
						F_display_db_error(false);
					}
				}
			}
		}
		break;
	}

	case 'add':{ // Add
		if($formstatus = F_check_form_fields()) { // check submitted form fields
			// check if name is unique
			if(!F_check_unique(K_TABLE_OBJECT_TYPES, 'obt_name=\''.F_escape_sql($obt_name).'\'')) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			$sql = 'INSERT INTO '.K_TABLE_OBJECT_TYPES.' (
				obt_name,
				obt_description,
				obt_color,
				obt_virtual
				) VALUES (
				\''.F_escape_sql($obt_name).'\',
				'.F_empty_to_null($obt_description).',
				'.F_empty_to_null($obt_color).',
				\''.$obt_virtual.'\'
				)';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$obt_id = F_db_insert_id($db, K_TABLE_OBJECT_TYPES, 'obt_id');
			}
			// add attribute
			if (!empty($object_attributes)) {
				foreach ($object_attributes as $atb_id) {
					$sql = 'INSERT INTO '.K_TABLE_OBJECT_ATTRIBUTES_MAP.' (
						oam_obt_id,
						oam_atb_id
						) VALUES (
						\''.$obt_id.'\',
						\''.$atb_id.'\'
						)';
					if (!$r = F_db_query($sql, $db)) {
						F_display_db_error(false);
					}
				}
			}
		}
		break;
	}

	case 'clear':{ // Clear form fields
		$obt_name = '';
		$obt_description = '';
		$obt_color = '';
		break;
	}

	default :{
		break;
	}

} //end of switch

// --- Initialize variables
if($formstatus) {
	if ($menu_mode != 'clear') {
		if(!isset($obt_id) OR empty($obt_id)) {
			$obt_id = 0;
			$obt_name = '';
			$obt_color = '';
			$obt_virtual = false;
		} else {
			$sql = 'SELECT * FROM '.K_TABLE_OBJECT_TYPES.' WHERE obt_id='.$obt_id.' LIMIT 1';
			if($r = F_db_query($sql, $db)) {
				if($m = F_db_fetch_array($r)) {
					$obt_id = $m['obt_id'];
					$obt_name = $m['obt_name'];
					$obt_description = $m['obt_description'];
					$obt_color = $m['obt_color'];
					$obt_virtual = F_getBoolean($m['obt_virtual']);
				} else {
					$obt_name = '';
					$obt_description = '';
					$obt_color = '';
					$obt_virtual = false;
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

echo F_select_object_type($obt_id, true);

echo '<div class="row"><hr /></div>'.K_NEWLINE;

echo getFormRowTextInput('obt_name', $l['w_name'], $l['h_object_name'], '', $obt_name, '', 255, false, false, false, '');
echo getFormRowTextBox('obt_description', $l['w_description'], $l['h_object_description'], $obt_description, false, '');

// select object attributes
echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="object_attributes">'.$l['w_attributes'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="object_attributes[]" id="object_attributes" size="10" multiple="multiple">'.K_NEWLINE;
$sql = 'SELECT * FROM '.K_TABLE_ATTRIBUTE_TYPES.' ORDER BY atb_name';
if ($r = F_db_query($sql, $db)) {
	while ($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['atb_id'].'"';
		if (F_isObjectAttribute($obt_id, $m['atb_id'])) {
			echo ' selected="selected"';
			$m['atb_name'] = '* '.$m['atb_name'];
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

echo F_select_color($obt_color, 'obt_color', $l['w_color'], $l['h_object_color']);

echo getFormRowCheckBox('obt_virtual', $l['w_virtual'], $l['h_virtual_object'], '', '1', $obt_virtual, false, '');

echo '<div class="row">'.K_NEWLINE;

// show buttons by case
if (isset($obt_id) AND ($obt_id > 0)) {
	echo '<span style="background-color:#999999;">';
	echo '<input type="checkbox" name="confirmupdate" id="confirmupdate" value="1" title="confirm &rarr; update" />';
	F_submit_button('update', $l['w_update'], $l['h_update']);
	echo '</span>';
	F_submit_button('delete', $l['w_delete'], $l['h_delete']);
	//F_submit_button('add', $l['w_add'], $l['h_add']);
} else {
	F_submit_button('add', $l['w_add'], $l['h_add']);
}
F_submit_button('clear', $l['w_clear'], $l['h_clear']);

// comma separated list of required fields
echo '<input type="hidden" name="ff_required" id="ff_required" value="obt_name" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="'.htmlspecialchars($l['w_name'], ENT_COMPAT, $l['a_meta_charset']).'" />'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_object_type_editor'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
