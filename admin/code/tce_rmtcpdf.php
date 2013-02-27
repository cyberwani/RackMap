<?php
//============================================================+
// File name   : tce_rmtcpdf.php
// Begin       : 2011-12-13
// Last Update : 2012-12-13
//
// Description : TCPDF extended class to print exported RackMap data.
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
 * TCPDF extended class to print exported RackMap data.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-12-13
 */

require_once('../config/tce_config.php');
require_once('../../shared/code/tcpdf.php');

/**
 * @class RMTCPDF
 * TCPDF extended class to print exported RackMap data.
 * @package net.rackmap.admin
 * @brief TCPDF extended class to print exported RackMap data.
 */
class RMTCPDF extends TCPDF {

	/**
	 * Path to print on header.
	 * @public
	 */
	public $header_path = '';

	/**
	 * Array containing all SSH access groups.
	 * @public
	 */
	protected $ssh_groups = array();

	/**
	 * If true show object details.
	 * @public
	 */
	protected $show_objects = true;

	/**
	 * Prints the page header.
	 * @public
	 */
	public function Header() {
		parent::Header();
		if (!empty($this->header_path)) {
			$this->SetFont('helvetica', '', 7);
			$this->y = 19;
			$this->Cell(0, 0, $this->header_path, 0, 1, 'R', false, '', 0, false, 'T', 'M');
		}
	}

	/**
	 * Prints the cover page and filtering information.
	 * @param $filter (array) array containing filtering info.
	 * @public
	 */
	public function printCoverPage($filter=array()) {
		$this->AddPage();
		// title
		$this->SetFont('times', 'B', 30);
		$this->SetTextColor(0,64,128);
		$this->ImageSVG(K_PATH_IMAGES.'rackmap_logo.svg', '', ($this->h / 5), $this->w, 30, '', 'N', 'C');
		$this->y = $this->getImageRBY() + 20;
		$this->Cell(0, 0, $this->l['t_datacenter_report'], 0, 1, 'C', false, '', 1, false, 'T', 'M');
		$this->Ln(20);
		$this->printFilterInfo($filter);
	}

	/**
	 * Prints the data filtering information.
	 * @param $filter (array) array containing filtering info.
	 * @public
	 */
	public function printFilterInfo($filter=array()) {
		$this->SetFont('helvetica', 'BI', 12);
		$this->SetTextColor(128,0,0);
		$this->Cell(0, 0, $this->l['t_selection filter'], 0, 1, 'C', false, '', 1, false, 'T', 'M');
		$this->Ln(5);
		// label width
		$lw = 80;
		// table for data filter
		$this->SetTextColor(0,0,0);
		$this->printDataLine($this->l['w_date'], $filter['date'], $lw);
		$this->printDataLine($this->l['w_datacenter'], $filter['datacenter'], $lw);
		$this->printDataLine($this->l['w_suite'], $filter['suite'], $lw);
		$this->printDataLine($this->l['w_rack'], $filter['rack'], $lw);
		$this->printDataLine($this->l['w_object_type'], $filter['object_type'], $lw);
		$this->printDataLine($this->l['w_owner'], $filter['owner'], $lw);
		$this->printDataLine($this->l['w_tenant'], $filter['tenant'], $lw);
		$this->printDataLine($this->l['w_keywords'], $filter['keywords'], $lw);
		$this->printDataLine($this->l['w_exclude'], $filter['exclude'], $lw);
		if ($filter['hideobj']) {
			// hide object details
			$this->show_objects = false;
		}
	}

	/**
	 * Prints a data line with label.
	 * @param $label (string) Label.
	 * @param $value (string) Value.
	 * @param $w (int) Width for label in user units.
	 * @param $fontsize (int) Font size.
	 * @public
	 */
	public function printDataLine($label='', $value='', $w=50, $fontsize=10) {
		$this->SetFont('helvetica', 'B', $fontsize);
		$this->Cell($w, 0, $label.': ', 0, 0, 'R', false, '', 1, false, 'T', 'M');
		$this->SetFont('helvetica', '', $fontsize);
		$this->MultiCell(0, 0, $value, 0, 'L', false, 1, '', '', true, 0, false, true, 0, 'T', false);
	}

	/**
	 * Prints a title.
	 * @param $title (string) Title.
	 * @param $level (int) Title level.
	 * @param $bookmark (boolean) If true set a PDF bookmark.
	 * @public
	 */
	public function printTitle($title='', $level=0) {
		$this->SetTextColor(0,64,128);
		$this->SetFont('times', 'B', (22 - (2 * $level)));
		$this->Bookmark($title, $level, 0, '', '', array(0,0,0));
		$this->Cell(0, 0, $title, 0, 1, 'L', false, '', 1, false, 'T', 'M');
		$this->Ln(10 - (0.5 * $level));
		$this->SetTextColor(0,0,0);
	}

