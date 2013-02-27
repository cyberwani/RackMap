<?php
//============================================================+
// File name   : tce_edit_suites.php
// Begin       : 2004-04-26
// Last Update : 2012-01-20
//
// Description : Edit datacenter suites.
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
 * Edit datacenter suites.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2004-04-27
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_SUITES;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_suite_editor'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_objects.php');
require_once('tce_functions_group_permissions.php');

$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
$userlevel = intval($_SESSION['session_user_level']);
$user_groups = F_getUserGroups($user_id);

$dcn_perm = 0;
$user_permissions = 0;
// selected or default datacenter and suite
if (isset($_REQUEST['dcn_id'])) {
	$dcn_id = intval($_REQUEST['dcn_id']);
	$dcn_perm = F_getUserPermission($user_id, K_TABLE_DATACENTER_GROUPS, $dcn_id);
	if ($dcn_perm == 0) {
		F_print_error('ERROR', $l['m_not_authorized_to_edit']);
		$dcn_id = 0;
		$sts_id = 0;
	} else {
		if (isset($_REQUEST['sts_id']) AND (!isset($_REQUEST['change_datacenter']) OR empty($_REQUEST['change_datacenter']))) {
			$sts_id = intval($_REQUEST['sts_id']);
			$user_permissions = F_getUserPermission($user_id, K_TABLE_SUITE_GROUPS, $sts_id);
			if ($user_permissions == 0) {
				F_print_error('ERROR', $l['m_not_authorized_to_edit']);
				$sts_id = 0;
			}
		} else {
			$sts_id = 0;
			
		}
	}
} else {
	$dcn_id = 0;
	$sts_id = 0;
}

if (($dcn_id == 0) AND isset($_REQUEST['sts_id']) AND ($_REQUEST['sts_id'] > 0)) {
	$sts_id = intval($_REQUEST['sts_id']);
	$sql = 'SELECT sts_dcn_id FROM '.K_TABLE_SUITES.' WHERE sts_id='.$sts_id.' LIMIT 1';
	if ($r = F_db_query($sql, $db)) {
		if ($m = F_db_fetch_array($r)) {
			$dcn_id = $m['sts_dcn_id'];
		}
	} else {
		F_display_db_error();
	}
}

if (isset($_REQUEST['sts_name'])) {
	$sts_name = $_REQUEST['sts_name'];
} else {
	$sts_name = '';
}
if (isset($_REQUEST['sts_description'])) {
	$sts_description = $_REQUEST['sts_description'];
} else {
	$sts_description = '';
}
if (isset($_REQUEST['sts_floor'])) {
	$sts_floor = intval($_REQUEST['sts_floor']);
} else {
	$sts_floor = 0;
}
if (isset($_REQUEST['sts_width'])) {
	$sts_width = intval($_REQUEST['sts_width']);
} else {
	$sts_width = 30;
}
if (isset($_REQUEST['sts_height'])) {
	$sts_height = intval($_REQUEST['sts_height']);
} else {
	$sts_height = 30;
}

// group permissions
$num_perms = 4;
$perms = array();
$sql = 'SELECT group_id FROM '.K_TABLE_GROUPS.' ORDER BY group_name';
if ($r = F_db_query($sql, $db)) {
	while ($m = F_db_fetch_array($r)) {
		if (($userlevel >= K_AUTH_ADMINISTRATOR) OR in_array($m['group_id'], $user_groups)) {
			$fieldname ='group_perm_'.$m['group_id'].'_';
			$permsum = 0;
			for ($i = 0; $i < $num_perms; ++$i) {
				$varname = $fieldname.$i;
				if (isset($_REQUEST[$varname]) AND (intval($_REQUEST[$varname]) > 0)) {
					$permsum += pow(2,$i);
				}
			}
			if ($permsum > 0) {
				// fix permissions
				if ($permsum > 1) {
					$permsum |= 1;
				}
				$perms[$m['group_id']] = $permsum;
			}
		}
	}
} else {
	F_display_db_error();
}

