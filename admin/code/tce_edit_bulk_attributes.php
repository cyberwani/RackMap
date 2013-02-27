<?php
//============================================================+
// File name   : tce_edit_bulk_attributes.php
// Begin       : 2011-10-31
// Last Update : 2012-01-11
//
// Description : Bulk change attributes on selected objects.
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
 * Bulk change attributes on selected objects.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-11-11
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_BULK_ATTRIBUTES;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_bulk_attribute_editor'];
$enable_calendar = true;
require_once('../code/tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_objects.php');

$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
//$userlevel = intval($_SESSION['session_user_level']);

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

if (isset($_REQUEST['atb_id'])) {
	$atb_id = intval($_REQUEST['atb_id']);
} else {
	$atb_id = 0;
}
if (isset($_REQUEST['atb_value'])) {
	$atb_value = $_REQUEST['atb_value'];
} else {
	$atb_value = '';
}

switch($menu_mode) { // process submitted data

	case 'update': { // generate script
		if ($atb_id <= 0) {
			break;
		}
		foreach ($_REQUEST as $k => $v) {
			if (substr($k, 0, 3) == 'so_') {
				$$k = true;
				// get object ID
				$object_id = intval(substr($k, 3));
				// delete previous value
				$sql = 'DELETE FROM '.K_TABLE_ATTRIBUTE_VALUES.' WHERE atv_obj_id='.$object_id.' AND atv_atb_id='.$atb_id.'';
				if (!$r = F_db_query($sql, $db)) {
					F_display_db_error(false);
				}
				if (strlen($atb_value) > 0) {
					$sql = 'INSERT INTO '.K_TABLE_ATTRIBUTE_VALUES.' (
						atv_obj_id,
						atv_atb_id,
						atv_value
						) VALUES (
						'.$object_id.',
						'.$atb_id.',
						\''.F_escape_sql($atb_value).'\'
						)';
					if (!$r = F_db_query($sql, $db)) {
						F_display_db_error(false);
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

// *** attribute type and value ***

echo '<fieldset class="subset" style="text-align:left;">'.K_NEWLINE;
echo '<legend>'.$l['w_attribute'].'</legend>'.K_NEWLINE;

// select attribute type
$atb_type = '';
echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="atb_id">'.$l['w_attribute'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="atb_id" id="atb_id" size="0" onchange="document.getElementById(\'form_editor\').submit()">'.K_NEWLINE;
echo '<option value="0" style="background-color:#009900;color:white;"';
if ($atb_id == 0) {
	echo ' selected="selected"';
}
echo '>&nbsp;</option>'.K_NEWLINE;
$sql = 'SELECT * FROM '.K_TABLE_ATTRIBUTE_TYPES.' WHERE 1 ORDER BY atb_name ASC';
if($r = F_db_query($sql, $db)) {
	while($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['atb_id'].'"';
		if($m['atb_id'] == $atb_id) {
			echo ' selected="selected"';
			$atb_type = $m['atb_type'];
		}
		echo '>'.htmlspecialchars($m['atb_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
	}
} else {
	echo '</select></span></div>'.K_NEWLINE;
	F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

switch ($atb_type) {
	case 'bool': {
		echo getFormRowCheckBox('atb_value', $l['w_value'], $l['w_value'], '', '1', $atb_value, false, '');
		break;
	}
	case 'int': {
		echo getFormRowTextInput('atb_value', $l['w_value'], $l['w_value'], '', $atb_value, '^([\+\-]?[0-9]*)$', 255, false, false, false, '');
		break;
	}
	case 'float': {
		echo getFormRowTextInput('atb_value', $l['w_value'], $l['w_value'], '', $atb_value, '^([\+\-]?[0-9\.]*)$', 255, false, false, false, '');
		break;
	}
	case 'string': {
		echo getFormRowTextInput('atb_value', $l['w_value'], $l['w_value'], '', $atb_value, '', 255, false, false, false, '');
		break;
	}
	case 'text': {
		echo getFormRowTextBox('atb_value', $l['w_value'], $l['w_value'], $atb_value, false, '');
		break;
	}
	case 'date': {
		echo getFormRowTextInput('atb_value', $l['w_value'], $l['w_value'], '', $atb_value, '', 255, true, false, false, '');
		break;
	}
	case 'datetime': {
		echo getFormRowTextInput('atb_value', $l['w_value'], $l['w_value'], '', $atb_value, '', 255, false, true, false, '');
		break;
	}
	case 'password': {
		echo getFormRowTextInput('atb_value', $l['w_value'], $l['w_value'], '', $atb_value, '', 255, false, false, true, '');
		break;
	}
}

echo '</fieldset>'.K_NEWLINE;

// display selected objects with checkboxes for selection
if ($filtered === true) {
	echo F_getSelectedObject($dcn_id, $sts_id, $rck_id, $obt_id, $obj_owner_id, $obj_tenant_id, $keywords);
	
	if ($atb_id > 0) {
		// generate button
		echo '<div class="row">'.K_NEWLINE;
		F_submit_button('update', $l['w_update'], $l['h_update']);
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

echo '<div class="pagehelp">'.$l['hp_bulk_attribute_editor'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
