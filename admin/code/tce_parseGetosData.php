<?php
//=============================================================================+
// File name   : tce_parseGetosData.php
// Begin       : 2012-12-19
// Last Update : 2013-01-08
// Version     : 1.0.000
//
// Description : PHP Class to parse raw data returned by getos.sh script.
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
//               UK
//               http://www.rackmap.net
//               support@rackmap.net
//
// License:
//    Copyright (C) 2012-2013 Fubra Limited
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
//=============================================================================+

/**
 * @file
 * Functions for objects.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2013-01-08
 */

/**
 * @class parseGetosData
 * PHP Class to parse raw data returned by getos.sh script.
 * @package net.rackmap.admin
 * @version 1.0.000
 * @author Nicola Asuni
 */
class parseGetosData {

	/**
	 * Parsed data array.
	 * @protected
	 */
	protected $pdata;
	
	/**
	 * Keys used on the returned array
	 * @protected
	 */
	protected $item_keys = array('ip', 'hostname', 'os release', 'uname', 'os type', 'kernel name', 'kernel release', 'kernel version', 'kernel architecture', 'manufacturer', 'product', 'serial', 'uuid', 'ram', 'hddsize', 'disks', 'network', 'cpu', 'dmidecode');

	/**
	 * Disk keys used on the returned array
	 * @protected
	 */
	protected $disk_item_keys = array(0 => 'disk', 2 => 'size');

	/**
	 * This is the class constructor.
	 * @param $datafile (string) file containing the data - leave empty to pass the data string directly with the $data parameter.
	 * @param $data (string) String containing the raw tab-separated list of data - leave empty when using the data file.
	 */
	public function __construct($datafile='', $data='') {
		if (!empty($datafile)) {
			$data = file_get_contents($datafile);
		}
		$this->parseRawData($data);
	}

	/**
	 * Return parsed data as PHP array.
	 * @return Parsed data as PHP array.
	 */
	public function getArray() {
		return $this->pdata;
	}

	/**
	 * Return parsed data as JSON string.
	 * @return Parsed data as JSON string.
	 */
	public function getJson() {
		return json_encode($this->pdata);
	}