	/**
	 * Prints a sub-title.
	 * @param $title (string) Title.
	 * @param $level (int) Title level.
	 * @param $bookmark (boolean) If true set a PDF bookmark.
	 * @public
	 */
	public function printSubTitle($title='', $level=0) {
		$this->SetTextColor(0,64,128);
		$this->SetFont('times', 'B', (22 - (2 * $level)));
		$this->Cell(0, 0, $title, 0, 1, 'L', false, '', 1, false, 'T', 'M');
		$this->Ln(5 - (0.5 * $level));
		$this->SetTextColor(0,0,0);
	}

	/**
	 * Print object conenctions.
	 * @param $data (array) Conenction data array.
	 */
	function printObjectConnections($data) {
		require_once('../../shared/code/tce_functions_general.php');
		$bgcolor = $data['cab_color'];
		$txtcolor = getContrastColor($data['cab_color']);
		$ret = '';
		$ret = '<span style="background-color:#'.$bgcolor.';color:#'.$txtcolor.';"> '.$data['cab_cbt_name'].' </span> '.str_replace('→', '>', $data['cab_path']);
		if (isset($data['cab_sub']) AND !empty($data['cab_sub'])) {
			$ret .= ' => '.$this->printObjectConnections($data['cab_sub']);
		}
		return $ret;
	}

	/**
	 * Print object info (including children and conenctions).
	 * @param $obj_id (int) Object ID.
	 * @param $objdata (array) Object data array.
	 * @param $tempfields (array) Array of template fields.
	 * @param $tfkeys (array) Array of template fields keys.
	 * @param $level (int) Indentation level.
	 */
	function printObjectInfo($obj_id, $objdata, $tempfields, $tfkeys, $level=3) {
		// list parent objects of virtual object
		if (isset($objdata['obj_parents']) AND !empty($objdata['obj_parents'])) {
			$parents = str_replace('<br />', "\n", $objdata['obj_parents']);
			$parents = preg_replace('/<[^>]+>/U', '', $parents);
			$parents = preg_replace('/[\x{2192}]/u', '>', $parents);
			$this->printDataLine($this->l['w_child_of'], $parents);
		}
		// manufacturer
		if (isset($objdata['obj_mnf']) AND !empty($objdata['obj_mnf'])) {
			$this->printDataLine($this->l['w_manufacturer'], $objdata['obj_mnf']);
		}
		// owner
		if (isset($objdata['obj_owner']) AND !empty($objdata['obj_owner'])) {
			$this->printDataLine($this->l['w_owner'], $objdata['obj_owner']);
		}
		// tenant
		if (isset($objdata['obj_tenant']) AND !empty($objdata['obj_tenant'])) {
			$this->printDataLine($this->l['w_tenant'], $objdata['obj_tenant']);
		}
		// label
		if (isset($objdata['obj_label']) AND !empty($objdata['obj_label'])) {
			$this->printDataLine($this->l['w_label'], $objdata['obj_label']);
		}
		// teg
		if (isset($objdata['obj_tag']) AND !empty($objdata['obj_tag'])) {
			$this->printDataLine($this->l['w_tag'], $objdata['obj_tag']);
		}
		// description
		if (isset($objdata['obj_description']) AND !empty($objdata['obj_description'])) {
			$this->printDataLine($this->l['w_description'], $objdata['obj_description']);
		}
		// attributes
		if (isset($objdata['attribute']) AND !empty($objdata['attribute'])) {
			foreach ($objdata['attribute'] as $atb_name => $atb_value) {
				if (!empty($atb_value)) {
					$val = $atb_value;
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
					$this->printDataLine($atb_name, $val);
				}
			}
		}
		// permission
		if (isset($objdata['permissions']) AND !empty($objdata['permissions'])) {
			$perms = '';
			foreach ($objdata['permissions'] as $group_id => $perm) {
				$perms .= "\n• ".$perm;
			}
			$this->printDataLine($this->l['t_permissions'], $perms);
		}
		// list connections
		if (isset($objdata['connection']) AND !empty($objdata['connection'])) {
			$this->SetFont('helvetica', 'B', 10);
			$this->Cell(50, 0, $this->l['w_connected_to'].': ', 0, 0, 'R', false, '', 1, false, 'T', 'M');
			$this->SetFont('helvetica', '', 10);
			$connection = $this->printObjectConnections($objdata['connection']);
			//$this->writeHTML($connection, true, false, true, false, '');
			$this->writeHTMLCell(0, 0, '', '', $connection, 0, 1, false, true, '', true);
		}
		$this->Ln(5);
		// *** list child ojbects (if any)
		if (isset($objdata['child']) AND !empty($objdata['child'])) {
			$lmargin = $this->lMargin;
			$this->lMargin += 10;
			$this->Ln(4);
			$this->printSubTitle($this->l['t_child_objects'].':', $level);
			++$level;
			$this->lMargin += 5;
			$this->Ln(1);
			foreach ($objdata['child'] as $name => $data) {
				$obj_name = $data['obj_name'];
				if ($data['obj_obt_virtual']) {
					$obj_name = '¤ '.$obj_name;
				}
				$this->printSubTitle($obj_name, $level);
				$this->printObjectInfo($data['obj_id'], $data, $tempfields, $tfkeys, $level);
			}
			$this->lMargin = $lmargin;
		}
	}

