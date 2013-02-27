<?php
//============================================================+
// File name   : tce_functions_netgraph.php
// Begin       : 2011-11-23
// Last Update : 2012-03-07
//
// Description : Functions to draw a graphic map of the network (require graphviz).
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
 * Functions to draw a graphic map of the network(require graphviz).
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-11-23
 */

/**
 * Get a network description in dot format for graphviz.
 * @param $cbt_id (int) ID of connection type or 0 for all connections.
 * @return string dot map.
 */
function F_get_network_dot_map($cbt_id=0) {
	global $l, $db;
	require_once('../config/tce_config.php');
	require_once('../../shared/code/htmlcolors.php');
	$dot = 'graph RackMapNetwork {'.K_NEWLINE;
	//$dot .= K_TAB.'splines=true;'.K_NEWLINE; // VERY SLOW OPTION!
	$dot .= K_TAB.'fontname=Helvetica;'.K_NEWLINE;
	$dot .= K_TAB.'fontsize=12;'.K_NEWLINE;
	$dot .= K_TAB.'fontcolor=black;'.K_NEWLINE;
	$dot .= K_TAB.'colorscheme=SVG;'.K_NEWLINE;
	// SELECT DATACENTER -------------------------------------------------------
	$sqldcn = 'SELECT * FROM '.K_TABLE_DATACENTERS.' ORDER BY dcn_name ASC';
	if ($rdcn = F_db_query($sqldcn, $db)) {
		while ($mdcn = F_db_fetch_array($rdcn)) {
			$dot .= K_TAB.'subgraph clusterDCN'.$mdcn['dcn_id'].' {'.K_NEWLINE;
			$dot .= K_TAB.K_TAB.'color=black;'.K_NEWLINE;
			$dot .= K_TAB.K_TAB.'bgcolor=palegreen;'.K_NEWLINE;
			$dot .= K_TAB.K_TAB.'label="'.F_compact_string($mdcn['dcn_name'], true).'";'.K_NEWLINE;
			$dot .= K_TAB.K_TAB.'URL="tce_view_datacenter.php?dcn_id='.$mdcn['dcn_id'].'";'.K_NEWLINE;
			$dot .= K_TAB.K_TAB.'tooltip="'.F_compact_string($mdcn['dcn_description'], true).'";'.K_NEWLINE;
			// SELECT SUITE ----------------------------------------------------
			$sqlsts = 'SELECT * FROM '.K_TABLE_SUITES.' WHERE sts_dcn_id='.$mdcn['dcn_id'].' ORDER BY sts_name ASC';
			if ($rsts = F_db_query($sqlsts, $db)) {
				while ($msts = F_db_fetch_array($rsts)) {
					$dot .= K_TAB.K_TAB.'subgraph clusterSTS'.$msts['sts_id'].' {'.K_NEWLINE;
					$dot .= K_TAB.K_TAB.K_TAB.'color=black;'.K_NEWLINE;
					$dot .= K_TAB.K_TAB.K_TAB.'bgcolor=azure;'.K_NEWLINE;
					$dot .= K_TAB.K_TAB.K_TAB.'label="'.F_compact_string($msts['sts_name'], true).'";'.K_NEWLINE;
					$dot .= K_TAB.K_TAB.K_TAB.'URL="tce_view_suite.php?dcn_id='.$mdcn['dcn_id'].'&sts_id='.$msts['sts_id'].'";'.K_NEWLINE;
					$dot .= K_TAB.K_TAB.K_TAB.'tooltip="'.F_compact_string($msts['sts_description'].' (F'.$msts['sts_floor'].' ['.$msts['sts_width'].'x'.$msts['sts_height'].'])', true).'";'.K_NEWLINE;
					// SELECT RACK --------------------------------------------
					$sqlrck = 'SELECT * FROM '.K_TABLE_RACKS.' WHERE rck_sts_id='.$msts['sts_id'].' ORDER BY rck_name ASC';
					if ($rrck = F_db_query($sqlrck, $db)) {
						while ($mrck = F_db_fetch_array($rrck)) {
							$dot .= K_TAB.K_TAB.K_TAB.'subgraph clusterRCK'.$mrck['rck_id'].' {'.K_NEWLINE;
							$dot .= K_TAB.K_TAB.K_TAB.K_TAB.'color=black;'.K_NEWLINE;
							$dot .= K_TAB.K_TAB.K_TAB.K_TAB.'bgcolor=lemonchiffon;'.K_NEWLINE;
							$dot .= K_TAB.K_TAB.K_TAB.K_TAB.'label="'.F_compact_string($mrck['rck_name'], true).'";'.K_NEWLINE;
							$dot .= K_TAB.K_TAB.K_TAB.K_TAB.'URL="tce_view_rack?dcn_id='.$mdcn['dcn_id'].'&sts_id='.$msts['sts_id'].'&rck_id='.$mrck['rck_id'].'";'.K_NEWLINE;
							$dot .= K_TAB.K_TAB.K_TAB.K_TAB.'tooltip="'.F_compact_string($mrck['rck_description'].' - '.$mrck['rck_label'].' - '.$mrck['rck_tag'], true).'";'.K_NEWLINE;
							$dot .= K_TAB.K_TAB.K_TAB.K_TAB.'node [shape=box,style=filled,color=black,fillcolor=lightpink,fontname=Helvetica,fontsize=12,fontcolor=black];'.K_NEWLINE;
							// SELECT OBJECT -----------------------------------
							$sqlobj = 'SELECT * FROM '.K_TABLE_RACKS.', '.K_TABLE_LOCATIONS.', '.K_TABLE_OBJECTS.' WHERE loc_obj_id=obj_id AND loc_rack_id=rck_id AND rck_id='.$mrck['rck_id'].' ORDER BY obj_name ASC';
							if ($robj = F_db_query($sqlobj, $db)) {
								while ($mobj = F_db_fetch_array($robj)) {
									$dot .= F_get_object_dot_map($mobj['obj_id'], 4);
								}
							} else {
								F_display_db_error();
							}
							// -------------------------------------------------
							$dot .= K_TAB.K_TAB.K_TAB.'}'.K_NEWLINE; // END RACK
						}
					} else {
						F_display_db_error();
					}
					// ---------------------------------------------------------
					$dot .= K_TAB.K_TAB.'}'.K_NEWLINE; // END SUITE
				}
			} else {
				F_display_db_error();
			}
			// -----------------------------------------------------------------
			$dot .= K_TAB.'}'.K_NEWLINE; // END DATACENTER
		}
	} else {
		F_display_db_error();
	}
	// SET CONNECTIONS
	$sql = 'SELECT * FROM '.K_TABLE_CABLES.'';
	if ($cbt_id > 0) {
		$sql .= ' WHERE cab_cbt_id='.intval($cbt_id).'';
	}
	$sql .= ' ORDER BY cab_a_obj_id ASC, cab_b_obj_id ASC';
	if ($r = F_db_query($sql, $db)) {
		while ($m = F_db_fetch_array($r)) {
			$color = array_keys($webcolor, $m['cab_color']);
			if (!empty($color) AND isset($color[0])) {
				$color = $color[0];
			} else {
				$color = 'gray';
			}
			$dot .= K_TAB.'OBJ'.$m['cab_a_obj_id'].' -- OBJ'.$m['cab_b_obj_id'].' [penwidth=3,color='.$color.',URL="tce_edit_connections.php?cab_ids='.$m['cab_a_obj_id'].'|'.$m['cab_b_obj_id'].'|'.$m['cab_cbt_id'].'",tooltip="cable"];'.K_NEWLINE;
		}
	} else {
		F_display_db_error();
	}
	// -------------------------------------------------------------------------
	$dot .= '}'.K_NEWLINE; // END NETWORK
	return $dot;
}

