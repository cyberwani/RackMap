<?php
//============================================================+
// File name   : tce_edit_objects.php
// Begin       : 2011-10-31
// Last Update : 2012-12-14
//
// Description : Edit Objects.
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
 * Edit Objects.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-10-31
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_OBJECTS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_object_editor'];
$enable_calendar = true;
require_once('../code/tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_objects.php');
require_once('tce_functions_user_select.php');
require_once('tce_functions_sshauth.php');
require_once('tce_functions_group_permissions.php');

$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
$userlevel = intval($_SESSION['session_user_level']);
$user_groups = F_getUserGroups($user_id);

$user_permissions = 0;
if (isset($_REQUEST['obj_id'])) {
	$obj_id = intval($_REQUEST['obj_id']);
	$user_permissions = F_getUserPermission($user_id, K_TABLE_OBJECT_GROUPS, $obj_id);
	if ($user_permissions == 0) {
		F_print_error('ERROR', $l['m_not_authorized_to_edit']);
		$obj_id = 0;
	}
} else {
	$obj_id = 0;
}
if (isset($_REQUEST['obj_name'])) {
	$obj_name = $_REQUEST['obj_name'];
} else {
	$obj_name = '';
}
if (isset($_REQUEST['obj_description'])) {
	$obj_description = $_REQUEST['obj_description'];
} else {
	$obj_description = '';
}
if (isset($_REQUEST['obj_label'])) {
	$obj_label = $_REQUEST['obj_label'];
} else {
	$obj_label = '';
}
if (isset($_REQUEST['obj_tag'])) {
	$obj_tag = $_REQUEST['obj_tag'];
} else {
	$obj_tag = '';
}
if (isset($_REQUEST['obj_mnf_id'])) {
	$obj_mnf_id = intval($_REQUEST['obj_mnf_id']);
} else {
	$obj_mnf_id = 0;
}
if (isset($_REQUEST['obj_owner_id'])) {
	$obj_owner_id = intval($_REQUEST['obj_owner_id']);
} else {
	$obj_owner_id = 0;
}
if (isset($_REQUEST['obj_tenant_id'])) {
	$obj_tenant_id = intval($_REQUEST['obj_tenant_id']);
} else {
	$obj_tenant_id = 0;
}
if (isset($_REQUEST['omp_parent_obj_ids']) AND !empty($_REQUEST['omp_parent_obj_ids']) AND !in_array(0, $_REQUEST['omp_parent_obj_ids'])) {
	foreach ($_REQUEST['omp_parent_obj_ids'] as $k => $v) {
		$omp_parent_obj_ids[$k] = intval($v);
	}
} else {
	$omp_parent_obj_ids = array();
}
// location
if (isset($_REQUEST['loc_rack_id'])) {
	$loc_rack_id = intval($_REQUEST['loc_rack_id']);
} else {
	$loc_rack_id = 0;
}
if (isset($_REQUEST['loc_row_top'])) {
	$loc_row_top = intval($_REQUEST['loc_row_top']);
} else {
	$loc_row_top = 0;
}
if (isset($_REQUEST['loc_row_bottom'])) {
	$loc_row_bottom = intval($_REQUEST['loc_row_bottom']);
} else {
	$loc_row_bottom = 0;
}
if (isset($_REQUEST['loc_front'])) {
	$loc_front = intval($_REQUEST['loc_front']);
} else {
	$loc_front = false;
}
if (isset($_REQUEST['loc_center'])) {
	$loc_center = intval($_REQUEST['loc_center']);
} else {
	$loc_center = false;
}
if (isset($_REQUEST['loc_rear'])) {
	$loc_rear = intval($_REQUEST['loc_rear']);
} else {
	$loc_rear = false;
}
$rack_sides = array('-'=>'', 'left'=>$l['w_left'], 'right'=>$l['w_right'], 'top'=>$l['w_top'], 'bottom'=>$l['w_bottom']);
if (isset($_REQUEST['loc_side']) AND isset($rack_sides[$_REQUEST['loc_side']])) {
	$loc_side = $_REQUEST['loc_side'];
	if (($loc_side == 'left') OR ($loc_side == 'right')) {
		$loc_center = false;
		if ($loc_front === $loc_rear) {
			$loc_front = false;
			$loc_rear = true;
		}
	}
} else {
	$loc_side = '-';
}

// fix some values
if (!$loc_front AND !$loc_center AND !$loc_rear) {
	$loc_front = true;
} elseif ($loc_front AND !$loc_center AND $loc_rear) {
	$loc_center = true;
}

if (isset($_POST['addchild'])) {
	$menu_mode = 'addchild';
}
if (isset($_POST['clone'])) {
	$menu_mode = 'clone';
}
if (isset($_POST['updatessh'])) {
	$menu_mode = 'updatessh';
}

if (isset($_REQUEST['new_child_quantity'])) {
	$new_child_quantity = intval($_REQUEST['new_child_quantity']);
} else {
	$new_child_quantity = 0;
}
if (isset($_REQUEST['new_child_type'])) {
	$new_child_type = intval($_REQUEST['new_child_type']);
} else {
	$new_child_type = 0;
}
if (isset($_REQUEST['new_child_name'])) {
	$new_child_name = preg_replace('/[^a-zA-Z0-9\%]/', '', $_REQUEST['new_child_name']);
} else {
	$new_child_name = 'PTR%03d';
}
// connections
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
if (isset($_REQUEST['cab_a_cbt_id'])) {
	$cab_a_cbt_id = intval($_REQUEST['cab_a_cbt_id']);
} else {
	$cab_a_cbt_id = 0;
}
if (isset($_REQUEST['cab_b_cbt_id'])) {
	$cab_b_cbt_id = intval($_REQUEST['cab_b_cbt_id']);
} else {
	$cab_b_cbt_id = 0;
}
if (isset($_REQUEST['cab_a_color'])) {
	$cab_a_color = $_REQUEST['cab_a_color'];
} else {
	$cab_a_color = '';
}
if (isset($_REQUEST['cab_b_color'])) {
	$cab_b_color = $_REQUEST['cab_b_color'];
} else {
	$cab_b_color = '';
}

