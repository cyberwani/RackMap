<?php
//============================================================+
// File name   : tce_edit_connections.php
// Begin       : 2011-11-16
// Last Update : 2011-12-09
//
// Description : Edit Connections.
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
 * Edit Connections.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-11-16
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_CONNECTIONS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_connection_editor'];
$enable_calendar = true;
require_once('../code/tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_objects.php');

require_once('../../shared/code/htmlcolors.php');

//$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
//$userlevel = intval($_SESSION['session_user_level']);

if (isset($_REQUEST['cab_ids']) AND !empty($_REQUEST['cab_ids'])) {
	$cab_ids = explode('|', $_REQUEST['cab_ids']);
	$cab_a_obj_id = intval($cab_ids[0]);
	$cab_b_obj_id = intval($cab_ids[1]);
	$cab_cbt_id = intval($cab_ids[2]);
	$sel_a_obj_id = $cab_a_obj_id;
	$sel_b_obj_id = $cab_b_obj_id;
	$sel_cbt_id = $cab_cbt_id;
} else {
	if (isset($_REQUEST['cab_a_obj_id'])) {
		$cab_a_obj_id = intval($_REQUEST['cab_a_obj_id']);
	} else {
		$cab_a_obj_id = 0;
	}
	if (isset($_REQUEST['cab_b_obj_id'])) {
		$cab_b_obj_id = intval($_REQUEST['cab_b_obj_id']);
	} else {
		$cab_b_obj_id = 0;
	}
	if (isset($_REQUEST['cbt_id'])) {
		$cab_cbt_id = intval($_REQUEST['cbt_id']);
	} else {
		$cab_cbt_id = 0;
	}
	$sel_a_obj_id = 0;
	$sel_b_obj_id = 0;
	$sel_cbt_id = 0;
}
if (isset($_REQUEST['cab_color']) AND in_array($_REQUEST['cab_color'], $webcolor)) {
	$cab_color = $_REQUEST['cab_color'];
} else {
	$cab_color = 'd3d3d3';
}

$cbt_name = '';

switch($menu_mode) { // process submitted data

	case 'delete':{
		F_stripslashes_formfields(); // ask confirmation
		F_print_error('WARNING', $l['m_delete_confirm']);
		echo '<div class="confirmbox">'.K_NEWLINE;
		echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_delete">'.K_NEWLINE;
		echo '<div>'.K_NEWLINE;
		echo '<input type="hidden" name="cab_a_obj_id" id="cab_a_obj_id" value="'.$cab_a_obj_id.'" />'.K_NEWLINE;
		echo '<input type="hidden" name="cab_b_obj_id" id="cab_b_obj_id" value="'.$cab_b_obj_id.'" />'.K_NEWLINE;
		echo '<input type="hidden" name="cab_cbt_id" id="cab_cbt_id" value="'.$cab_cbt_id.'" />'.K_NEWLINE;
		F_submit_button('forcedelete', $l['w_delete'], $l['h_delete']);
		F_submit_button('cancel', $l['w_cancel'], $l['h_cancel']);
		echo '</div>'.K_NEWLINE;
		echo '</form>'.K_NEWLINE;
		echo '</div>'.K_NEWLINE;
		break;
	}

	case 'forcedelete':{
		F_stripslashes_formfields();
		if ($forcedelete == $l['w_delete']) { //check if delete button has been pushed (redundant check)
			$sql = 'DELETE FROM '.K_TABLE_CABLES.' WHERE cab_a_obj_id='.$cab_a_obj_id.' AND cab_b_obj_id='.$cab_b_obj_id.' AND cab_cbt_id='.$cab_cbt_id.'';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$sql = 'DELETE FROM '.K_TABLE_CABLES.' WHERE cab_a_obj_id='.$cab_b_obj_id.' AND cab_b_obj_id='.$cab_a_obj_id.' AND cab_cbt_id='.$cab_cbt_id.'';
				if (!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
				} else {
					$cab_a_obj_id = false;
					$cab_b_obj_id = false;
					$cab_cbt_id = 1;
					F_print_error('MESSAGE', $l['m_deleted']);
				}
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
		if (F_check_unique(K_TABLE_CABLES, 'cab_a_obj_id='.$cab_a_obj_id.' AND cab_b_obj_id='.$cab_b_obj_id.' AND cab_cbt_id='.$cab_cbt_id)) {
				F_print_error('WARNING', $l['m_add_for_update']);
				$formstatus = false;
				F_stripslashes_formfields();
				break;
		}
		if ($formstatus = F_check_form_fields()) {
			$sql = 'UPDATE '.K_TABLE_CABLES.' SET
				cab_color=\''.F_escape_sql($cab_color).'\'
				WHERE cab_a_obj_id='.$cab_a_obj_id.' AND cab_b_obj_id='.$cab_b_obj_id.' AND cab_cbt_id='.$cab_cbt_id.'';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				F_print_error('MESSAGE', $l['m_updated']);
			}
		}
		break;
	}

	case 'add':{ // Add
		if ($formstatus = F_check_form_fields()) { // check submitted form fields
			// check for loop connection
			if ($cab_a_obj_id == $cab_b_obj_id) {
				F_print_error('WARNING', $l['m_connection_loop']);
				$formstatus = false;
				F_stripslashes_formfields();
				break;
			}
			// check if the connection is unique
			if (!F_check_unique(K_TABLE_CABLES, 'cab_a_obj_id='.$cab_a_obj_id.' AND cab_b_obj_id='.$cab_b_obj_id.' AND cab_cbt_id='.$cab_cbt_id)) {
				F_print_error('WARNING', $l['m_duplicate_connection']);
				$formstatus = false;
				F_stripslashes_formfields();
				break;
			}
			$sql = 'INSERT INTO '.K_TABLE_CABLES.' (
				cab_a_obj_id,
				cab_b_obj_id,
				cab_cbt_id,
				cab_color
				) VALUES (
				'.$cab_a_obj_id.',
				'.$cab_b_obj_id.',
				'.$cab_cbt_id.',
				\''.F_escape_sql($cab_color).'\'
				)';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			}
			$cab_ids = $cab_a_obj_id.'|'.$cab_b_obj_id.'|'.$cab_cbt_id;
			$sel_a_obj_id = $cab_a_obj_id;
			$sel_b_obj_id = $cab_b_obj_id;
			$sel_cbt_id = $cab_cbt_id;
		}
		break;
	}

	case 'clear':{ // Clear form fields
		$cbt_name = '';
		$cab_color = 'd3d3d3';
		break;
	}

	default :{
		break;
	}

} //end of switch

