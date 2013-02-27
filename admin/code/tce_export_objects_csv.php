<?php
//============================================================+
// File name   : tce_export_objects_csv.php
// Begin       : 2012-02-17
// Last Update : 2012-02-17
//
// Description : Export a list of objects in CSV format.
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
 * Export a list of objects in CSV format.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-11-10
 */

/**
 */


require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMINISTRATOR; // WARNING: the permissions are not checked here, all list is exported
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_export_objects_csv'];
$enable_calendar = true;
require_once('../code/tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_objects.php');
require_once('tce_functions_user_select.php');
require_once('tce_functions_group_permissions.php');

$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
//$userlevel = intval($_SESSION['session_user_level']);

switch($menu_mode) { // process submitted data

	case 'generate': {

		// object selection SQL query
		$sql = 'SELECT obt_name, obj_name, obj_description, obj_label, obj_tag, mnf_name';
		$sql .= ', dcn_name, sts_name, rck_name, loc_row_top, loc_row_bottom';
		$sql .= ', owner.user_name as owner_name, owner.user_email as owner_email, owner.user_firstname as owner_firstname, owner.user_lastname as owner_lastname';
		$sql .= ', tenant.user_name as tenant_name, tenant.user_email as tenant_email, tenant.user_firstname as tenant_firstname, tenant.user_lastname as tenant_lastname';
		$sql .= ' FROM '.K_TABLE_OBJECTS.'';
		$sql .= ' INNER JOIN '.K_TABLE_OBJECT_TYPES.' ON obt_id=obj_obt_id';
		$sql .= ' LEFT JOIN '.K_TABLE_MANUFACTURES.' ON obj_mnf_id=mnf_id';
		$sql .= ' LEFT JOIN '.K_TABLE_USERS.' owner ON obj_owner_id=owner.user_id';
		$sql .= ' LEFT JOIN '.K_TABLE_USERS.' tenant ON obj_tenant_id=tenant.user_id';
		$sql .= ' LEFT JOIN ('.K_TABLE_LOCATIONS.'';
		$sql .= ' INNER JOIN ('.K_TABLE_RACKS.'';
		$sql .= ' INNER JOIN ('.K_TABLE_SUITES.'';
		$sql .= ' INNER JOIN ('.K_TABLE_DATACENTERS.') ON sts_dcn_id=dcn_id';
		$sql .= ') ON rck_sts_id=sts_id';
		$sql .= ') ON loc_rack_id=rck_id';
		$sql .= ') ON obj_id=loc_obj_id';
		
		// CSV headers
		$csv = "obt_name\tobj_name\tobj_description\tobj_label\tobj_tag\tmnf_name\tdcn_name\tsts_name\trck_name\tloc_row_top\tloc_row_bottom\towner_name\towner_email\towner_firstname\towner_lastname\ttenant_name\ttenant_email\ttenant_firstname\ttenant_lastname\n";
		if ($r = F_db_query($sql, $db)) {
			while ($m = F_db_fetch_assoc($r)) {
				$csv .= implode("\t", $m)."\n";
			}
		} else {
			F_display_db_error();
		}

		// convert and save exporting file
		$outfile = date('YmdHis').'_rackmap_objects_list_'.md5($sql).'.csv';
		// save data file
		if (file_put_contents(K_PATH_CONFIG_SCRIPTS.$outfile, $csv) !== false) {
			F_print_error('MESSAGE', $l['m_file_saved'].': <a href="'.K_PATH_URL_CONFIG_SCRIPTS.$outfile.'" title="'.$l['w_download'].'" onclick="pdfWindow=window.open(\''.K_PATH_URL_CONFIG_SCRIPTS.$outfile.'\',\'expWindow\',\'dependent,menubar=yes,resizable=yes,scrollbars=yes,status=yes,toolbar=yes\'); return false;">'.$outfile.'</a>');
		} else {
			F_print_error('ERROR', $l['m_file_save_error'].': '.$outfile);
		}
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

// export button
echo '<div class="row">'.K_NEWLINE;
F_submit_button('generate', $l['w_export'], $l['w_export_data']);
echo '</div>'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '&nbsp;'.K_NEWLINE;

// comma separated list of required fields
echo '<input type="hidden" name="ff_required" id="ff_required" value="" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="" />'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_export_objects_csv'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