// group permissions
if ($userlevel >= K_AUTH_ADMINISTRATOR) {
	// only administrators are allowed to set IPMI and SSH permissions bits (5 and 6)
	$num_perms = 6;
} else {
	$num_perms = 4;
}
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

// get valid attributes for the specified object type
$attributes = array();
if (isset($_REQUEST['obj_obt_id'])) {
	$obj_obt_id = intval($_REQUEST['obj_obt_id']);
	$sql = 'SELECT * FROM '.K_TABLE_OBJECT_ATTRIBUTES_MAP.', '.K_TABLE_ATTRIBUTE_TYPES.' WHERE oam_atb_id=atb_id AND oam_obt_id='.$obj_obt_id.' ORDER BY atb_name';
	if ($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_array($r)) {
			$attributes[$m['atb_id']] = $m;
			$anum = sprintf('%03d', $m['atb_id']);
			$afield = 'atb_id_'.$anum;
			if (isset($_REQUEST[$afield])) {
				$$afield = F_get_attribute_value($m['atb_type'], $_REQUEST[$afield]);
			} else {
				// set default value
				$$afield = $m['atb_default'];
			}
		}
	} else {
		F_display_db_error();
	}
} else {
	$obj_obt_id = 1;
}

switch($menu_mode) { // process submitted data

	case 'addchild':{ // and new child objects
		F_stripslashes_formfields();
		if (($userlevel < K_AUTH_ADMINISTRATOR) AND (($user_permissions & 2) == 0)) {
			F_print_error('ERROR', $l['m_not_authorized_to_edit']);
			break;
		}
		if (!(($new_child_quantity > 0) AND ($new_child_type > 0) AND (strlen($new_child_name) > 0))) {
			break;
		}
		for ($i = 1; $i <= $new_child_quantity; ++$i) {
			$obj_name = F_escape_sql(sprintf($new_child_name, $i));
			// check if this child already exist
			$sqlus = 'SELECT obj_id FROM '.K_TABLE_OBJECTS.', '.K_TABLE_OBJECTS_MAP.' WHERE omp_child_obj_id=obj_id AND omp_parent_obj_id='.$obj_id.' AND obj_name=\''.$obj_name.'\' LIMIT 1';
			if ($rus = F_db_query($sqlus, $db)) {
				if (!F_db_fetch_assoc($rus)) {
					// add new child
					$sql = 'INSERT INTO '.K_TABLE_OBJECTS.' (
						obj_obt_id,
						obj_name
						) VALUES (
						'.$new_child_type.',
						\''.$obj_name.'\'
						)';
					if (!$r = F_db_query($sql, $db)) {
						F_display_db_error(false);
					} else {
						$cobj_id = F_db_insert_id($db, K_TABLE_OBJECTS, 'obj_id');
					}
					// update parent-child map
					$sql = 'INSERT INTO '.K_TABLE_OBJECTS_MAP.' (
						omp_parent_obj_id,
						omp_child_obj_id
						) VALUES (
						'.$obj_id.',
						'.$cobj_id.'
						)';
					if (!$r = F_db_query($sql, $db)) {
						F_display_db_error(false);
					}
				}
			} else {
				F_display_db_error();
			}
		}
		break;
	}

	case 'delete':{
		F_stripslashes_formfields(); // ask confirmation
		if (($userlevel < K_AUTH_ADMINISTRATOR) AND (($user_permissions & 8) == 0)) {
			F_print_error('ERROR', $l['m_not_authorized_to_edit']);
			break;
		}
		F_print_error('WARNING', $l['m_delete_confirm']);
		echo '<div class="confirmbox">'.K_NEWLINE;
		echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_delete">'.K_NEWLINE;
		echo '<div>'.K_NEWLINE;
		echo '<input type="hidden" name="obj_id" id="obj_id" value="'.$obj_id.'" />'.K_NEWLINE;
		echo '<input type="hidden" name="obj_name" id="obj_name" value="'.stripslashes($obj_name).'" />'.K_NEWLINE;
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
			F_print_error('ERROR', $l['m_not_authorized_to_edit']);
			break;
		}
		if ($forcedelete == $l['w_delete']) { //check if delete button has been pushed (redundant check)
			// delete child objects
			$sql = 'DELETE FROM '.K_TABLE_OBJECTS.' WHERE obj_id IN (SELECT omp_child_obj_id FROM '.K_TABLE_OBJECTS_MAP.' WHERE omp_parent_obj_id='.$obj_id.')';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			}
			// delete object
			$sql = 'DELETE FROM '.K_TABLE_OBJECTS.' WHERE obj_id='.$obj_id.'';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$obj_id=FALSE;
				F_print_error('MESSAGE', '['.stripslashes($obj_name).'] '.$l['m_deleted']);
			}
		}
		break;
	}

	case 'updatessh': // Update SSH keys on remote servers
	case 'update':{ // Update
		// check permission
		if ($userlevel < K_AUTH_ADMINISTRATOR) {
			if (($user_permissions & 4) == 0) {
				F_print_error('ERROR', $l['m_not_authorized_to_edit']);
				F_stripslashes_formfields();
				break;
			}
			// check permission to add a child
			if (!empty($omp_parent_obj_ids)) {
				$parentperm = 0;
				foreach ($omp_parent_obj_ids as $parent_obj_id) {
					//DEBUG
					$parentperm |= F_getUserPermission($user_id, K_TABLE_OBJECT_GROUPS, $obj_id);
				}
				if (($parentperm & 2) == 0) {
					F_print_error('ERROR', $l['m_not_authorized_to_edit']);
					F_stripslashes_formfields();
					break;
				}
			}
		}
		// check if the confirmation chekbox has been selected
		if (!isset($_REQUEST['confirmupdate']) OR ($_REQUEST['confirmupdate'] != 1)) {
			F_print_error('WARNING', $l['m_form_missing_fields'].': '.$l['w_confirm'].' &rarr; '.$l['w_update']);
			F_stripslashes_formfields();
			break;
		}
		if ($formstatus = F_check_form_fields()) {
			// check if name is unique
			/*
			if (!F_check_unique(K_TABLE_OBJECTS, 'obj_name=\''.F_escape_sql($obj_name).'\' AND obj_obt_id='.$obj_obt_id.'', 'obj_id', $obj_id)) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			*/
			$sql = 'UPDATE '.K_TABLE_OBJECTS.' SET
				obj_obt_id='.$obj_obt_id.',
				obj_name=\''.F_escape_sql($obj_name).'\',
				obj_description='.F_empty_to_null($obj_description).',
				obj_label='.F_empty_to_null($obj_label).',
				obj_tag='.F_empty_to_null($obj_tag).',
				obj_mnf_id='.F_zero_to_null($obj_mnf_id).',
				obj_owner_id='.F_zero_to_null($obj_owner_id).',
				obj_tenant_id='.F_zero_to_null($obj_tenant_id).'
				WHERE obj_id='.$obj_id.'';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				F_print_error('MESSAGE', $obj_name.': '.$l['m_updated']);
			}
			// update parent-child map
			$sql = 'DELETE FROM '.K_TABLE_OBJECTS_MAP.' WHERE omp_child_obj_id='.$obj_id.'';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			}
			// update location
			$sql = 'DELETE FROM '.K_TABLE_LOCATIONS.' WHERE loc_obj_id='.$obj_id.'';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			}
			if (!empty($omp_parent_obj_ids)) {
				foreach ($omp_parent_obj_ids as $parent_obj_id) {
					$sql = 'INSERT INTO '.K_TABLE_OBJECTS_MAP.' (
						omp_parent_obj_id,
						omp_child_obj_id
						) VALUES (
						'.$parent_obj_id.',
						'.$obj_id.'
						)';
					if (!$r = F_db_query($sql, $db)) {
						F_display_db_error(false);
					}
				}
			} elseif ($loc_rack_id > 0) {
				$sql = 'INSERT INTO '.K_TABLE_LOCATIONS.' (
					loc_obj_id,
					loc_rack_id,
					loc_row_top,
					loc_row_bottom,
					loc_front,
					loc_center,
					loc_rear,
					loc_side
					) VALUES (
					'.$obj_id.',
					'.$loc_rack_id.',
					'.$loc_row_top.',
					'.$loc_row_bottom.',
					\''.$loc_front.'\',
					\''.$loc_center.'\',
					\''.$loc_rear.'\',
					\''.$loc_side.'\'
					)';
				if (!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
				}
			}
			// attributes
			$sql = 'DELETE FROM '.K_TABLE_ATTRIBUTE_VALUES.' WHERE atv_obj_id='.$obj_id.'';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			}
			foreach ($attributes as $k => $m) {
				$anum = sprintf('%03d', $m['atb_id']);
				$afield = 'atb_id_'.$anum;
				$sqla = 'INSERT INTO '.K_TABLE_ATTRIBUTE_VALUES.' (
					atv_obj_id,
					atv_atb_id,
					atv_value
					) VALUES (
					'.$obj_id.',
					'.$m['atb_id'].',
					\''.F_escape_sql($$afield).'\'
					)';
				if (!$ra = F_db_query($sqla, $db)) {
					F_display_db_error(false);
				}
			}
			// delete existing connections
			$sql = 'DELETE FROM '.K_TABLE_CABLES.' WHERE (cab_a_obj_id='.$obj_id.' OR cab_b_obj_id='.$obj_id.')';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			}
			// first connection
			if (isset($cab_a_obj_id) AND ($cab_a_obj_id > 0)) {
				$sql = 'INSERT INTO '.K_TABLE_CABLES.' (
					cab_a_obj_id,
					cab_b_obj_id,
					cab_cbt_id,
					cab_color
					) VALUES (
					'.$obj_id.',
					'.$cab_a_obj_id.',
					'.$cab_a_cbt_id.',
					\''.F_escape_sql($cab_a_color).'\'
					)';
				if (!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
				}
			}
			// second connection (used for patch panel port)
			if (isset($cab_b_obj_id) AND ($cab_b_obj_id > 0)) {
				$sql = 'INSERT INTO '.K_TABLE_CABLES.' (
					cab_a_obj_id,
					cab_b_obj_id,
					cab_cbt_id,
					cab_color
					) VALUES (
					'.$obj_id.',
					'.$cab_b_obj_id.',
					'.$cab_b_cbt_id.',
					\''.F_escape_sql($cab_b_color).'\'
					)';
				if (!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
				}
			}
			// delete previous groups permissions
			if ($userlevel >= K_AUTH_ADMINISTRATOR) {
				$sql = 'DELETE FROM '.K_TABLE_OBJECT_GROUPS.' WHERE obg_obj_id='.$obj_id;
			} else {
				$sql = 'DELETE FROM '.K_TABLE_OBJECT_GROUPS.' WHERE obg_obj_id='.$obj_id.' AND obj_group_id IN ('.implode(',', $user_groups).')';
			}
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			}
			// insert groups
			$newkeys = '';
			// insert groups permissions
			if (!empty($perms)) {
				foreach ($perms as $group_id => $pval) {
					if ($userlevel < K_AUTH_ADMINISTRATOR) {
						// non-administrator cannot increase their own level
						$pval &= $user_permissions;
					}
					$sql = 'INSERT INTO '.K_TABLE_OBJECT_GROUPS.' (
						obg_obj_id,
						obg_group_id,
						obg_permission
						) VALUES (
						'.$obj_id.',
						'.$group_id.',
						'.$pval.'
						)';
					if (!$r = F_db_query($sql, $db)) {
						F_display_db_error(false);
					}
					if (($userlevel >= K_AUTH_ADMINISTRATOR) AND (($pval & 32) > 0)) { // bit 6 is SSH permission
						// get the SSH keys of the users on this group
						$sqlg = 'SELECT user_email, user_sshkey FROM '.K_TABLE_USERS.', '.K_TABLE_USERGROUP.' WHERE usrgrp_user_id=user_id AND usrgrp_group_id='.$group_id.'';	
						if ($rg = F_db_query($sqlg, $db)) {
							while ($mg = F_db_fetch_array($rg)) {
								if (!empty($mg['user_sshkey'])) {
									$newkeys .= $mg['user_sshkey'].' '.$mg['user_email'].' RACKMAP_MANAGED'.K_NEWLINE;
								}
							}
						} else {
							F_display_db_error();
						}
					}
				}
			}
			if (($menu_mode == 'updatessh') AND (!empty($newkeys)) AND ($userlevel >= K_AUTH_ADMINISTRATOR)) {
				// update keys on remote server
				if (F_updateRemoteKeys($obj_id, $newkeys, false, ($userlevel < K_AUTH_ADMINISTRATOR)) === false) {
					F_print_error('WARNING', $l['m_unable_to_update_ssh_keys']);
				}
			}
		}
		break;
	}

	case 'add':
	case 'clone': { // Add
		$old_obj_id = $obj_id;
		if ($formstatus = F_check_form_fields()) { // check submitted form fields
			// check permission
			if ($userlevel < K_AUTH_ADMINISTRATOR) {
				// check permission
				if (!empty($omp_parent_obj_ids)) {
					$parentperm = 0;
					foreach ($omp_parent_obj_ids as $parent_obj_id) {
						//DEBUG
						$parentperm |= F_getUserPermission($user_id, K_TABLE_OBJECT_GROUPS, $obj_id);
					}
					if (($parentperm & 2) == 0) {
						F_print_error('ERROR', $l['m_not_authorized_to_edit']);
						F_stripslashes_formfields();
						break;
					} else {
						$perms = $parentperm;
					}
				}
			}
			
			// check if name is unique
			/*
			if (!F_check_unique(K_TABLE_OBJECTS, 'obj_name=\''.F_escape_sql($obj_name).'\' AND obj_obt_id='.$obj_obt_id.'')) {
				F_print_error('WARNING', $l['m_duplicate_name']);
				$formstatus = FALSE; F_stripslashes_formfields();
				break;
			}
			*/
			$sql = 'INSERT INTO '.K_TABLE_OBJECTS.' (
				obj_obt_id,
				obj_name,
				obj_description,
				obj_label,
				obj_tag,
				obj_mnf_id,
				obj_owner_id,
				obj_tenant_id
				) VALUES (
				'.$obj_obt_id.',
				\''.F_escape_sql($obj_name).'\',
				'.F_empty_to_null($obj_description).',
				'.F_empty_to_null($obj_label).',
				'.F_empty_to_null($obj_tag).',
				'.F_zero_to_null($obj_mnf_id).',
				'.F_zero_to_null($obj_owner_id).',
				'.F_zero_to_null($obj_tenant_id).'
				)';
			if (!$r = F_db_query($sql, $db)) {
				F_display_db_error(false);
			} else {
				$obj_id = F_db_insert_id($db, K_TABLE_OBJECTS, 'obj_id');
			}
			// update parent-child map
			if (!empty($omp_parent_obj_ids)) {
				foreach ($omp_parent_obj_ids as $parent_obj_id) {
					$sql = 'INSERT INTO '.K_TABLE_OBJECTS_MAP.' (
						omp_parent_obj_id,
						omp_child_obj_id
						) VALUES (
						'.$parent_obj_id.',
						'.$obj_id.'
						)';
					if (!$r = F_db_query($sql, $db)) {
						F_display_db_error(false);
					}
				}
			} elseif ($loc_rack_id > 0) { // location
				$sql = 'INSERT INTO '.K_TABLE_LOCATIONS.' (
					loc_obj_id,
					loc_rack_id,
					loc_row_top,
					loc_row_bottom,
					loc_front,
					loc_center,
					loc_rear,
					loc_side
					) VALUES (
					'.$obj_id.',
					'.$loc_rack_id.',
					'.$loc_row_top.',
					'.$loc_row_bottom.',
					\''.$loc_front.'\',
					\''.$loc_center.'\',
					\''.$loc_rear.'\',
					\''.$loc_side.'\'
					)';
				if (!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
				}
			}
			// attributes
			foreach ($attributes as $k => $m) {
				$anum = sprintf('%03d', $m['atb_id']);
				$afield = 'atb_id_'.$anum;
				$sqla = 'INSERT INTO '.K_TABLE_ATTRIBUTE_VALUES.' (
					atv_obj_id,
					atv_atb_id,
					atv_value
					) VALUES (
					'.$obj_id.',
					'.$m['atb_id'].',
					\''.F_escape_sql($$afield).'\'
					)';
				if (!$ra = F_db_query($sqla, $db)) {
					F_display_db_error(false);
				}
			}
			// first connection
			if (isset($cab_a_obj_id) AND ($cab_a_obj_id > 0)) {
				$sql = 'INSERT INTO '.K_TABLE_CABLES.' (
					cab_a_obj_id,
					cab_b_obj_id,
					cab_cbt_id,
					cab_color
					) VALUES (
					'.$obj_id.',
					'.$cab_a_obj_id.',
					'.$cab_a_cbt_id.',
					\''.F_escape_sql($cab_a_color).'\'
					)';
				if (!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
				}
			}
			// second connection (used for patch panel port)
			if (isset($cab_b_obj_id) AND ($cab_b_obj_id > 0)) {
				$sql = 'INSERT INTO '.K_TABLE_CABLES.' (
					cab_a_obj_id,
					cab_b_obj_id,
					cab_cbt_id,
					cab_color
					) VALUES (
					'.$obj_id.',
					'.$cab_b_obj_id.',
					'.$cab_b_cbt_id.',
					\''.F_escape_sql($cab_b_color).'\'
					)';
				if (!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
				}
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
					$sql = 'INSERT INTO '.K_TABLE_OBJECT_GROUPS.' (
						obg_obj_id,
						obg_group_id,
						obg_permission
						) VALUES (
						'.$obj_id.',
						'.$group_id.',
						'.$pval.'
						)';
					if (!$r = F_db_query($sql, $db)) {
						F_display_db_error(false);
					}
				}
			}
			// if the "clone" button has been pressed, then clone the child objects
			if ($menu_mode == 'clone') {
				F_clone_child_objects($old_obj_id, $obj_id);
			}
		}
		break;
	}

	case 'clear':{ // Clear form fields
		$obj_obt_id = 1;
		$obj_name = '';
		$obj_description = '';
		$obj_label = '';
		$obj_tag = '';
		$obj_mnf_id = 0;
		$obj_owner_id = 0;
		$obj_tenant_id = 0;
		$loc_rack_id = 0;
		$loc_row_top = 0;
		$loc_row_bottom = 0;
		$loc_front = true;
		$loc_center = true;
		$loc_rear = true;
		$loc_side = '-';
		$omp_parent_obj_ids = array();
		$cab_a_obj_id = 0;
		$cab_b_obj_id = 0;
		$cab_a_cbt_id = 1;
		$cab_b_cbt_id = 1;
		$cab_a_color = '';
		$cab_b_color = '';
		break;
	}

	default :{
		break;
	}

} //end of switch

