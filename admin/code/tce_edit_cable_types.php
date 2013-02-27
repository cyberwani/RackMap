<?php
//============================================================+
// File name   : tce_edit_cable_types.php
// Begin       : 2011-12-08
// Last Update : 2011-12-08
//
// Description : Edit cable types.
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
 * Edit cable types.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-12-08
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_EDIT_CABLE_TYPES;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_connection_type_editor'];
require_once('../code/tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');

require_once('tce_functions_objects.php');

//$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
//$userlevel = intval($_SESSION['session_user_level']);

if (isset($_REQUEST['cbt_id'])) {
	$cbt_id = intval($_REQUEST['cbt_id']);
} else {
	$cbt_id = 0;
}
if (isset($_REQUEST['cbt_name'])) {
	$cbt_name = $_REQUEST['cbt_name'];
} else {
	$cbt_name = '';
}
if (isset($_REQUEST['cbt_description'])) {
	$cbt_description = $_REQUEST['cbt_description'];
} else {
	$cbt_description = '';
}


switch($menu_mode) { // process submitted data

	case 'delete':{
		F_stripslashes_formfields(); // ask confirmation
		F_print_error('WARNING', $l['m_delete_confirm']);
		echo '<div class="confirmbox">'.K_NEWLINE;
		echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_delete">'.K_NEWLINE;
		echo '<div>'.K_NEWLINE;
		echo '<input type="hidden" name="cbt_id" id="cbt_id" value="'.$cbt_id.'" />'.K_NEWLINE;
		echo '<input type="hidden" name="cbt_name" id="cbt_name" value="'.stripslashes($cbt_name).'" />'.K_NEWLINE;
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
			$sql = 'DELETE FROM '.K_TABLE_CABLE_TYPES.' WHERE cbt_id='.$cbt_id.'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$cbt_id=FALSE;
				F_print_error('MESSAGE', '['.stripslashes($cbt_name).'] '.$l['m_deleted']);
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
			if(!F_check_unique(K_TABLE_CABLE_TYPES, 'cbt_name=\''.F_escape_sql($cbt_name).'\'', 'cbt_id', $cbt_id)) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			$sql = 'UPDATE '.K_TABLE_CABLE_TYPES.' SET
				cbt_name=\''.F_escape_sql($cbt_name).'\',
				cbt_description='.F_empty_to_null($cbt_description).'
				WHERE cbt_id='.$cbt_id.'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				F_print_error('MESSAGE', $cbt_name.': '.$l['m_updated']);
			}
		}
		break;
	}

	case 'add':{ // Add
		if($formstatus = F_check_form_fields()) { // check submitted form fields
			// check if name is unique
			if(!F_check_unique(K_TABLE_CABLE_TYPES, 'cbt_name=\''.F_escape_sql($cbt_name).'\'')) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			$sql = 'INSERT INTO '.K_TABLE_CABLE_TYPES.' (
				cbt_name,
				cbt_description
				) VALUES (
				\''.F_escape_sql($cbt_name).'\',
				'.F_empty_to_null($cbt_description).'
				)';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$cbt_id = F_db_insert_id($db, K_TABLE_CABLE_TYPES, 'cbt_id');
			}
		}
		break;
	}

	case 'clear':{ // Clear form fields
		$cbt_name = '';
		$cbt_description = '';
		break;
	}

	default :{
		break;
	}

} //end of switch

// --- Initialize variables
if($formstatus) {
	if ($menu_mode != 'clear') {
		if(!isset($cbt_id) OR empty($cbt_id)) {
			$cbt_id = 0;
			$cbt_name = '';
		} else {
			$sql = 'SELECT * FROM '.K_TABLE_CABLE_TYPES.' WHERE cbt_id='.$cbt_id.' LIMIT 1';
			if($r = F_db_query($sql, $db)) {
				if($m = F_db_fetch_array($r)) {
					$cbt_id = $m['cbt_id'];
					$cbt_name = $m['cbt_name'];
					$cbt_description = $m['cbt_description'];
				} else {
					$cbt_name = '';
					$cbt_description = '';
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

echo F_select_connection_type($cbt_id, true);

echo '<div class="row"><hr /></div>'.K_NEWLINE;

echo getFormRowTextInput('cbt_name', $l['w_name'], $l['h_object_name'], '', $cbt_name, '', 255, false, false, false, '');
echo getFormRowTextBox('cbt_description', $l['w_description'], $l['h_object_description'], $cbt_description, false, '');

echo '<div class="row">'.K_NEWLINE;

// show buttons by case
if (isset($cbt_id) AND ($cbt_id > 0)) {
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
echo '<input type="hidden" name="ff_required" id="ff_required" value="cbt_name" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="'.htmlspecialchars($l['w_name'], ENT_COMPAT, $l['a_meta_charset']).'" />'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_connection_type_editor'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
