<?php
//============================================================+
// File name   : tce_functions_objects.php
// Begin       : 2001-09-13
// Last Update : 2012-12-13
//
// Description : Functions for objects.
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
 * Functions for objects.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-10-31
 */

/**
 * Return true if the selected attribute belongs to the specified object.
 * @param $obt_id (int) Object Type ID
 * @param $atb_id (int) Attribute Type ID
 * @return boolean true/false
 */
function F_isObjectAttribute($obt_id, $atb_id) {
	global $l, $db;
	require_once('../config/tce_config.php');
	$sql = 'SELECT oam_atb_id FROM '.K_TABLE_OBJECT_ATTRIBUTES_MAP.' WHERE oam_obt_id='.intval($obt_id).' AND oam_atb_id='.intval($atb_id).' LIMIT 1';
	if ($r = F_db_query($sql, $db)) {
		if ($m = F_db_fetch_array($r)) {
			return true;
		}
	}
	return false;
}

/**
 * Returns an array containing attributes IDs associated to the specified object type.
 * @param $obt_id (int) Obect Type ID
 * @return array with Attribute types IDs
 */
function F_get_object_attributes($obt_id) {
	global $l, $db;
	require_once('../config/tce_config.php');
	$ids = array();
	$sql = 'SELECT oam_atb_id FROM '.K_TABLE_OBJECT_ATTRIBUTES_MAP.' WHERE oam_obt_id='.intval($obt_id).'';
	if ($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_array($r)) {
			$ids[] = $m['oam_atb_id'];
		}
	} else {
		F_display_db_error();
	}
	return $ids;
}

/**
 * Returns an value by type (used for object attributes).
 * @param $type (string) Attribute type.
 * @param $value (string) Attribute value.
 * @return mixed attribute value
 */
function F_get_attribute_value($type, $value) {
	$ret = '';
	switch ($type) {
		case 'bool': {
			$ret = F_getBoolean($value);
			break;
		}
		case 'int': {
			$ret = intval($value);
			break;
		}
		case 'float': {
			$ret = floatval($value);
			break;
		}
		case 'string': {
			$ret = $value;
			break;
		}
		case 'text': {
			$ret = $value;
			break;
		}
		case 'date': {
			$ret = $value;
			break;
		}
		case 'datetime': {
			$ret = $value;
			break;
		}
		case 'password': {
			$ret = $value;
			break;
		}
	}
	return $ret;
}

/**
 * Returns object connections as array.
 * @param $obj_id (int) Object ID.
 * @param $old_obj_id (int) Already listed Object ID.
 * @return string Object connections.
 */
function F_get_object_connections_array($obj_id, $old_obj_id=0) {
	global $l, $db;
	require_once('../config/tce_config.php');
	$data = array();
	// list cable connection (if any)
	$sql = 'SELECT * FROM '.K_TABLE_CABLES.', '.K_TABLE_CABLE_TYPES.' WHERE cab_cbt_id=cbt_id AND ((cab_a_obj_id='.$obj_id.' OR cab_b_obj_id='.$obj_id.') AND (cab_a_obj_id<>'.$old_obj_id.') AND (cab_b_obj_id<>'.$old_obj_id.')) ORDER BY cab_cbt_id, cab_a_obj_id';
	if ($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_array($r)) {
			if ($m['cab_a_obj_id'] == $obj_id) {
				$c_obj_id = $m['cab_b_obj_id'];
			} else {
				$c_obj_id = $m['cab_a_obj_id'];
			}
			$data['cab_obj_id'] = $c_obj_id;
			$data['cab_color'] = $m['cab_color'];
			$data['cab_cbt_id'] = $m['cab_cbt_id'];
			$data['cab_cbt_name'] = $m['cbt_name'];
			$data['cab_cbt_description'] = $m['cbt_description'];
			$data['cab_path'] = F_get_object_path($c_obj_id, false, $odcn_id, $osts_id, $orck_id);
			// get child connection
			$data['cab_sub'] = F_get_object_connections_array($c_obj_id, $obj_id);
		}
	} else {
		F_display_db_error();
	}
	return $data;
}

/**
 * Returns object connections as HTML code.
 * @param $obj_id (int) Object ID.
 * @param $old_obj_id (int) Already listed Object ID.
 * @return string Object connections.
 */
function F_get_object_connections($obj_id, $old_obj_id=0) {
	global $l, $db;
	require_once('../config/tce_config.php');
	$ret = '';
	// show cable connection (if any)
	$sql = 'SELECT * FROM '.K_TABLE_CABLES.', '.K_TABLE_CABLE_TYPES.' WHERE cab_cbt_id=cbt_id AND ((cab_a_obj_id='.$obj_id.' OR cab_b_obj_id='.$obj_id.') AND (cab_a_obj_id<>'.$old_obj_id.') AND (cab_b_obj_id<>'.$old_obj_id.')) ORDER BY cab_cbt_id, cab_a_obj_id';
	if ($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_array($r)) {
			// $m['cab_color'];
			if ($m['cab_a_obj_id'] == $obj_id) {
				$c_obj_id = $m['cab_b_obj_id'];
			} else {
				$c_obj_id = $m['cab_a_obj_id'];
			}
			$cab_ids = $m['cab_a_obj_id'].'|'.$m['cab_b_obj_id'].'|'.$m['cab_cbt_id'];
			$connection = '<a href="tce_edit_connections.php?cab_ids='.$cab_ids.'" title="'.$l['t_connection_editor'].' ['.$cab_ids.']"><span style="border:1px solid black;background-color:#'.$m['cab_color'].';color:#'.getContrastColor($m['cab_color']).';padding:0 4px 0 4px;" title="'.$m['cbt_description'].'">'.$m['cbt_name'].'</span></a>';
			$connection .= ' '.F_get_object_path($c_obj_id, true, $odcn_id, $osts_id, $orck_id).' ';
			if ($old_obj_id > 0) {
				$ret .= ' &rArr; ';
			}
			$ret .= $connection;
			// get child connection
			$ret .= F_get_object_connections($c_obj_id, $obj_id);
		}
	} else {
		F_display_db_error();
	}
	return $ret;
}

/**
 * Returns an HTML description of a single object array.
 * @param $obj_id (int) Object ID.
 * @param $objdata (array) Object data array.
 * @param $tempfields (array) Array of template fields.
 * @param $tfkeys (array) Array of template fields keys.
 * @param $level (int) Count nesting level of child objects.
 * @return string Object data.
 */
function F_get_object_info($obj_id, $objdata, $tempfields, $tfkeys, $level=0) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('tce_functions_group_permissions.php');
	$ret = '';
	// list parent objects of virtual object
	if (isset($objdata['obj_parents']) AND !empty($objdata['obj_parents'])) {
		$ret .= getFormDescriptionLine($l['w_child_of'], '', $objdata['obj_parents']);
	}
	// manufacturer
	if (isset($objdata['obj_mnf']) AND !empty($objdata['obj_mnf'])) {
		$ret .= getFormDescriptionLine($l['w_manufacturer'], '', htmlspecialchars($objdata['obj_mnf'], ENT_COMPAT, $l['a_meta_charset']));
	}
	// owner
	if (isset($objdata['obj_owner']) AND !empty($objdata['obj_owner'])) {
		$ret .= getFormDescriptionLine($l['w_owner'], '', htmlspecialchars($objdata['obj_owner'], ENT_COMPAT, $l['a_meta_charset']));
	}
	// tenant
	if (isset($objdata['obj_tenant']) AND !empty($objdata['obj_tenant'])) {
		$ret .= getFormDescriptionLine($l['w_tenant'], '', htmlspecialchars($objdata['obj_tenant'], ENT_COMPAT, $l['a_meta_charset']));
	}
	// label
	if (isset($objdata['obj_label']) AND !empty($objdata['obj_label'])) {
		$ret .= getFormDescriptionLine($l['w_label'], $l['h_ojbect_label'], htmlspecialchars($objdata['obj_label'], ENT_COMPAT, $l['a_meta_charset']));
	}
	// tag
	if (isset($objdata['obj_tag']) AND !empty($objdata['obj_tag'])) {
		$ret .= getFormDescriptionLine($l['w_tag'], $l['h_ojbect_tag'], htmlspecialchars($objdata['obj_tag'], ENT_COMPAT, $l['a_meta_charset']));
	}
	// description
	if (isset($objdata['obj_description']) AND !empty($objdata['obj_description'])) {
		$ret .= getFormDescriptionLine($l['w_description'], $l['h_object_description'], htmlspecialchars($objdata['obj_description'], ENT_COMPAT, $l['a_meta_charset']));
	}
	// attributes
	if (isset($objdata['attribute']) AND !empty($objdata['attribute'])) {
		foreach ($objdata['attribute'] as $atb_name => $atb_value) {
			if (!empty($atb_value)) {
				$val = htmlspecialchars($atb_value, ENT_COMPAT, $l['a_meta_charset']);
				if ($atb_name == 'IP') {
					$ret .= getFormDescriptionLine($atb_name, '', '<a href="http://'.$val.'" title="IP" onclick="pdfWindow=window.open(\'http://'.$val.'\',\'pdfWindow\',\'dependent,menubar=yes,resizable=yes,scrollbars=yes,status=yes,toolbar=yes\'); return false;">'.$val.'</a>');
				} else {
					// replace template tokens on attribute value (if any)
					$tmpval = $val;
					// search #~...~# pattern on $val
					preg_match_all('/([#][~][^~]+[~][#])/U', $val, $matches, PREG_SET_ORDER);
					foreach ($matches as $v) {
						if (isset($v[1])) {
							$pattern = str_replace('*', '[A-Z0-9]', $v[1]);
							if (preg_match('/'.$pattern.'/U', $tfkeys, $mk) > 0) {
								if (isset($mk[0]) AND !empty($mk[0]) AND isset($tempfields[$mk[0]])) {
									$tmpval = str_replace($v[1], $tempfields[$mk[0]], $tmpval);
								}
							}
						}
					}
					$val = $tmpval;
					// check for link type
					if (F_isURL($val)) {
						// the attribute is a link
						$val = '<a href="'.$val.'" title="'.$atb_name.'">'.$val.'</a>';
					}
					$ret .= getFormDescriptionLine($atb_name, '', $val);
				}
			}
		}
	}
	// Permissions
	if ($level == 0) {
		// print permissions only for parent objects
		$ret .= '<div class="row">'.K_NEWLINE;
		$ret .= '<span class="label">'.K_NEWLINE;
		$ret .= $l['w_permissions'].':'.K_NEWLINE;
		$ret .= '</span>'.K_NEWLINE;
		$ret .= '<div class="value">';
		$ret .= F_groupsPermsSelector($objdata['permissions'], true, true);
		$ret .= '</div>'.K_NEWLINE;
		$ret .= '</div>'.K_NEWLINE;
	}
	// list connections
	$connections = F_get_object_connections($obj_id);
	if (!empty($connections)) {
		$ret .= getFormDescriptionLine($l['w_connected_to'], '', $connections);
	}
	// *** list child ojbects (if any)
	if (isset($objdata['child']) AND !empty($objdata['child'])) {
		$ret .= '<div class="row">'.K_NEWLINE;
		$ret .= '<span class="label">'.K_NEWLINE;
		$ret .= $l['w_child_objects'].':'.K_NEWLINE;
		$ret .= '</span>'.K_NEWLINE;
		$ret .= '<br /><div class="value">';
		foreach ($objdata['child'] as $name => $data) {
			$ret .= '<div style="background-color:#ffff99;font-weight:bold;">';
			$ret .= '<a href="tce_edit_objects.php?obj_id='.$data['obj_id'].'" title="'.$l['t_object_editor'].': '.$data['obj_name'].'">';
			if ($data['obj_obt_virtual']) {
				$ret .= '&otimes; ';
			}
			$ret .= htmlspecialchars($data['obj_name'], ENT_NOQUOTES, $l['a_meta_charset']);
			$ret .= '</a>';
			$ret .= '</div>';
			$ret .= F_get_object_info($data['obj_id'], $data, $tempfields, $tfkeys, ++$level);
		}
		$ret .= '</div>'.K_NEWLINE;
		$ret .= '</div>'.K_NEWLINE;
	}
	$ret .= '<div style="clear:both;margin:0;padding:0;height:0;font-size:0;">&nbsp;</div>'.K_NEWLINE;
	return $ret;
}

