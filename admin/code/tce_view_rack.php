<?php
//============================================================+
// File name   : tce_view_rack.php
// Begin       : 2004-04-29
// Last Update : 2012-03-21
//
// Description : Display objects on rack.
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
 * Display objects on rack.
 * @package net.rackmap.admin
 * @brief Display objects on rack.
 * @author Nicola Asuni
 * @since 2011-11-15
 */

/**
 */

require_once('../config/tce_config.php');
$pagelevel = K_AUTH_VIEW_SUITE;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_view_rack'];

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



// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_editor">'.K_NEWLINE;

echo F_select_datacenter($dcn_id);
echo F_select_suite($dcn_id, $sts_id, $suite_data);
echo F_select_rack($dcn_id, $sts_id, $rck_id, $rack_data, $rack_pos, $rack_name);

echo '<div class="row" style="margin-bottom:10px;"><hr /></div>'.K_NEWLINE;

// *** MAP OF OBJECTS ON SELECTED RACK
if (isset($rack_data)) {
	$rackobjs = '<div class="row">'.K_NEWLINE;
	$rackobjs .= getRackStack($dcn_id, $sts_id, $rck_id, $rack_data);
	$rackobjs .= '</div>'.K_NEWLINE;
	echo $rackobjs;

	// create guest and capacity list
	$guestlist = '';
	$capacitylist = '';
	foreach ($rack_data['rackstack'] as $rckobj) {
		// capacity (report free ports)
		if (isset($rckobj['capacity']) AND !empty($rckobj['capacity'])) {
			foreach ($rckobj['capacity'] as $ck => $cv) {
				if ($cv['free'] > 0) {
					$capacitylist .= '<tr>';
					$capacitylist .= sprintf('<td style="text-align:center;">%02d - %02d</td>',$rckobj['loc_row_top'], $rckobj['loc_row_bottom']);
					$capacitylist .= '<td><a href="tce_view_object.php?dcn_id='.$dcn_id.'&amp;sts_id='.$sts_id.'&amp;rck_id='.$rck_id.'&amp;obj_id='.$rckobj['obj_id'].'#object" title="'.$l['w_show_details'].'">'.$rckobj['obj_name'].' - '.$rckobj['obj_label'].' - '.$rckobj['obj_tag'].'</a></td>';
					$capacitylist .= '<td style="text-align:center;">'.$ck.'</td>';
					$capacitylist .= '<td style="text-align:right;">'.$cv['total'].'</td>';
					$capacitylist .= '<td style="text-align:right;color:#006600;font-weight:bold;">'.$cv['free'].'</td>';
					$capacitylist .= '</tr>'.K_NEWLINE;
				}
			}
		}
		// guests
		if (!empty($rckobj['guests'])) {
			$guestlist .= '<li><strong>['.$rckobj['loc_row_top'].'-'.$rckobj['loc_row_bottom'].'] <a href="tce_view_object.php?dcn_id='.$dcn_id.'&amp;sts_id='.$sts_id.'&amp;rck_id='.$rck_id.'&amp;obj_id='.$rckobj['obj_id'].'#object" title="'.$l['w_show_details'].'">'.$rckobj['obj_name'].' - '.$rckobj['obj_label'].' - '.$rckobj['obj_tag'].'</a></strong><ul>'.K_NEWLINE;
			foreach ($rckobj['guests'] as $guest) {
				$guestlist .= '<li><a href="tce_edit_objects.php?obj_id='.$guest['obj_id'].'" title="'.$l['w_edit_item'].'">'.$guest['obj_label'].'</a></li>'.K_NEWLINE;
			}
			$guestlist .= '</ul></li>'.K_NEWLINE;
		}
	}
	if (!empty($capacitylist)) {
		echo '<div class="row" style="text-align:left;">'.K_NEWLINE;
		echo '<h2>'.$l['t_capacity_report'].'</h2>'.K_NEWLINE;
		echo '<table border="1" celpadding="2" cellspacing="0" style="font-size:80%;">'.K_NEWLINE;
		echo '<tr style="text-align:center;background-color:#003399;color:white;"><th>'.$l['w_position'].'</th><th>'.$l['w_object'].'</th><th>'.$l['w_port'].'</th><th>'.$l['w_total'].'</th><th>'.$l['w_free'].'</th></tr>'.K_NEWLINE;
		echo $capacitylist.K_NEWLINE;
		echo '</table>'.K_NEWLINE;
		echo '</div>'.K_NEWLINE;
	}
	if (!empty($guestlist)) {
		echo '<div class="row" style="text-align:left;">'.K_NEWLINE;
		echo '<h2>'.$l['t_guest_list'].'</h2>'.K_NEWLINE;
		echo '<ul>'.$guestlist.'</ul>'.K_NEWLINE;
		echo '</div>'.K_NEWLINE;
	}

}

echo '<div class="row">'.K_NEWLINE;
echo '&nbsp;'.K_NEWLINE;
// comma separated list of required fields
echo '<input type="hidden" name="ff_required" id="ff_required" value="" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="" />'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_view_rack'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

flush();

// update the background status of objects on rack based on ILO information
echo '<script type="text/javascript">'.K_NEWLINE;
echo '//<![CDATA['.K_NEWLINE;
foreach ($rack_data['rackstack'] as $obj) {
	$status = F_get_ilo_power_status($obj['obj_id']);
	if ($status == 'on') {
		echo 'document.getElementById(\'robj_'.$obj['obj_id'].'\').style.backgroundColor="#006600";'.K_NEWLINE;
	} elseif ($status == 'off') {
		echo 'document.getElementById(\'robj_'.$obj['obj_id'].'\').style.backgroundColor="#660000";'.K_NEWLINE;
	}
}
echo '//]]>'.K_NEWLINE;
echo '</script>'.K_NEWLINE;

require_once('tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
