<?php
//============================================================+
// File name   : tce_functions_group_permissions.php
// Begin       : 2012-01-18
// Last Update : 2012-12-13
//
// Description : Functions for setting group permissions to
//               access and manage objects, racks, suites and
//               datacenters.
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
 * Functions for setting group permissions to access and manage objects, racks, suites and datacenters.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2012-01-18
 */

/**
 * Diplay form fields to select groups and related permissions.
 * @param $data (array) Array of selected data (group_id is the key, permission number is the value).
 * @param $admin_mode (boolean) If true displays additional permission fields.
 * @param $readonly (boolean) If true displays fields in read-only mode.
 * @return (string) HTML code.
 */
function F_groupsPermsSelector($data=array(), $admin_mode=true, $readonly=false) {
	global $l, $db;
	require_once('../config/tce_config.php');
	$userlevel = intval($_SESSION['session_user_level']);
	if ($userlevel < K_AUTH_ADV_OPERATOR) {
		return '';
	}
	$permission_desc = array($l['h_perm_read'], $l['h_perm_add'], $l['h_perm_update'], $l['h_perm_delete'], $l['h_perm_ipmi'], $l['h_perm_ssh']);
	$out = '';
	// for each group
	$sql = 'SELECT * FROM '.K_TABLE_GROUPS.' ORDER BY group_name';
	if ($r = F_db_query($sql, $db)) {
		$out .= '<table style="margin-left:10px;font-size:95%;">'.K_NEWLINE;
		$out .= '<tr style="text-align:center;font-size:90%;color:#003366;">';
		$out .= '<th title="'.$l['w_group'].'" style="text-align:left;">'.$l['w_group'].'</th>';
		$out .= '<th title="'.$l['h_perm_read'].'" style="width:10%;">'.$l['w_read'].'</th>';
		$out .= '<th title="'.$l['h_perm_add'].'" style="width:10%;">'.$l['w_add'].'</th>';
		$out .= '<th title="'.$l['h_perm_update'].'" style="width:10%;">'.$l['w_update'].'</th>';
		$out .= '<th title="'.$l['h_perm_delete'].'" style="width:10%;">'.$l['w_delete'].'</th>';
		$num_perm_fields = 4;
		if ($admin_mode) {
			$out .= '<th title="'.$l['h_perm_ipmi'].'" style="width:10%;">'.$l['w_ipmi'].'</th>';
			$out .= '<th title="'.$l['h_perm_ssh'].'" style="width:10%;">'.$l['w_ssh'].'</th>';
			$num_perm_fields += 2;
		}
		$out .= '</tr>'.K_NEWLINE;
		while ($m = F_db_fetch_array($r)) {
			// one row for each group
			$out .= '<tr style="text-align:center;">';
			$out .= '<td style="text-align:left;"><a href="tce_edit_group.php?group_id='.$m['group_id'].'" title="'.$l['t_group_editor'].' ('.$m['group_id'].')">'.htmlspecialchars($m['group_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</a></td>'.K_NEWLINE;
			$fieldname ='group_perm_'.$m['group_id'].'_';
			for ($i = 0; $i < $num_perm_fields; ++$i) {
				$out .= '<td><input type="checkbox" name="'.$fieldname.$i.'" id="'.$fieldname.$i.'" value="1" title="'.$permission_desc[$i].' ('.$m['group_id'].'-'.$i.')"';
				if (isset($data[$m['group_id']]) AND (($data[$m['group_id']] & pow(2,$i)) > 0)) {
					$out .= ' checked="checked"';
				}
				if ($readonly) {
					$out .= ' disabled="disabled"';
				}
				$out .= ' /></td>'.K_NEWLINE;
			}
			$out .= '</tr>'.K_NEWLINE;
		}
		$out .= '</table>'.K_NEWLINE;
	} else {
		F_display_db_error();
	}
	return $out;
}

/**
 * Return the group permission for the selected record.
 * @param $table (string) Database table name.
 * @param $record_id (int) Table record ID.
 * @return (array) Groups with relative permissions.
 */
function F_getGroupsPermissions($table, $record_id) {
	global $l, $db;
	require_once('../config/tce_config.php');
	$perms = array();
	// check table name
	switch ($table) {
		case K_TABLE_OBJECT_GROUPS: {
			$record_column = 'obg_obj_id';
			$group_column = 'obg_group_id';
			$permission_column = 'obg_permission';
			break;
		}
		case K_TABLE_RACK_GROUPS: {
			$record_column = 'rkg_rck_id';
			$group_column = 'rkg_group_id';
			$permission_column = 'rkg_permission';
			break;
		}
		case K_TABLE_SUITE_GROUPS: {
			$record_column = 'stg_sts_id';
			$group_column = 'stg_group_id';
			$permission_column = 'stg_permission';
			break;
		}
		case K_TABLE_DATACENTER_GROUPS: {
			$record_column = 'dcg_dcn_id';
			$group_column = 'dcg_group_id';
			$permission_column = 'dcg_permission';
			break;
		}
		default: {
			return 0;
		}
	}
	// get user permissions
	$sql = 'SELECT '.$group_column.', '.$permission_column.' FROM '.$table.' WHERE '.$record_column.'='.$record_id.'';
	if ($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_array($r)) {
			$perms[$m[$group_column]] = $m[$permission_column];
		}
	} else {
		F_display_db_error();
	}
	return $perms;
}

/**
 * Returns a descriptions of group permisssions.
 * @param $perms (array) Array of group permissions.
 * @return (array) Groups with relative permissions.
 */
function F_getGroupsPermsDesc($perms) {
	global $l, $db;
	require_once('../config/tce_config.php');
	$desc = array();
	if (empty($perms)) {
		return $desc;
	}
	foreach ($perms as $group_id => $permval) {
		// get user permissions
		$sql = 'SELECT group_name FROM '.K_TABLE_GROUPS.' WHERE group_id='.$group_id.' LIMIT 1';
		if ($r = F_db_query($sql, $db)) {
			if ($m = F_db_fetch_array($r)) {
				$desc[$group_id] = $m['group_name'].' ('.$permval.'):';
				// permission names
				if (($permval & 1) > 0) {
					$desc[$group_id] .= '[READ]';
				}
				if (($permval & 2) > 0) {
					$desc[$group_id] .= '[ADD]';
				}
				if (($permval & 4) > 0) {
					$desc[$group_id] .= '[UPDATE]';
				}
				if (($permval & 8) > 0) {
					$desc[$group_id] .= '[DELETE]';
				}
				if (($permval & 16) > 0) {
					$desc[$group_id] .= '[IPMI]';
				}
				if (($permval & 32) > 0) {
					$desc[$group_id] .= '[SSH]';
				}
			}
		} else {
			F_display_db_error();
		}
	}
	return $desc;
}

/**
 * Return the user permission for the selected record.
 * @param $user_id (int) User ID.
 * @param $table (string) Database table name.
 * @param $record_id (int) Table record ID.
 * @return (int) Permission value (bits: 1=read, 2=add, 3=update, 4=delete, 5=IPMI, 6=SSH).
 */
function F_getUserPermission($user_id, $table, $record_id) {
	global $l, $db;
	require_once('../config/tce_config.php');
	$permission = 0;
	// check table name
	switch ($table) {
		case K_TABLE_OBJECT_GROUPS: {
			$record_column = 'obg_obj_id';
			$group_column = 'obg_group_id';
			$permission_column = 'obg_permission';
			break;
		}
		case K_TABLE_RACK_GROUPS: {
			$record_column = 'rkg_rck_id';
			$group_column = 'rkg_group_id';
			$permission_column = 'rkg_permission';
			break;
		}
		case K_TABLE_SUITE_GROUPS: {
			$record_column = 'stg_sts_id';
			$group_column = 'stg_group_id';
			$permission_column = 'stg_permission';
			break;
		}
		case K_TABLE_DATACENTER_GROUPS: {
			$record_column = 'dcg_dcn_id';
			$group_column = 'dcg_group_id';
			$permission_column = 'dcg_permission';
			break;
		}
		default: {
			return 0;
		}
	}
	// check if user is an administrator
	$sql = 'SELECT user_level FROM '.K_TABLE_USERS.' WHERE user_id='.$user_id.' LIMIT 1';
	if ($r = F_db_query($sql, $db)) {
		if ($m = F_db_fetch_array($r)) {
			if ($m['user_level'] >= K_AUTH_ADMINISTRATOR) {
				// return maximum permission value (including reserved bits)
				return 255;
			}
		}
	} else {
		F_display_db_error();
	}
	// get user permissions
	$sql = 'SELECT '.$permission_column.'
		FROM '.K_TABLE_USERGROUP.', '.K_TABLE_GROUPS.', '.$table.'
		WHERE usrgrp_user_id='.$user_id.' AND group_id=usrgrp_group_id AND '.$group_column.'=group_id AND '.$record_column.'='.$record_id.'';
	if ($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_array($r)) {
			$permission |= $m[$permission_column];
		}
	} else {
		F_display_db_error();
	}

	// get parent object permissions
	if ($table == K_TABLE_OBJECTS_MAP) {
		$sql = 'SELECT omp_parent_obj_id FROM '.K_TABLE_OBJECTS_MAP.' WHERE omp_child_obj_id='.$record_id.'';
		if ($r = F_db_query($sql, $db)) {
			while ($m = F_db_fetch_array($r)) {
				$permission |=  F_getUserPermission($user_id, K_TABLE_OBJECTS_MAP, $m['omp_parent_obj_id']);
			}
		} else {
			F_display_db_error();
		}
	}

	return $permission;
}

/**
 * Return the IDs of groups asociated to the selected user.
 * @param $user_id (int) User ID.
 * @return (array) Array of group IDs.
 */
function F_getUserGroups($user_id) {
	global $l, $db;
	require_once('../config/tce_config.php');
	$groups = array();
	// check if user is an administrator
	$sql = 'SELECT usrgrp_group_id FROM '.K_TABLE_USERGROUP.' WHERE usrgrp_user_id='.$user_id.'';
	if ($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_array($r)) {
			$groups[] = $m['usrgrp_group_id'];
		}
	} else {
		F_display_db_error();
	}
	return $groups;
}

/**
 * Return a comma-separated list of group IDs asociated to the selected user.
 * @param $user_id (int) User ID.
 * @return (string) List of group IDs.
 */
function F_getUserGroupsList($user_id) {
	global $l, $db;
	require_once('../config/tce_config.php');
	$groups = F_getUserGroups($user_id);
	return implode(',', $groups);
}

//============================================================+
// END OF FILE
//============================================================+
