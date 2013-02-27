<?php
//============================================================+
// File name   : tce_view_datacenter.php
// Begin       : 2004-04-29
// Last Update : 2012-12-13
//
// Description : Display datacenter info.
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
 * Display datacenter info.
 * @package net.rackmap.admin
 * @brief Display datacenter info.
 * @author Nicola Asuni
 * @since 2011-11-15
 */

/**
 */

require_once('../config/tce_config.php');
$pagelevel = K_AUTH_VIEW_DATACENTER;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_view_datacenter'];

require_once('tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_objects.php');
require_once('tce_functions_group_permissions.php');

$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
$userlevel = intval($_SESSION['session_user_level']);

// selected or default datacenter
$user_permissions = 0;
if (isset($_REQUEST['dcn_id'])) {
	$dcn_id = intval($_REQUEST['dcn_id']);
	$user_permissions = F_getUserPermission($user_id, K_TABLE_DATACENTER_GROUPS, $dcn_id);
	if ($user_permissions == 0) {
		F_print_error('ERROR', $l['m_not_authorized_to_view']);
		$dcn_id = 0;
	}
} else {
	$dcn_id = 0;
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

echo '<div class="container">'.K_NEWLINE;

echo F_select_datacenter($dcn_id, $data, false);


	
echo '<div class="tceformbox">'.K_NEWLINE;
	
if (isset($data['dcn_name'])) {
	echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_editor">'.K_NEWLINE;

	echo '<div class="row" style="margin-bottom:10px;"><hr /></div>'.K_NEWLINE;

	echo  getFormDescriptionLine($l['w_name'], $l['h_datacenter_name'], '<strong>'.htmlspecialchars($data['dcn_name'], ENT_COMPAT, $l['a_meta_charset']).'</strong>');
	echo  getFormDescriptionLine($l['w_website'], $l['h_website_url'], '<a href="'.htmlspecialchars($data['dcn_website_url'], ENT_COMPAT, $l['a_meta_charset']).'" onclick="pdfWindow=window.open(\''.htmlspecialchars($data['dcn_website_url'], ENT_COMPAT, $l['a_meta_charset']).'\',\'pdfWindow\',\'dependent,menubar=yes,resizable=yes,scrollbars=yes,status=yes,toolbar=yes\'); return false;" title="'.htmlspecialchars($data['dcn_name'], ENT_COMPAT, $l['a_meta_charset']).'">'.htmlspecialchars($data['dcn_website_url'], ENT_COMPAT, $l['a_meta_charset']).'</a>');
	echo  getFormDescriptionLine($l['w_map'], $l['w_map'], '<a href="'.htmlspecialchars($data['dcn_map_url'], ENT_COMPAT, $l['a_meta_charset']).'"onclick="pdfWindow=window.open(\''.htmlspecialchars($data['dcn_map_url'], ENT_COMPAT, $l['a_meta_charset']).'\',\'pdfWindow\',\'dependent,menubar=yes,resizable=yes,scrollbars=yes,status=yes,toolbar=yes\'); return false;" title="'.htmlspecialchars($l['w_map'], ENT_COMPAT, $l['a_meta_charset']).'">'.htmlspecialchars($data['dcn_map_url'], ENT_COMPAT, $l['a_meta_charset']).'</a>');
	echo  getFormDescriptionLine($l['w_description'], $l['h_datacenter_description'], str_replace("\n", '<br />', htmlspecialchars($data['dcn_description'], ENT_COMPAT, $l['a_meta_charset'])));

	// list of suites
	echo '<div class="row">'.K_NEWLINE;
	echo '<span class="label">'.$l['w_suites'].'</span>'.K_NEWLINE;
	echo '<br /><div class="value">'.K_NEWLINE;
	$sql = 'SELECT * FROM '.K_TABLE_SUITES.' WHERE sts_dcn_id='.$dcn_id.' ORDER BY sts_name ASC';
	if ($r = F_db_query($sql, $db)) {
		echo '<ul>'.K_NEWLINE;
		while ($m = F_db_fetch_array($r)) {
			$sts_permission = F_getUserPermission($user_id, K_TABLE_SUITE_GROUPS, $m['sts_id']);
			if ($sts_permission > 0) {
				echo '<li><a href="tce_view_suite.php?dcn_id='.$dcn_id.'&amp;sts_id='.$m['sts_id'].'" title="'.$l['w_view'].': '.$m['sts_name'].'">'.htmlspecialchars($m['sts_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</a></li>'.K_NEWLINE;
			}
		}
		echo '</ul>'.K_NEWLINE;
	} else {
		F_display_db_error();
	}
	echo '</div>'.K_NEWLINE;
	echo '</div>'.K_NEWLINE;

	echo '<div class="row">'.K_NEWLINE;
	echo '&nbsp;'.K_NEWLINE;
	// comma separated list of required fields
	echo '<input type="hidden" name="ff_required" id="ff_required" value="" />'.K_NEWLINE;
	echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="" />'.K_NEWLINE;
	echo '</div>'.K_NEWLINE;

	echo '</form>'.K_NEWLINE;
}
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_view_datacenter'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
