<?php
//============================================================+
// File name   : tce_ssh_commander.php
// Begin       : 2011-11-20
// Last Update : 2012-01-11
//
// Description : SSH bulk commander.
//				 Send bulk SSH commands to multiple servers
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
 * SSH bulk commander. Sends bulk SSH commands to multiple servers
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-11-20
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_SSH_COMMANDER;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_ssh_commander'];
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

if (isset($_REQUEST['tmp_id'])) {
	$tmp_id = intval($_REQUEST['tmp_id']);
} else {
	$tmp_id = 0;
}
if (isset($_REQUEST['header_tmp_id'])) {
	$header_tmp_id = intval($_REQUEST['header_tmp_id']);
} else {
	$header_tmp_id = 0;
}
if (isset($_REQUEST['footer_tmp_id'])) {
	$footer_tmp_id = intval($_REQUEST['footer_tmp_id']);
} else {
	$footer_tmp_id = 0;
}

switch($menu_mode) { // process submitted data

	case 'generate': { // generate commands and send them
		// build template
		$template = '';
		// add header template
		$template .= F_get_template($header_tmp_id)."\n";
		// add body template
		$template .= F_get_template($tmp_id)."\n";
		// add footer template
		$template .= F_get_template($footer_tmp_id);
		$template = trim($template);
		if (empty($template)) {
			break;
		}
		$missing_tokens = '';
		$script ='#!/usr/bin/expect -f'."\n";
		$script .= 'set timeout 3600'."\n\n"; // *** SET HERE THE MAXIMUM EXECUTION TIME
		// for each selected server
		foreach ($_REQUEST as $k => $v) {
			if (substr($k, 0, 3) == 'so_') {
				$$k = true;
				// get object ID
				$object_id = intval(substr($k, 3));
				// get array of all object data
				$tempfields = F_get_objects_templates_array($object_id);
				// extract template keys for lookup
				$tfkeys = implode("\n", array_keys($tempfields));
				// DEBUG echo $tfkeys; // DEBUG
				// process template with object data
				$obj_template = $template;
				// search #~...~# pattern on $template
				preg_match_all('/([#][~][^~]+[~][#])/U', $template, $matches, PREG_SET_ORDER);
				foreach ($matches as $v) {
					if (isset($v[1])) {
						$pattern = str_replace('*', '[A-Z0-9]', $v[1]);
						$missing_link = ', <a href="tce_edit_objects.php?obj_id='.$object_id.'" title="'.$l['w_edit'].'">'.$v[1].'</a>';
						if (preg_match('/'.$pattern.'/U', $tfkeys, $mk) > 0) {
							if (isset($mk[0]) AND !empty($mk[0]) AND isset($tempfields[$mk[0]])) {
								$obj_template = str_replace($v[1], $tempfields[$mk[0]], $obj_template);
								if (strlen($tempfields[$mk[0]]) == 0) {
									$missing_tokens .= $missing_link;
								}
							} else {
								$missing_tokens .= $missing_link;
							}
						} else {
							$missing_tokens .= ', '.$v[1];
						}
					}
				}
				$script .= $obj_template."\n\n";
				$script .= '# ******************************************************************************'."\n\n"; 				
			}
		}
		// save script
		$scriptfile = date('YmdHis').'_SSH_'.md5($script).'_'.$user_id.'.txt';
		if (file_put_contents(K_PATH_CONFIG_SCRIPTS.$scriptfile, $script) !== false) {
			F_print_error('MESSAGE', $l['m_script_saved'].': <a href="'.K_PATH_URL_CONFIG_SCRIPTS.$scriptfile.'" title="'.$l['w_download'].'" onclick="pdfWindow=window.open(\''.K_PATH_URL_CONFIG_SCRIPTS.$scriptfile.'\',\'pdfWindow\',\'dependent,menubar=yes,resizable=yes,scrollbars=yes,status=yes,toolbar=yes\'); return false;">'.$scriptfile.'</a>');
			if (!empty($missing_tokens)) {
				F_print_error('ERROR', $l['m_missing_tokens'].': '.substr($missing_tokens, 2));
			}
		} else {
			F_print_error('ERROR', $l['m_file_save_error'].': '.$scriptfile);
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
echo F_getDataFilter($dcn_id, $sts_id, $rck_id, $obt_id, $obj_owner_id, $obj_tenant_id, $keywords);

// display selected objects with checkboxes for selection
if ($filtered === true) {
	echo F_getSelectedObject($dcn_id, $sts_id, $rck_id, $obt_id, $obj_owner_id, $obj_tenant_id, $keywords);
}

// select header template to apply
echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="tmp_id">'.$l['w_header_template'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="header_tmp_id" id="header_tmp_id" size="0">'.K_NEWLINE;
echo '<option value="0" style="background-color:#009900;color:white;"';
if ($header_tmp_id == 0) {
	echo ' selected="selected"';
}
echo '>&nbsp;</option>'.K_NEWLINE;
$sql = 'SELECT * FROM '.K_TABLE_TEMPLATES.' WHERE tmp_name LIKE \'SSH%HEADER\' ORDER BY tmp_name ASC';
if ($r = F_db_query($sql, $db)) {
	while ($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['tmp_id'].'"';
		if ($m['tmp_id'] == $header_tmp_id) {
			echo ' selected="selected"';
		}
		echo '>'.htmlspecialchars($m['tmp_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
	}
} else {
	echo '</select></span></div>'.K_NEWLINE;
	F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

// select template to apply
echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="tmp_id">'.$l['w_template'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="tmp_id" id="tmp_id" size="0">'.K_NEWLINE;
$sql = 'SELECT * FROM '.K_TABLE_TEMPLATES.' WHERE tmp_name LIKE \'SSH%\' AND (tmp_name NOT LIKE \'%HEADER\') AND ( tmp_name NOT LIKE \'%FOOTER\') ORDER BY tmp_name ASC';
if ($r = F_db_query($sql, $db)) {
	while ($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['tmp_id'].'"';
		if ($m['tmp_id'] == $tmp_id) {
			echo ' selected="selected"';
		}
		echo '>'.htmlspecialchars($m['tmp_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
	}
} else {
	echo '</select></span></div>'.K_NEWLINE;
	F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

// select footer template to apply
echo '<div class="row">'.K_NEWLINE;
echo '<span class="label">'.K_NEWLINE;
echo '<label for="tmp_id">'.$l['w_footer_template'].'</label>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '<span class="formw">'.K_NEWLINE;
echo '<select name="footer_tmp_id" id="footer_tmp_id" size="0">'.K_NEWLINE;
echo '<option value="0" style="background-color:#009900;color:white;"';
if ($footer_tmp_id == 0) {
	echo ' selected="selected"';
}
echo '>&nbsp;</option>'.K_NEWLINE;
$sql = 'SELECT * FROM '.K_TABLE_TEMPLATES.' WHERE tmp_name LIKE \'SSH%FOOTER\' ORDER BY tmp_name ASC';
if ($r = F_db_query($sql, $db)) {
	while ($m = F_db_fetch_array($r)) {
		echo '<option value="'.$m['tmp_id'].'"';
		if ($m['tmp_id'] == $footer_tmp_id) {
			echo ' selected="selected"';
		}
		echo '>'.htmlspecialchars($m['tmp_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
	}
} else {
	echo '</select></span></div>'.K_NEWLINE;
	F_display_db_error();
}
echo '</select>'.K_NEWLINE;
echo '</span>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;


// generate button
echo '<div class="row">'.K_NEWLINE;
F_submit_button('generate', $l['w_generate'], $l['h_generate_ssh_commands']);
echo '</div>'.K_NEWLINE;

echo '<div class="row">'.K_NEWLINE;
echo '&nbsp;'.K_NEWLINE;

// comma separated list of required fields
echo '<input type="hidden" name="ff_required" id="ff_required" value="" />'.K_NEWLINE;
echo '<input type="hidden" name="ff_required_labels" id="ff_required_labels" value="" />'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '</form>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_ssh_commander'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
