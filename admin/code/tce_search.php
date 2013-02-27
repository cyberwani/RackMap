<?php
//============================================================+
// File name   : tce_search.php
// Begin       : 2012-01-16
// Last Update : 2012-01-16
//
// Description : Search for objects on RackMap system.
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
 * Search for objects on RackMap system.
 * @package net.rackmap.admin
 * @brief Main page of RackMap system.
 * @author Nicola Asuni
 * @since 2011-01-16
 */

/**
 */

require_once('../config/tce_config.php');
$pagelevel = K_AUTH_SEARCH;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_search_object'];
require_once('tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_objects.php');

//$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
$userlevel = intval($_SESSION['session_user_level']);

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

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;

echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_editor">'.K_NEWLINE;
// *** selection filter ***
echo F_getDataFilter($dcn_id, $sts_id, $rck_id, $obt_id, $obj_owner_id, $obj_tenant_id, $keywords);
// display selected objects with checkboxes for selection
if ($filtered === true) {
	echo F_getSelectedObject($dcn_id, $sts_id, $rck_id, $obt_id, $obj_owner_id, $obj_tenant_id, $keywords, true);
}
echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_search_object'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;


require_once('tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