// --- Initialize variables
if ($formstatus) {
	if ($menu_mode != 'clear') {
		if (!isset($obj_id) OR empty($obj_id)) {
			$obj_id = 0;
			$obj_name = '';
			$obj_description = '';
			$obj_label = '';
			$obj_tag = '';
			$obj_mnf_id = 0;
			$obj_owner_id = 0;
			$obj_tenant_id = 0;
			$loc_front = true;
			$loc_center = true;
			$loc_rear = true;
			$omp_parent_obj_ids = array();
			$cab_a_obj_id = 0;
			$cab_b_obj_id = 0;
			$cab_a_cbt_id = 1;
			$cab_b_cbt_id = 1;
			$cab_a_color = '';
			$cab_b_color = '';
			// set empty values for attributes
			$sql = 'SELECT * FROM '.K_TABLE_OBJECT_ATTRIBUTES_MAP.', '.K_TABLE_ATTRIBUTE_TYPES.' WHERE oam_atb_id=atb_id ORDER BY atb_name';
			if ($r = F_db_query($sql, $db)) {
				while ($m = F_db_fetch_array($r)) {
					$anum = sprintf('%03d', $m['atb_id']);
					$afield = 'atb_id_'.$anum;
					$$afield = $m['atb_default'];
				}
			} else {
				F_display_db_error();
			}
		} else {
			$perms = F_getGroupsPermissions(K_TABLE_OBJECT_GROUPS, $obj_id);
			$sql = 'SELECT * FROM '.K_TABLE_OBJECTS.' LEFT JOIN '.K_TABLE_LOCATIONS.' ON obj_id=loc_obj_id WHERE obj_id='.$obj_id.' LIMIT 1';
			if ($r = F_db_query($sql, $db)) {
				if ($m = F_db_fetch_array($r)) {
					$obj_id = $m['obj_id'];
					$obj_obt_id = $m['obj_obt_id'];
					$obj_name = $m['obj_name'];
					$obj_description = $m['obj_description'];
					$obj_label = $m['obj_label'];
					$obj_tag = $m['obj_tag'];
					$obj_mnf_id = $m['obj_mnf_id'];
					$obj_owner_id = $m['obj_owner_id'];
					$obj_tenant_id = $m['obj_tenant_id'];
					$loc_rack_id = $m['loc_rack_id'];
					$loc_row_top = $m['loc_row_top'];
					$loc_row_bottom = $m['loc_row_bottom'];
					$loc_front = F_getBoolean($m['loc_front']);
					$loc_center = F_getBoolean($m['loc_center']);
					$loc_rear = F_getBoolean($m['loc_rear']);
					$loc_side = $m['loc_side'];
					// get attributes
					$attributes = array();
					$sql = 'SELECT * FROM '.K_TABLE_OBJECT_ATTRIBUTES_MAP.', '.K_TABLE_ATTRIBUTE_TYPES.' WHERE oam_atb_id=atb_id AND oam_obt_id='.$obj_obt_id.' ORDER BY atb_name';
					if ($r = F_db_query($sql, $db)) {
						while ($m = F_db_fetch_array($r)) {
							$attributes[$m['atb_id']] = $m;
							$anum = sprintf('%03d', $m['atb_id']);
							$afield = 'atb_id_'.$anum;
							$$afield = '';
						}
					} else {
						F_display_db_error();
					}
					$sql = 'SELECT * FROM '.K_TABLE_ATTRIBUTE_VALUES.' WHERE atv_obj_id='.$obj_id.'';
					if ($r = F_db_query($sql, $db)) {
						while ($m = F_db_fetch_array($r)) {
							$anum = sprintf('%03d', $m['atv_atb_id']);
							$afield = 'atb_id_'.$anum;
							$$afield = $m['atv_value'];
						}
					} else {
						F_display_db_error();
					}
					// get parent object
					$omp_parent_obj_ids = array();
					$sqla = 'SELECT omp_parent_obj_id FROM '.K_TABLE_OBJECTS_MAP.' WHERE omp_child_obj_id='.$obj_id.'';
					if ($ra = F_db_query($sqla, $db)) {
						while ($ma = F_db_fetch_array($ra)) {
							$omp_parent_obj_ids[] = $ma['omp_parent_obj_id'];
						}
					} else {
						F_display_db_error();
					}
					// get connections
					$conn = array();
					$cab_a_obj_id = 0;
					$cab_b_obj_id = 0;
					$cab_a_cbt_id = 1;
					$cab_b_cbt_id = 1;
					$cab_a_color = '';
					$cab_b_color = '';
					$sqlc = 'SELECT * FROM '.K_TABLE_CABLES.' WHERE (cab_a_obj_id='.$obj_id.' OR cab_b_obj_id='.$obj_id.') ORDER BY cab_a_obj_id';
					if ($rc = F_db_query($sqlc, $db)) {
						while ($mc = F_db_fetch_assoc($rc)) {
							$conn[] = $mc;
						}
						if (isset($conn[0])) {
							if ($conn[0]['cab_b_obj_id'] == $obj_id) {
								$cab_a_obj_id = $conn[0]['cab_a_obj_id'];
							} else {
								$cab_a_obj_id = $conn[0]['cab_b_obj_id'];
							}
							$cab_a_cbt_id = $conn[0]['cab_cbt_id'];
							$cab_a_color = $conn[0]['cab_color'];
						}
						if (isset($conn[1])) {
							if ($conn[1]['cab_b_obj_id'] == $obj_id) {
								$cab_b_obj_id = $conn[1]['cab_a_obj_id'];
							} else {
								$cab_b_obj_id = $conn[1]['cab_b_obj_id'];
							}
							$cab_b_cbt_id = $conn[1]['cab_cbt_id'];
							$cab_b_color = $conn[1]['cab_color'];
						}
					} else {
						F_display_db_error();
					}
				} else {
					$obj_name = '';
					$obj_description = '';
					$obj_label = '';
					$obj_tag = '';
					$obj_mnf_id = 0;
					$obj_owner_id = 0;
					$obj_tenant_id = 0;
					$loc_front = true;
					$loc_center = true;
					$loc_rear = true;
					$omp_parent_obj_ids = array();
					$cab_a_obj_id = 0;
					$cab_b_obj_id = 0;
					$cab_a_color = '';
					$cab_b_color = '';
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

echo F_object_selector($obj_id, 'obj_id', $l['w_object'], true, true, empty($omp_parent_obj_id), false);
echo getFormNoscriptSelect('selectrecord');

if ($obj_id > 0) {
	// display a cliccable path
	echo '<div class="row">'.K_NEWLINE;
	echo '<span class="formw">'.K_NEWLINE;
	echo F_get_object_path($obj_id, true);
	echo '</span>'.K_NEWLINE;
	echo '</div>'.K_NEWLINE;
}

echo '<div class="row"><hr /></div>'.K_NEWLINE;

// object type
$virtual = false;
echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="obj_obt_id">'.$l['w_object_type'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="obj_obt_id" id="obj_obt_id" size="0" onchange="document.getElementById(\'form_editor\').submit()">'.K_NEWLINE;
$sql = 'SELECT * FROM '.K_TABLE_OBJECT_TYPES.' WHERE 1 ORDER BY obt_name ASC';
if ($r = F_db_query($sql, $db)) {
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['obt_id'].'"';
		if (strlen($m['obt_color']) == 6) {
			echo ' style="background-color:#'.$m['obt_color'].';color:#'.getContrastColor($m['obt_color']).'"';
		}
		if ($m['obt_id'] == $obj_obt_id) {
			echo ' selected="selected"';
			if (F_getBoolean($m['obt_virtual'])) {
				$virtual = true;
			}
		}
		echo '>';
		if (F_getBoolean($m['obt_virtual'])) {
			echo '&otimes; ';
		}
		echo htmlspecialchars($m['obt_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
	}
} else {
	echo '</select></span></div>'.K_NEWLINE;
	F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

// child of
echo F_parent_object_selector($omp_parent_obj_ids, 'omp_parent_obj_ids', $virtual);

// manufacturer
echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="obj_mnf_id">'.$l['w_manufacturer'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="obj_mnf_id" id="obj_mnf_id" size="0" style="width:20em;max-width:20em;" onchange="">'.K_NEWLINE;
echo '<option value="0" style="background-color:#009900;color:white;"';
if ($obj_mnf_id == 0) {
	echo ' selected="selected"';
}
echo '></option>'.K_NEWLINE;
$sql = 'SELECT * FROM '.K_TABLE_MANUFACTURES.' WHERE 1 ORDER BY mnf_name ASC';
if ($r = F_db_query($sql, $db)) {
	$lastch = '';
	while($m = F_db_fetch_array($r)) {
		$currentch = strtoupper($m['mnf_name'][0]);
		if ($currentch != $lastch) {
			echo '<option value="0" style="background-color:#003399;color:#ffffff;font-weight:bold;">'.$currentch.'</option>'.K_NEWLINE;
			$lastch = $currentch;
		}
		echo '<option value="'.$m['mnf_id'].'"';
		if ($m['mnf_id'] == $obj_mnf_id) {
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
$jsaction = 'selectWindow=window.open(\'tce_select_manuf_popup.php?cid=obj_mnf_id\', \'selectWindow\', \'dependent, height=600, width=800, menubar=no, resizable=yes, scrollbars=yes, status=no, toolbar=no\');return false;';
echo '<a href="#" onclick="'.$jsaction.'" class="xmlbutton" title="'.$l['w_select'].'">...</a>';

echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo F_get_user_selectbox($l['w_owner'], $obj_owner_id, 'obj_owner_id');
echo F_get_user_selectbox($l['w_tenant'], $obj_tenant_id, 'obj_tenant_id');

echo getFormRowTextInput('obj_name', $l['w_name'], $l['h_ojbect_name'], '', $obj_name, '', 255, false, false, false, '');
echo getFormRowTextInput('obj_label', $l['w_label'], $l['h_ojbect_label'], '', $obj_label, '', 255, false, false, false, '');
echo getFormRowTextInput('obj_tag', $l['w_tag'], $l['h_ojbect_tag'], '', $obj_tag, '', 255, false, false, false, '');
echo getFormRowTextBox('obj_description', $l['w_description'], $l['h_object_description'], $obj_description, false, '');

// *** attributes
echo '<fieldset class="subset" style="text-align:left;">'.K_NEWLINE;
echo '<legend>'.$l['w_attributes'].'</legend>'.K_NEWLINE;

$authlist = false;
foreach ($attributes as $k => $m) {
	$anum = sprintf('%03d', $m['atb_id']);
	$afield = 'atb_id_'.$anum;
	switch ($m['atb_type']) {
		case 'bool': {
			echo getFormRowCheckBox($afield, $m['atb_name'], $m['atb_description'], '', '1', $$afield, false, '');
			break;
		}
		case 'int': {
			echo getFormRowTextInput($afield, $m['atb_name'], $m['atb_description'], '', $$afield, '^([\+\-]?[0-9]*)$', 255, false, false, false, '');
			break;
		}
		case 'float': {
			echo getFormRowTextInput($afield, $m['atb_name'], $m['atb_description'], '', $$afield, '^([\+\-]?[0-9\.]*)$', 255, false, false, false, '');
			break;
		}
		case 'string': {
			echo getFormRowTextInput($afield, $m['atb_name'], $m['atb_description'], '', $$afield, '', 255, false, false, false, '');
			break;
		}
		case 'text': {
			echo getFormRowTextBox($afield, $m['atb_name'], $m['atb_description'], $$afield, false, '');
			break;
		}
		case 'date': {
			echo getFormRowTextInput($afield, $m['atb_name'], $m['atb_description'], '', $$afield, '', 255, true, false, false, '');
			break;
		}
		case 'datetime': {
			echo getFormRowTextInput($afield, $m['atb_name'], $m['atb_description'], '', $$afield, '', 255, false, true, false, '');
			break;
		}
		case 'password': {
			echo getFormRowTextInput($afield, $m['atb_name'], $m['atb_description'], '', $$afield, '', 255, false, false, true, '');
			break;
		}
	}
}

echo '</fieldset>'.K_NEWLINE;

// *** position
echo '<fieldset class="subset" style="text-align:left;">'.K_NEWLINE;
echo '<legend>'.$l['w_position'].'</legend>'.K_NEWLINE;

// rack ID
echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="loc_rack_id">'.$l['w_rack'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="loc_rack_id" id="loc_rack_id" size="0">'.K_NEWLINE;
echo '<option value="0" style="background-color:#009900;color:white;"';
if ($obj_id == 0) {
	echo ' selected="selected"';
}
echo '>-</option>'.K_NEWLINE;
$sql = 'SELECT dcn_name, sts_name, rck_name, rck_id
	FROM '.K_TABLE_DATACENTERS.', '.K_TABLE_SUITES.', '.K_TABLE_RACKS.'
	WHERE rck_sts_id=sts_id AND sts_dcn_id=dcn_id
	ORDER BY dcn_name, sts_name, rck_name ASC';
if ($r = F_db_query($sql, $db)) {
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['rck_id'].'"';
		if ($m['rck_id'] == $loc_rack_id) {
			echo ' selected="selected"';
		}
		echo '>'.htmlspecialchars($m['dcn_name'].' → '.$m['sts_name'].' → '.$m['rck_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
	}
} else {
	echo '</select></span></div>'.K_NEWLINE;
	F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo getFormRowSelectBox('loc_side', $l['w_side'], $l['h_rack_side'], '', $loc_side, $rack_sides, '');

$racks_slots = range(0, 50, 1);
echo getFormRowSelectBox('loc_row_top', $l['w_starting_row'], $l['h_rack_starting_row'], '', $loc_row_top, $racks_slots, '');
echo getFormRowSelectBox('loc_row_bottom', $l['w_ending_row'], $l['h_rack_ending_row'], '', $loc_row_bottom, $racks_slots, '');
echo getFormRowCheckBox('loc_front', $l['w_front'], $l['h_position_front'], '', '1', $loc_front, false, '');
echo getFormRowCheckBox('loc_center', $l['w_center'], $l['h_position_center'], '', '1', $loc_center, false, '');
echo getFormRowCheckBox('loc_rear', $l['w_rear'], $l['h_position_rear'], '', '1', $loc_rear, false, '');

echo '</fieldset>'.K_NEWLINE;

if (isset($obj_id) AND ($obj_id > 0)) {

	// *** list child ojbects ----------------------------------------------------

	echo '<fieldset class="subset" style="text-align:left;">'.K_NEWLINE;
	echo '<legend>'.$l['w_child_objects'].'</legend>'.K_NEWLINE;

	echo '<div>'.K_NEWLINE;
	$sql = 'SELECT obj_id, obj_name, obj_label, obj_tag FROM '.K_TABLE_OBJECTS.', '.K_TABLE_OBJECTS_MAP.' WHERE omp_child_obj_id=obj_id AND omp_parent_obj_id='.$obj_id.' ORDER BY obj_name ASC';
	if ($r = F_db_query($sql, $db)) {
		echo '<ul>'.K_NEWLINE;
		while($m = F_db_fetch_array($r)) {
			echo '<li><a href="'.$_SERVER['SCRIPT_NAME'].'?obj_id='.$m['obj_id'].'" title="'.$l['t_object_editor'].': '.$m['obj_name'].'">'.htmlspecialchars($m['obj_name'].' - '.$m['obj_label'].' - '.$m['obj_tag'], ENT_NOQUOTES, $l['a_meta_charset']).'</a></li>'.K_NEWLINE;
		}
		echo '</ul>'.K_NEWLINE;
	} else {
		F_display_db_error();
	}
	echo '</div>'.K_NEWLINE;

	// *** add child ojbects -----------------------------------------------------
	
	if (($userlevel >= K_AUTH_ADMINISTRATOR) OR (($perms & 2) > 0)) {
		echo '<table>'.K_NEWLINE;
		echo '<tr style="text-align:center;"><td>&nbsp;</td><td><label for="new_child_quantity">'.$l['w_quantity'].'</label></td><td><label for="new_child_type">'.$l['w_type'].'</label></td><td><label for="new_child_name">'.$l['w_name'].'</label></td><td>&nbsp;</td></tr>'.K_NEWLINE;
		echo '<tr>'.K_NEWLINE;
		echo '<td>'.$l['w_add_child_objects'].': </td>'.K_NEWLINE;
		echo '<td><input type="text" name="new_child_quantity" id="new_child_quantity" value="" size="3" maxlength="4" title="'.$l['w_quantity'].'" /></td>'.K_NEWLINE;
		echo '<td><select name="new_child_type" id="new_child_type" size="0">'.K_NEWLINE;
		$sql = 'SELECT * FROM '.K_TABLE_OBJECT_TYPES.' WHERE 1 ORDER BY obt_name ASC';
		if ($r = F_db_query($sql, $db)) {
			while($m = F_db_fetch_array($r)) {
				echo '<option value="'.$m['obt_id'].'">'.htmlspecialchars($m['obt_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
			}
		} else {
			echo '</select></td>'.K_NEWLINE;
			F_display_db_error();
		}
		echo '</select></td>'.K_NEWLINE;
		echo '<td><input type="text" name="new_child_name" id="new_child_name" value="PRT%02d" size="3" maxlength="255" title="'.$l['h_child_name_template'].'" /></td>'.K_NEWLINE;
		echo '<td><input type="submit" name="addchild" id="addchild" value="'.$l['w_add'].'" title="'.$l['w_add_child_objects'].'" /></td>'.K_NEWLINE;
		echo '</tr>'.K_NEWLINE;
		echo '</table>'.K_NEWLINE;
	}
	
	echo '</fieldset>'.K_NEWLINE;

	// *** connections -----------------------------------------------------------
	
	echo '<fieldset class="subset" style="text-align:left;">'.K_NEWLINE;
	echo '<legend>'.$l['w_connections'].'</legend>'.K_NEWLINE;
	
	echo '<fieldset class="subset" style="text-align:left;margin:10px;width:98%;">'.K_NEWLINE;
	echo '<legend>'.$l['w_connection_first'].'</legend>'.K_NEWLINE;
	echo F_object_selector($cab_a_obj_id, 'cab_a_obj_id', $l['w_object'], true, false, true, true);
	echo F_select_connection_type($cab_a_cbt_id, false, false, 'cab_a_cbt_id');
	echo F_select_color($cab_a_color, 'cab_a_color', $l['w_color'], $l['h_cable_color']);
	echo '</fieldset>'.K_NEWLINE;
	
	echo '<fieldset class="subset" style="text-align:left;margin:10px;width:98%;">'.K_NEWLINE;
	echo '<legend>'.$l['w_connection_second'].'</legend>'.K_NEWLINE;
	echo F_object_selector($cab_b_obj_id, 'cab_b_obj_id', $l['w_object'], true, false, true, true);
	echo F_select_connection_type($cab_b_cbt_id, false, false, 'cab_b_cbt_id');
	echo F_select_color($cab_b_color, 'cab_b_color', $l['w_color'], $l['h_cable_color']);
	echo '</fieldset>'.K_NEWLINE;
	
	echo '</fieldset>'.K_NEWLINE;
	
// -----------------------------------------------------------------------------
	// group permissions
	echo '<fieldset class="subset" style="text-align:left;">'.K_NEWLINE;
	echo '<legend>'.$l['t_permissions'].'</legend>'.K_NEWLINE;
	echo F_groupsPermsSelector($perms, ($num_perms > 4));
	echo '</fieldset>'.K_NEWLINE;
// -----------------------------------------------------------------------------

}

echo '<div class="row">'.K_NEWLINE;

// show buttons by case
if (isset($obj_id) AND ($obj_id > 0)) {
	if (($userlevel >= K_AUTH_ADMINISTRATOR) OR (($perms & 4) > 0)) {
		echo '<span style="background-color:#999999;">';
		echo '<input type="checkbox" name="confirmupdate" id="confirmupdate" value="1" title="confirm &rarr; update" />';
		F_submit_button('update', $l['w_update'], $l['h_update']);
		if ($userlevel >= K_AUTH_ADMINISTRATOR) {
			F_submit_button('updatessh', $l['w_update_ssh'], $l['h_update_ssh']);
		}
		echo '</span>';
	}
	F_submit_button('add', ''.$l['w_add'].'', $l['h_add']);
	F_submit_button('clone', ''.$l['w_clone'].'', $l['h_clone']);
	if (($userlevel >= K_AUTH_ADMINISTRATOR) OR (($perms & 8) > 0)) {
		F_submit_button('delete', $l['w_delete'], $l['h_delete']);
	}
} else {
	F_submit_button('add', $l['w_add'], $l['h_add']);
}
F_submit_button('clear', $l['w_clear'], $l['h_clear']);
echo '</div>'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '&nbsp;'.K_NEWLINE;

// comma separated list of required fields
echo '<input type="hidden" name="ff_required" id="ff_required" value="obj_name" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="'.htmlspecialchars($l['w_name'], ENT_COMPAT, $l['a_meta_charset']).'" />'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_object_editor'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

//the following code is used to avoid for submission triggered by laser barcode scanner
echo '<script type="text/javascript">'.K_NEWLINE;
echo '//<![CDATA['.K_NEWLINE;
// do this for all <input> <textarea> and <select> elements on the page.
echo 'input.addEventListener("keypress",function(event){if(event.which==\'10\'||event.which==\'13\'){event.preventDefault();}},false);'.K_NEWLINE;
echo '//]]>'.K_NEWLINE;
echo '</script>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
