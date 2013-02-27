<?php
//============================================================+
// File name   : tce_edit_datacenters.php
// Begin       : 2011-10-31
// Last Update : 2012-01-20
//
// Description : Edit Datacenters.
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
 * Edit Datacenters.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-10-31
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_DATACENTERS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_datacenter_editor'];
require_once('../code/tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_objects.php');
require_once('tce_functions_group_permissions.php');

$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
$userlevel = intval($_SESSION['session_user_level']);
$user_groups = F_getUserGroups($user_id);

$user_permissions = 0;
if (isset($_REQUEST['dcn_id'])) {
	$dcn_id = intval($_REQUEST['dcn_id']);
	$user_permissions = F_getUserPermission($user_id, K_TABLE_DATACENTER_GROUPS, $dcn_id);
	if ($user_permissions == 0) {
		F_print_error('ERROR', $l['m_not_authorized_to_edit']);
		$dcn_id = 0;
	}
} else {
	$dcn_id = 0;
}
if (isset($_REQUEST['dcn_name'])) {
	$dcn_name = $_REQUEST['dcn_name'];
} else {
	$dcn_name = '';
}
if (isset($_REQUEST['dcn_description'])) {
	$dcn_description = $_REQUEST['dcn_description'];
} else {
	$dcn_description = '';
}
if (isset($_REQUEST['dcn_website_url'])) {
	$dcn_website_url = $_REQUEST['dcn_website_url'];
} else {
	$dcn_website_url = '';
}
if (isset($_REQUEST['dcn_map_url'])) {
	$dcn_map_url = $_REQUEST['dcn_map_url'];
} else {
	$dcn_map_url = '';
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

switch($menu_mode) { // process submitted data

	case 'delete':{
		F_stripslashes_formfields(); // ask confirmation
		if (($userlevel < K_AUTH_ADMINISTRATOR) AND (($user_permissions & 8) == 0)) {
			break;
		}
		F_print_error('WARNING', $l['m_delete_confirm']);
		echo '<div class="confirmbox">'.K_NEWLINE;
		echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_delete">'.K_NEWLINE;
		echo '<div>'.K_NEWLINE;
		echo '<input type="hidden" name="dcn_id" id="dcn_id" value="'.$dcn_id.'" />'.K_NEWLINE;
		echo '<input type="hidden" name="dcn_name" id="dcn_name" value="'.stripslashes($dcn_name).'" />'.K_NEWLINE;
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
		if($forcedelete == $l['w_delete']) { //check if delete button has been pushed (redundant check)
			$sql = 'DELETE FROM '.K_TABLE_DATACENTERS.' WHERE dcn_id='.$dcn_id.'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$dcn_id=FALSE;
				F_print_error('MESSAGE', '['.stripslashes($dcn_name).'] '.$l['m_deleted']);
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
		if($formstatus = F_check_form_fields()) {
			// check if name is unique
			if(!F_check_unique(K_TABLE_DATACENTERS, 'dcn_name=\''.F_escape_sql($dcn_name).'\'', 'dcn_id', $dcn_id)) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			$sql = 'UPDATE '.K_TABLE_DATACENTERS.' SET
				dcn_name=\''.F_escape_sql($dcn_name).'\',
				dcn_description='.F_empty_to_null($dcn_description).',
				dcn_website_url='.F_empty_to_null($dcn_website_url).',
				dcn_map_url='.F_empty_to_null($dcn_map_url).'
				WHERE dcn_id='.$dcn_id.'';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				F_print_error('MESSAGE', $dcn_name.': '.$l['m_updated']);
			}
			// delete previous groups permissions
			if ($userlevel >= K_AUTH_ADMINISTRATOR) {
				$sql = 'DELETE FROM '.K_TABLE_DATACENTER_GROUPS.' WHERE dcg_dcn_id='.$dcn_id;
			} else {
				$sql = 'DELETE FROM '.K_TABLE_DATACENTER_GROUPS.' WHERE dcg_dcn_id='.$dcn_id.' AND dcg_group_id IN ('.implode(',', $user_groups).')';
			}
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			}
			// insert groups permissions
			if (!empty($perms)) {
				foreach ($perms as $group_id => $pval) {
					$sql = 'INSERT INTO '.K_TABLE_DATACENTER_GROUPS.' (
						dcg_dcn_id,
						dcg_group_id,
						dcg_permission
						) VALUES (
						'.$dcn_id.',
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
		if($formstatus = F_check_form_fields()) { // check submitted form fields
			// check if name is unique
			if(!F_check_unique(K_TABLE_DATACENTERS, 'dcn_name=\''.F_escape_sql($dcn_name).'\'')) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			$sql = 'INSERT INTO '.K_TABLE_DATACENTERS.' (
				dcn_name,
				dcn_description,
				dcn_website_url,
				dcn_map_url
				) VALUES (
				\''.F_escape_sql($dcn_name).'\',
				'.F_empty_to_null($dcn_description).',
				'.F_empty_to_null($dcn_website_url).',
				'.F_empty_to_null($dcn_map_url).'
				
				)';
			if(!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$dcn_id = F_db_insert_id($db, K_TABLE_DATACENTERS, 'dcn_id');
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
					$sql = 'INSERT INTO '.K_TABLE_DATACENTER_GROUPS.' (
						dcg_dcn_id,
						dcg_group_id,
						dcg_permission
						) VALUES (
						'.$dcn_id.',
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
		$dcn_name = '';
		$dcn_description = '';
		$dcn_website_url = '';
		$dcn_map_url = '';
		break;
	}

	default :{
		break;
	}

} //end of switch

// --- Initialize variables
if($formstatus) {
	if ($menu_mode != 'clear') {
		if(!isset($dcn_id) OR empty($dcn_id)) {
			$dcn_id = 0;
			$dcn_name = '';
		} else {
			$perms = F_getGroupsPermissions(K_TABLE_DATACENTER_GROUPS, $dcn_id);
			$sql = 'SELECT * FROM '.K_TABLE_DATACENTERS.' WHERE dcn_id='.$dcn_id.' LIMIT 1';
			if($r = F_db_query($sql, $db)) {
				if($m = F_db_fetch_array($r)) {
					$dcn_id = $m['dcn_id'];
					$dcn_name = $m['dcn_name'];
					$dcn_description = $m['dcn_description'];
					$dcn_website_url = $m['dcn_website_url'];
					$dcn_map_url = $m['dcn_map_url'];
				} else {
					$dcn_name = '';
					$dcn_description = '';
					$dcn_website_url = '';
					$dcn_map_url = '';
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

echo F_select_datacenter($dcn_id, $data, true);

echo '<div class="row"><hr /></div>'.K_NEWLINE;

echo getFormRowTextInput('dcn_name', $l['w_name'], $l['h_datacenter_name'], '', $dcn_name, '', 255, false, false, false, '');
echo getFormRowTextBox('dcn_description', $l['w_description'], $l['h_datacenter_description'], $dcn_description, false, '');
echo getFormRowTextInput('dcn_website_url', $l['w_website'], $l['h_website_url'], '', $dcn_website_url, '', 255, false, false, false, '');
echo getFormRowTextInput('dcn_map_url', $l['w_map_url'], $l['h_map_url'], '', $dcn_map_url, '', 255, false, false, false, '');

// -----------------------------------------------------------------------------

// group permissions
echo '<fieldset class="subset" style="text-align:left;">'.K_NEWLINE;
echo '<legend>'.$l['t_permissions'].'</legend>'.K_NEWLINE;
echo F_groupsPermsSelector($perms, false);
echo '</fieldset>'.K_NEWLINE;

// -----------------------------------------------------------------------------

echo '<div class="row">'.K_NEWLINE;

// show buttons by case
if (isset($dcn_id) AND ($dcn_id > 0)) {
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
	F_submit_button('add', $l['w_add'], $l['h_add']);
}
F_submit_button('clear', $l['w_clear'], $l['h_clear']);
echo '</div>'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '<span class="right">'.K_NEWLINE;
if (isset($dcn_id) AND ($dcn_id > 0)) {
	echo '<a href="tce_edit_suites.php?dcn_id='.$dcn_id.'" title="'.$l['t_suite_editor'].'" class="xmlbutton">'.$l['t_suite_editor'].' &gt;</a>';
}
echo '&nbsp;'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '&nbsp;'.K_NEWLINE;

// comma separated list of required fields
echo '<input type="hidden" name="ff_required" id="ff_required" value="dcn_name" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="'.htmlspecialchars($l['w_name'], ENT_COMPAT, $l['a_meta_charset']).'" />'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_datacenter_editor'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
