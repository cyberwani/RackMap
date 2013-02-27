<?php
//============================================================+
// File name   : tce_edit_racks.php
// Begin       : 2004-04-26
// Last Update : 2012-01-20
//
// Description : Edit racks.
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
 * Edit racks.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2004-04-27
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_SUITES;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_rack_editor'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_objects.php');
require_once('tce_functions_group_permissions.php');

$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
$userlevel = intval($_SESSION['session_user_level']);
$user_groups = F_getUserGroups($user_id);

// set default values
$dcn_perm = 0;
$sts_perm = 0;
$user_permissions = 0;
if (isset($_REQUEST['dcn_id'])) {
	$dcn_id = intval($_REQUEST['dcn_id']);
	$dcn_perm = F_getUserPermission($user_id, K_TABLE_DATACENTER_GROUPS, $dcn_id);
	if ($dcn_perm == 0) {
		F_print_error('ERROR', $l['m_not_authorized_to_edit']);
		$dcn_id = 0;
		$sts_id = 0;
		$rck_id = 0;
	} else {
		if (isset($_REQUEST['sts_id']) AND (!isset($_REQUEST['change_datacenter']) OR empty($_REQUEST['change_datacenter']))) {
			$sts_id = intval($_REQUEST['sts_id']);
			$sts_perm = F_getUserPermission($user_id, K_TABLE_SUITE_GROUPS, $sts_id);
			if ($sts_perm == 0) {
				F_print_error('ERROR', $l['m_not_authorized_to_edit']);
				$sts_id = 0;
				$rck_id = 0;
			} else {
				if (isset($_REQUEST['rck_id']) AND (!isset($_REQUEST['change_suite']) OR empty($_REQUEST['change_suite']))) {
					$rck_id = intval($_REQUEST['rck_id']);
					$user_permissions = F_getUserPermission($user_id, K_TABLE_RACK_GROUPS, $rck_id);
					if ($user_permissions == 0) {
						F_print_error('ERROR', $l['m_not_authorized_to_edit']);
						$rck_id = 0;
					}
				} else {
					$rck_id = 0;
				}
			}
		} else {
			$sts_id = 0;
			$rck_id = 0;
		}
	}
} else {
	$dcn_id = 0;
	$sts_id = 0;
	$rck_id = 0;
}

if (($sts_id == 0) AND isset($_REQUEST['rck_id']) AND ($_REQUEST['rck_id'] > 0)) {
	$rck_id = intval($_REQUEST['rck_id']);
	$sql = 'SELECT rck_sts_id FROM '.K_TABLE_RACKS.' WHERE rck_id='.$rck_id.' LIMIT 1';
	if ($r = F_db_query($sql, $db)) {
		if ($m = F_db_fetch_array($r)) {
			$sts_id = $m['rck_sts_id'];
		}
	} else {
		F_display_db_error();
	}
}

if (($dcn_id == 0) AND isset($sts_id) AND ($sts_id > 0)) {
	$sql = 'SELECT sts_dcn_id FROM '.K_TABLE_SUITES.' WHERE sts_id='.$sts_id.' LIMIT 1';
	if ($r = F_db_query($sql, $db)) {
		if ($m = F_db_fetch_array($r)) {
			$dcn_id = $m['sts_dcn_id'];
		}
	} else {
		F_display_db_error();
	}
}