/**
 * Returns object data.
 * @param $obj_id (int) Object ID.
 * @param $ilo (array) Data required to access ILO on servers.

 * @return string Object data.
 */
function F_get_object_data($obj_id, &$ilo=array(), &$capacity=array()) {
	global $l, $db;
	require_once('../config/tce_config.php');
	$ret = '';
	if (empty($obj_id)) {
		return '';
	}
	$obj_id = intval($obj_id);
	// get all object info
	$objdata = F_get_object_data_array($obj_id, $ilo, $capacity);
	if (empty($objdata)) {
		return '';
	}
	// get array of all object data
	$tempfields = F_get_template_array($objdata);
	// extract template keys for lookup
	$tfkeys = implode("\n", array_keys($tempfields));
	$ret .= F_get_object_info($obj_id, $objdata, $tempfields, $tfkeys);
	return $ret;
}

/**
 * Print select options for child objects.
 * @param $obj_id (int) Parent Object ID.
 * @param $selected_ids (array) IDs of selected objects.
 * @param $obj_level (int) Nesting level.
 * @param $listed_objs (string) Comma separated list of selected objects.
 * @param $prefix (string) String prefix.
 * @return string form select options.
 */
function F_get_child_objects_items($obj_id, $selected_ids=array(), $obj_level=1, &$listed_objs='', $prefix='') {
	global $l, $db;
	require_once('../config/tce_config.php');
	$obj_id = intval($obj_id);
	$bgcolors = array('ffffdd', 'ccffcc', 'ffeecc');
	$out = '';
	$listed_objs .= ','.$obj_id;
	$sql = 'SELECT obj_id, obj_name, obj_label, obj_tag, obt_virtual FROM '.K_TABLE_OBJECTS.', '.K_TABLE_OBJECT_TYPES.', '.K_TABLE_OBJECTS_MAP.' WHERE obj_obt_id=obt_id AND omp_child_obj_id=obj_id AND omp_parent_obj_id='.$obj_id.' ORDER BY obj_name ASC';
	if ($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_array($r)) {
			$out .= '<option value="'.$m['obj_id'].'"';
			if (!empty($selected_ids) AND in_array($m['obj_id'], $selected_ids)) {
				$out .= ' selected="selected" style="background-color:#ff9999;"';
			} else {
				$col = 'ffffee';
				if (isset($bgcolors[($obj_level - 1)])) {
					$col = $bgcolors[($obj_level - 1)];
				}
				$out .= ' style="background-color:#'.$col.';"';
			}
			$out .= '>';
			$name = htmlspecialchars($m['obj_name'], ENT_NOQUOTES, $l['a_meta_charset']);
			if (F_getBoolean($m['obt_virtual'])) {
				$name = '&otimes; '.$name;
			}
			$name = ' &rarr; '.$name;
			$out .= $prefix.$name.'</option>'.K_NEWLINE;
			$listed_objs .= ','.$m['obj_id'];
			$out .= F_get_child_objects_items($m['obj_id'], $selected_ids, ($obj_level + 1), $listed_objs, $prefix.$name);
		}
	} else {
		F_display_db_error();
	}
	return $out;
}

/**
 * Returns object data array.
 * @param $obj_id (int) Object ID.
 * @param $ilo (array) Data required to access ILO on servers.
 * @param $capacity (array) Count used and free ports.
 * @return array Object data array.
 */
function F_get_object_data_array($obj_id, &$ilo=array(), &$capacity=array()) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('tce_functions_group_permissions.php');
	$obj_id = intval($obj_id);
	$objdata = array();
	if (empty($obj_id)) {
		return $data;
	}
	// type of ports used on capacity calculation
	$port_types = array('NIC','OUTLET','PRT'); // 'ILO','NIC','OUTLET','PRT','PSU'
	// get first level data
	$sql = 'SELECT * FROM '.K_TABLE_OBJECTS.' LEFT JOIN '.K_TABLE_LOCATIONS.' ON obj_id=loc_obj_id WHERE obj_id='.$obj_id.' LIMIT 1';
	if ($r = F_db_query($sql, $db)) {
		if ($m = F_db_fetch_assoc($r)) {
			$objdata = $m;
		}
	} else {
		F_display_db_error();
	}
	// object type
	if (isset($objdata['obj_obt_id']) AND ($objdata['obj_obt_id'] > 0)) {
		$sql = 'SELECT * FROM '.K_TABLE_OBJECT_TYPES.' WHERE obt_id='.$objdata['obj_obt_id'].' LIMIT 1';
		if ($r = F_db_query($sql, $db)) {
			if ($m = F_db_fetch_assoc($r)) {
				$objdata['obj_obt_name'] = $m['obt_name'];
				$objdata['obj_obt_description'] = $m['obt_description'];
				//$objdata['obj_obt_color'] = $m['obt_color'];
				$objdata['obj_obt_virtual'] = F_getBoolean($m['obt_virtual']);
				// list parent objects of virtual object
				if ($objdata['obj_obt_virtual']) {
					$objdata['obj_parents'] = '';
					$sqlp = 'SELECT omp_parent_obj_id FROM '.K_TABLE_OBJECTS_MAP.' WHERE omp_child_obj_id='.$obj_id.'';
					if ($rp = F_db_query($sqlp, $db)) {
						while ($mp = F_db_fetch_array($rp)) {
							$objdata['obj_parents'] .= '<br />'.F_get_object_path($mp['omp_parent_obj_id'], true, $odcn_id, $osts_id, $orck_id);
						}
						if (!empty($objdata['obj_parents'])) {
							$objdata['obj_parents'] = substr($objdata['obj_parents'], 6);
						}
					} else {
						F_display_db_error();
					}
				}
			}
		} else {
			F_display_db_error();
		}
	}
	// manufacturer
	if (isset($objdata['obj_mnf_id']) AND ($objdata['obj_mnf_id'] > 0)) {
		$sql = 'SELECT mnf_name FROM '.K_TABLE_MANUFACTURES.' WHERE mnf_id='.$objdata['obj_mnf_id'].' LIMIT 1';
		if ($r = F_db_query($sql, $db)) {
			if ($m = F_db_fetch_assoc($r)) {
				$objdata['obj_mnf'] = $m['mnf_name'];
			}
		} else {
			F_display_db_error();
		}
	}
	// owner
	if (isset($objdata['obj_owner_id']) AND ($objdata['obj_owner_id'] > 0)) {
		$sql = 'SELECT * FROM '.K_TABLE_USERS.' WHERE user_id='.$objdata['obj_owner_id'].' LIMIT 1';
		if ($r = F_db_query($sql, $db)) {
			if ($m = F_db_fetch_assoc($r)) {
				$objdata['obj_owner'] = $m['user_lastname'].' '.$m['user_firstname'].' - '.$m['user_name'];
			}
		} else {
			F_display_db_error();
		}
	}
	// tenant
	if (isset($objdata['obj_tenant_id']) AND ($objdata['obj_tenant_id'] > 0)) {
		$sql = 'SELECT * FROM '.K_TABLE_USERS.' WHERE user_id='.$objdata['obj_tenant_id'].' LIMIT 1';
		if ($r = F_db_query($sql, $db)) {
			if ($m = F_db_fetch_assoc($r)) {
				$objdata['obj_tenant'] = $m['user_lastname'].' '.$m['user_firstname'].' - '.$m['user_name'];
			}
		} else {
			F_display_db_error();
		}
	}
	// get all possible attributes
	$sql = 'SELECT * FROM '.K_TABLE_OBJECT_ATTRIBUTES_MAP.', '.K_TABLE_ATTRIBUTE_TYPES.' WHERE oam_atb_id=atb_id AND oam_obt_id='.$objdata['obj_obt_id'].' ORDER BY atb_name';
	if ($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_assoc($r)) {
			if ($m['atb_type'] == 'bool') {
				$objdata['attribute'][$m['atb_name']] = '0';
			} else {
				$objdata['attribute'][$m['atb_name']] = '';
			}
		}
	} else {
		F_display_db_error();
	}
	// get attribute values
	$sqla = 'SELECT *
		FROM '.K_TABLE_ATTRIBUTE_VALUES.', '.K_TABLE_ATTRIBUTE_TYPES.'
		WHERE atb_id=atv_atb_id AND atv_obj_id='.$obj_id.' ORDER BY atb_name';
	if ($ra = F_db_query($sqla, $db)) {
		while ($ma = F_db_fetch_assoc($ra)) {
			if (isset($ma['atv_value'][0])) {
				$objdata['attribute'][$ma['atb_name']] = $ma['atv_value'];
				// get ILO user and password
				if ($ma['atb_name'] == 'ILO user') {
					$ilo['user'] = $ma['atv_value'];
				} elseif ($ma['atb_name'] == 'ILO password') {
					$ilo['password'] = $ma['atv_value'];
				}
				if (($objdata['obj_name'] == 'ILO') AND ($ma['atb_name'] == 'IP')) {
					$ilo['ip'] = $ma['atv_value'];
				}
			}
		}
	} else {
		F_display_db_error();
	}
	// get permission info
	$objdata['permissions'] = F_getGroupsPermissions(K_TABLE_OBJECT_GROUPS, $obj_id);
	// get connections
	$objdata['connection'] = F_get_object_connections_array($obj_id);
	// calculate capacity for some object types
	if (in_array($objdata['obj_obt_name'], $port_types)) {
		if (!isset($capacity[$objdata['obj_obt_name']])) {
			$capacity[$objdata['obj_obt_name']] = array('total'=>0, 'used'=>0, 'free'=>0);
		}
		++$capacity[$objdata['obj_obt_name']]['total'];
		if (empty($objdata['connection'])) {
			// free port
			++$capacity[$objdata['obj_obt_name']]['free'];
		} else {
			// used port
			++$capacity[$objdata['obj_obt_name']]['used'];
		}
	}
	// *** get data from child ojbects (if any)
	$sql = 'SELECT obj_id, obj_name, obj_label, obj_tag FROM '.K_TABLE_OBJECTS.', '.K_TABLE_OBJECTS_MAP.' WHERE omp_child_obj_id=obj_id AND omp_parent_obj_id='.$obj_id.' ORDER BY obj_name ASC';
	if ($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_assoc($r)) {
			$objdata['child'][$m['obj_name']] = F_get_object_data_array($m['obj_id'], $ilo, $capacity);
		}
	} else {
		F_display_db_error();
	}
	return $objdata;
}

/**
 * Convert array keys to template format.
 * @param $arr (array) Array to convert.
 * @param $prefix (string) Prefix to add to array keys.
 * @return array Template data array.
 */
function F_get_template_array($arr, $prefix='') {
	$tmp = array();
	if (!empty($prefix)) {
		$prefix .= '_';
	}
	foreach ($arr as $k => $v) {
		$newk = preg_replace('/[^a-zA-Z0-9]+/', '', $k);
		if (is_array($v)) {
			$tmp += F_get_template_array($v, $prefix.$newk);
		} else {
			$tmp['#~'.strtoupper($prefix.$newk).'~#'] = $v;
		}
	}
	return $tmp;
}

/**
 * Returns object data temnplates array.
 * @param $obj_id (int) Object ID.
 * @return array Object data array.
 */
function F_get_objects_templates_array($obj_id) {
	return F_get_template_array(F_get_object_data_array($obj_id));
}

/**
 * Get requested template and resolve nested templates.
 * @param $tmp_id (int) Template ID.
 * @return string template.
 */
function F_get_template($tmp_id) {
	global $l, $db;
	require_once('../config/tce_config.php');
	if (empty($tmp_id)) {
		return '';
	}
	// get template
	$template = '';
	$template_ids = '0';
	$sql = 'SELECT * FROM '.K_TABLE_TEMPLATES.' WHERE tmp_id='.intval($tmp_id).' LIMIT 1';
	if ($r = F_db_query($sql, $db)) {
		if ($m = F_db_fetch_array($r)) {
			$template_ids .= ','.$m['tmp_id'];
			$template = $m['tmp_template'];
			// replace nested templates
			$lp = 4; // maximum nesting level
			while (($lp > 0) AND (preg_match_all('/[#][~][T][E][M][P][L][A][T][E][_]([^~]+)[~][#]/U', $template, $matches, PREG_SET_ORDER) > 0)) {
				--$lp;
				foreach ($matches as $v) {
					if (isset($v[1])) {
						$tname = F_escape_sql(strtoupper($v[1]));
						$sqlt = 'SELECT * FROM '.K_TABLE_TEMPLATES.' WHERE tmp_name=\''.$tname.'\' AND tmp_id NOT IN ('.$template_ids.') LIMIT 1';
						if ($rt = F_db_query($sqlt, $db)) {
							if ($mt = F_db_fetch_array($rt)) {
								$template_ids .= ','.$mt['tmp_id'];
								$template = str_replace('#~TEMPLATE_'.$tname.'~#', $mt['tmp_template'], $template);
							}
						} else {
							F_display_db_error();
						}
					}
				}
			}
		}
	} else {
		F_display_db_error();
	}
	return $template;
}

