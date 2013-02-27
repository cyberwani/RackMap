<?php
//============================================================+
// File name   : tce_export_data.php
// Begin       : 2011-12-12
// Last Update : 2012-03-30
//
// Description : Export filtered data in various formats.
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
 * Export filtered data in various formats.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-11-10
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_EXPORT;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_export_data'];
$enable_calendar = true;
require_once('../code/tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_objects.php');
require_once('tce_functions_user_select.php');
require_once('tce_functions_group_permissions.php');

$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
$userlevel = intval($_SESSION['session_user_level']);

// object selection SQL query
$sqls = 'SELECT *';
$sqls .= ' FROM '.K_TABLE_DATACENTERS.', '.K_TABLE_SUITES.', '.K_TABLE_RACKS.', '.K_TABLE_LOCATIONS.', '.K_TABLE_OBJECTS.'';
$sqls .= ' WHERE loc_obj_id=obj_id AND loc_rack_id=rck_id AND rck_sts_id=sts_id AND sts_dcn_id=dcn_id';

$filtered = false;
$filter = array('dcn_id' => 0, 'sts_id' => 0, 'rck_ids' => array(), 'obt_ids' => array(), 'obj_owner_id' => 0, 'obj_tenant_id' => 0, 'keywords' => '', 'exclude' => '');