if (isset($_REQUEST['rck_name'])) {
	$rck_name = $_REQUEST['rck_name'];
} else {
	$rck_name = '';
}
if (isset($_REQUEST['rck_description'])) {
	$rck_description = $_REQUEST['rck_description'];
} else {
	$rck_description = '';
}
if (isset($_REQUEST['rck_label'])) {
	$rck_label = $_REQUEST['rck_label'];
} else {
	$rck_label = '';
}
if (isset($_REQUEST['rck_tag'])) {
	$rck_tag = $_REQUEST['rck_tag'];
} else {
	$rck_tag = '';
}
if (isset($_REQUEST['rck_height'])) {
	$rck_height = intval($_REQUEST['rck_height']);
} else {
	$rck_height = 42;
}
if (isset($_REQUEST['rck_position_x'])) {
	$rck_position_x = intval($_REQUEST['rck_position_x']);
} else {
	$rck_position_x = 1;
}
if (isset($_REQUEST['rck_position_y'])) {
	$rck_position_y = intval($_REQUEST['rck_position_y']);
} else {
	$rck_position_y = 1;
}
if (isset($selectdatacenter)) {
	$changedatacenter = 1;
}
if (isset($selectsuite)) {
	$changesuite = 1;
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
		F_print_error('WARNING', $l['m_delete_confirm']);
		echo '<div class="confirmbox">'.K_NEWLINE;
		echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_delete">'.K_NEWLINE;
		echo '<div>'.K_NEWLINE;
		echo '<input type="hidden" name="rck_id" id="rck_id" value="'.$rck_id.'" />'.K_NEWLINE;
		echo '<input type="hidden" name="rck_name" id="rck_name" value="'.stripslashes($rck_name).'" />'.K_NEWLINE;
		echo '<input type="hidden" name="rck_sts_id" id="rck_sts_id" value="'.$sts_id.'" />'.K_NEWLINE;
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
		if ($forcedelete == $l['w_delete']) { //check if delete button has been pushed (redundant check)
			$sql = 'DELETE FROM '.K_TABLE_RACKS.' WHERE rck_id='.$rck_id.' AND rck_sts_id='.$sts_id.'';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$rck_id = FALSE;
				F_print_error('MESSAGE', $rck_name.': '.$l['m_deleted']);
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
			// check if name is unique for selected module
			if (!F_check_unique(K_TABLE_RACKS, 'rck_name=\''.F_escape_sql($rck_name).'\' AND rck_sts_id='.$sts_id.'', 'rck_id', $rck_id)) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE;
				F_stripslashes_formfields();
				break;
			}
			$sql = 'UPDATE '.K_TABLE_RACKS.' SET
				rck_name=\''.F_escape_sql($rck_name).'\',
				rck_description='.F_empty_to_null($rck_description).',
				rck_label='.F_empty_to_null($rck_label).',
				rck_tag='.F_empty_to_null($rck_tag).',
				rck_height='.$rck_height.',
				rck_position_x='.$rck_position_x.',
				rck_position_y='.$rck_position_y.'
				WHERE rck_id='.$rck_id.'';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				F_print_error('MESSAGE', $l['m_updated']);
			}
			// delete previous groups permissions
			if ($userlevel >= K_AUTH_ADMINISTRATOR) {
				$sql = 'DELETE FROM '.K_TABLE_RACK_GROUPS.' WHERE rkg_rck_id='.$rck_id;
			} else {
				$sql = 'DELETE FROM '.K_TABLE_RACK_GROUPS.' WHERE rkg_rck_id='.$rck_id.' AND rkg_group_id IN ('.implode(',', $user_groups).')';
			}
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			}
			// insert groups permissions
			if (!empty($perms)) {
				foreach ($perms as $group_id => $pval) {
					$sql = 'INSERT INTO '.K_TABLE_RACK_GROUPS.' (
						rkg_rck_id,
						rkg_group_id,
						rkg_permission
						) VALUES (
						'.$rck_id.',
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
		 if (($userlevel < K_AUTH_ADMINISTRATOR) AND (($sts_perm & 2) == 0)) {
			F_print_error('ERROR', $l['m_not_authorized_to_add_child']);
			F_stripslashes_formfields();
			break;
		}
		if ($formstatus = F_check_form_fields()) {
			// check if name is unique
			if (!F_check_unique(K_TABLE_RACKS, 'rck_name=\''.F_escape_sql($rck_name).'\' AND rck_sts_id='.$sts_id.'')) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			$sql = 'INSERT INTO '.K_TABLE_RACKS.' (
				rck_sts_id,
				rck_name,
				rck_description,
				rck_label,
				rck_tag,
				rck_height,
				rck_position_x,
				rck_position_y
				) VALUES (
				'.$sts_id.',
				\''.F_escape_sql($rck_name).'\',
				'.F_empty_to_null($rck_description).',
				'.F_empty_to_null($rck_label).',
				'.F_empty_to_null($rck_tag).',
				'.$rck_height.',
				'.$rck_position_x.',
				'.$rck_position_y.'
				)';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$rck_id = F_db_insert_id($db, K_TABLE_RACKS, 'rck_id');
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
					$sql = 'INSERT INTO '.K_TABLE_RACK_GROUPS.' (
						rkg_rck_id,
						rkg_group_id,
						rkg_permission
						) VALUES (
						'.$rck_id.',
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
		$rck_name = '';
		$rck_description = '';
		$rck_label = '';
		$rck_tag = '';
		$rck_height = 42;
		$rck_position_x = 1;
		$rck_position_y = 1;
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
			OR (isset($_REQUEST['change_suite']) AND !empty($_REQUEST['change_suite']))
			OR (!isset($rck_id)) OR empty($rck_id)) {
			$rck_id = 0;
			$rck_name = '';
			$rck_description = '';
			$rck_label = '';
			$rck_tag = '';
			$rck_height = 42;
		} else {
			$perms = F_getGroupsPermissions(K_TABLE_RACK_GROUPS, $rck_id);
			$sql = 'SELECT * FROM '.K_TABLE_RACKS.' WHERE rck_id='.$rck_id.' LIMIT 1';
			if ($r = F_db_query($sql, $db)) {
				if ($m = F_db_fetch_array($r)) {
					$rck_id = $m['rck_id'];
					$sts_id = $m['rck_sts_id'];
					$rck_name = $m['rck_name'];
					$rck_description = $m['rck_description'];
					$rck_label = $m['rck_label'];
					$rck_tag = $m['rck_tag'];
					$rck_height = $m['rck_height'];
					$rck_position_x = $m['rck_position_x'];
					$rck_position_y = $m['rck_position_y'];
				} else {
					$rck_name = '';
					$rck_description = '';
					$rck_label = '';
					$rck_tag = '';
					$rck_height = 42;
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
echo F_select_suite($dcn_id, $sts_id, $suite_data, false);
echo F_select_rack($dcn_id, $sts_id, $rck_id, $rack_data, $rack_pos, $rack_name, true);

echo '<div class="row"><hr /></div>'.K_NEWLINE;

echo getFormRowTextInput('rck_name', $l['w_name'], $l['h_rack_name'], '', $rck_name, '', 255, false, false, false, '');
echo getFormRowTextBox('rck_description', $l['w_description'], $l['h_rack_description'], $rck_description, false, '');
echo getFormRowTextInput('rck_label', $l['w_label'], $l['h_rack_label'], '', $rck_label, '', 255, false, false, false, '');
echo getFormRowTextInput('rck_tag', $l['w_tag'], $l['h_rack_tag'], '', $rck_tag, '', 255, false, false, false, '');
echo getFormRowTextInput('rck_height', $l['w_height'], $l['h_rack_height'], '', $rck_height, '', 255, false, false, false, '');
echo getFormRowTextInput('rck_position_x', $l['w_position_x'], $l['h_rack_pos_x'], '', $rck_position_x, '', 255, false, false, false, '');
echo getFormRowTextInput('rck_position_y', $l['w_position_y'], $l['h_rack_pos_y'], '', $rck_position_y, '', 255, false, false, false, '');

// -----------------------------------------------------------------------------

// group permissions
echo '<fieldset class="subset" style="text-align:left;">'.K_NEWLINE;
echo '<legend>'.$l['t_permissions'].'</legend>'.K_NEWLINE;
echo F_groupsPermsSelector($perms, false);
echo '</fieldset>'.K_NEWLINE;

// -----------------------------------------------------------------------------

echo '<div class="row">'.K_NEWLINE;

// show buttons by case
if (isset($rck_id) AND ($rck_id > 0)) {
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
	if (($userlevel >= K_AUTH_ADMINISTRATOR) OR (($sts_perm & 2) > 0)) {
		F_submit_button('add', $l['w_add'], $l['h_add']);
	}
}
F_submit_button('clear', $l['w_clear'], $l['h_clear']);

echo '</div>'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="left">'.K_NEWLINE;
echo '&nbsp;'.K_NEWLINE;

if (isset($sts_id) AND ($sts_id > 0)) {
	echo '<a href="tce_edit_suites.php?sts_id='.$sts_id.'" title="'.$l['t_suite_editor'].'" class="xmlbutton">&lt; '.$l['t_suite_editor'].'</a>';
}

echo '</span>'.K_NEWLINE;
echo '&nbsp;'.K_NEWLINE;
// comma separated list of required fields
echo '<input type="hidden" name="ff_required" id="ff_required" value="rck_name" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="'.htmlspecialchars($l['w_name'], ENT_COMPAT, $l['a_meta_charset']).'" />'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_rack_editor'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