/**
 * Returns object ILO data (if available).
 * @param $obj_id (int) Object ID.
 * @return array with ILO information (IP, user, password)
 */
function F_get_object_ilo_info($obj_id) {
	global $l, $db;
	require_once('../config/tce_config.php');
	if (empty($obj_id)) {
		return false;
	}
	F_get_object_data($obj_id, $ilo);
	if (isset($ilo['ip']) AND isset($ilo['user']) AND isset($ilo['password'])) {
		return $ilo;
	}
	return array();
}

/**
 * Get the ILO power status.
 * @param $obj_id (int) Object ID.
 * @return string response ('on', 'off', '').
 */
function F_get_ilo_power_status($obj_id) {
	global $l, $db;
	require_once('../config/tce_config.php');
	$status = '';
	// get objects data and ILO power status
	$ilo = F_get_object_ilo_info(intval($obj_id));
	// GET power status using IPMI ILO (slow process)
	if (isset($ilo['ip']) AND isset($ilo['user']) AND isset($ilo['password'])) {
		// send one ping with the deadline, and if it succeeds continue with ipmitool
		$ilo_cmd = 'ping -c 1 -w 1 '.escapeshellarg($ilo['ip']).' >/dev/null 2>&1';
		// send ipmi command
		$ilo_cmd .= ' && ipmitool -I lanplus -H '.escapeshellarg($ilo['ip']).' -U '.escapeshellarg($ilo['user']).' -P '.escapeshellarg($ilo['password']);
		// ipmi option
		$ilo_cmd .= ' chassis power status';
		$ilo_status = exec($ilo_cmd);
		if (!empty($ilo_status)) {
			if (strpos($ilo_status, 'is on') !== false) {
				$status = 'on';
			} else {
				$status = 'off';
			}
		}
	}
	return $status;
}

/**
 * Get a form selector for datacenters.
 * @param $dcn_id (int) ID of selected datacenter.
 * @param $data (array) Datacenter data.
 * @param $edit (boolean) Set to true if the selector is used on edit form.
 * @return string HTML select form field code.
 */
function F_select_datacenter(&$dcn_id=0, &$data=array(), $edit=false) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_form.php');
	require_once('tce_functions_group_permissions.php');
	$user_id = intval($_SESSION['session_user_id']);
	$dcn_id = intval($dcn_id);
	if ($dcn_id > 0) {
		$dcn_selperm = F_getUserPermission($user_id, K_TABLE_DATACENTER_GROUPS, $dcn_id);
	}
	$out = '';
	$out .= '<div class="row">'.K_NEWLINE;
	$out .= '<span class="label">'.K_NEWLINE;
	$out .= '<label for="dcn_id"><a href="tce_view_datacenter.php?dcn_id='.$dcn_id.'" title="'.$l['t_view_datacenter'].'">'.$l['w_datacenter'].'</a></label>'.K_NEWLINE;
	$out .= '</span>'.K_NEWLINE;
	$out .= '<span class="formw">'.K_NEWLINE;
	$out .= '<input type="hidden" name="change_datacenter" id="change_datacenter" value="" />'.K_NEWLINE;
	$out .= '<select name="dcn_id" id="dcn_id" size="0" onchange="document.getElementById(\'change_datacenter\').value=1;document.getElementById(\'form_editor\').submit();" title="'.$l['w_datacenter'].'">'.K_NEWLINE;
	if ($edit) {
		$out .= '<option value="0" style="background-color:#009900;color:white;"';
		if ($dcn_id == 0) {
			$out .= ' selected="selected"';
		}
		$out .= '>+</option>'.K_NEWLINE;
	}
	$sql = 'SELECT * FROM '.K_TABLE_DATACENTERS.' ORDER BY dcn_name ASC';
	if ($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_array($r)) {
			$dcn_permission = F_getUserPermission($user_id, K_TABLE_DATACENTER_GROUPS, $m['dcn_id']);
			if ($dcn_permission > 0) {
				if (!isset($dcn_id) OR (($edit === false) AND empty($dcn_id))) {
					$dcn_id = $m['dcn_id'];
					$dcn_selperm = $dcn_permission;
				}
				$out .= '<option value="'.$m['dcn_id'].'"';
				if ($m['dcn_id'] == $dcn_id) {
					$out .= ' selected="selected"';
					$data = $m;
				}
				$out .= '>'.htmlspecialchars($m['dcn_name'], ENT_NOQUOTES, $l['a_meta_charset']).'&nbsp;</option>'.K_NEWLINE;
			}
		}
	} else {
		$out .= '</select></span></div>'.K_NEWLINE;
		F_display_db_error();
	}
	$out .= '</select>'.K_NEWLINE;
	if (isset($dcn_selperm) AND ($dcn_id > 0)) {
		if ($edit AND ($dcn_selperm > 0)) {
			$out .= '<a href="tce_view_datacenter.php?dcn_id='.$dcn_id.'" title="'.$l['t_view_datacenter'].'" class="xmlbutton">'.$l['w_view'].'</a>';
		} elseif ($dcn_selperm > 2) {
			$out .= '<a href="tce_edit_datacenters.php?dcn_id='.$dcn_id.'" title="'.$l['t_datacenter_editor'].'" class="xmlbutton">'.$l['w_edit_item'].'</a>';
		}
	}
	$out .= '</span>'.K_NEWLINE;
	$out .= '</div>'.K_NEWLINE;
	$out .= getFormNoscriptSelect('selectdatacenter');
	return $out;
}

/**
 * Get a form selector for suites.
 * @param $dcn_id (int) ID of selected datacenter.
 * @param $sts_id (int) ID of selected suite.
 * @param $suite_data (array) Suite data.
 * @param $edit (boolean) Set to true if the selector is used on edit form.
 * @return string HTML select form field code.
 */
function F_select_suite($dcn_id=0, &$sts_id=0, &$suite_data=array(), $edit=false) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_form.php');
	require_once('tce_functions_group_permissions.php');
	$user_id = intval($_SESSION['session_user_id']);
	$dcn_id = intval($dcn_id);
	$sts_id = intval($sts_id);
	if ($sts_id > 0) {
		$sts_selperm = F_getUserPermission($user_id, K_TABLE_SUITE_GROUPS, $sts_id);
	}
	$out = '';
	$out .= '<div class="row">'.K_NEWLINE;
	$out .= '<span class="label">'.K_NEWLINE;
	$out .= '<label for="sts_id"><a href="tce_view_suite.php?dcn_id='.$dcn_id.'&amp;sts_id='.$sts_id.'" title="'.$l['t_view_suite'].'">'.$l['w_suite'].'</a></label>'.K_NEWLINE;
	$out .= '</span>'.K_NEWLINE;
	$out .= '<span class="formw">'.K_NEWLINE;
	$out .= '<input type="hidden" name="change_suite" id="change_suite" value="" />'.K_NEWLINE;
	$out .= '<select name="sts_id" id="sts_id" size="0" onchange="document.getElementById(\'change_suite\').value=1;document.getElementById(\'form_editor\').submit();" title="'.$l['w_datacenter'].'">'.K_NEWLINE;
	if ($edit) {
		$out .= '<option value="0" style="background-color:#009900;color:white;"';
		if ($sts_id == 0) {
			$out .= ' selected="selected"';
		}
		$out .= '>+</option>'.K_NEWLINE;
	}
	$sql = 'SELECT * FROM '.K_TABLE_SUITES.' WHERE sts_dcn_id='.$dcn_id.' ORDER BY sts_name ASC';
	if ($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_array($r)) {
			$sts_permission = F_getUserPermission($user_id, K_TABLE_SUITE_GROUPS, $m['sts_id']);
			if ($sts_permission > 0) {
				if (!isset($sts_id) OR (($edit === false) AND empty($sts_id))) {
					$sts_id = $m['sts_id'];
					$sts_selperm = $sts_permission;
				}
				$out .= '<option value="'.$m['sts_id'].'"';
				if ($m['sts_id'] == $sts_id) {
					$out .= ' selected="selected"';
					$suite_data = $m;
				}
				$out .= '>'.htmlspecialchars($m['sts_name'], ENT_NOQUOTES, $l['a_meta_charset']).'&nbsp;</option>'.K_NEWLINE;
			}
		}
	} else {
		$out .= '</select></span></div>'.K_NEWLINE;
		F_display_db_error();
	}
	$out .= '</select>'.K_NEWLINE;
	if (isset($sts_selperm) AND ($sts_id > 0)) {
		if ($edit AND ($sts_selperm > 0)) {
			$out .= '<a href="tce_view_suite.php?dcn_id='.$dcn_id.'&amp;sts_id='.$sts_id.'" title="'.$l['t_view_suite'].'" class="xmlbutton">'.$l['w_view'].'</a>';
		} elseif ($sts_selperm > 2) {
			$out .= '<a href="tce_edit_suites.php?dcn_id='.$dcn_id.'&amp;sts_id='.$sts_id.'" title="'.$l['t_suite_editor'].'" class="xmlbutton">'.$l['w_edit_item'].'</a>';
		}
	}
	$out .= '</span>'.K_NEWLINE;
	$out .= '</div>'.K_NEWLINE;
	$out .= getFormNoscriptSelect('selectsuite');
	return $out;
}

/**
 * Get a form selector for racks.
 * @param $dcn_id (int) ID of selected datacenter.
 * @param $sts_id (int) ID of selected suite.
 * @param $rck_id (int) ID of selected rack.
 * @param $rack_data (array) Data of selected rack.
 * @param $rack_pos (array) Array of objects positions inside the rack.
 * @param $rack_name (array) Array of rack objects names.
 * @param $edit (boolean) Set to true if the selector is used on edit form.
 * @return string HTML select form field code.
 */
function F_select_rack($dcn_id=0, $sts_id=0, &$rck_id=0, &$rack_data=array(), &$rack_pos=array(), &$rack_name=array(), $edit=false) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_form.php');
	require_once('tce_functions_group_permissions.php');
	$user_id = intval($_SESSION['session_user_id']);
	$dcn_id = intval($dcn_id);
	$sts_id = intval($sts_id);
	$rck_id = intval($rck_id);
	if ($rck_id > 0) {
		$rck_selperm = F_getUserPermission($user_id, K_TABLE_RACK_GROUPS, $rck_id);
	}
	$out = '';
	$out .= '<div class="row">'.K_NEWLINE;
	$out .= '<span class="label">'.K_NEWLINE;
	$out .= '<label for="rck_id"><a href="tce_view_rack.php?dcn_id='.$dcn_id.'&amp;sts_id='.$sts_id.'&amp;rck_id='.$rck_id.'" title="'.$l['t_view_rack'].'">'.$l['w_rack'].'</a></label>'.K_NEWLINE;
	$out .= '</span>'.K_NEWLINE;
	$out .= '<span class="formw">'.K_NEWLINE;
	$out .= '<input type="hidden" name="change_rack" id="change_rack" value="" />'.K_NEWLINE;
	$out .= '<select name="rck_id" id="rck_id" size="0" onchange="document.getElementById(\'change_rack\').value=1;document.getElementById(\'form_editor\').submit()" title="'.$l['w_rack'].'">'.K_NEWLINE;
	if ($edit) {
		$out .= '<option value="0" style="background-color:#009900;color:white;"';
		if ($rck_id == 0) {
			$out .= ' selected="selected"';
		}
		$out .= '>+</option>'.K_NEWLINE;
	}
	$sql = 'SELECT * FROM '.K_TABLE_RACKS.' WHERE rck_sts_id='.$sts_id.' ORDER BY rck_name ASC';
	if ($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_array($r)) {
			$rck_permission = F_getUserPermission($user_id, K_TABLE_RACK_GROUPS, $m['rck_id']);
			if ($rck_permission > 0) {
				if (!isset($rck_id) OR (($edit === false) AND empty($rck_id))) {
					$rck_id = $m['rck_id'];
					$rck_selperm = $rck_permission;
				}
				$out .= '<option value="'.$m['rck_id'].'"';
				if ($m['rck_id'] == $rck_id) {
					$out .= ' selected="selected"';
					$rack_data = $m;
				}
				$out .= '>'.htmlspecialchars($m['rck_name'], ENT_NOQUOTES, $l['a_meta_charset']).'&nbsp;</option>'.K_NEWLINE;
				$rack_pos[$m['rck_position_x']][$m['rck_position_y']] = $m['rck_id'];
				$rack_name[$m['rck_id']] = $m['rck_name'].' - '.$m['rck_label'].' - '.$m['rck_tag'];
			}
		}
	} else {
		$out .= '</select></span></div>'.K_NEWLINE;
		F_display_db_error();
	}
	$out .= '</select>'.K_NEWLINE;
	if (isset($rck_selperm) AND ($rck_id > 0)) {
		if ($edit AND ($rck_selperm > 0)) {
			$out .= '<a href="tce_view_rack.php?dcn_id='.$dcn_id.'&amp;sts_id='.$sts_id.'&amp;rck_id='.$rck_id.'" title="'.$l['t_view_rack'].'" class="xmlbutton">'.$l['w_view'].'</a>';
		} elseif ($rck_selperm > 2) {
			$out .= '<a href="tce_edit_racks.php?dcn_id='.$dcn_id.'&amp;sts_id='.$sts_id.'&amp;rck_id='.$rck_id.'" title="'.$l['t_rack_editor'].'" class="xmlbutton">'.$l['w_edit_item'].'</a>';
		}
	}
	$out .= '</span>'.K_NEWLINE;
	$out .= '</div>'.K_NEWLINE;
	$out .= getFormNoscriptSelect('selectrack');
	return $out;
}