// set default values
$dcn_perm = 0;
$sts_perm = 0;
$user_permissions = 0;
if (isset($_REQUEST['dcn_id']) AND !empty($_REQUEST['dcn_id'])) {
	$dcn_id = intval($_REQUEST['dcn_id']);
	$dcn_perm = F_getUserPermission($user_id, K_TABLE_DATACENTER_GROUPS, $dcn_id);
	if ($dcn_perm == 0) {
		F_print_error('ERROR', $l['m_not_authorized_to_view']);
		$dcn_id = 0;
		$sts_id = 0;
		$rck_ids = array();
	} else {
		$sqls .= ' AND dcn_id='.$dcn_id.'';
		$filter['dcn_id'] = $dcn_id;
		if (isset($_REQUEST['sts_id']) AND !empty($_REQUEST['sts_id']) AND (!isset($_REQUEST['change_datacenter']) OR empty($_REQUEST['change_datacenter']))) {
			$sts_id = intval($_REQUEST['sts_id']);
			$sts_perm = F_getUserPermission($user_id, K_TABLE_SUITE_GROUPS, $sts_id);
			if ($sts_perm == 0) {
				F_print_error('ERROR', $l['m_not_authorized_to_view']);
				$sts_id = 0;
				$rck_ids = array();
			} else {
				$sqls .= ' AND sts_id='.$sts_id.'';
				$filter['sts_id'] = $sts_id;
				if (isset($_REQUEST['rck_ids']) AND !empty($_REQUEST['rck_ids']) AND !in_array(0, $_REQUEST['rck_ids']) AND (!isset($_REQUEST['change_suite']) OR empty($_REQUEST['change_suite']))) {
					$filter['rck_ids'] = '';
					foreach ($_REQUEST['rck_ids'] as $rckid) {
						$rckid = intval($rckid);
						$rck_permissions = F_getUserPermission($user_id, K_TABLE_RACK_GROUPS, $rckid);
						if ($rck_permissions == 0) {
							//F_print_error('ERROR', $l['m_not_authorized_to_view']);
						} else {
							$rck_ids[] = $rckid;
							$filter['rck_ids'] .= ','.$rckid;
						}
					}
					if (!empty($filter['rck_ids'])) {
						$filter['rck_ids'] = substr($filter['rck_ids'], 1);
						$sqls .= ' AND rck_id IN ('.$filter['rck_ids'].')';
					}
				} else {
					$rck_ids = array();
				}
			}
		} else {
			$sts_id = 0;
			$rck_ids = array();
		}
	}
} else {
	$dcn_id = 0;
	$sts_id = 0;
	$rck_ids = array();
}
if (isset($_REQUEST['obt_ids']) AND !empty($_REQUEST['obt_ids']) AND !in_array(0, $_REQUEST['obt_ids'])) {
	$filter['obt_ids'] = '';
	foreach ($_REQUEST['obt_ids'] as $obtid) {
		$obtid = intval($obtid);
		$obt_ids[] = $obtid;
		$filter['obt_ids'] .= ','.$obtid;
	}
	$filter['obt_ids'] = substr($filter['obt_ids'], 1);
	$sqls .= ' AND obj_obt_id IN ('.$filter['obt_ids'].')';
	$filtered = true;
} else {
	$obt_ids = array();
}
if (isset($_REQUEST['obj_owner_id']) AND !empty($_REQUEST['obj_owner_id'])) {
	$obj_owner_id = intval($_REQUEST['obj_owner_id']);
	$sqls .= ' AND obj_owner_id='.$obj_owner_id.'';
	$filter['obj_owner_id'] = $obj_owner_id;
} else {
	$obj_owner_id = 0;
}
if (isset($_REQUEST['obj_tenant_id']) AND !empty($_REQUEST['obj_tenant_id'])) {
	$obj_tenant_id = intval($_REQUEST['obj_tenant_id']);
	$sqls .= ' AND obj_tenant_id='.$obj_tenant_id.'';
	$filter['obj_tenant_id'] = $obj_tenant_id;
} else {
	$obj_tenant_id = 0;
}
if (isset($_REQUEST['keywords']) AND !empty($_REQUEST['keywords'])) {
	$keywords = $_REQUEST['keywords'];
	// build a search query
	$keywords = trim($keywords);
	$filter['keywords'] = $keywords;
	// get all the words into an array
	$terms = preg_split("/[\s]+/i", $keywords);
	$wheresearch = '';
	foreach ($terms as $word) {
		$word = F_escape_sql($word);
		$wheresearch .= ' AND ((obj_name LIKE \'%'.$word.'%\')';
		$wheresearch .= ' OR (obj_label LIKE \'%'.$word.'%\')';
		$wheresearch .= ' OR (obj_tag LIKE \'%'.$word.'%\'))';
	}
	$sqls .= ' AND'.substr($wheresearch, 5);
	$filtered = true;
} else {
	$keywords = '';
}
$format_types = array(0 => 'PDF', 1 => 'XML', 2 => 'CSV', 3 => 'JSON', 4 => 'Serialized PHP Array');
if (isset($_REQUEST['format_type']) AND !empty($_REQUEST['format_type'])) {
	$format_type = intval($_REQUEST['format_type']);
} else {
	$format_type = 0;
}
$filter['hideobj'] = false;
$pattern = '';
if (isset($_REQUEST['exclude']) AND !empty($_REQUEST['exclude'])) {
	$exclude = $_REQUEST['exclude'];
	if (strpos($exclude, 'object') !== false) {
		$exclude = str_replace('object', '', $exclude);
		$filter['hideobj'] = true;
	}
	$exclude = trim($exclude);
	$filter['exclude'] = preg_replace("/[\s\,]+/i", ' ', $exclude);
	if (!empty($filter['exclude'])) {
		$pattern = '/('.str_replace(' ', '|', $filter['exclude']).')/Uis';
	}
	if ($filter['hideobj']) {
		$filter['exclude'] .= ' object';
	}
	$exclude = $filter['exclude'];
} else {
	$exclude = '';
}
if ($format_type > 0) {
	// remove rackstack info from export
	$pattern = str_replace(')/Uis', '|rck_rackstack)/Uis', $pattern);
}

$sqls .= ' ORDER BY dcn_name, sts_name, rck_name, loc_row_top, obj_name';