switch($menu_mode) {

	case 'delete':{
		F_stripslashes_formfields(); // ask confirmation
		if (($userlevel < K_AUTH_ADMINISTRATOR) AND (($user_permissions & 8) == 0)) {
			break;
		}
		F_print_error('WARNING', $l['m_delete_confirm']);
		echo '<div class="confirmbox">'.K_NEWLINE;
		echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_delete">'.K_NEWLINE;
		echo '<div>'.K_NEWLINE;
		echo '<input type="hidden" name="sts_id" id="sts_id" value="'.$sts_id.'" />'.K_NEWLINE;
		echo '<input type="hidden" name="sts_name" id="sts_name" value="'.stripslashes($sts_name).'" />'.K_NEWLINE;
		echo '<input type="hidden" name="sts_dcn_id" id="sts_dcn_id" value="'.$dcn_id.'" />'.K_NEWLINE;
		F_submit_button('forcedelete', $l['w_delete'], $l['h_delete']);
		F_submit_button('cancel', $l['w_cancel'], $l['h_cancel']);
		echo '</div>'.K_NEWLINE;
		echo '</form>'.K_NEWLINE;
		echo '</div>'.K_NEWLINE;
		break;
	}

	case 'forcedelete':{
		F_stripslashes_formfields();
		if (($userlevel < K_AUTH_ADMINISTRATOR) AND (($user_permissions & 8) == 0)) {
			break;
		}
		if ($forcedelete == $l['w_delete']) { //check if delete button has been pushed (redundant check)
			$sql = 'DELETE FROM '.K_TABLE_SUITES.' WHERE sts_id='.$sts_id.' AND sts_dcn_id='.$dcn_id.'';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$sts_id=FALSE;
				F_print_error('MESSAGE', $sts_name.': '.$l['m_deleted']);
			}
		}
		break;
	}

	case 'update':{ // Update
		if (($userlevel < K_AUTH_ADMINISTRATOR) AND (($user_permissions & 4) == 0)) {
			F_stripslashes_formfields();
			break;
		}
		// check if the confirmation chekbox has been selected
		if (!isset($_REQUEST['confirmupdate']) OR ($_REQUEST['confirmupdate'] != 1)) {
			F_print_error('WARNING', $l['m_form_missing_fields'].': '.$l['w_confirm'].' &rarr; '.$l['w_update']);
			F_stripslashes_formfields();
			break;
		}
		if ($formstatus = F_check_form_fields()) {
			// check if name is unique for selected module
			if (!F_check_unique(K_TABLE_SUITES, 'sts_name=\''.F_escape_sql($sts_name).'\' AND sts_dcn_id='.$dcn_id.'', 'sts_id', $sts_id)) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE;
				F_stripslashes_formfields();
				break;
			}
			$sql = 'UPDATE '.K_TABLE_SUITES.' SET
				sts_name=\''.F_escape_sql($sts_name).'\',
				sts_description='.F_empty_to_null($sts_description).',
				sts_floor='.$sts_floor.',
				sts_width='.$sts_width.',
				sts_height='.$sts_height.'
				WHERE sts_id='.$sts_id.'';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				F_print_error('MESSAGE', $l['m_updated']);
			}
			// delete previous groups permissions
			if ($userlevel >= K_AUTH_ADMINISTRATOR) {
				$sql = 'DELETE FROM '.K_TABLE_SUITE_GROUPS.' WHERE stg_sts_id='.$sts_id;
			} else {
				$sql = 'DELETE FROM '.K_TABLE_SUITE_GROUPS.' WHERE stg_sts_id='.$sts_id.' AND stg_group_id IN ('.implode(',', $user_groups).')';
			}
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			}
			// insert groups permissions
			if (!empty($perms)) {
				foreach ($perms as $group_id => $pval) {
					$sql = 'INSERT INTO '.K_TABLE_SUITE_GROUPS.' (
						stg_sts_id,
						stg_group_id,
						stg_permission
						) VALUES (
						'.$sts_id.',
						'.$group_id.',
						'.$pval.'
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
		if (($userlevel < K_AUTH_ADMINISTRATOR) AND (($dcn_perm & 2) == 0)) {
			F_print_error('ERROR', $l['m_not_authorized_to_add_child']);
			F_stripslashes_formfields();
			break;
		}
		if ($formstatus = F_check_form_fields()) {
			// check if name is unique
			if (!F_check_unique(K_TABLE_SUITES, 'sts_name=\''.F_escape_sql($sts_name).'\' AND sts_dcn_id='.$dcn_id.'')) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			$sql = 'INSERT INTO '.K_TABLE_SUITES.' (
				sts_dcn_id,
				sts_name,
				sts_description,
				sts_floor,
				sts_width,
				sts_height
				) VALUES (
				'.$dcn_id.',
				\''.F_escape_sql($sts_name).'\',
				'.F_empty_to_null($sts_description).',
				'.$sts_floor.',
				'.$sts_width.',
				'.$sts_height.'
				)';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$sts_id = F_db_insert_id($db, K_TABLE_SUITES, 'sts_id');
			}
			// add default permission for non administrators
			if (($userlevel < K_AUTH_ADMINISTRATOR) AND empty($perms)) {
				foreach ($user_groups as $grp) {
					$perms[$grp] = 15; // read + add + update + delete
				}
			}
			// insert groups permissions
			if (!empty($perms)) {
				foreach ($perms as $group_id => $pval) {
					$sql = 'INSERT INTO '.K_TABLE_SUITE_GROUPS.' (
						stg_sts_id,
						stg_group_id,
						stg_permission
						) VALUES (
						'.$sts_id.',
						'.$group_id.',
						'.$pval.'
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
		$sts_name = '';
		$sts_description = '';
		$sts_floor = 0;
		$sts_width = 30;
		$sts_height = 30;
		break;
	}

	default :{
		break;
	}

} //end of switch

// --- Initialize variables
if ($formstatus) {
	if ($menu_mode != 'clear') {
		if ((isset($_REQUEST['change_datacenter']) AND !empty($_REQUEST['change_datacenter']))
			OR (!isset($sts_id)) OR empty($sts_id)) {
			$sts_id = 0;
			$sts_name = '';
			$sts_description = '';
			$sts_floor = 0;
			$sts_width = 30;
			$sts_height = 30;
		} else {
			$perms = F_getGroupsPermissions(K_TABLE_SUITE_GROUPS, $sts_id);
			$sql = 'SELECT * FROM '.K_TABLE_SUITES.' WHERE sts_id='.$sts_id.' LIMIT 1';
			if ($r = F_db_query($sql, $db)) {
				if ($m = F_db_fetch_array($r)) {
					$sts_id = $m['sts_id'];
					$dcn_id = $m['sts_dcn_id'];
					$sts_name = $m['sts_name'];
					$sts_description = $m['sts_description'];
					$sts_floor = $m['sts_floor'];
					$sts_width = $m['sts_width'];
					$sts_height = $m['sts_height'];
				} else {
					$sts_name = '';
					$sts_description = '';
					$sts_floor = 0;
					$sts_width = 30;
					$sts_height = 30;
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

echo F_select_datacenter($dcn_id, $datacenter_data, false);
echo F_select_suite($dcn_id, $sts_id, $suite_data, true);

echo '<div class="row"><hr /></div>'.K_NEWLINE;

echo getFormRowTextInput('sts_name', $l['w_name'], $l['h_suite_name'], '', $sts_name, '', 255, false, false, false, '');
echo getFormRowTextBox('sts_description', $l['w_description'], $l['h_suite_description'], $sts_description, false, '');
echo getFormRowTextInput('sts_floor', $l['w_floor'], $l['h_suite_floor'], '', $sts_floor, '', 255, false, false, false, '');
echo getFormRowTextInput('sts_width', $l['w_width'], $l['h_suite_width'], '[# '.$l['w_racks'].']', $sts_width, '', 255, false, false, false, '');
echo getFormRowTextInput('sts_height', $l['w_height'], $l['h_suite_height'], '[# '.$l['w_racks'].']', $sts_height, '', 255, false, false, false, '');

// -----------------------------------------------------------------------------

// group permissions
echo '<fieldset class="subset" style="text-align:left;">'.K_NEWLINE;
echo '<legend>'.$l['t_permissions'].'</legend>'.K_NEWLINE;
echo F_groupsPermsSelector($perms, false);
echo '</fieldset>'.K_NEWLINE;

// -----------------------------------------------------------------------------

echo '<div class="row">'.K_NEWLINE;

// show buttons by case
if (isset($sts_id) AND ($sts_id > 0)) {
	if (($userlevel >= K_AUTH_ADMINISTRATOR) OR (($perms & 4) > 0)) {
		echo '<span style="background-color:#999999;">';
		echo '<input type="checkbox" name="confirmupdate" id="confirmupdate" value="1" title="confirm &rarr; update" />';
		F_submit_button('update', $l['w_update'], $l['h_update']);
		echo '</span>';
	}
	if (($userlevel >= K_AUTH_ADMINISTRATOR) OR (($perms & 8) > 0)) {
		F_submit_button('delete', $l['w_delete'], $l['h_delete']);
	}
} else {
	if (($userlevel >= K_AUTH_ADMINISTRATOR) OR (($dcn_perm & 2) > 0)) {
		F_submit_button('add', $l['w_add'], $l['h_add']);
	}
}
F_submit_button('clear', $l['w_clear'], $l['h_clear']);

echo '</div>'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="left">'.K_NEWLINE;
echo '&nbsp;'.K_NEWLINE;

if (isset($dcn_id) AND ($dcn_id > 0)) {
	echo '<a href="tce_edit_datacenters.php?dcn_id='.$dcn_id.'" title="'.$l['t_datacenter_editor'].'" class="xmlbutton">&lt; '.$l['t_datacenter_editor'].'</a>';
}

echo '</span>'.K_NEWLINE;
echo '<span class="right">'.K_NEWLINE;

if (isset($sts_id) AND ($sts_id > 0)) {
	echo '<a href="tce_edit_racks.php?sts_id='.$sts_id.'" title="'.$l['t_rack_editor'].'" class="xmlbutton">'.$l['t_rack_editor'].' &gt;</a>';
}

echo '&nbsp;'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '&nbsp;'.K_NEWLINE;
// comma separated list of required fields
echo '<input type="hidden" name="ff_required" id="ff_required" value="sts_name" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="'.htmlspecialchars($l['w_name'], ENT_COMPAT, $l['a_meta_charset']).'" />'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_suite_editor'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
