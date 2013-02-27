<?php
//============================================================+
// File name   : tce_view_suite.php
// Begin       : 2004-04-29
// Last Update : 2012-03-21
//
// Description : Display object details.
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
 * Display object details.
 * @package net.rackmap.admin
 * @brief Display object details.
 * @author Nicola Asuni
 * @since 2011-11-15
 */

/**
 */

require_once('../config/tce_config.php');
$pagelevel = K_AUTH_VIEW_SUITE;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_view_object'];

require_once('tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_objects.php');
require_once('tce_functions_group_permissions.php');

$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
$userlevel = intval($_SESSION['session_user_level']);

// selected or default datacenter, suite and rack
$dcn_perm = 0;
$sts_perm = 0;
$user_permissions = 0;
if (isset($_REQUEST['dcn_id'])) {
	$dcn_id = intval($_REQUEST['dcn_id']);
	$dcn_perm = F_getUserPermission($user_id, K_TABLE_DATACENTER_GROUPS, $dcn_id);
	if ($dcn_perm == 0) {
		F_print_error('ERROR', $l['m_not_authorized_to_view']);
		$dcn_id = 0;
		$sts_id = 0;
		$rck_id = 0;
	} else {
		if (isset($_REQUEST['sts_id']) AND (!isset($_REQUEST['change_datacenter']) OR empty($_REQUEST['change_datacenter']))) {
			$sts_id = intval($_REQUEST['sts_id']);
			$sts_perm = F_getUserPermission($user_id, K_TABLE_SUITE_GROUPS, $sts_id);
			if ($sts_perm == 0) {
				F_print_error('ERROR', $l['m_not_authorized_to_view']);
				$sts_id = 0;
				$rck_id = 0;
			} else {
				if (isset($_REQUEST['rck_id']) AND (!isset($_REQUEST['change_suite']) OR empty($_REQUEST['change_suite']))) {
					$rck_id = intval($_REQUEST['rck_id']);
					$user_permissions = F_getUserPermission($user_id, K_TABLE_RACK_GROUPS, $rck_id);
					if ($user_permissions == 0) {
						F_print_error('ERROR', $l['m_not_authorized_to_view']);
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

// selected or default object
if (isset($_REQUEST['obj_id']) 
	AND (!isset($_REQUEST['change_datacenter']) OR empty($_REQUEST['change_datacenter']))
	AND (!isset($_REQUEST['change_suite']) OR empty($_REQUEST['change_suite']))
	AND (!isset($_REQUEST['change_rack']) OR empty($_REQUEST['change_rack']))) {	
	$obj_id = intval($_REQUEST['obj_id']);
	$user_permissions = F_getUserPermission($user_id, K_TABLE_OBJECT_GROUPS, $obj_id);
	if ($user_permissions == 0) {
		F_print_error('ERROR', $l['m_not_authorized_to_edit']);
		$obj_id = 0;
	}
	if (($obj_id > 0) AND (($dcn_id == 0) OR ($sts_id == 0) OR ($rck_id == 0))) {
		// retrive location values
		$sql = 'SELECT dcn_id, sts_id, rck_id
		FROM '.K_TABLE_DATACENTERS.', '.K_TABLE_SUITES.', '.K_TABLE_RACKS.', '.K_TABLE_LOCATIONS.', '.K_TABLE_OBJECTS.'
		WHERE loc_obj_id=obj_id AND loc_rack_id=rck_id AND rck_sts_id=sts_id AND sts_dcn_id=dcn_id AND obj_id='.$obj_id.' LIMIT 1';
		if ($r = F_db_query($sql, $db)) {
			if ($m = F_db_fetch_array($r)) {
				$dcn_id = $m['dcn_id'];
				$sts_id = $m['sts_id'];
				$rck_id = $m['rck_id'];
			}
		} else {
			F_display_db_error();
		}
	}
} else {
	$obj_id = 0;
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_editor">'.K_NEWLINE;

echo F_select_datacenter($dcn_id);
echo F_select_suite($dcn_id, $sts_id, $suite_data);
echo F_select_rack($dcn_id, $sts_id, $rck_id, $rack_data, $rack_pos, $rack_name);
echo F_select_object($dcn_id, $sts_id, $rck_id, $obj_id);

echo '<div class="row" style="margin-bottom:10px;"><hr /></div>'.K_NEWLINE;

// *** GET OBJECT DATA
if ($obj_id > 0) {
	$object_data = F_get_object_data($obj_id, $ilo, $capacity);

	// *** ILO STATUS AND BUTTONS
	if (isset($ilo['ip']) AND isset($ilo['user']) AND isset($ilo['password'])) {
		$ilo_data = '';
		// send one ping with the deadline, and if it succeeds continue with ipmitool
		$base_ilo_command = 'ping -c 1 -w 2 '.escapeshellarg($ilo['ip']).' >/dev/null 2>&1';
		$base_ilo_command .= ' && ipmitool -I lanplus -H '.escapeshellarg($ilo['ip']).' -U '.escapeshellarg($ilo['user']).' -P '.escapeshellarg($ilo['password']);
		// execute ILO command
		if (isset($_REQUEST['ilo_identify'])) {
			exec($base_ilo_command.' chassis identify 30');
		}
		if (($userlevel >= K_AUTH_ILO) AND isset($_REQUEST['confirmupdate']) AND ($_REQUEST['confirmupdate'] == 1)) {
			if (isset($_REQUEST['ilo_shutdown'])) {
				exec($base_ilo_command.' chassis power soft');
			} elseif (isset($_REQUEST['ilo_power_off'])) {
				exec($base_ilo_command.' chassis power off');
			} elseif (isset($_REQUEST['ilo_reset'])) {
				exec($base_ilo_command.' chassis power reset');
			} elseif (isset($_REQUEST['ilo_power_on'])) {
				exec($base_ilo_command.' chassis power on');
			}
		}
		$ilo_data .= '<div class="row">'.K_NEWLINE;
		$ilo_data .= '<span class="label">'.$l['w_status'].'</span>'.K_NEWLINE;
		$ilo_data .= '<span class="formw">'.K_NEWLINE;
		$power_status = exec($base_ilo_command.' chassis power status');
		if (empty($power_status)) {
			$ilo_data .= '<span style="color:#ff0000;">'.$l['m_ilo_no_access'].'</span>';
		} else {
			$ilo_poh = exec($base_ilo_command.' chassis poh');
			$ilo_is_on = (strpos($power_status, 'is on') !== false);
			if ($ilo_is_on) {
				$ilo_data .= '<span style="background-color:#00ff00;color:#000000;padding:0 10px 0 10px">'.$l['w_on'].'</span>';
				$ilo_data .= ' <span style="color:#003300;font-size:90%;">['.substr($ilo_poh, 15).']</span>'.K_NEWLINE;
			} else {
				$ilo_data .= '<span style="background-color:#ff0000;color:#000000;padding:0 10px 0 10px">'.$l['w_off'].'</span>';
			}
		}
		$ilo_data .= '</span>'.K_NEWLINE;
		$ilo_data .= '</div>'.K_NEWLINE;
		if (($userlevel >= K_AUTH_ILO) AND !empty($power_status)) {
			$ilo_data .= '<div class="row">'.K_NEWLINE;
			$ilo_data .= '<span class="label">'.$l['w_commands'].'</span>'.K_NEWLINE;
			$ilo_data .= '<span class="formw">'.K_NEWLINE;
			// ssh link
			$ilo_data .= '<a href="ssh://'.$ilo['user'].'@'.$ilo['ip'].'" title="'.$l['h_ssh'].'" class="xmlbutton">'.$l['w_ssh'].'</a>'.K_NEWLINE;
			// indetify with UID Lights
			$ilo_data .= '<input type="submit" name="ilo_identify" id="ilo_identify" value="'.$l['w_ilo_identify'].'" title="'.$l['h_ilo_identify'].'" />'.K_NEWLINE;
			// commands with confirmation checkbox
			$ilo_data .= '<span style="background-color:#999999;">';
			$ilo_data .= '<input type="checkbox" name="confirmupdate" id="confirmupdate" value="1" title="confirm &rarr; update" />';
			if ($ilo_is_on) {
				$ilo_data .= ' <input type="submit" name="ilo_shutdown" id="ilo_shutdown" value="'.$l['w_ilo_shutdown'].'" title="'.$l['h_ilo_shutdown'].'" />'.K_NEWLINE;
				$ilo_data .= ' <input type="submit" name="ilo_power_off" id="ilo_power_off" value="'.$l['w_ilo_power_off'].'" title="'.$l['h_ilo_power_off'].'" />'.K_NEWLINE;
				$ilo_data .= ' <input type="submit" name="ilo_reset" id="ilo_reset" value="'.$l['w_ilo_reset'].'" title="'.$l['h_ilo_reset'].'" />'.K_NEWLINE;
			} else {
				$ilo_data .=  ' <input type="submit" name="ilo_power_on" id="ilo_power_on" value="'.$l['w_ilo_power_on'].'" title="'.$l['h_ilo_power_on'].'" />'.K_NEWLINE;
			}
			$ilo_data .= '</span>'.K_NEWLINE;
			$ilo_data .= '</span>'.K_NEWLINE;
			$ilo_data .= '</div>'.K_NEWLINE;
		}
		echo $ilo_data;
	}
	// capacity report
	if (isset($capacity) AND !empty($capacity)) {
		$capacity_data = '';
		$capacity_data .= '<div class="row">'.K_NEWLINE;
		$capacity_data .= '<span class="label">'.K_NEWLINE;
		$capacity_data .= '<span>'.$l['w_capacity'].':</span>'.K_NEWLINE;
		$capacity_data .= '</span>'.K_NEWLINE;
		$capacity_data .= '<div class="value">'.K_NEWLINE;
		foreach ($capacity as $ck => $cv) {
			$capacity_data .= '<span title="'.$l['w_port'].'" style="font-weight:bold;font-style:italic;">'.$ck.'</span>: '.$cv['total'].' = (<span style="color:#660000;">'.$cv['used'].' '.$l['w_used'].'</span> + <span style="color:#006600;">'.$cv['free'].' '.$l['w_free'].'</span>)<br />'.K_NEWLINE;
		}
		$capacity_data .= '</div>'.K_NEWLINE;
		$capacity_data .= '</div>'.K_NEWLINE;
		echo $capacity_data;
	}

	// *** DISPLAY OBJECT DATA
	echo $object_data;
}

echo '<div class="row">'.K_NEWLINE;
echo '&nbsp;'.K_NEWLINE;
// comma separated list of required fields
echo '<input type="hidden" name="ff_required" id="ff_required" value="" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="" />'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_view_object'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
