<?php
//============================================================+
// File name   : tce_import_dhcp_dump.php
// Begin       : 2006-03-17
// Last Update : 2011-11-09
//
// Description : Import MAC and IP from DHCP dump data file.
//               The key is the serial number
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
 * Import MAC and IP from DHCP dump data file. The key is the serial number
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-11-09
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_IMPORT_DHCP;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_dhcp_importer'];
require_once('../code/tce_page_header.php');
require_once('../../shared/code/tce_functions_form.php');

switch($menu_mode) {

	case 'upload': {
		if ($_FILES['userfile']['name']) {
			require_once('../code/tce_functions_upload.php');
			// upload file
			$uploadedfile = F_upload_file('userfile', K_PATH_CACHE);
			if ($uploadedfile !== false) {
				$datafile = K_PATH_CACHE.$uploadedfile;

				// read the file and find relevant data
				$data = file_get_contents($datafile);

				// data delimiter
				$delimiter = '---------------------------------------------------------------------------';
				$delimiter_len = (strlen($delimiter) + 1);

				// parsed data
				$retdata = array();

				// find the end of last block of data (if any)
				$data_lenght = (strrpos($data, $delimiter) + $delimiter_len);

				// check if at least one block of data exist
				if ($data_lenght !== false) {
	
					// get only entire blocks of data
					$data = substr($data, 0, $data_lenght);
	
					// split data in blocks
					$blocks = explode($delimiter, $data);

					// process blocks
					foreach ($blocks as $block) {
		
						// only process DHCPREQUEST blocks
						if (strpos($block, 'DHCPREQUEST') !== false) {

							// array containing the discovred data
							$info = array();

							// get date-time (TIME)
							/*
							$info['time'] = '';
							if (preg_match("/TIME:[\s]+([^\n]*)/", $block, $match) > 0) {
								$info['time'] = $match[1];
							}
							*/
			
							// get MAC address (CHADDR)
							$info['mac'] = '';
							if (preg_match("/CHADDR:[\s]+([0-9a-f]{1,2}:[0-9a-f]{1,2}:[0-9a-f]{1,2}:[0-9a-f]{1,2}:[0-9a-f]{1,2}:[0-9a-f]{1,2})/i", $block, $match) > 0) {
								$info['mac'] = $match[1];
							}
			
							// get IP address (Request IP address)
							$info['ip'] = '';
							if (preg_match("/Request IP address[\s]+([^\n]*)/i", $block, $match) > 0) {
								$info['ip'] = $match[1];
							}
			
							// get serial number
							$info['serial'] = '';
							if (preg_match("/UUID\/GUID[\s]+[^\s]*[\s]+([^\n\s]*)[\n\s]+[^\s]*[\s]+([^\n\s]*)[\n\s]+[^\s]*[\s]+([^\n\s]*)/i", $block, $match) > 0) {
								$info['serial'] = substr($match[1], -1).$match[2].$match[3];
								$info['type'] = 'NIC';
								$info['name'] = 'ETH01';
							} elseif (preg_match("/Host name[\s]+([^\n]*)/i", $block, $match) > 0) {
								$info['serial'] = $match[1];
								if (substr($match[1], 0, 3) == 'ILO') {
									$info['serial'] = substr($info['serial'], 3);
									$info['type'] = 'ILO';
									$info['name'] = 'ILO';
								}
							}
							
							if (!empty($info['serial'])) {
								$retdata[$info['serial']] = $info;
							}
						}
		
					}
				}
				
				// delete uploaded file
				unlink($datafile);
				
				// DEBUG print_r($retdata); exit;
				
				// get number of records
				$num_items = count($retdata);
				
				F_print_error('MESSAGE', $l['m_items_found'].': '.$num_items);

				
				// get ID of MAC attribute type
				$sql = 'SELECT atb_id FROM '.K_TABLE_ATTRIBUTE_TYPES.' WHERE atb_name=\'MAC\' LIMIT 1';
				if ($r = F_db_query($sql, $db)) {
					if ($m = F_db_fetch_array($r)) {
						$mac_type_id = $m['atb_id'];
					}
				} else {
					F_display_db_error();
				}
				
				// get ID of IP attribute type
				$sql = 'SELECT atb_id FROM '.K_TABLE_ATTRIBUTE_TYPES.' WHERE atb_name=\'IP\' LIMIT 1';
				if ($r = F_db_query($sql, $db)) {
					if ($m = F_db_fetch_array($r)) {
						$ip_type_id = $m['atb_id'];
					}
				} else {
					F_display_db_error();
				}
				
				$obj_type_id = array();
				
				// get ID of NIC object type
				$sql = 'SELECT obt_id FROM '.K_TABLE_OBJECT_TYPES.' WHERE obt_name=\'NIC\' LIMIT 1';
				if ($r = F_db_query($sql, $db)) {
					if ($m = F_db_fetch_array($r)) {
						$obj_type_id['NIC'] = $m['obt_id'];
					}
				} else {
					F_display_db_error();
				}
				
				// get ID of ILO object type
				$sql = 'SELECT obt_id FROM '.K_TABLE_OBJECT_TYPES.' WHERE obt_name=\'ILO\' LIMIT 1';
				if ($r = F_db_query($sql, $db)) {
					if ($m = F_db_fetch_array($r)) {
						$obj_type_id['ILO'] = $m['obt_id'];
					}
				} else {
					F_display_db_error();
				}
				
				// update objects on database
				foreach($retdata as $k => $v) {
					// find the target object
					$sql = 'SELECT obj_child.obj_id 
						FROM '.K_TABLE_OBJECTS.' as obj_parent, '.K_TABLE_OBJECTS_MAP.', '.K_TABLE_OBJECTS.' as obj_child
						WHERE omp_parent_obj_id=obj_parent.obj_id AND omp_child_obj_id=obj_child.obj_id
							AND obj_parent.obj_tag=\''.$v['serial'].'\' 
							AND obj_child.obj_obt_id='.$obj_type_id[$v['type']].'
							LIMIT 1';
					if ($r = F_db_query($sql, $db)) {
						if ($m = F_db_fetch_array($r)) {
							
							// MAC address
							if (!empty($v['mac'])) {
								// delete previous value
								$sqla = 'DELETE FROM '.K_TABLE_ATTRIBUTE_VALUES.' WHERE atv_obj_id='.$m['obj_id'].' AND atv_atb_id='.$mac_type_id.'';
								if (!$ra = F_db_query($sqla, $db)) {
									F_display_db_error(false);
								}
								// insert new values
								$sqla = 'INSERT INTO '.K_TABLE_ATTRIBUTE_VALUES.' (
									atv_obj_id,
									atv_atb_id,
									atv_value
									) VALUES (
									'.$m['obj_id'].',
									'.$mac_type_id.',
									\''.$v['mac'].'\'
									)';
								if (!$ra = F_db_query($sqla, $db)) {
									F_display_db_error(false);
								}
							}
							
							// IP address
							if (!empty($v['ip'])) {
								// delete previous value
								$sqla = 'DELETE FROM '.K_TABLE_ATTRIBUTE_VALUES.' WHERE atv_obj_id='.$m['obj_id'].' AND atv_atb_id='.$ip_type_id.'';
								if (!$ra = F_db_query($sqla, $db)) {
									F_display_db_error(false);
								}
								// insert new values
								$sqla = 'INSERT INTO '.K_TABLE_ATTRIBUTE_VALUES.' (
									atv_obj_id,
									atv_atb_id,
									atv_value
									) VALUES (
									'.$m['obj_id'].',
									'.$ip_type_id.',
									\''.$v['ip'].'\'
									)';
								if (!$ra = F_db_query($sqla, $db)) {
									F_display_db_error(false);
								}
							}
						}
					} else {
						F_display_db_error();
					}
				}
			}
		}
		break;
	}

	default: {
		break;
	}

} //end of switch
?>

<div class="container">

<div class="tceformbox">
<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" id="form_import">

<div class="row">
<span class="label">
<label for="userfile"><?php echo $l['w_upload_file']; ?></label>
</span>
<span class="formw">
<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo K_MAX_UPLOAD_SIZE ?>" />
<input type="file" name="userfile" id="userfile" size="20" title="<?php echo $l['h_upload_file']; ?>" />
</span>
&nbsp;
</div>

<div class="row">
<?php
// show buttons by case
F_submit_button("upload", $l['w_upload'], $l['h_submit_file']);
?>
</div>

</form>

</div>
<?php

echo '<div class="pagehelp">'.$l['hp_import_dhcp'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