// --- Initialize variables
if ($formstatus) {
	if ($menu_mode != 'clear') {
		if (isset($cab_ids) AND !empty($cab_ids)) {
			$sql = 'SELECT * FROM '.K_TABLE_CABLES.', '.K_TABLE_CABLE_TYPES.' WHERE cab_cbt_id=cbt_id AND cab_a_obj_id='.$cab_a_obj_id.' AND cab_b_obj_id='.$cab_b_obj_id.' AND cab_cbt_id='.$cab_cbt_id.' LIMIT 1';
			if ($r = F_db_query($sql, $db)) {
				if ($m = F_db_fetch_array($r)) {
					$cbt_name = $m['cbt_name'];
					$cab_color = $m['cab_color'];
				} else {
					$cbt_name = '';
					$cab_color = 'd3d3d3';
				}
			} else {
				F_display_db_error();
			}
		} else {
			$cbt_name = '';
			$cab_color = 'd3d3d3';
		}
	}
}

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_editor">'.K_NEWLINE;

echo F_connection_selector($sel_a_obj_id, $sel_b_obj_id, $sel_cbt_id);
echo getFormNoscriptSelect('selectrecord');

echo '<div class="row"><hr /></div>'.K_NEWLINE;

if (isset($cab_ids) AND !empty($cab_ids)) {
	echo getFormDescriptionLine($l['w_object'].' (A)', '', F_get_object_path($cab_a_obj_id, true));
	echo getFormDescriptionLine($l['w_object'].' (B)', '', F_get_object_path($cab_b_obj_id, true));
	echo getFormDescriptionLine($l['w_connection_type'], '', $cbt_name);
} else {
	echo F_object_selector($cab_a_obj_id, 'cab_a_obj_id', $l['w_object'].' (A)', false, false);
	echo F_object_selector($cab_b_obj_id, 'cab_b_obj_id', $l['w_object'].' (B)', false, false);
	echo F_select_connection_type($cab_cbt_id);
}

echo F_select_color($cab_color, 'cab_color', $l['w_color'], $l['h_cable_color']);

echo '<div class="row">'.K_NEWLINE;

// show buttons by case
if (isset($cab_ids) AND !empty($cab_ids)) {
	echo '<span style="background-color:#999999;">';
	echo '<input type="checkbox" name="confirmupdate" id="confirmupdate" value="1" title="confirm &rarr; update" />';
	F_submit_button('update', $l['w_update'], $l['h_update']);
	echo '</span>';
	F_submit_button('delete', $l['w_delete'], $l['h_delete']);
} else {
	F_submit_button('add', $l['w_add'], $l['h_add']);
}
F_submit_button('clear', $l['w_clear'], $l['h_clear']);

echo '</div>'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '&nbsp;'.K_NEWLINE;

// comma separated list of required fields
echo '<input type="hidden" name="ff_required" id="ff_required" value="cab_color" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="'.htmlspecialchars($l['w_color'], ENT_COMPAT, $l['a_meta_charset']).'" />'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_connection_editor'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