	/**
	 * Prints the data.
	 * @param $data (array) Array containing data to print.
	 * @public
	 */
	public function printData($data) {
		global $l, $db;
		require_once('../config/tce_config.php');
		$dcn_num = 0;
		// for each datacenter
		foreach ($data['datacenter'] as $key => $dcn_value) {
			// new datacenter ..................................................
			++$dcn_num;
			$sts_num = 0;
			$this->header_path = $dcn_value['dcn_name'];
			$this->AddPage();
			$this->printTitle($dcn_num.'. '.$this->l['t_datacenter'].': '.$dcn_value['dcn_name'], 0);
			$this->printDataLine($this->l['w_description'], $dcn_value['dcn_description']);
			$this->printDataLine($this->l['w_website'], $dcn_value['dcn_website_url']);
			$this->printDataLine($this->l['w_map'], $dcn_value['dcn_map_url']);
			// permission
			if (isset($dcn_value['dcn_permissions']) AND !empty($dcn_value['dcn_permissions'])) {
				$perms = '';
				foreach ($dcn_value['dcn_permissions'] as $group_id => $perm) {
					$perms .= "\n• ".$perm;
				}
				$this->printDataLine($this->l['t_permissions'], $perms);
			}
			foreach ($dcn_value['suite'] as $sts_key => $sts_value) {
				// new suite ...................................................
				++$sts_num;
				$rck_num = 0;
				$this->header_path = $dcn_value['dcn_name'].' > '.$sts_value['sts_name'];
				$this->AddPage();
				$this->printTitle($dcn_num.'.'.$sts_num.'. '.$this->l['t_suite'].': '.$sts_value['sts_name'], 1);
				$this->printDataLine($this->l['w_description'], $sts_value['sts_description']);
				$this->printDataLine($this->l['w_floor'], $sts_value['sts_floor']);
				$this->printDataLine($this->l['w_width'], $sts_value['sts_width']);
				$this->printDataLine($this->l['w_height'], $sts_value['sts_height']);
				// permission
				if (isset($sts_value['sts_permissions']) AND !empty($sts_value['sts_permissions'])) {
					$perms = '';
					foreach ($sts_value['sts_permissions'] as $group_id => $perm) {
						$perms .= "\n• ".$perm;
					}
					$this->printDataLine($this->l['t_permissions'], $perms);
				}
				$this->Ln(10);
				// get rack position info
				$rack_pos = array();
				foreach ($sts_value['rack'] as $sts_rack) {
					$rack_pos[$sts_rack['rck_position_x']][$sts_rack['rck_position_y']] = $sts_rack['rck_label'];
				}
				$this->SetFont('helvetica', '', 7);
				$this->SetLineStyle(array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
				// print suite map
				$rw = (($this->w - $this->lMargin - $this->rMargin) / ($sts_value['sts_width'] + 2));
				$rh = (($this->h - $this->y - $this->bMargin) / ($sts_value['sts_height'] + 2));
				$rw = min($rw, $rh, 10);
				$rh = $rw;
				for ($y = $sts_value['sts_height']; $y >= 0 ; --$y) {
					$this->SetTextColor(33,33,33);
					$this->Cell($rw, $rw, $y, 0, 0, 'C', false, '', 1, true, 'T', 'M');
					for ($x = 1; $x <= $sts_value['sts_width']; ++$x) {
						if ($y == 0) {
							$this->SetTextColor(33,33,33);
							$this->Cell($rw, $rw, $x, 0, 0, 'C', false, '', 1, true, 'T', 'M');
						} else {
							if (isset($rack_pos[$x][$y])) {
								$this->SetTextColor(255,255,255);
								$this->SetFillColor(0,128,0);
								$this->Cell($rw, $rw, $rack_pos[$x][$y], 1, 0, 'C', true, '', 1, true, 'T', 'M');
							} else {
								$this->SetFillColor(200,200,200);
								$this->Cell($rw, $rw, '', 1, 0, 'C', true, '', 1, true, 'T', 'M');
							}
						}
					}
					$this->Ln();
				}
				$this->SetFont('helvetica', '', 10);
				$this->SetTextColor(0,0,0);
				foreach ($sts_value['rack'] as $rck_value) {
					// new rack ................................................
					++$rck_num;
					$obj_num = 0;
					$this->header_path = $dcn_value['dcn_name'].' > '.$sts_value['sts_name'].' > '.$rck_value['rck_name'];
					$this->AddPage();
					$this->printTitle($dcn_num.'.'.$sts_num.'.'.$rck_num.'. '.$this->l['t_rack'].': '.$rck_value['rck_name'], 2);
					$this->printDataLine($this->l['w_description'], $rck_value['rck_description']);
					$this->printDataLine($this->l['w_label'], $rck_value['rck_label']);
					$this->printDataLine($this->l['w_tag'], $rck_value['rck_tag']);
					$this->printDataLine($this->l['w_height'], $rck_value['rck_height']);
					$this->printDataLine($this->l['w_position_x'], $rck_value['rck_position_x']);
					$this->printDataLine($this->l['w_position_y'], $rck_value['rck_position_y']);
					// permission
					if (isset($rck_value['rck_permissions']) AND !empty($rck_value['rck_permissions'])) {
						$perms = '';
						foreach ($rck_value['rck_permissions'] as $group_id => $perm) {
							$perms .= "\n• ".$perm;
						}
						$this->printDataLine($this->l['t_permissions'], $perms);
					}

					$this->Ln(10);

					// get HTML table code
					$rackobjs = $rck_value['rck_table'];

					// clean up the html code:
					// remove links
					$rackobjs = preg_replace('/<a[^\>]*>(.*?)<\/a>/si', "\\1", $rackobjs);
					$rackobjs = str_replace('>new<', 'style="background-color:#dddddd;"><', $rackobjs);
					$rackobjs = str_replace('style="max-width:20px;"', '', $rackobjs);
					$rackobjs = str_replace('class="rackobject"', 'style="background-color:#008000;color:#ffffff;"', $rackobjs);
					$rackobjs = str_replace('<th', '<th align="center"', $rackobjs);
					$rackobjs = str_replace('<td', '<td align="center"', $rackobjs);
					$rackobjs = str_replace('<tr style="font-size:80%;"><th align="center">#</th><th align="center" title="front - left">FL</th><th align="center" title="front - right">FR</th><th align="center" style="min-width:50px;">front</th><th align="center" style="min-width:50px;">center</th><th align="center" style="min-width:50px;">rear</th><th align="center" title="rear - left">RL</th><th align="center" title="rear - right">RR</th></tr><tr>', '<tr style="font-weight:bold;"><th align="center" width="10mm">#</th><th align="center" width="20mm">FL</th><th align="center" width="20mm">FR</th><th align="center" width="30mm">front</th><th align="center" width="30mm">center</th><th align="center" width="30mm">rear</th><th align="center" width="20mm">RL</th><th align="center" width="20mm">RR</th></tr><tr>', $rackobjs);
					$rackobjs = str_replace('>&nbsp;', ' style="background-color:#dddddd;">', $rackobjs);
					$rackobjs = str_replace('<table class="rack">', '<table cellpadding="0" cellspacing="0" border="1">', $rackobjs);
					$this->SetFont('helvetica', '', 7);
					$this->writeHTML($rackobjs, true, false, true, false, '');

					$this->SetFont('helvetica', '', 10);
					$this->SetTextColor(0,0,0);

					// create guest and capacity list
					$guestlist = '';
					$capacitylist = '';
					foreach ($rck_value['rck_rackstack'] as $rckobj) {
						// capacity (report free ports)
						if (isset($rckobj['capacity']) AND !empty($rckobj['capacity'])) {
							foreach ($rckobj['capacity'] as $ck => $cv) {
								if ($cv['free'] > 0) {
									$capacitylist .= '<tr>';
									$capacitylist .= sprintf('<td style="text-align:center;">%02d - %02d</td>',$rckobj['loc_row_top'], $rckobj['loc_row_bottom']);
									$capacitylist .= '<td>'.$rckobj['obj_name'].' - '.$rckobj['obj_label'].' - '.$rckobj['obj_tag'].'</td>';
									$capacitylist .= '<td style="text-align:center;">'.$ck.'</td>';
									$capacitylist .= '<td style="text-align:right;">'.$cv['total'].'</td>';
									$capacitylist .= '<td style="text-align:right;color:#006600;font-weight:bold;">'.$cv['free'].'</td>';
									$capacitylist .= '</tr>'.K_NEWLINE;
								}
							}
						}
						// guests
						if (!empty($rckobj['guests'])) {
							$guestlist .= '<li><strong>['.$rckobj['loc_row_top'].'-'.$rckobj['loc_row_bottom'].'] '.$rckobj['obj_name'].' - '.$rckobj['obj_label'].' - '.$rckobj['obj_tag'].'</strong><ul>';
							foreach ($rckobj['guests'] as $guest) {
								$guestlist .= '<li>'.$guest['obj_label'].'</li>'.K_NEWLINE;
							}
							$guestlist .= '</ul></li>';
						}
					}
					if (!empty($capacitylist)) {
						$this->AddPage();
						$this->printTitle($l['t_capacity_report'], 3);
						$capacitylist = '<table border="1" celpadding="2" cellspacing="0" style="font-size:80%;"><tr style="text-align:center;background-color:#003399;color:white;"><th width="10%">'.$l['w_position'].'</th><th width="60%">'.$l['w_object'].'</th><th width="10%">'.$l['w_port'].'</th><th width="10%">'.$l['w_total'].'</th><th width="10%">'.$l['w_free'].'</th></tr>'.$capacitylist.'</table>'.K_NEWLINE;
						$this->SetFont('helvetica', '', 7);
						$this->writeHTML($capacitylist, true, false, true, false, '');
					}
					if (!empty($guestlist)) {
						$this->AddPage();
						$this->printTitle($l['t_guest_list'], 3);
						$guestlist = '<ul>'.$guestlist.'</ul>';
						$this->SetFont('helvetica', '', 7);
						$this->writeHTML($guestlist, true, false, true, false, '');
					}

					$this->SetFont('helvetica', '', 10);
					$this->SetTextColor(0,0,0);

					// display objects details
					if ($this->show_objects) {
						foreach ($rck_value['object'] as $obj_value) {
							// new object ..........................................
							++$obj_num;
							$this->header_path = $dcn_value['dcn_name'].' > '.$sts_value['sts_name'].' > '.$rck_value['rck_name'].' > '.$obj_value['obj_name'];
							$this->AddPage();
							$this->printTitle($dcn_num.'.'.$sts_num.'.'.$rck_num.'.'.$obj_num.'. '.$this->l['t_object'].': '.$obj_value['obj_name'], 3);
							// get array of all object data
							$tempfields = F_get_template_array($obj_value);
							// extract template keys for lookup
							$tfkeys = implode("\n", array_keys($tempfields));
							// print port capacity
							if (isset($obj_value['capacity']) AND !empty($obj_value['capacity'])) {
								$capacity_data = '';
								foreach ($obj_value['capacity'] as $ck => $cv) {
									$capacity_data .= "\n• ".$ck.': '.$cv['total'].' = ('.$cv['used'].' '.$l['w_used'].' + '.$cv['free'].' '.$l['w_free'].')'.K_NEWLINE;
								}
								$this->printDataLine($this->l['w_capacity'], substr($capacity_data, 1));
							}
							$this->printObjectInfo($obj_value['obj_id'], $obj_value, $tempfields, $tfkeys, 4);
						} // end foreach object
					}

				} // end foreach rack
			} // end foreach suite
		} // end foreach datacenter
	}

	/**
	 * Prints the users tha belongs to each group.
	 * @param $data (array) Array containing data to print.
	 * @public
	 */
	public function printGroups($data) {
		if (empty($data)) {
			return;
		}
		$this->AddPage();
		$this->printTitle($this->l['t_user_groups'], 0);
		foreach ($data as $group => $users) {
			$this->printTitle($group, 1);
			$this->SetFont('helvetica', '', 10);
			foreach ($users as $user) {
				$this->Cell(0, 0, '• '.$user, 0, 1, '', false, '', 1, false, 'T', 'M');
			}
			$this->Ln(10);
		}
	}

} // END OF RMTCPDF CLASS

//============================================================+
// END OF FILE
//============================================================+
