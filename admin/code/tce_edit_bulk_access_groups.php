<?php
//============================================================+
// File name   : tce_edit_bulk_access_groups.php
// Begin       : 2011-10-31
// Last Update : 2012-01-20
//
// Description : Bulk change SSH access groups associated to selected object.
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
 * Bulk change SSH access groups associated to selected object.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-11-11
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_BULK_ACCESS_GROUPS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_bulk_access_groups'];
$enable_calendar = true;
require_once('../code/tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_objects.php');
require_once('tce_functions_sshauth.php');
require_once('tce_functions_group_permissions.php');

$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
$userlevel = intval($_SESSION['session_user_level']);
$user_groups = F_getUserGroups($user_id);

// set default values
$filtered = false;
if (isset($_REQUEST['dcn_id']) AND !empty($_REQUEST['dcn_id'])) {
	$dcn_id = intval($_REQUEST['dcn_id']);
	if (isset($_REQUEST['sts_id']) AND !empty($_REQUEST['sts_id']) AND (!isset($_REQUEST['change_datacenter']) OR empty($_REQUEST['change_datacenter']))) {
		$sts_id = intval($_REQUEST['sts_id']);
		if (isset($_REQUEST['rck_id']) AND !empty($_REQUEST['rck_id']) AND (!isset($_REQUEST['change_suite']) OR empty($_REQUEST['change_suite']))) {
			$rck_id = intval($_REQUEST['rck_id']);
		} else {
			$rck_id = 0;
		}
	} else {
		$sts_id = 0;
		$rck_id = 0;
	}
	$filtered = true;
} else {
	$dcn_id = 0;
	$sts_id = 0;
	$rck_id = 0;
}
if (isset($_REQUEST['obt_id']) AND ($_REQUEST['obt_id'] > 0)) {
	$obt_id = intval($_REQUEST['obt_id']);
	$filtered = true;
} else {
	$obt_id = 0;
}
if (isset($_REQUEST['obj_owner_id']) AND !empty($_REQUEST['obj_owner_id'])) {
	$obj_owner_id = intval($_REQUEST['obj_owner_id']);
	$filtered = true;
} else {
	$obj_owner_id = 0;
}
if (isset($_REQUEST['obj_tenant_id']) AND !empty($_REQUEST['obj_tenant_id'])) {
	$obj_tenant_id = intval($_REQUEST['obj_tenant_id']);
	$filtered = true;
} else {
	$obj_tenant_id = 0;
}
if (isset($_REQUEST['keywords']) AND !empty($_REQUEST['keywords'])) {
	$keywords = trim($_REQUEST['keywords']);
	$filtered = true;
} else {
	$keywords = '';
}

if (isset($_REQUEST['sshkey_overwrite']) AND !empty($_REQUEST['sshkey_overwrite'])) {
	$sshkey_overwrite = intval($_REQUEST['sshkey_overwrite']);
} else {
	$sshkey_overwrite = false;
}

$num_perms = 6;
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

if (isset($_POST['updatessh'])) {
	$menu_mode = 'updatessh';
}

switch($menu_mode) { // process submitted data
	
	case 'updatessh': // Update SSH keys on remote servers
	case 'update': {
		foreach ($_REQUEST as $k => $v) {
			if (substr($k, 0, 3) == 'so_') {
				$$k = true;
				// get object ID
				$object_id = intval(substr($k, 3));
				// delete previous groups permissions
				if ($userlevel >= K_AUTH_ADMINISTRATOR) {
					$sql = 'DELETE FROM '.K_TABLE_OBJECT_GROUPS.' WHERE obg_obj_id='.$object_id;
				} else {
					$sql = 'DELETE FROM '.K_TABLE_OBJECT_GROUPS.' WHERE obg_obj_id='.$object_id.' AND obj_group_id IN ('.implode(',', $user_groups).')';
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
							'.$object_id.',
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
				if ($sshkey_overwrite AND empty($object_groups)) {
					F_print_error('MESSAGE', $l['m_empty_auth_sshkeys']);
				} elseif (($menu_mode == 'updatessh') AND (!empty($newkeys)) AND ($userlevel >= K_AUTH_ADMINISTRATOR)) {
					// update keys on remote server
					if (F_updateRemoteKeys($object_id, $newkeys, false, ($userlevel < K_AUTH_ADMINISTRATOR)) === false) {
						F_print_error('WARNING', $l['m_unable_to_update_ssh_keys']);
					}
				}
			} // end for each object
		}
		F_print_error('MESSAGE', $l['m_updated']);
		break;
	}

	default: {
		break;
	}

} //end of switch

// -----------------------------------------------------------------------------

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_editor">'.K_NEWLINE;

// *** selection filter ***
echo F_getDataFilter($dcn_id, $sts_id, $rck_id, $obt_id, $obj_owner_id, $obj_tenant_id, $keywords);

// display selected objects with checkboxes for selection
if ($filtered === true) {
	echo F_getSelectedObject($dcn_id, $sts_id, $rck_id, $obt_id, $obj_owner_id, $obj_tenant_id, $keywords);

	// *** user groups ***
	
		
// -----------------------------------------------------------------------------
	// group permissions
	echo '<fieldset class="subset" style="text-align:left;">'.K_NEWLINE;
	echo '<legend>'.$l['t_permissions'].'</legend>'.K_NEWLINE;
	echo F_groupsPermsSelector($perms, true);
	echo '</fieldset>'.K_NEWLINE;
// -----------------------------------------------------------------------------
	
	echo getFormRowCheckBox('sshkey_overwrite', $l['w_overwrite'], $l['h_sshkey_overwrite'], '', '1', $sshkey_overwrite, false, '');

	// generate button
	echo '<div class="row">'.K_NEWLINE;
	F_submit_button('update', $l['w_update'], $l['h_update']);
	if ($userlevel >= K_AUTH_ADMINISTRATOR) {
		F_submit_button('updatessh', $l['w_update_ssh'], $l['h_update_ssh']);
	}
	echo '</div>'.K_NEWLINE;
}

echo '<div class="row">'.K_NEWLINE;
echo '&nbsp;'.K_NEWLINE;

// comma separated list of required fields
echo '<input type="hidden" name="ff_required" id="ff_required" value="" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="" />'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_bulk_access_groups'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