/**
 * Get an object description in dot format for graphviz.
 * @param $obj_id (int) ID of parent object.
 * @param $obj_level (int) Nesting level.
 * @return string dot map.
 */
function F_get_object_dot_map($obj_id, $obj_level) {
	global $l, $db;
	require_once('../config/tce_config.php');
	include('../../shared/code/htmlcolors.php');
	$dot = '';
	$spacer = str_repeat(K_TAB, $obj_level);
	$spacerb = $spacer.K_TAB;
	// get object data
	$sqlobj = 'SELECT * FROM '.K_TABLE_OBJECTS.', '.K_TABLE_OBJECT_TYPES.' WHERE obj_obt_id=obt_id AND obj_id='.$obj_id.' ORDER BY obj_name ASC';
	if ($robj = F_db_query($sqlobj, $db)) {
		$mobj = F_db_fetch_array($robj);
	} else {
		F_display_db_error();
	}
	$color = array_keys($webcolor, $mobj['obt_color']);
	if (!empty($color) AND isset($color[0])) {
		$color = $color[0];
	} else {
		$color = 'white';
	}
	if (F_count_rows(K_TABLE_OBJECTS.', '.K_TABLE_OBJECTS_MAP, 'WHERE omp_child_obj_id=obj_id AND omp_parent_obj_id='.$obj_id) > 0) {
		// CLUSTER
		$dot .= $spacer.'subgraph clusterOBJ'.$mobj['obj_id'].' {'.K_NEWLINE;
		$dot .= $spacerb.'color='.((getContrastColor($webcolor[$color])=='000000')?'black':'white').';'.K_NEWLINE;
		$dot .= $spacerb.'bgcolor='.$color.';'.K_NEWLINE;
		$dot .= $spacerb.'label="'.F_compact_string($mobj['obj_name'], true).'";'.K_NEWLINE;
		$dot .= $spacerb.'URL="tce_view_object?obj_id='.$mobj['obj_id'].'";'.K_NEWLINE;
		$dot .= $spacerb.'tooltip="'.F_compact_string($mobj['obj_description'].' - '.$mobj['obj_label'].' - '.$mobj['obj_tag'], true).'";'.K_NEWLINE;
		$dot .= $spacerb.'node [shape=circle,style=filled,color=black,fontname=Helvetica,fontsize=12,fontcolor=black];'.K_NEWLINE;
		// for each child
		$sql = 'SELECT obj_id, obj_name, obj_label, obj_tag FROM '.K_TABLE_OBJECTS.', '.K_TABLE_OBJECTS_MAP.' WHERE omp_child_obj_id=obj_id AND omp_parent_obj_id='.$obj_id.' ORDER BY obj_name ASC';
		if ($r = F_db_query($sql, $db)) {
			while ($m = F_db_fetch_array($r)) {
				$dot .= F_get_object_dot_map($m['obj_id'], ($obj_level + 1));
			}
		} else {
			F_display_db_error();
		}
		$dot .= $spacer.'}'.K_NEWLINE;
	} else {
		// NODE
		$dot .= $spacer.'OBJ'.$mobj['obj_id'].' [fillcolor='.$color.',label="'.F_compact_string($mobj['obj_name'], true).'",URL="tce_edit_objects.php?obj_id='.$mobj['obj_id'].'",tooltip="'.F_compact_string($mobj['obj_description'].' - '.$mobj['obj_label'].' - '.$mobj['obj_tag'], true).'"];'.K_NEWLINE;
	}
	return $dot;
}