/**
 * Get a form selector for racks (multi mode).
 * @param $dcn_id (int) ID of selected datacenter.
 * @param $sts_id (int) ID of selected suite.
 * @param $rck_ids (array) Array of selected rack IDs.
 * @return string HTML select form field code.
 */
function F_select_multiple_racks($dcn_id=0, $sts_id=0, $rck_ids=array()) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_form.php');
	require_once('tce_functions_group_permissions.php');
	$user_id = intval($_SESSION['session_user_id']);
	$dcn_id = intval($dcn_id);
	$sts_id = intval($sts_id);
	$out = '';
	$out .= '<div class="row">'.K_NEWLINE;
	$out .= '<span class="label">'.K_NEWLINE;
	$out .= '<label for="rck_ids">'.$l['w_rack'].'</label>'.K_NEWLINE;
	$out .= '</span>'.K_NEWLINE;
	$out .= '<span class="formw">'.K_NEWLINE;
	$out .= '<select name="rck_ids[]" id="rck_ids" size="5" multiple="multiple">'.K_NEWLINE;
	$out .= '<option value="0" style="background-color:#009900;color:white;"';
	if (empty($rck_ids)) {
		$out .= ' selected="selected"';
	}
	$out .= '>*** '.$l['w_all'].' ***</option>'.K_NEWLINE;
	$sql = 'SELECT * FROM '.K_TABLE_RACKS.' WHERE rck_sts_id='.$sts_id.' ORDER BY rck_name ASC';
	if ($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_array($r)) {
			$rck_permission = F_getUserPermission($user_id, K_TABLE_RACK_GROUPS, $m['rck_id']);
			if ($rck_permission > 0) {
				$out .= '<option value="'.$m['rck_id'].'"';
				if (!empty($rck_ids) AND in_array($m['rck_id'], $rck_ids)) {
					$out .= ' selected="selected"';
					$out .= ' style="background-color:#ffffcc;color:black;"';
					$m['rck_name'] = '* '.$m['rck_name'];
				}
				$out .= '>'.htmlspecialchars($m['rck_name'], ENT_NOQUOTES, $l['a_meta_charset']).'&nbsp;</option>'.K_NEWLINE;
			}
		}
	} else {
		$out .= '</select></span></div>'.K_NEWLINE;
		F_display_db_error();
	}
	$out .= '</select>'.K_NEWLINE;
	$out .= '</span>'.K_NEWLINE;
	$out .= '</div>'.K_NEWLINE;
	return $out;
}

/**
 * Get a form selector for objects.
 * @param $dcn_id (int) ID of selected datacenter.
 * @param $sts_id (int) ID of selected suite.
 * @param $rck_id (int) ID of selected rack.
 * @param $obj_id (int) ID of selected object.
 * @param $edit (boolean) Set to true if the selector is used on edit form.
 * @return string HTML select form field code.
 */
function F_select_object($dcn_id=0, $sts_id=0, $rck_id=0, &$obj_id=0, $edit=false) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_form.php');
	require_once('tce_functions_group_permissions.php');
	$user_id = intval($_SESSION['session_user_id']);
	$dcn_id = intval($dcn_id);
	$sts_id = intval($sts_id);
	$rck_id = intval($rck_id);
	$obj_id = intval($obj_id);
	if ($obj_id > 0) {
		$obj_selperm = F_getUserPermission($user_id, K_TABLE_OBJECT_GROUPS, $obj_id);
	}
	$out = '';
	$out .= '<div class="row">'.K_NEWLINE;
	$out .= '<span class="label">'.K_NEWLINE;
	$out .= '<label for="obj_id"><a href="tce_view_object.php?dcn_id='.$dcn_id.'&amp;sts_id='.$sts_id.'&amp;rck_id='.$rck_id.'&amp;obj_id='.$obj_id.'" title="'.$l['t_view_object'].'">'.$l['w_object'].'</a></label>'.K_NEWLINE;
	$out .= '</span>'.K_NEWLINE;
	$out .= '<span class="formw">'.K_NEWLINE;
	$out .= '<input type="hidden" name="change_object" id="change_object" value="" />'.K_NEWLINE;
	$out .= '<select name="obj_id" id="obj_id" size="0" onchange="document.getElementById(\'change_object\').value=1;document.getElementById(\'form_editor\').submit()">'.K_NEWLINE;
	if ($edit) {
		$out .= '<option value="0" style="background-color:#009900;color:white;"';
		if ($obj_id == 0) {
			$out .= ' selected="selected"';
		}
		$out .= '>+</option>'.K_NEWLINE;
	}
	$sql = 'SELECT * FROM '.K_TABLE_OBJECTS.', '.K_TABLE_LOCATIONS.' WHERE obj_id=loc_obj_id AND loc_rack_id='.$rck_id.' ORDER BY loc_row_top DESC';
	if ($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_assoc($r)) {
			$obj_permission = F_getUserPermission($user_id, K_TABLE_OBJECT_GROUPS, $m['obj_id']);
			if ($obj_permission > 0) {
				if (!isset($obj_id) OR (($edit === false) AND empty($obj_id))) {
					$obj_id = $m['obj_id'];
					$obj_selperm = $obj_permission;
				}
				$out .= '<option value="'.$m['obj_id'].'"';
				if ($m['obj_id'] == $obj_id) {
					$out .= ' selected="selected"';
				}
				$position = sprintf('[%02d - %02d]', $m['loc_row_top'], $m['loc_row_bottom']);
				$out .= '>'.htmlspecialchars($position.' '.$m['obj_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
			}
		}
	} else {
		$out .= '</select></span></div>'.K_NEWLINE;
		F_display_db_error();
	}
	$out .= '</select>'.K_NEWLINE;
	if (isset($obj_selperm) AND ($obj_id > 0)) {
		if ($edit AND ($obj_selperm > 0)) {
			$out .= '<a href="tce_view_object.php?obj_id='.$obj_id.'" title="'.$l['t_view_object'].'" class="xmlbutton">'.$l['w_view'].'</a>';
		} elseif ($obj_selperm > 2) {
			$out .= '<a href="tce_edit_objects.php?obj_id='.$obj_id.'" title="'.$l['t_object_editor'].'" class="xmlbutton">'.$l['w_edit_item'].'</a>';
		}
	}
	$out .= '</span>'.K_NEWLINE;
	$out .= '</div>'.K_NEWLINE;
	$out .= getFormNoscriptSelect('selecobject');
	return $out;
}

/**
 * Get a form selector for object type.
 * @param $obt_id (int) ID of object type.
 * @param $edit (boolean) Set to true if the selector is used on edit form.
 * @return string HTML select form field code.
 */