	/**
	 * Parse raw data and fill $data_array.
	 * @param $data (string) String containing the raw tab-separated list of data.
	 */
	public function parseRawData($data) {
		// initialize empty array
		$this->pdata = array();
		if (empty($data) OR ($data === false)) {
			return;
		}
		// remove first line (headers)
		$data = preg_replace('/^.+\n/', '', $data);
		$h = 0; // count hosts (lines)
		foreach (preg_split("/((\r?\n)|(\r\n?))/", $data) as $line) {
			if (!empty($line)) {
				$this->pdata[$h] = array();
				$i = 0; // count items (columns)
				foreach (preg_split("/(\t)/", $line) as $item) {
					switch ($this->item_keys[$i]) {
						case 'disks': {
							$this->pdata[$h][$this->item_keys[$i]] = array();
							$s = 0;
							foreach (preg_split("/<N>/", $item) as $disk) {
								$si = 0;
								$this->pdata[$h][$this->item_keys[$i]][$s] = array();
								foreach (preg_split("/<T>/", $disk) as $disk_item) {
									if (isset($this->disk_item_keys[$si])) {
										$this->pdata[$h][$this->item_keys[$i]][$s][$this->disk_item_keys[$si]] = trim($disk_item);
									}
									++$si;
								}
								++$s;
							}
							break;
						} 
						case 'network': {
							$this->pdata[$h][$this->item_keys[$i]] = array();
							$s = 0;
							foreach (preg_split("/<N>/", $item) as $net) {
								$this->pdata[$h][$this->item_keys[$i]][$s] = array();
								if (preg_match('/^([^< ]+)/', $net, $net_item) > 0) {
									$this->pdata[$h][$this->item_keys[$i]][$s]['device'] = trim($net_item[1]);
									if (preg_match('/HWaddr[\s]*([^\s<]+)/', $net, $net_item) > 0) {
										$this->pdata[$h][$this->item_keys[$i]][$s]['mac'] = trim($net_item[1]);
									}
									if (preg_match('/inet addr:[\s]*([^\s<]+)/', $net, $net_item) > 0) {
										$this->pdata[$h][$this->item_keys[$i]][$s]['ipv4'] = trim($net_item[1]);
									}
									if (preg_match('/Bcast:[\s]*([^\s<]+)/', $net, $net_item) > 0) {
										$this->pdata[$h][$this->item_keys[$i]][$s]['bcast'] = trim($net_item[1]);
									}
									if (preg_match('/Mask:[\s]*([^\s<]+)/', $net, $net_item) > 0) {
										$this->pdata[$h][$this->item_keys[$i]][$s]['mask'] = trim($net_item[1]);
									}
									if (preg_match('/inet6 addr:[\s]*([^\s<]+)/', $net, $net_item) > 0) {
										$this->pdata[$h][$this->item_keys[$i]][$s]['ipv6'] = trim($net_item[1]);
									}
									if (preg_match('/encap:[\s]*([^\s<]+)/', $net, $net_item) > 0) {
										$this->pdata[$h][$this->item_keys[$i]][$s]['encap'] = trim($net_item[1]);
									}
									if (preg_match('/Scope:[\s]*([^\s<]+)/', $net, $net_item) > 0) {
										$this->pdata[$h][$this->item_keys[$i]][$s]['scope'] = trim($net_item[1]);
									}
									if (preg_match('/MTU:[\s]*([^\s<]+)/', $net, $net_item) > 0) {
										$this->pdata[$h][$this->item_keys[$i]][$s]['mtu'] = trim($net_item[1]);
									}
									if (preg_match('/Metric:[\s]*([^\s<]+)/', $net, $net_item) > 0) {
										$this->pdata[$h][$this->item_keys[$i]][$s]['metric'] = trim($net_item[1]);
									}
								}
								++$s;
							}
							break;
						}
						case 'cpu': {
							$this->pdata[$h]['cpu'] = array();
							foreach (preg_split("/<N>/", $item) as $cpublock) {
								if (preg_match('/^([^<]+)<T>(.*)/', $cpublock, $match) > 0) {
									$this->pdata[$h]['cpu'][$match[1]] = $match[2];
									if ($match[1] == 'CPU socket(s)') {
										$this->pdata[$h]['cpu']['Socket(s)'] = $match[2];
									} elseif ($match[1] == 'Socket(s)') {
										$this->pdata[$h]['cpu']['CPU socket(s)'] = $match[2];
									}
								}
							}
							break;
						}
						case 'dmidecode': {
							$this->pdata[$h]['dmi'] = array();
							foreach (preg_split("/<N><N>/", $item) as $dmiblock) {
								if (preg_match('/^([^<]+)/', $dmiblock, $match, PREG_OFFSET_CAPTURE) > 0) {
									$offset = $match[1][1];
									$dmitype = trim($match[1][0]);
									if (!isset($this->pdata[$h]['dmi'][$dmitype])) {
										$s = 0;
										$this->pdata[$h]['dmi'][$dmitype] = array();
									}
									$this->pdata[$h]['dmi'][$dmitype][$s] = array();
									while (preg_match('/<N><T>([^<]+)/', $dmiblock, $match, PREG_OFFSET_CAPTURE, $offset) > 0) {
										$offset = $match[1][1];
										if (preg_match('/^([^:]+)[:]([ ]?)([^<]*)/', $match[1][0], $submatch) > 0) {
											if (!empty($submatch[2])) {
												$this->pdata[$h]['dmi'][$dmitype][$s][$submatch[1]] = $submatch[3];
											}	else {
												$this->pdata[$h]['dmi'][$dmitype][$s][$submatch[1]] = array();
												$si = 0;
												while (preg_match('/<N><T><T>([^<]+)/', $dmiblock, $match, PREG_OFFSET_CAPTURE, $offset) > 0) {
													$offset = $match[1][1];
													$this->pdata[$h]['dmi'][$dmitype][$s][$submatch[1]][$si] = $match[1][0];
													++$si;
												}
											}
										}
									}
								}
								++$s;
							}
							break;
						}
						case 'ram':
						case 'hddsize': {
							// convert value from kb to Bytes
							$this->pdata[$h][$this->item_keys[$i]] = (intval($item) * 1024);
							break;
						} 
						default: {
							$this->pdata[$h][$this->item_keys[$i]] = trim($item);
							break;
						}
					}
					++$i;
				}
				++$h;
			}
		}
	}

} // END OF CLASS

//=============================================================================+
// END OF FILE
//=============================================================================+