/**
 * Create an SVG graph of the network and return the image filename.
 * @param $cbt_id (int) ID of connection type or 0 for all connections.
 * @return string SVG filename or empty string in case of error.
 */
function F_get_network_svg_map($cbt_id=0) {
	global $l, $db;
	require_once('../config/tce_config.php');
	// get the dot descriotion of the network
	$dot = F_get_network_dot_map($cbt_id);
	// save the dot file on a temp directory
	$dotfile = 'rackmap_network.dot';
	$svgfile = 'rackmap_network.svg';
	if (file_put_contents(K_PATH_CACHE.$dotfile, $dot) !== false) {
		// generate SVG map
		exec(K_PATH_GRAPHVIZDOT.' -Tsvg '.K_PATH_CACHE.$dotfile.' > '.K_PATH_CACHE.$svgfile);
		// remove temporary dot file
		//DEBUG unlink(K_PATH_CACHE.$dotfile);
		// open the SVG file
		$svg = file_get_contents(K_PATH_CACHE.$svgfile);
		unlink(K_PATH_CACHE.$svgfile);
		/*
		// add pan and zoom controls
		$ctr = '<!-- PAN AND ZOOM SCRIPT -->'.K_NEWLINE;
		$ctr .= '<script type="text/ecmascript">'.K_NEWLINE;
		$ctr .= '<![CDATA['.K_NEWLINE;
		$ctr .= 'if(!window){window = this;}'.K_NEWLINE;
		$ctr .= 'function handleLoad(evt) {if(!document){window.document=evt.target.ownerDocument;}}'.K_NEWLINE;
		$ctr .= 'function handleZoom(evt){try {if(evt.newScale===undefined){throw \'bad interface\'};var tlist = document.getElementById(\'zoomControls\').transform.baseVal;tlist.getItem(0).setScale(1/evt.newScale, 1/evt.newScale);tlist.getItem(1).setTranslate(-evt.newTranslate.x,-evt.newTranslate.y);}catch(e){var de=document.documentElement;var tform=\'scale(\'+1/de.currentScale+\') \'+\'translate(\'+(-de.currentTranslate.x)+\', \'+(-de.currentTranslate.y)+\')\';document.getElementById(\'zoomControls\').setAttributeNS(null,\'transform\',tform);}}'.K_NEWLINE;
		$ctr .= 'function handlePan(evt){var ct=document.documentElement.currentTranslate;try {var tlist=document.getElementById(\'zoomControls\').transform.baseVal;tlist.getItem(1).setTranslate(-ct.x,-ct.y);}catch(e){var tform=\'scale(\'+1/document.documentElement.currentScale+\') \'+\'translate(\'+(-ct.x)+\', \'+(-ct.y)+\')\';document.getElementById(\'zoomControls\').setAttributeNS(null,\'transform\',tform);}}'.K_NEWLINE;
		$ctr .= 'function zoom(type){var de=document.documentElement;var oldScale = de.currentScale;var oldTranslate={x:de.currentTranslate.x,y:de.currentTranslate.y};var s=2;if(type==\'in\') {de.currentScale*=s;}else if(type==\'out\'){de.currentScale/=s;}var vp_width, vp_height;try {vp_width=de.viewport.width;vp_height=de.viewport.height;}catch (e){vp_width=window.innerWidth;vp_height=window.innerHeight;}de.currentTranslate.x=vp_width/2-(de.currentScale/oldScale)*(vp_width/2-oldTranslate.x);de.currentTranslate.y=vp_height/2-(de.currentScale/oldScale)*(vp_height/2-oldTranslate.y);'.K_NEWLINE;
		$ctr .= '}'.K_NEWLINE;
		$ctr .= 'function pan(type){var de=document.documentElement;var ct=de.currentTranslate;var t=30;if(type == \'right\'){ct.x+=t;}else if (type==\'down\'){ct.y+=t;}else if(type==\'left\'){ct.x-=t;}else if(type==\'up\'){ct.y-=t;}}'.K_NEWLINE;
		$ctr .= ']]>'.K_NEWLINE;
		$ctr .= '</script>'.K_NEWLINE;
		$ctr .= '<g id="zoomControls" transform="scale(1) translate(0,0)"><path d="m 20.000002,60.000033 19.999998,0 20,0 -10,9.999999 -9.999999,10 -10,-9.999999 z" id="panup" style="fill:#0000ff;stroke:none" onclick="pan(\'up\');" /><path d="m 19.999999,20.00003 0,19.999998 0,20 -9.9999991,-10 L 1.80876e-7,40.000029 9.9999992,30.000029 z" id="panright" style="fill:#0000ff;stroke:none" onclick="pan(\'right\');" /><path d="m 60.000001,60 0,-19.999998 0,-20 9.999999,10 10,9.999999 -9.999999,10 z" id="panleft" style="fill:#0000ff;stroke:none" onclick="pan(\'left\');" /><path d="m 59.999998,19.999977 -19.999998,0 -20,0 10,-9.9999991 9.999999,-9.999999719124 10,9.999999019124 z" id="pandown" style="fill:#0000ff;stroke:none" onclick="pan(\'down\');" /><path d="M 59.999997,59.999999 C 46.641969,46.700122 33.358587,33.264689 19.999999,20.000001 l 39.999998,0 z" id="zoomintriangle" style="fill:#ff0000;stroke:none" onclick="zoom(\'in\');" /><path d="m 46.678849,37.763823 0,-4.136471 -4.169042,0 0,-2.855358 4.169042,0 0,-4.136471 2.779359,0 0,4.136471 4.1799,0 0,2.855358 -4.1799,0 0,4.136471 -2.779359,0" id="zoomoutplus" style="line-height:125%;fill:#ffffff;fill-opacity:1;stroke:#800000;stroke-width:0.22222221" onclick="zoom(\'in\');" /><path d="m 20.000003,20.000001 c 13.358029,13.299877 26.64141,26.73531 39.999998,39.999998 l -39.999998,0 z" id="zoomouttriangle" style="fill:#00ff00;stroke:none" onclick="zoom(\'out\');" /><path d="m 28.573387,50.542308 0,-3.050782 5.992998,0 0,3.050782 -5.992998,0" id="zoomoutminus" style="line-height:125%;fill:#ffffff;fill-opacity:1;stroke:#008000;stroke-width:0.22222221" onclick="zoom(\'out\');" /></g>'.K_NEWLINE;
		$ctr .= '</svg>'.K_NEWLINE;
		$svg = str_replace('</svg>', $ctr, $svg);
		$svg = preg_replace('/viewBox="[^"]*"/U', 'onzoom="handleZoom(evt);" onscroll="handlePan(evt);" onload="handleLoad(evt);" xmlns:ev="http://www.w3.org/2001/xml-events"', $svg);
		*/
		// save the new svg version
		//file_put_contents(K_PATH_CACHE.$svgfile, $svg); // store a file cache
		$svg = preg_replace('/^(.*)<svg/Uis', '<svg', $svg);
		return $svg;
	}
	return '';
}

/**
 * Get a link to open SVG network map on a separate borowser window.
 * @return string HTML code.
 */
function F_get_network_map_iframe() {
	global $l, $db;
	require_once('../config/tce_config.php');
	$svg = F_get_network_svg_map();
	if ($svg === false) {
		return '';
	}
	$jsaction = 'selectWindow=window.open(\''.K_PATH_URL_CACHE.$svg.'\', \'networkMap\', \'dependent, height=600, width=800, menubar=no, resizable=yes, scrollbars=yes, status=no, toolbar=no\');return false;';
	$ret = '<iframe src="'.K_PATH_URL_CACHE.$svg.'" width="100%" height="800px">'.K_NEWLINE;
	$ret .= '<a href="'.K_PATH_URL_CACHE.$svg.'" onclick="'.$jsaction.'" title="'.$l['w_network_map'].'">'.$l['w_network_map'].'</a>'.K_NEWLINE;
	$ret .= '</iframe>'.K_NEWLINE;
	return $ret;
}

//============================================================+
// END OF FILE
//============================================================+
