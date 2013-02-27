<?php
//============================================================+
// File name   : tce_view_suite.php
// Begin       : 2004-04-29
// Last Update : 2012-01-23
//
// Description : Display suite map.
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
 * Display suite map.
 * @package net.rackmap.admin
 * @brief Display suite map.
 * @author Nicola Asuni
 * @since 2011-11-15
 */

/**
 */

require_once('../config/tce_config.php');
$pagelevel = K_AUTH_VIEW_SUITE;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_view_suite'];

require_once('tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_objects.php');
require_once('tce_functions_group_permissions.php');

$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
$userlevel = intval($_SESSION['session_user_level']);

// selected or default datacenter and suite
$dcn_perm = 0;
$user_permissions = 0;
// selected or default datacenter and suite
if (isset($_REQUEST['dcn_id'])) {
	$dcn_id = intval($_REQUEST['dcn_id']);
	$dcn_perm = F_getUserPermission($user_id, K_TABLE_DATACENTER_GROUPS, $dcn_id);
	if ($dcn_perm == 0) {
		F_print_error('ERROR', $l['m_not_authorized_to_view']);
		$dcn_id = 0;
		$sts_id = 0;
	} else {
		if (isset($_REQUEST['sts_id']) AND (!isset($_REQUEST['change_datacenter']) OR empty($_REQUEST['change_datacenter']))) {
			$sts_id = intval($_REQUEST['sts_id']);
			$user_permissions = F_getUserPermission($user_id, K_TABLE_SUITE_GROUPS, $sts_id);
			if ($user_permissions == 0) {
				F_print_error('ERROR', $l['m_not_authorized_to_view']);
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

$sts_permission = F_getUserPermission($user_id, K_TABLE_SUITE_GROUPS, $sts_id);

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_editor">'.K_NEWLINE;

echo F_select_datacenter($dcn_id);
echo F_select_suite($dcn_id, $sts_id, $suite_data);

echo '<div class="row" style="margin-bottom:10px;"><hr /></div>'.K_NEWLINE;

// *** GET RACK INFO
$rack_pos = array(); // store rack positions
$rack_name = array(); //store rack names
$sql = 'SELECT * FROM '.K_TABLE_RACKS.' WHERE rck_sts_id='.$sts_id.' ORDER BY rck_name ASC';
if ($r = F_db_query($sql, $db)) {
	while ($m = F_db_fetch_array($r)) {
		$rack_pos[$m['rck_position_x']][$m['rck_position_y']] = $m['rck_id'];
		$rack_name[$m['rck_id']] = $m['rck_name'].' - '.$m['rck_label'].' - '.$m['rck_tag'];
		$rck_permission[$m['rck_id']] = F_getUserPermission($user_id, K_TABLE_RACK_GROUPS, $m['rck_id']);
	}
} else {
	F_display_db_error();
}

// *** MAP OF RACKS ON SELECTED SUITE
if (isset($suite_data)) {
	echo '<div class="row">'.K_NEWLINE;
	echo '<table class="suite">'.K_NEWLINE;
	//for ($y = 0; $y <= $suite_data['sts_height']; ++$y) {
	for ($y = $suite_data['sts_height']; $y >= 0 ; --$y) {
		echo '<tr>';
		echo '<td style="text-align:center;">'.$y.'</td>';
		for ($x = 1; $x <= $suite_data['sts_width']; ++$x) {
			if ($y == 0) {
				echo '<td style="text-align:center;">'.$x.'</td>';
			} else {
				if (isset($rack_pos[$x][$y]) AND (($rck_permission[$rack_pos[$x][$y]] & 1) == 1)) {
					echo '<td><a href="tce_view_rack.php?dcn_id='.$dcn_id.'&amp;sts_id='.$sts_id.'&amp;rck_id='.$rack_pos[$x][$y].'#rack" title="['.$x.' - '.$y.'] '.$rack_name[$rack_pos[$x][$y]].'" class="rackbutton">&nbsp;</a></td>'.K_NEWLINE;
				} elseif ($sts_permission > 1) {
					echo '<td><a href="tce_edit_racks.php?dcn_id='.$dcn_id.'&amp;sts_id='.$sts_id.'&amp;rck_id=0&amp;rck_position_x='.$x.'&amp;rck_position_y='.$y.'" title="['.$x.' - '.$y.'] '.$l['w_add_new_rack'].'" class="newrackbutton">&nbsp;</a></td>'.K_NEWLINE;
				} else {
					echo '<td>&nbsp;</td>'.K_NEWLINE;
				}
			}
		}
		echo '</tr>'.K_NEWLINE;
	}
	echo '</table>'.K_NEWLINE;
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

echo '<div class="pagehelp">'.$l['hp_view_suite'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
