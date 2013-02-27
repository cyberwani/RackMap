<?php
//============================================================+
// File name   : tce_edit_bulk_objects.php
// Begin       : 2011-10-31
// Last Update : 2012-01-11
//
// Description : Bulk add chilld objects on selected objects.
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
 * Bulk add chilld objects on selected objects.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-12-09
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_BULK_OBJECTS;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_bulk_object_editor'];
$enable_calendar = true;
require_once('../code/tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_objects.php');

$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
//$userlevel = intval($_SESSION['session_user_level']);

$filtered = false;

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

switch($menu_mode) { // process submitted data

	case 'update': { // generate script
		if (!(($new_child_quantity > 0) AND ($new_child_type > 0) AND (strlen($new_child_name) > 0))) {
			break;
		}
		foreach ($_REQUEST as $k => $v) {
			if (substr($k, 0, 3) == 'so_') {
				$$k = true;
				// get object ID
				$object_id = intval(substr($k, 3));
				for ($i = 1; $i <= $new_child_quantity; ++$i) {
					$obj_name = F_escape_sql(sprintf($new_child_name, $i));
					// check if this child already exist
					$sqlus = 'SELECT obj_id FROM '.K_TABLE_OBJECTS.', '.K_TABLE_OBJECTS_MAP.' WHERE omp_child_obj_id=obj_id AND omp_parent_obj_id='.$object_id.' AND obj_name=\''.$obj_name.'\' LIMIT 1';
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
								'.$object_id.',
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
			}
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

// *** object type and template ***

echo '<fieldset class="subset" style="text-align:left;">'.K_NEWLINE;
echo '<legend>'.$l['w_child_objects'].'</legend>'.K_NEWLINE;

// *** add child ojbects
echo '<table>'.K_NEWLINE;
echo '<tr style="text-align:center;"><td>&nbsp;</td><td><label for="new_child_quantity">'.$l['w_quantity'].'</label></td><td><label for="new_child_type">'.$l['w_type'].'</label></td><td><label for="new_child_name">'.$l['w_name'].'</label></td></tr>'.K_NEWLINE;
echo '<tr>'.K_NEWLINE;
echo '<td>'.$l['w_add'].': </td>'.K_NEWLINE;
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
echo '</tr>'.K_NEWLINE;
echo '</table>'.K_NEWLINE;

echo '</fieldset>'.K_NEWLINE;

// display selected objects with checkboxes for selection
if ($filtered === true) {
	echo F_getSelectedObject($dcn_id, $sts_id, $rck_id, $obt_id, $obj_owner_id, $obj_tenant_id, $keywords);
	
	// generate button
	echo '<div class="row">'.K_NEWLINE;
	F_submit_button('update', $l['w_update'], $l['h_update']);
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

echo '<div class="pagehelp">'.$l['hp_bulk_object_editor'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