function F_select_object_type(&$obt_id=0, $edit=false) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_form.php');
	$out = '';
	$out .= '<div class="row">'.K_NEWLINE;
	$out .= '<span class="label">'.K_NEWLINE;
	$out .= '<label for="obt_id">'.$l['w_object_type'].'</label>'.K_NEWLINE;
	$out .= '</span>'.K_NEWLINE;
	$out .= '<span class="formw">'.K_NEWLINE;
	$out .= '<input type="hidden" name="change_object_type" id="change_object_type" value="" />'.K_NEWLINE;
	$out .= '<select name="obt_id" id="obt_id" size="0" onchange="document.getElementById(\'change_object_type\').value=1;document.getElementById(\'form_editor\').submit()">'.K_NEWLINE;
	if ($edit) {
		$out .= '<option value="0" style="background-color:#009900;color:white;"';
		if ($obt_id == 0) {
			$out .= ' selected="selected"';
		}
		$out .= '>+</option>'.K_NEWLINE;
	}
	$sql = 'SELECT * FROM '.K_TABLE_OBJECT_TYPES.' WHERE 1 ORDER BY obt_name ASC';
	if($r = F_db_query($sql, $db)) {
		while($m = F_db_fetch_array($r)) {
			if (!isset($obt_id) OR (($edit === false) AND empty($obt_id))) {
				$obt_id = $m['obt_id'];
			}
			$out .= '<option value="'.$m['obt_id'].'"';
			if (strlen($m['obt_color']) == 6) {
				$out .= ' style="background-color:#'.$m['obt_color'].';color:#'.getContrastColor($m['obt_color']).'"';
			}
			if ($m['obt_id'] == $obt_id) {
				$out .= ' selected="selected"';
			}
			$out .= '>';
			if (F_getBoolean($m['obt_virtual'])) {
				$out .= '&otimes; ';
			}
			$out .= htmlspecialchars($m['obt_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
		}
	} else {
		$out .= '</select></span></div>'.K_NEWLINE;
		F_display_db_error();
	}
	$out .= '</select>'.K_NEWLINE;
	$out .= '</span>'.K_NEWLINE;
	$out .= '</div>'.K_NEWLINE;
	$out .= getFormNoscriptSelect('selectobjecttype');
	return $out;
}

/**
 * Get a form selector for object types (multi mode).
 * @param $obt_ids (array) Array of selected object type IDs.
 * @return string HTML select form field code.
 */
function F_select_multiple_object_types($obt_ids=array()) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_form.php');
	$out = '';
	$out .= '<div class="row">'.K_NEWLINE;
	$out .= '<span class="label">'.K_NEWLINE;
	$out .= '<label for="obt_ids">'.$l['w_object_type'].'</label>'.K_NEWLINE;
	$out .= '</span>'.K_NEWLINE;
	$out .= '<span class="formw">'.K_NEWLINE;
	$out .= '<select name="obt_ids[]" id="obt_ids" size="5" multiple="multiple">'.K_NEWLINE;
	$out .= '<option value="0" style="background-color:#009900;color:white;"';
	if (empty($obt_ids)) {
		$out .= ' selected="selected"';
	}
	$out .= '>*** '.$l['w_all'].' ***</option>'.K_NEWLINE;
	$sql = 'SELECT * FROM '.K_TABLE_OBJECT_TYPES.' WHERE 1 ORDER BY obt_name ASC';
	if ($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_array($r)) {
			$out .= '<option value="'.$m['obt_id'].'"';
			if (!empty($obt_ids) AND in_array($m['obt_id'], $obt_ids)) {
				$out .= ' selected="selected"';
				$out .= ' style="background-color:#ffffcc;color:black;"';
				$m['obt_name'] = '* '.$m['obt_name'];
			}
			$out .= '>'.htmlspecialchars($m['obt_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
		}
	} else {
		$out .= '</select></span></div>'.K_NEWLINE;
		F_display_db_error();
	}
	$out .= '</select>'.K_NEWLINE;
	$out .= '</span>'.K_NEWLINE;
	$out .= '</div>'.K_NEWLINE;
	return $out;
}

/**
 * Get a form selector for objects with parent info.
 * @param $obj_id (int) ID of selected object.
 * @param $field_name (string) Field name.
 * @param $label (string) Label.
 * @param $optzero (boolean) If true includes the empty option.
 * @param $submit (boolean) If true submit the form on change.
 * @param $view (boolean) If true display view button.
 * @param $edit (boolean) If true display edit button.
 * @return string HTML select form field code.
 */
function F_object_selector($obj_id=0, $field_name='obj_id', $label='object', $optzero=false, $submit=false, $view=false, $edit=false) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_form.php');
	require_once('tce_functions_group_permissions.php');
	$user_id = intval($_SESSION['session_user_id']);
	$obj_id = intval($obj_id);
	if ($obj_id > 0) {
		$obj_selperm = F_getUserPermission($user_id, K_TABLE_OBJECT_GROUPS, $obj_id);
	}
	$out = '';
	$out .= '<div class="row">'.K_NEWLINE;
	$out .= '<span class="label">'.K_NEWLINE;
	$out .= '<label for="'.$field_name.'">'.$label.'</label>'.K_NEWLINE;
	$out .= '</span>'.K_NEWLINE;
	$out .= '<span class="formw">'.K_NEWLINE;
	$out .= '<select name="'.$field_name.'" id="'.$field_name.'" size="0"';
	if ($submit) {
		$out .= ' onchange="document.getElementById(\'form_editor\').submit()"';
	}
	$out .= '>'.K_NEWLINE;
	if ($optzero) {
		$out .= '<option value="0" style="background-color:#009900;color:white;"';
		if ($obj_id == 0) {
			$out .= ' selected="selected"';
		}
		$out .= '>+</option>'.K_NEWLINE;
	}
	$sql = 'SELECT dcn_name, sts_name, rck_name, obj_name, obj_id
		FROM '.K_TABLE_DATACENTERS.', '.K_TABLE_SUITES.', '.K_TABLE_RACKS.', '.K_TABLE_LOCATIONS.', '.K_TABLE_OBJECTS.'
		WHERE loc_obj_id=obj_id AND loc_rack_id=rck_id AND rck_sts_id=sts_id AND sts_dcn_id=dcn_id
		ORDER BY dcn_name, sts_name, rck_name, obj_name ASC';
	if ($r = F_db_query($sql, $db)) {
		$listed_objs = '0'; // track listed objects
		while ($m = F_db_fetch_array($r)) {
			$obj_permission = F_getUserPermission($user_id, K_TABLE_OBJECT_GROUPS, $m['obj_id']);
			if ($obj_permission > 0) {
				$out .= '<option value="'.$m['obj_id'].'"';
				if ($m['obj_id'] == $obj_id) {
					$out .= ' selected="selected" style="background-color:#ffff00;"';
					$obj_selperm = $obj_permission;
				} else {
					$out .= ' style="background-color:#aaddff;"';
				}
				$name = htmlspecialchars($m['dcn_name'].' → '.$m['sts_name'].' → '.$m['rck_name'].' → '.$m['obj_name'], ENT_NOQUOTES, $l['a_meta_charset']);
				$out .= '>'.$name.'</option>'.K_NEWLINE;
				$out .= F_get_child_objects_items($m['obj_id'], array($obj_id), 1, $listed_objs, $name);
			}
		}
		// list objects without a location
		if (!empty($listed_objs)) {
			$sqlb = 'SELECT obj_id, obj_name FROM '.K_TABLE_OBJECTS.' WHERE obj_id NOT IN ('.$listed_objs.') ORDER BY obj_name ASC';
			if ($rb = F_db_query($sqlb, $db)) {
				while ($mb = F_db_fetch_array($rb)) {
					$out .= '<option value="'.$mb['obj_id'].'"';
					if ($mb['obj_id'] == $obj_id) {
						$out .= ' selected="selected" style="background-color:#ff9999;"';
					} else {
						$out .= ' style="background-color:#bbddff;"';
					}
					$out .= '>'.htmlspecialchars('['.$mb['obj_id'].'] '.$mb['obj_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
				}
			} else {
				F_display_db_error();
			}
		}
	} else {
		$out .= '</select></span></div>'.K_NEWLINE;
		F_display_db_error();
	}
	$out .= '</select>'.K_NEWLINE;
	if (isset($obj_selperm) AND ($obj_id > 0)) {
		if ($edit AND ($obj_selperm > 1)) {
			$out .= '<a href="tce_edit_objects.php?obj_id='.$obj_id.'" title="'.$l['t_object_editor'].'" class="xmlbutton">'.$l['w_edit'].'</a>'.K_NEWLINE;
		}
		if ($view AND ($obj_selperm > 0)) {
			$out .= '<a href="tce_view_object.php?obj_id='.$obj_id.'" title="'.$l['t_view_object'].'" class="xmlbutton">'.$l['w_view'].'</a>'.K_NEWLINE;
		}
	}
	$out .= '</span>'.K_NEWLINE;
	$out .= '</div>'.K_NEWLINE;
	return $out;
}

/**
 * Get a form selector for parent objects.
 * @param $obj_ids (array) IDs of selected objects.
 * @param $field_name (string) Field name.
 * @param $multiple (boolean) If true activte multiple selection mode.
 * @return string HTML select form field code.
 */
function F_parent_object_selector($obj_ids=array(), $field_name='omp_parent_obj_ids', $multiple=false) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_form.php');
	require_once('tce_functions_group_permissions.php');
	$user_id = intval($_SESSION['session_user_id']);
	$out = '';
	$out .= '<div class="row">'.K_NEWLINE;
	$out .= '<span class="label">'.K_NEWLINE;
	$out .= '<label for="'.$field_name.'">'.$l['w_child_of'].'</label>'.K_NEWLINE;
	$out .= '</span>'.K_NEWLINE;
	$out .= '<span class="formw">'.K_NEWLINE;
	$out .= '<select name="'.$field_name.'[]" id="'.$field_name.'"'.K_NEWLINE;
	if ($multiple) {
		$out .= 'size="5" multiple="multiple">'.K_NEWLINE;
	} else {
		$out .= 'size="0">'.K_NEWLINE;
	}
	$out .= '<option value="0"';
	if (empty($obj_ids)) {
		$out .= ' selected="selected"';
	}
	$out .= '>&nbsp;</option>'.K_NEWLINE;
	$sql = 'SELECT dcn_id, dcn_name, sts_id, sts_name, rck_id rck_name, obj_id, obj_name
		FROM '.K_TABLE_DATACENTERS.', '.K_TABLE_SUITES.', '.K_TABLE_RACKS.', '.K_TABLE_LOCATIONS.', '.K_TABLE_OBJECTS.'
		WHERE loc_obj_id=obj_id AND loc_rack_id=rck_id AND rck_sts_id=sts_id AND sts_dcn_id=dcn_id
		ORDER BY dcn_name, sts_name, rck_name, obj_name ASC';
	if ($r = F_db_query($sql, $db)) {
		$listed_objs = '0'; // track listed objects
		while ($m = F_db_fetch_array($r)) {
			$obj_permission = F_getUserPermission($user_id, K_TABLE_OBJECT_GROUPS, $m['obj_id']);
			if ($obj_permission > 0) {
				$out .= '<option value="'.$m['obj_id'].'"';
				$name = '';
				if (!empty($obj_ids) AND in_array($m['obj_id'], $obj_ids)) {
					$out .= ' selected="selected" style="background-color:#ffff00;"';
					$selected = '* ';
				} else {
					$out .= ' style="background-color:#aaddff;"';
					$selected = '';
				}
				$name .= htmlspecialchars($m['dcn_name'].' → '.$m['sts_name'].' → '.$m['rck_name'].' → '.$m['obj_name'], ENT_NOQUOTES, $l['a_meta_charset']);
				$out .= '>'.$selected.''.$name.'</option>'.K_NEWLINE;
				$out .= F_get_child_objects_items($m['obj_id'], $obj_ids, 1, $listed_objs, $name);
			}
		}
		// list objects without a location
		if (!empty($listed_objs)) {
			$sqlb = 'SELECT obj_id, obj_name FROM '.K_TABLE_OBJECTS.' WHERE obj_id NOT IN ('.$listed_objs.') ORDER BY obj_name ASC';
			if ($rb = F_db_query($sqlb, $db)) {
				while ($mb = F_db_fetch_array($rb)) {
					$out .= '<option value="'.$mb['obj_id'].'"';
					if (!empty($obj_ids) AND in_array($m['obj_id'], $obj_ids)) {
						$out .= ' selected="selected" style="background-color:#ffff00;"';
						$name .= '* ';
					} else {
						$out .= ' style="background-color:#aaddff;"';
					}
					$out .= '>'.htmlspecialchars('['.$mb['obj_id'].'] '.$mb['obj_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
				}
			} else {
				F_display_db_error();
			}
		}
	} else {
		$out .= '</select></span></div>'.K_NEWLINE;
		F_display_db_error();
	}
	$out .= '</select>'.K_NEWLINE;
	$out .= '</span>'.K_NEWLINE;
	$out .= '</div>'.K_NEWLINE;
	return $out;
}

/**
 * Get a form selector for colors.
 * @param $color (int) Selected $color in HEX RGB.
 * @param $field_name (string) Field name.
 * @param $label (string) Label.
 * @return string HTML select form field code.
 */
function F_select_color($color='d3d3d3', $field_name='cab_color', $label='color', $title='color') {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_form.php');
	include('../../shared/code/htmlcolors.php');
	$out = '';
	$out .= '<div class="row">'.K_NEWLINE;
	$out .= '<span class="label">'.K_NEWLINE;
	$out .= '<label for="'.$field_name.'">'.$label.'</label>'.K_NEWLINE;
	$out .= '</span>'.K_NEWLINE;
	$out .= '<span class="formw">'.K_NEWLINE;
	$out .= '<select name="'.$field_name.'" id="'.$field_name.'" size="0" title="'.$title.'" onchange="document.getElementById(\'selected_color\').style.backgroundColor=\'#\'+document.getElementById(\''.$field_name.'\').options[document.getElementById(\''.$field_name.'\').selectedIndex].value">'.K_NEWLINE;
	foreach ($webcolor as $name => $col) {
		$out .= '<option value="'.$col.'" style="background-color:#'.$col.';color:#'.getContrastColor($col).'"';
		if ($color == $col) {
			$out .= ' selected="selected"';
		}
		$out .= '>'.$name.' ['.$col.']</option>'.K_NEWLINE;
	}
	$out .= '</select>'.K_NEWLINE;
	$out .= '<span id="selected_color" style="border:1px solid black;background-color:#'.$color.';padding-left:30px;" title="'.$l['w_selected_color'].'">&nbsp;</span>'.K_NEWLINE;
	$out .= '<span style="border:1px solid black;background-color:#'.$color.';padding-left:30px;" title="'.$l['w_current_color'].'">&nbsp;</span>'.K_NEWLINE;
	$out .= '</span>'.K_NEWLINE;
	$out .= '</div>'.K_NEWLINE;
	return $out;
}

/**
 * Get the full object path listing all parents.
 * @param $obj_id (int) ID of selected object.
 * @param $link (boolean) If true includes html code for links.
 * @param $dcn_id (int) Datacenter ID.
 * @param $sts_id (int) Suite ID.
 * @param $rck_id (int) Rack ID.x
 * @return string object path.
 */
function F_get_object_path($obj_id, $link=false, &$dcn_id=0, &$sts_id=0, &$rck_id=0) {
	global $l, $db;
	require_once('../config/tce_config.php');
	if (empty($obj_id)) {
		return '';
	}
	$obj_id = intval($obj_id);
	$path = '';
	$sql = 'SELECT obj_name FROM '.K_TABLE_OBJECTS.' WHERE obj_id='.intval($obj_id).' LIMIT 1';
	if ($r = F_db_query($sql, $db)) {
		if ($m = F_db_fetch_array($r)) {
			$path = htmlspecialchars($m['obj_name'], ENT_NOQUOTES, $l['a_meta_charset']);
			// search for parents
			$sqlp = 'SELECT obj_id FROM '.K_TABLE_OBJECTS.', '.K_TABLE_OBJECTS_MAP.' WHERE omp_parent_obj_id=obj_id AND omp_child_obj_id='.$obj_id.' LIMIT 1';
			if ($rp = F_db_query($sqlp, $db)) {
				if ($mp = F_db_fetch_array($rp)) {
					if ($link) {
						$path = F_get_object_path($mp['obj_id'], $link, $dcn_id, $sts_id, $rck_id).' → <a href="tce_edit_objects.php?obj_id='.$obj_id.'" title="'.$l['t_object_editor'].'">'.$path.'</a>';
					} else {
						$path = F_get_object_path($mp['obj_id'], $link, $dcn_id, $sts_id, $rck_id).' → '.$path;
					}
				} else {
					// check for object location (if any)
					$sqll = 'SELECT dcn_id, sts_id, rck_id, dcn_name, sts_name, rck_name
						FROM '.K_TABLE_DATACENTERS.', '.K_TABLE_SUITES.', '.K_TABLE_RACKS.', '.K_TABLE_LOCATIONS.'
						WHERE loc_obj_id='.$obj_id.' AND loc_rack_id=rck_id AND rck_sts_id=sts_id AND sts_dcn_id=dcn_id
						LIMIT 1';
					if ($rl = F_db_query($sqll, $db)) {
						if ($ml = F_db_fetch_array($rl)) {
							$dcn_id = $ml['dcn_id'];
							$sts_id = $ml['sts_id'];
							$rck_id = $ml['rck_id'];
							if ($link) {
								$ppath = '<a href="tce_view_datacenter.php?dcn_id='.$ml['dcn_id'].'" title="'.$l['t_view_datacenter'].'">'.htmlspecialchars($ml['dcn_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</a>';
								$ppath .= ' → <a href="tce_view_suite.php?dcn_id='.$ml['dcn_id'].'&amp;sts_id='.$ml['sts_id'].'" title="'.$l['t_view_suite'].'">'.htmlspecialchars($ml['sts_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</a>';
								$ppath .= ' → <a href="tce_view_rack.php?dcn_id='.$ml['dcn_id'].'&amp;sts_id='.$ml['sts_id'].'&amp;rck_id='.$ml['rck_id'].'" title="'.$l['t_view_rack'].'">'.htmlspecialchars($ml['rck_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</a>';
								$path = $ppath.' → <a href="tce_view_object.php?dcn_id='.$ml['dcn_id'].'&amp;sts_id='.$ml['sts_id'].'&amp;rck_id='.$ml['rck_id'].'&amp;obj_id='.$obj_id.'" title="'.$l['t_view_object'].'">'.$path.'</a>';
							} else {
								$path = htmlspecialchars($ml['dcn_name'].' → '.$ml['sts_name'].' → '.$ml['rck_name'], ENT_NOQUOTES, $l['a_meta_charset']).' → '.$path;
							}
						}
					} else {
						F_display_db_error();
					}
				}
			} else {
				F_display_db_error();
			}
		}
	} else {
		F_display_db_error();
	}
	return $path;
}

/**
 * Get a form selector for connections between objects.
 * @param $cab_a_obj_id (int) ID of first object.
 * @param $cab_b_obj_id (int) ID of second object.
 * @param $cab_cbt_id (int) ID of cable type.
 * @return string HTML select form field code.
 */
function F_connection_selector($cab_a_obj_id=0, $cab_b_obj_id=0, $cab_cbt_id=0) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_form.php');
	$cab_a_obj_id = intval($cab_a_obj_id);
	$cab_b_obj_id = intval($cab_b_obj_id);
	$cab_cbt_id = intval($cab_cbt_id);
	$out = '';
	$out .= '<div class="row">'.K_NEWLINE;
	$out .= '<span class="label">'.K_NEWLINE;
	$out .= '<label for="cab_ids">'.$l['w_connection'].'</label>'.K_NEWLINE;
	$out .= '</span>'.K_NEWLINE;
	$out .= '<span class="formw">'.K_NEWLINE;
	$out .= '<select name="cab_ids" id="cab_ids" size="0"';
	$out .= ' onchange="document.getElementById(\'form_editor\').submit()"';
	$out .= '>'.K_NEWLINE;
	$out .= '<option value="0" style="background-color:#009900;color:white;"';
	if (($cab_a_obj_id == 0) OR ($cab_b_obj_id == 0)) {
		$out .= ' selected="selected"';
	}
	$out .= '>+</option>'.K_NEWLINE;
	$sql = 'SELECT * FROM '.K_TABLE_CABLES.', '.K_TABLE_CABLE_TYPES.' WHERE cab_cbt_id=cbt_id ORDER BY cab_cbt_id ASC, cab_color ASC';
	if ($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_array($r)) {
			$out .= '<option value="'.$m['cab_a_obj_id'].'|'.$m['cab_b_obj_id'].'|'.$m['cab_cbt_id'].'"';
			if (($m['cab_a_obj_id'] == $cab_a_obj_id) AND ($m['cab_b_obj_id'] == $cab_b_obj_id) AND ($m['cab_cbt_id'] == $cab_cbt_id)){
				$out .= ' selected="selected"';
			}
			$out .= ' style="background-color:#'.$m['cab_color'].';color:#'.getContrastColor($m['cab_color']).'"';
			$out .= '>'.htmlspecialchars(''.$m['cbt_name'].': ['.$m['cab_a_obj_id'].'] ↔ ['.$m['cab_b_obj_id'].']', ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
		}
	} else {
		$out .= '</select></span></div>'.K_NEWLINE;
		F_display_db_error();
	}
	$out .= '</select>'.K_NEWLINE;
	$out .= '</span>'.K_NEWLINE;
	$out .= '</div>'.K_NEWLINE;
	return $out;
}

/**
 * Get a form selector for connection type.
 * @param $cbt_id (int) ID of conenction type.
 * @param $edit (boolean) If true include features for the editing form.
 * @param $submit (boolean) If true submit the form on change but without all edit features.
 * @param $filedname (string) Field name.
 * @return string HTML select form field code.
 */
function F_select_connection_type($cbt_id=0, $edit=false, $submit=false, $filedname='cbt_id') {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/tce_functions_form.php');
	$cbt_id = intval($cbt_id);
	$out = '';
	$out .= '<div class="row">'.K_NEWLINE;
	$out .= '<span class="label">'.K_NEWLINE;
	$out .= '<label for="'.$filedname.'">'.$l['w_connection_type'].'</label>'.K_NEWLINE;
	$out .= '</span>'.K_NEWLINE;
	$out .= '<span class="formw">'.K_NEWLINE;
	if ($edit OR $submit) {
		$out .= '<input type="hidden" name="change_connection_type" id="change_connection_type" value="" />'.K_NEWLINE;
		$out .= '<select name="'.$filedname.'" id="'.$filedname.'" size="0" onchange="document.getElementById(\'change_connection_type\').value=1;document.getElementById(\'form_editor\').submit()">'.K_NEWLINE;
		$out .= '<option value="0" style="background-color:#009900;color:white;"';
		if ($cbt_id == 0) {
			$out .= ' selected="selected"';
		}
		$out .= '>+</option>'.K_NEWLINE;
	} else {
		$out .= '<select name="'.$filedname.'" id="'.$filedname.'" size="0">'.K_NEWLINE;
	}
	$sql = 'SELECT * FROM '.K_TABLE_CABLE_TYPES.' WHERE 1 ORDER BY cbt_name ASC';
	if($r = F_db_query($sql, $db)) {
		while($m = F_db_fetch_array($r)) {
			$out .= '<option value="'.$m['cbt_id'].'"';
			if($m['cbt_id'] == $cbt_id) {
				$out .= ' selected="selected"';
			}
			$out .= '>'.htmlspecialchars($m['cbt_name'], ENT_NOQUOTES, $l['a_meta_charset']).'</option>'.K_NEWLINE;
		}
	} else {
		$out .= '</select></span></div>'.K_NEWLINE;
		F_display_db_error();
	}
	$out .= '</select>'.K_NEWLINE;
	$out .= '</span>'.K_NEWLINE;
	$out .= '</div>'.K_NEWLINE;
	if ($edit) {
		$out .= getFormNoscriptSelect('selectconnectiontype');
	}
	return $out;
}


/**
 * Get data headers (keys) in CSV header (tab separated text values).
 * @param $data (array) Array of data (key => value).
 * @param $prefix (string) Prefix to add to keys.
 * @return string data
 */
function getNestedDataCSVHeader($data, $prefix='') {
	$csv = '';
	foreach ($data as $key => $value) {
		if (substr($key, 0, 3) == 'OBJ') {
			return $csv;
		}
		if (is_array($value)) {
			$csv .= getNestedDataCSVHeader($value, $prefix.$key.'_');
		} else {
			$csv .= "\t".$prefix.$key;
		}
	}
	return $csv;
}

/**
 * Get data in CSV format (tab separated text values) from a nested array data.
 * @param $data (array) Array of data.
 * @return string CSV data
 */
function getNestedDataCSV($data) {
	$csv = '';
	foreach ($data as $key => $value) {
		if (substr($key, 0, 3) == 'OBJ') {
			$csv .= "\n".getNestedDataCSVHeader($value)."\n";
		}
		if (is_array($value)) {
			$csv .= getNestedDataCSV($value);
		} else {
			$csv .= "\t".preg_replace("/[\t\n\r]+/", ' ', $value);
		}
	}
	return $csv;
}

/**
 * Remove unwanted items from an array using a key pattern.
 * @param $data (array) Array of data.
 * @param $pattern (string) Regular expression pattern for key to be removed.
 * @return array sanitized array.
 */
function sanitizeExportArray($data, $pattern) {
	foreach ($data as $k=> $v) {
		if (preg_match($pattern, $k) > 0) {
			unset($data[$k]);
		} elseif (is_array($v)) {
			$data[$k] = sanitizeExportArray($v, $pattern);
		}
	}
	return $data;
}

/**
 * Get an HTML representation of objects on the selected rack.
 * @param $dcn_id (int) ID of selected datacenter.
 * @param $sts_id (int) ID of selected suite.
 * @param $rck_id (int) ID of selected rack.
 * @param $rack_data (array) Array of rack data objects.
 * @return string.
 */
function getRackStack($dcn_id, $sts_id, $rck_id, &$rack_data) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('tce_functions_group_permissions.php');
	$user_id = intval($_SESSION['session_user_id']);
	$dcn_id = intval($dcn_id);
	$sts_id = intval($sts_id);
	$rck_id = intval($rck_id);
	// get ID for 'guest' object type
	$guest_obt_id = F_getObjectTypeID('guest');
	$rck_permission = F_getUserPermission($user_id, K_TABLE_RACK_GROUPS, $rck_id);
	// get objects on rack
	$rackstack = array();
	$sql = 'SELECT * FROM '.K_TABLE_OBJECTS.', '.K_TABLE_LOCATIONS.' WHERE obj_id=loc_obj_id AND loc_rack_id='.$rck_id.' ORDER BY loc_row_top DESC, loc_front DESC, loc_center DESC, loc_rear DESC';
	if ($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_assoc($r)) {
			$obj_permission = F_getUserPermission($user_id, K_TABLE_OBJECT_GROUPS, $m['obj_id']);
			if ($obj_permission > 0) {
				// get object position on the row
				$rpos = bindec(intval($m['loc_front']).intval($m['loc_center']).intval($m['loc_rear']));
				switch ($m['loc_side']) {
					case 'left':
					case 'right': {
						$rkey = $m['loc_side'].'_'.$m['loc_row_top'];
						if (F_getBoolean($m['loc_front'])) {
							$rkey .= '_front';
						} elseif (F_getBoolean($m['loc_rear'])) {
							$rkey .= '_rear';
						}
						break;
					}
					case 'top':
					case 'bottom': {
						$rkey = $m['loc_side'].'_'.$m['loc_row_top'].'_'.$rpos;
						break;
					}
					case '-':
					default: {
						$rkey = $m['loc_row_top'].'_'.$rpos;
						break;
					}
				}
				$rackstack[$rkey] = $m;
				// get guest list
				$rackstack[$rkey]['guests'] = F_getGuestList($m['obj_id'], $guest_obt_id);
				// get capacity for each object on rack
				$capacity = array();
				F_get_object_data_array($m['obj_id'], $ilo, $capacity);
				if (isset($capacity) AND !empty($capacity)) {
					$rackstack[$rkey]['capacity'] = $capacity;
				}
			}
		}
	} else {
		F_display_db_error();
	}
	
	$rackobjs = '<table class="rack">'.K_NEWLINE;
	$rackobjs .= '<tr style="font-size:80%;"><th>#</th>';
	$rackobjs .= '<th title="'.$l['w_front'].' - '.$l['w_left'].'">FL</th><th title="'.$l['w_front'].' - '.$l['w_right'].'">FR</th>';
	$rackobjs .= '<th style="min-width:50px;">'.$l['w_front'].'</th>';
	$rackobjs .= '<th style="min-width:50px;">'.$l['w_center'].'</th>';
	$rackobjs .= '<th style="min-width:50px;">'.$l['w_rear'].'</th>';
	$rackobjs .= '<th title="'.$l['w_rear'].' - '.$l['w_left'].'">RL</th><th title="'.$l['w_rear'].' - '.$l['w_right'].'">RR</th></tr>';

	$nextrow_front = ($rack_data['rck_height'] + 1);
	$nextrow_center = $nextrow_front;
	$nextrow_rear = $nextrow_front;
	$nextrow_frontleft = $nextrow_front;
	$nextrow_frontright = $nextrow_front;
	$nextrow_rearleft = $nextrow_front;
	$nextrow_rearright = $nextrow_front;
	
	// initialize array to store the number of free slots
	$freeslots = array('3L'=>0, '2L'=>0, '1L'=>0);

	for ($pos = ($rack_data['rck_height'] + 1); $pos >= 0 ; --$pos) {

		if ($pos > $rack_data['rck_height']) {
			// top
			$slidenum = $l['w_top'];
			$oidx = 'top_'.$pos;
		} elseif ($pos == 0) {
			// bottom
			$slidenum = $l['w_bottom'];
			$oidx = 'bottom_'.$pos;
		} else {
			$slidenum = $pos;
			$oidx = $pos;
		}

		$rackobjs .= '<tr>'.K_NEWLINE;
		$rackobjs .= '<td>'.$slidenum.'</td>';
		
		// object capacity
		$objcap = '';
		
		// LEFT-FRONT RACK SIDE
		if (isset($rackstack['left_'.$pos.'_front'])) {
			$oidx = 'left_'.$pos.'_front';
			if (isset($rackstack[$oidx]['capacity'])) {
				$objcap = F_getObjectCapacityString($rackstack[$oidx]['capacity']);
			}
			$lrowspan = (1 + $pos - $rackstack[$oidx]['loc_row_bottom']);
			$rackobjs .= '<td style="max-width:20px;" rowspan="'.$lrowspan.'" class="rackobject" id="robj_'.$rackstack[$oidx]['obj_id'].'"><a class="vtext" href="tce_view_object.php?dcn_id='.$dcn_id.'&amp;sts_id='.$sts_id.'&amp;rck_id='.$rck_id.'&amp;obj_id='.$rackstack[$oidx]['obj_id'].'#object" title="'.$l['w_show_details'].'">'.$rackstack[$oidx]['obj_name'].' - '.$rackstack[$oidx]['obj_label'].' - '.$rackstack[$oidx]['obj_tag'].$objcap.'</a></td>';
			$nextrow_frontleft = ($rackstack[$oidx]['loc_row_bottom'] - 1);
		} elseif ($pos <= $nextrow_frontleft) {
			if ($rck_permission > 1) {
				$rackobjs .= '<td><a href="tce_edit_objects.php?loc_rack_id='.$rck_id.'&amp;loc_side=left&amp;loc_row_top='.$pos.'&amp;loc_row_bottom='.$pos.'" title="'.$l['t_object_editor'].'">'.$l['w_new'].'</a></td>'.K_NEWLINE;
			} else {
				$rackobjs .= '<td>&nbsp;</td>'.K_NEWLINE;
			}
		}

		// RIGHT-FRONT RACK SIDE
		if (isset($rackstack['right_'.$pos.'_front'])) {
			$oidx = 'right_'.$pos.'_front';
			if (isset($rackstack[$oidx]['capacity'])) {
				$objcap = F_getObjectCapacityString($rackstack[$oidx]['capacity']);
			}
			$lrowspan = (1 + $pos - $rackstack[$oidx]['loc_row_bottom']);
			$rackobjs .= '<td style="max-width:20px;" rowspan="'.$lrowspan.'" class="rackobject" id="robj_'.$rackstack[$oidx]['obj_id'].'"><a class="vtext" href="tce_view_object.php?dcn_id='.$dcn_id.'&amp;sts_id='.$sts_id.'&amp;rck_id='.$rck_id.'&amp;obj_id='.$rackstack[$oidx]['obj_id'].'#object" title="'.$l['w_show_details'].'">'.$rackstack[$oidx]['obj_name'].' - '.$rackstack[$oidx]['obj_label'].' - '.$rackstack[$oidx]['obj_tag'].$objcap.'</a></td>';
			$nextrow_frontright = ($rackstack[$oidx]['loc_row_bottom'] - 1);
		} elseif ($pos <= $nextrow_frontright) {
			if ($rck_permission > 1) {
				$rackobjs .= '<td><a href="tce_edit_objects.php?loc_rack_id='.$rck_id.'&amp;loc_side=right&amp;loc_row_top='.$pos.'&amp;loc_row_bottom='.$pos.'" title="'.$l['t_object_editor'].'">'.$l['w_new'].'</a></td>'.K_NEWLINE;
			} else {
				$rackobjs .= '<td>&nbsp;</td>'.K_NEWLINE;
			}
		}

		// possible positions for single object on a rack row are:
		// 000 invalid
		// 001 1
		// 010 2
		// 011 3
		// 100 4
		// 101 invalid
		// 110 6
		// 111 7
		$rowpos = array(7,6,4,3,2,1);

		$tmpfront = false;
		$tmpcenter = false;
		$tmprear = false;

		// for each possible position combinations
		foreach ($rowpos as $rp) {
			$oidxr = $oidx.'_'.$rp;
			if (isset($rackstack[$oidxr])) {
				if (isset($rackstack[$oidxr]['capacity'])) {
					$objcap = F_getObjectCapacityString($rackstack[$oidxr]['capacity']);
				}
				// print object
				$rowspan = (1 + $pos - $rackstack[$oidxr]['loc_row_bottom']);
				// calculate object width (1, 2 or 3)
				$obj_cols = (intval($rackstack[$oidxr]['loc_front']) + intval($rackstack[$oidxr]['loc_center']) + intval($rackstack[$oidxr]['loc_rear']));
				// print object
				$obj_cell = '<td rowspan="'.$rowspan.'" colspan="'.$obj_cols.'" class="rackobject" id="robj_'.$rackstack[$oidxr]['obj_id'].'"><a href="tce_view_object.php?dcn_id='.$dcn_id.'&amp;sts_id='.$sts_id.'&amp;rck_id='.$rck_id.'&amp;obj_id='.$rackstack[$oidxr]['obj_id'].'#object" title="'.$l['w_show_details'].'">'.$rackstack[$oidxr]['obj_name'].' - '.$rackstack[$oidxr]['obj_label'].' - '.$rackstack[$oidxr]['obj_tag'].$objcap.'</a></td>';
				$nextrow = ($rackstack[$oidxr]['loc_row_bottom'] - 1);

				if ($rackstack[$oidxr]['loc_front']) {
					$tmpfront = true;
					$nextrow_front = $nextrow;
				}
				if ($rackstack[$oidxr]['loc_center']) {
					$tmpcenter = true;
					$nextrow_center = $nextrow;
				}
				if ($rackstack[$oidxr]['loc_rear']) {
					$tmprear = true;
					$nextrow_rear = $nextrow;
				}

				$empty_cols = 0;
				if ($rp < 4) {
					if (!$tmpfront) {
						if ($pos <= $nextrow_front) {
							++$empty_cols;
						}
					}
					if ($rp < 2) {
						if (!$tmpcenter) {
							if ($pos <= $nextrow_center) {
								++$empty_cols;
							}
						}
					}
				}
				// check for empty slots before this
				if ($empty_cols > 0) {
					if (($rp != 1) OR ($empty_cols != 1)) {
						if ($rck_permission > 1) {
							$rackobjs .= '<td colspan="'.$empty_cols.'"><a href="tce_edit_objects.php?loc_rack_id='.$rck_id.'&amp;loc_row_top='.$pos.'&amp;loc_row_bottom='.$pos.'" title="'.$l['t_object_editor'].'">'.$l['w_new'].'</a></td>'.K_NEWLINE;
						} else {
							$rackobjs .= '<td colspan="'.$empty_cols.'">&nbsp;</td>'.K_NEWLINE;
						}
						// count free slots
						if (($pos > 0) AND ($pos <= $rack_data['rck_height'])) {
							$freeslots[$empty_cols.'L'] += 1;
						}
					} else {
						// you cannot add an object in the middle
						$rackobjs .= '<td colspan="'.$empty_cols.'" style="background-color:#aaaaaa;">&nbsp;</td>'.K_NEWLINE;
					}
				}
				// print object
				$rackobjs .= $obj_cell;
			}
		} // end for each position

		// print last empty rows
		$empty_cols = 0;

		if (!$tmprear) {
			if ($pos <= $nextrow_rear) {
				++$empty_cols;
			}
			if (!$tmpcenter) {
				if ($pos <= $nextrow_center) {
					++$empty_cols;
				}
				if (!$tmpfront) {
					if ($pos <= $nextrow_front) {
						++$empty_cols;
					}
				}
			}
		}

		if ($empty_cols > 0) {
			if ($rck_permission > 1) {
				$rackobjs .= '<td colspan="'.$empty_cols.'"><a href="tce_edit_objects.php?loc_rack_id='.$rck_id.'&amp;loc_row_top='.$pos.'&amp;loc_row_bottom='.$pos.'" title="'.$l['t_object_editor'].'">'.$l['w_new'].'</a></td>'.K_NEWLINE;
			} else {
				$rackobjs .= '<td colspan="'.$empty_cols.'">&nbsp;</td>'.K_NEWLINE;
			}
			// count free slots
			if (($pos > 0) AND ($pos <= $rack_data['rck_height'])) {
				$freeslots[$empty_cols.'L'] += 1;
			}
		}

		// LEFT-REAR RACK SIDE
		if (isset($rackstack['left_'.$pos.'_rear'])) {
			$oidx = 'left_'.$pos.'_rear';
			if (isset($rackstack[$oidx]['capacity'])) {
				$objcap = F_getObjectCapacityString($rackstack[$oidx]['capacity']);
			}
			$lrowspan = (1 + $pos - $rackstack[$oidx]['loc_row_bottom']);
			$rackobjs .= '<td style="max-width:20px;" rowspan="'.$lrowspan.'" class="rackobject" id="robj_'.$rackstack[$oidx]['obj_id'].'"><a class="vtext" href="tce_view_object.php?dcn_id='.$dcn_id.'&amp;sts_id='.$sts_id.'&amp;rck_id='.$rck_id.'&amp;obj_id='.$rackstack[$oidx]['obj_id'].'#object" title="'.$l['w_show_details'].'">'.$rackstack[$oidx]['obj_name'].' - '.$rackstack[$oidx]['obj_label'].' - '.$rackstack[$oidx]['obj_tag'].$objcap.'</a></td>';
			$nextrow_rearleft = ($rackstack[$oidx]['loc_row_bottom'] - 1);
		} elseif ($pos <= $nextrow_rearleft) {
			if ($rck_permission > 1) {
				$rackobjs .= '<td><a href="tce_edit_objects.php?loc_rack_id='.$rck_id.'&amp;loc_side=left&amp;loc_row_top='.$pos.'&amp;loc_row_bottom='.$pos.'" title="'.$l['t_object_editor'].'">'.$l['w_new'].'</a></td>'.K_NEWLINE;
			} else {
				$rackobjs .= '<td>&nbsp;</td>'.K_NEWLINE;
			}
		}

		// RIGHT-REAR RACK SIDE
		if (isset($rackstack['right_'.$pos.'_rear'])) {
			$oidx = 'right_'.$pos.'_rear';
			if (isset($rackstack[$oidx]['capacity'])) {
				$objcap = F_getObjectCapacityString($rackstack[$oidx]['capacity']);
			}
			$lrowspan = (1 + $pos - $rackstack[$oidx]['loc_row_bottom']);
			$rackobjs .= '<td style="max-width:20px;" rowspan="'.$lrowspan.'" class="rackobject" id="robj_'.$rackstack[$oidx]['obj_id'].'"><a class="vtext" href="tce_view_object.php?dcn_id='.$dcn_id.'&amp;sts_id='.$sts_id.'&amp;rck_id='.$rck_id.'&amp;obj_id='.$rackstack[$oidx]['obj_id'].'#object" title="'.$l['w_show_details'].'">'.$rackstack[$oidx]['obj_name'].' - '.$rackstack[$oidx]['obj_label'].' - '.$rackstack[$oidx]['obj_tag'].$objcap.'</a></td>';
			$nextrow_rearright = ($rackstack[$oidx]['loc_row_bottom'] - 1);
		} elseif ($pos <= $nextrow_rearright) {
			if ($rck_permission > 1) {
				$rackobjs .= '<td><a href="tce_edit_objects.php?loc_rack_id='.$rck_id.'&amp;loc_side=right&amp;loc_row_top='.$pos.'&amp;loc_row_bottom='.$pos.'" title="'.$l['t_object_editor'].'">'.$l['w_new'].'</a></td>'.K_NEWLINE;
			} else {
				$rackobjs .= '<td>&nbsp;</td>'.K_NEWLINE;
			}
		}

		$rackobjs .= '</tr>'.K_NEWLINE;
	}
	
	// store rack stack
	$rack_data['rackstack'] = $rackstack;

	// store free slots info
	$rack_data['free_slots'] = $freeslots;
	
	$rackobjs .= '<tr><td colspan="8" style="background-color:#ffffcc;color:#000000;text-align:right;">'.$l['t_free_1u_slots'].': 3L='.$rack_data['free_slots']['3L'].', 2L='.$rack_data['free_slots']['2L'].', 1L='.$rack_data['free_slots']['1L'].'</td></tr>'.K_NEWLINE;

	$rackobjs .= '</table>'.K_NEWLINE;

	return $rackobjs;
}

/**
 * Return an HTML code containing form fields to filter data
 * @param $dcn_id (int) Datacenter ID.
 * @param $sts_id (int) Suite ID.
 * @param $rck_id (int) Rack ID.
 * @param $obt_id (int) Object ID.
 * @param $obj_owner_id (int) Owner ID.
 * @param $obj_tenant_id (int) Tenant ID.
 * @param $keywords (string) Keywords.
 * @return html code
 */
function F_getDataFilter($dcn_id=0, $sts_id=0, $rck_id=0, $obt_id=0, $obj_owner_id=0, $obj_tenant_id=0, $keywords=0) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('tce_functions_user_select.php');
	$out = '';
	$out .= '<fieldset class="subset" style="text-align:left;">'.K_NEWLINE;
	$out .= '<legend>'.$l['w_selection filter'].'</legend>'.K_NEWLINE;
	$out .= F_select_datacenter($dcn_id, $datacenter_data, true);
	$out .= F_select_suite($dcn_id, $sts_id, $suite_data, true);
	$out .= F_select_rack($dcn_id, $sts_id, $rck_id, $rack_data, $rack_pos, $rack_name, true);
	$out .= F_select_object_type($obt_id, true);
	$out .= F_get_user_selectbox($l['w_owner'], $obj_owner_id, 'obj_owner_id');
	$out .= F_get_user_selectbox($l['w_tenant'], $obj_tenant_id, 'obj_tenant_id');
	$out .= getFormRowTextInput('keywords', $l['w_keywords'], $l['w_search_keywords'], '', $keywords, '', 255, false, false, false, '');
	// generate button
	$out .= '<div class="row">';
	$out .= '<input type="submit" name="filter" id="filter" value="'.$l['w_filter'].'" title="'.$l['h_filter_objects'].'" />';
	$out .= '</div>'.K_NEWLINE;
	$out .= '</fieldset>'.K_NEWLINE;
	return $out;
}

/**
 * Return an HTML code containing selected objects.
 * @param $dcn_id (int) Datacenter ID.
 * @param $sts_id (int) Suite ID.
 * @param $rck_id (int) Rack ID.
 * @param $obt_id (int) Object ID.
 * @param $obj_owner_id (int) Owner ID.
 * @param $obj_tenant_id (int) Tenant ID.
 * @param $keywords (string) Keywords.
 * @param $list (boolean) If true output an unordered list instead of checkboxes list.
 * @return html code
 */
function F_getSelectedObject($dcn_id=0, $sts_id=0, $rck_id=0, $obt_id=0, $obj_owner_id=0, $obj_tenant_id=0, $keywords=0, $list=false) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('tce_functions_user_select.php');
	$user_id = intval($_SESSION['session_user_id']);
	$dcn_id = intval($dcn_id);
	$sts_id = intval($sts_id);
	$rck_id = intval($rck_id);
	$obt_id = intval($obt_id);
	$obj_owner_id = intval($obj_owner_id);
	$obj_tenant_id = intval($obj_tenant_id);
	$out = '';
	$out .= '<fieldset class="subset" style="text-align:left;">'.K_NEWLINE;
	echo '<legend>'.$l['w_objects'].'</legend>'.K_NEWLINE;
	// basic selection query
	$sql = 'SELECT obj_id FROM '.K_TABLE_OBJECTS.' WHERE obj_id>0';
	// basic object filtering
	if ($obt_id > 0) {
		$sql .= ' AND obj_obt_id='.$obt_id;
	}
	if ($obj_owner_id > 0) {
		$sql .= ' AND obj_owner_id='.$obj_owner_id;
	}
	if ($obj_tenant_id > 0) {
		$sql .= ' AND obj_tenant_id='.$obj_tenant_id;
	}
	if (!empty($keywords)) {
		// get all the words into an array
		$terms = preg_split("/[\s]+/i", $keywords);
		$wheresearch = '';
		foreach ($terms as $word) {
			$word = F_escape_sql($word);
			$wheresearch .= ' AND ((obj_name LIKE \'%'.$word.'%\')';
			$wheresearch .= ' OR (obj_label LIKE \'%'.$word.'%\')';
			$wheresearch .= ' OR (obj_tag LIKE \'%'.$word.'%\'))';
		}
		$sql .= ' AND'.substr($wheresearch, 5);
	}
	if ($r = F_db_query($sql, $db)) {
		$out .= '<div class="rowl">'.K_NEWLINE;
		if ($list) {
			$out .= '<ul>'.K_NEWLINE;
		}
		while ($m = F_db_fetch_array($r)) {
			$odcn_id = 0;
			$osts_id = 0;
			$orck_id = 0;
			$object_name = F_get_object_path($m['obj_id'], true, $odcn_id, $osts_id, $orck_id);
			// check if the current object ID respect other filtering options:
			$include = true;
			if ($dcn_id > 0) {
				$include = ($dcn_id == $odcn_id);
				if ($sts_id > 0) {
					$include = ($sts_id == $osts_id);
					if ($rck_id > 0) {
						$include = ($rck_id == $orck_id);
					}
				}
			}
			if ($include) {
				if ($list) {
					$out .= '<li>'.$object_name.'</li>'.K_NEWLINE;
				} else {
					$varname ='so_'.$m['obj_id'];
					$out .= '<input type="checkbox" name="'.$varname.'" id="'.$varname.'" value="1" title="'.$m['obj_id'].'"';
					if ((isset($_REQUEST['checkall']) AND ($_REQUEST['checkall'] == 1)) OR (isset($_REQUEST[$varname]) AND !isset($_REQUEST['checkall']))) {
						$out .= ' checked="checked"';
					}
					$out .= ' />';
					$out .= ' <label for="'.$varname.'">'.$object_name.'</label><br />'.K_NEWLINE;
				}
			}
		}
		if ($list) {
			$out .= '</ul>'.K_NEWLINE;
		} else {
			// check/uncheck all options
			$out .= '<br /><span>';
			$out .= '<input type="radio" name="checkall" id="checkall1" value="1" onclick="document.getElementById(\'form_editor\').submit()" />';
			$out .= '<label for="checkall1">'.$l['w_check_all'].'</label> ';
			$out .= '<input type="radio" name="checkall" id="checkall0" value="0" onclick="document.getElementById(\'form_editor\').submit()" />';
			$out .= '<label for="checkall0">'.$l['w_uncheck_all'].'</label>';
			$out .= '</span>'.K_NEWLINE;
		}
		$out .= '</div>'.K_NEWLINE;
	} else {
		F_display_db_error();
	}
	$out .= '</fieldset>'.K_NEWLINE;
	return $out;
}

/**
 * Return a list of child objects of the specified type for the selected parent object.
 * @param $obt_id (int) Object ID.
 * @param $obt_id (int) Object Type ID.
 * @return string.
 */
function F_getGuestList($obj_id, $obt_id) {
	global $l, $db;
	require_once('../config/tce_config.php');
	$obj_id = intval($obj_id);
	$obt_id = intval($obt_id);
	$cobj = array();
	$sql = 'SELECT * FROM '.K_TABLE_OBJECTS.', '.K_TABLE_OBJECTS_MAP.' WHERE omp_child_obj_id=obj_id AND omp_parent_obj_id='.intval($obj_id).' AND obj_obt_id='.intval($obt_id).' ORDER BY obj_name ASC';
	if ($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_assoc($r)) {
			$cobj[] = $m;
		}
	} else {
		F_display_db_error();
	}
	return $cobj;
}

/**
 * Return the object type ID with the selected name.
 * @param $name (string) Name of the object type.
 * @return int.
 */
function F_getObjectTypeID($name) {
	global $l, $db;
	require_once('../config/tce_config.php');
	$obt_id = 0;
	$sql = 'SELECT obt_id FROM '.K_TABLE_OBJECT_TYPES.' WHERE obt_name=\''.F_escape_sql($name).'\' LIMIT 1';
	if ($r = F_db_query($sql, $db)) {
		if ($m = F_db_fetch_array($r)) {
			$obt_id = $m['obt_id'];
		}
	} else {
		F_display_db_error();
	}
	return $obt_id;
}

/**
 * Return a string containing object capacity info.
 * @param $capacity (array) Array containing capacity data.
 * @return string.
 */
function F_getObjectCapacityString($capacity) {
	global $l, $db;
	require_once('../config/tce_config.php');
	$objcap = ''; // object capacity
	if (!empty($capacity)) {
		foreach ($capacity as $ck => $cv) {
			$objcap .= ' <span title="'.$l['w_port'].'">'.$ck.'</span>:<span title="'.$l['w_total'].'">'.$cv['total'].'</span>(<span title="'.$l['w_free'].'" style="font-weight:bold;">'.$cv['free'].'</span>)';
		}
		$objcap = ' ['.$objcap.']';
	}
	return $objcap;
}

/**
 * Clone the specified object, including child objects
 * @param $source_obj_id (int) Source parent object ID.
 * @param $target_obj_id (int) Target parent object ID.
 */
function F_clone_child_objects($source_obj_id, $target_obj_id) {
	global $l, $db;
	require_once('../config/tce_config.php');
	$sql = 'SELECT * FROM '.K_TABLE_OBJECTS.', '.K_TABLE_OBJECTS_MAP.' WHERE omp_child_obj_id=obj_id AND omp_parent_obj_id='.$source_obj_id.'';
	if ($r = F_db_query($sql, $db)) {
		while($m = F_db_fetch_array($r)) {
			// create new object
			$sqli = 'INSERT INTO '.K_TABLE_OBJECTS.' (
				obj_obt_id,
				obj_name,
				obj_description,
				obj_label,
				obj_tag,
				obj_mnf_id,
				obj_owner_id,
				obj_tenant_id
				) VALUES (
				'.$m['obj_obt_id'].',
				\''.$m['obj_name'].'\',
				'.F_empty_to_null($m['obj_description']).',
				'.F_empty_to_null($m['obj_label']).',
				'.F_empty_to_null($m['obj_tag']).',
				'.F_empty_to_null($m['obj_mnf_id']).',
				'.F_empty_to_null($m['obj_owner_id']).',
				'.F_empty_to_null($m['obj_tenant_id']).'
				)';
			if (!$ri = F_db_query($sqli, $db)) {
				F_display_db_error(false);
			} else {
				$child_obj_id = F_db_insert_id($db, K_TABLE_OBJECTS, 'obj_id');
				// add new object as child
				$sqli = 'INSERT INTO '.K_TABLE_OBJECTS_MAP.' (
					omp_parent_obj_id,
					omp_child_obj_id
					) VALUES (
					'.$target_obj_id.',
					'.$child_obj_id.'
					)';
				if (!$ri = F_db_query($sqli, $db)) {
					F_display_db_error(false);
				}
				F_clone_child_objects($m['obj_id'], $child_obj_id);
				}
		}
	} else {
		F_display_db_error();
	}
}

//============================================================+
// END OF FILE
//============================================================+