switch($menu_mode) { // process submitted data

	case 'generate': {
		// generate an array of data to export
		$data = array();
		// initialize filter information
		$data['filter'] = array('date' => date(K_TIMESTAMP_FORMAT), 'datacenter' => '', 'suite' => '', 'rack' => '', 'object_type' => '', 'owner' => '', 'tenant' => '', 'keywords' => $filter['keywords'], 'exclude' => $filter['exclude'], 'hideobj' => $filter['hideobj']);
		if ($filter['dcn_id'] > 0) {
			$sql = 'SELECT dcn_name FROM '.K_TABLE_DATACENTERS.' WHERE dcn_id='.$filter['dcn_id'].' LIMIT 1';
			if ($r = F_db_query($sql, $db)) {
				if ($m = F_db_fetch_array($r)) {
					$data['filter']['datacenter'] = $m['dcn_name'];
				}
			} else {
				F_display_db_error();
			}
		}
		if ($filter['sts_id'] > 0) {
			$sql = 'SELECT sts_name FROM '.K_TABLE_SUITES.' WHERE sts_id='.$filter['sts_id'].' LIMIT 1';
			if ($r = F_db_query($sql, $db)) {
				if ($m = F_db_fetch_array($r)) {
					$data['filter']['suite'] = $m['sts_name'];
				}
			} else {
				F_display_db_error();
			}
		}
		if (!empty($filter['rck_ids'])) {
			$sql = 'SELECT rck_name FROM '.K_TABLE_RACKS.' WHERE rck_id IN ('.$filter['rck_ids'].')';
			if ($r = F_db_query($sql, $db)) {
				while ($m = F_db_fetch_array($r)) {
					$data['filter']['rack'] .= "\n".$m['rck_name'];
				}
				$data['filter']['rack'] = substr($data['filter']['rack'], 1);
			} else {
				F_display_db_error();
			}
		}
		if (!empty($filter['obt_ids'])) {
			$sql = 'SELECT obt_name FROM '.K_TABLE_OBJECT_TYPES.' WHERE obt_id IN ('.$filter['obt_ids'].')';
			if ($r = F_db_query($sql, $db)) {
				while ($m = F_db_fetch_array($r)) {
					$data['filter']['object_type'] .= "\n".$m['obt_name'];
				}
				$data['filter']['object_type'] = substr($data['filter']['object_type'], 1);
			} else {
				F_display_db_error();
			}
		}
		if ($filter['obj_owner_id'] > 0) {
			$sql = 'SELECT user_lastname, user_firstname FROM '.K_TABLE_USERS.' WHERE user_id='.$filter['obj_owner_id'].' LIMIT 1';
			if ($r = F_db_query($sql, $db)) {
				if ($m = F_db_fetch_array($r)) {
					$data['filter']['owner'] = $m['user_lastname'].' '.$m['user_firstname'];
				}
			} else {
				F_display_db_error();
			}
		}
		if ($filter['obj_tenant_id'] > 0) {
			$sql = 'SELECT user_lastname, user_firstname FROM '.K_TABLE_USERS.' WHERE user_id='.$filter['obj_tenant_id'].' LIMIT 1';
			if ($r = F_db_query($sql, $db)) {
				if ($m = F_db_fetch_array($r)) {
					$data['filter']['tenant'] = $m['user_lastname'].' '.$m['user_firstname'];
				}
			} else {
				F_display_db_error();
			}
		}
		$prev_dcn_id = 0;
		$prev_sts_id = 0;
		$prev_rck_id = 0;
		$dcn_perm = 0;
		$sts_perm = 0;
		$rck_perm = 0;
		$obj_perm = 0;
		$perm_groups = array();
		$data['asset']['datacenter'] = array();
		if ($r = F_db_query($sqls, $db)) {
			while ($m = F_db_fetch_array($r)) {
				// datacenter data
				if ($m['dcn_id'] != $prev_dcn_id) {
					$dcn_perm = F_getUserPermission($user_id, K_TABLE_DATACENTER_GROUPS, $m['dcn_id']);
					if (($dcn_perm & 1) > 0) {
						$data['asset']['datacenter']['DCN'.$m['dcn_id']] = array(
							'dcn_id' => $m['dcn_id'],
							'dcn_name' => $m['dcn_name'],
							'dcn_description' => $m['dcn_description'],
							'dcn_website_url' => $m['dcn_website_url'],
							'dcn_map_url' => $m['dcn_map_url'],
							'suite' => array()
						);
						if ($userlevel >= K_AUTH_ADMINISTRATOR) {
							$data['asset']['datacenter']['DCN'.$m['dcn_id']]['dcn_permissions'] = F_getGroupsPermsDesc(F_getGroupsPermissions(K_TABLE_DATACENTER_GROUPS, $m['dcn_id']));
						}
					}
					$prev_dcn_id = $m['dcn_id'];
				}
				// suite data
				if ($m['sts_id'] != $prev_sts_id) {
					$sts_perm = F_getUserPermission($user_id, K_TABLE_SUITE_GROUPS, $m['sts_id']);
					if ((($dcn_perm & 1) > 0) AND (($sts_perm & 1) > 0)) {
						$data['asset']['datacenter']['DCN'.$m['dcn_id']]['suite']['STS'.$m['sts_id']] = array(
							'sts_id' => $m['sts_id'],
							'sts_dcn_id' => $m['sts_dcn_id'],
							'sts_name' => $m['sts_name'],
							'sts_description' => $m['sts_description'],
							'sts_floor' => $m['sts_floor'],
							'sts_width' => $m['sts_width'],
							'sts_height' => $m['sts_height'],
							'rack' => array()
						);
						if ($userlevel >= K_AUTH_ADMINISTRATOR) {
							$data['asset']['datacenter']['DCN'.$m['dcn_id']]['suite']['STS'.$m['sts_id']]['sts_permissions'] = F_getGroupsPermsDesc(F_getGroupsPermissions( K_TABLE_SUITE_GROUPS, $m['sts_id']));
						}
					}
					$prev_sts_id = $m['sts_id'];
				}
				// rack data
				if ($m['rck_id'] != $prev_rck_id) {
					$rck_perm = F_getUserPermission($user_id, K_TABLE_RACK_GROUPS, $m['rck_id']);
					if ((($dcn_perm & 1) > 0) AND (($sts_perm & 1) > 0) AND (($rck_perm & 1) > 0)) {
						$rck_value = $m;
						$data['asset']['datacenter']['DCN'.$m['dcn_id']]['suite']['STS'.$m['sts_id']]['rack']['RCK'.$m['rck_id']] = array(
							'rck_id' => $m['rck_id'],
							'rck_sts_id' => $m['rck_sts_id'],
							'rck_name' => $m['rck_name'],
							'rck_description' => $m['rck_description'],
							'rck_label' => $m['rck_label'],
							'rck_tag' => $m['rck_tag'],
							'rck_height' => $m['rck_height'],
							'rck_position_x' => $m['rck_position_x'],
							'rck_position_y' => $m['rck_position_y'],
							'rck_table' => getRackStack($m['dcn_id'], $m['sts_id'], $m['rck_id'], $rck_value),
							'rck_rackstack' => $rck_value['rackstack'],
							'rck_free_slots' => $rck_value['free_slots'],
							'object' => array()
						);
						if ($userlevel >= K_AUTH_ADMINISTRATOR) {
							$data['asset']['datacenter']['DCN'.$m['dcn_id']]['suite']['STS'.$m['sts_id']]['rack']['RCK'.$m['rck_id']]['rck_permissions'] = F_getGroupsPermsDesc(F_getGroupsPermissions(K_TABLE_RACK_GROUPS, $m['rck_id']));
						}
					}
					$prev_rck_id = $m['rck_id'];
				}
				$obj_perm = F_getUserPermission($user_id, K_TABLE_OBJECT_GROUPS, $m['obj_id']);
				if (($obj_perm & 1) > 0) {
					// object data
					$objdata = F_get_object_data_array($m['obj_id'], $ilo, $capacity);
					if (isset($capacity) AND !empty($capacity)) {
						$objdata['capacity'] = $capacity;
					}
					// get all groups IDs
					$perm_groups = array_merge($perm_groups, array_keys($objdata['permissions']));
					if ($userlevel >= K_AUTH_ADMINISTRATOR) {
						$objdata['permissions'] = F_getGroupsPermsDesc($objdata['permissions']);
					}
					$data['asset']['datacenter']['DCN'.$m['dcn_id']]['suite']['STS'.$m['sts_id']]['rack']['RCK'.$m['rck_id']]['object']['OBJ'.$m['obj_id']] = $objdata;
				}
			}
		} else {
			F_display_db_error();
		}
		// filter data by removing unwanted fields
		if (!empty($pattern)) {
			$data['asset'] = sanitizeExportArray($data['asset'], $pattern);
		}
		// create a list of users that belongs to each group (if any)
		$data['groups'] = array();
		if ($userlevel >= K_AUTH_ADMINISTRATOR) {
			if (!empty($perm_groups)) {
				foreach ($perm_groups as $group_id => $prm) {
					$sqlug = 'SELECT user_id, user_lastname, user_firstname, user_name, user_email, group_name
						FROM '.K_TABLE_USERS.', '.K_TABLE_USERGROUP.', '.K_TABLE_GROUPS.'
						WHERE usrgrp_user_id=user_id AND group_id=usrgrp_group_id AND usrgrp_group_id='.$group_id.'
						ORDER BY group_name, user_lastname, user_firstname, user_name';
					if ($rug = F_db_query($sqlug, $db)) {
						while ($mug = F_db_fetch_array($rug)) {
							$data['groups'][$mug['group_name']][] = $mug['user_lastname'].' '.$mug['user_firstname'].' ['.$mug['user_name'].'] - '.$mug['user_email'];
						}
					} else {
						F_display_db_error();
					}
				}
			}
		}
		// convert and save exporting file
		$outfile = date('YmdHis').'_rackmap_data_'.md5($sqls).'.';
		switch ($format_type) {
			case 0: { // PDF
				$outfile .= 'pdf';
				require_once('tce_pdf_data.php');
				// get PDF data
				$exdata = getDataPDF($data);
				break;
			}
			case 1: { // XML
				$outfile .= 'xml';
				$exdata = '<'.'?xml version="1.0" encoding="UTF-8" ?'.'>'.K_NEWLINE;
				$exdata .= '<rackmap version="'.K_RACKMAP_VERSION.'">'.K_NEWLINE;
				$exdata .= getDataXML($data);
				$exdata .= '</rackmap>'.K_NEWLINE;
				break;
			}
			case 2: { // CSV
				$outfile .= 'csv';
				$exdata = getNestedDataCSV($data);
				break;
			}
			case 3: { // JSON
				$outfile .= 'json';
				$exdata = json_encode($data);
				break;
			}
			case 4: { // Serialized PHP Array
				$outfile .= 'phps';
				$exdata = serialize($data);
				break;
			}
		}
		// save data file
		if (file_put_contents(K_PATH_CONFIG_SCRIPTS.$outfile, $exdata) !== false) {
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

// *** selection filter ***

echo '<fieldset class="subset" style="text-align:left;">'.K_NEWLINE;
echo '<legend>'.$l['w_selection filter'].'</legend>'.K_NEWLINE;
echo F_select_datacenter($dcn_id, $datacenter_data, true);
echo F_select_suite($dcn_id, $sts_id, $suite_data, true);
echo F_select_multiple_racks($dcn_id, $sts_id, $rck_ids);
echo F_select_multiple_object_types($obt_ids);
echo F_get_user_selectbox($l['w_owner'], $obj_owner_id, 'obj_owner_id');
echo F_get_user_selectbox($l['w_tenant'], $obj_tenant_id, 'obj_tenant_id');
echo getFormRowTextInput('keywords', $l['w_keywords'], $l['w_search_keywords'], '', $keywords, '', 255, false, false, false, '');
echo '</fieldset>'.K_NEWLINE;

echo '<fieldset class="subset" style="text-align:left;">'.K_NEWLINE;
echo '<legend>'.$l['w_options'].'</legend>'.K_NEWLINE;
echo getFormRowTextInput('exclude', $l['w_exclude'], $l['h_exclude_pattern'], '[i.e.: user password license child connection permissions object capacity]', $exclude, '', 255, false, false, false, '');
echo getFormRowSelectBox('format_type', $l['w_format'], $l['h_export_format'], '', $format_type, $format_types, '');
echo '</fieldset>'.K_NEWLINE;

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

echo '<div class="pagehelp">'.$l['hp_export_data'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
