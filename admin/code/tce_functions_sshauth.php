<?php
//============================================================+
// File name   : tce_functions_sshauth.php
// Begin       : 2012-01-13
// Last Update : 2012-01-20
//
// Description : Functions for updating SSH keys on remote authorize_keys files
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
 * Functions for updating SSH keys on remote authorize_keys files
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2012-01-13
 */

/**
 * Update remote authorize_keys file for the selected object.
 * @param $object_id (int) Object ID.
 * @param $newkeys (string) New keys to add.
 * @param $overwrite (boolean) If true removes any key not managed by RackMap.
 * @param $keep (boolean) If true keeps existing keys.
 * @return boolean true in case of success, false otherwise.
 */
function F_updateRemoteKeys($object_id=0, $newkeys='', $overwrite=false, $keep=false) {
	global $l, $db;
	require_once('../config/tce_config.php');
	// get object data
	$objdata = F_get_object_data_array($object_id);
	// get FQDN
	if (isset($objdata['attribute']['FQDN']) AND !empty($objdata['attribute']['FQDN'])) {
		// sanitize host name
		$host = preg_replace('/[^A-Za-z0-9\.\-]*/', '', $objdata['attribute']['FQDN']);
		// download the authorize_keys file on a temporary file
		$tmpfile = K_PATH_CACHE.'sshkeys/authorized_keys.'.$host;
		$cmd = 'scp -q -o StrictHostKeyChecking=no root@'.$host.':/root/.ssh/authorized_keys '.$tmpfile;
		exec($cmd);
		// create a backup of original permission file
		$cmd = 'scp -q -o StrictHostKeyChecking=no '.$tmpfile.' root@'.$host.':/root/.ssh/authorized_keys.BAK'.date('YmdHis');
		exec($cmd);
		// check if the file has been downloaded
		if (file_exists($tmpfile)) {
			// read the file
			$keys = file($tmpfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			$objkeys = $newkeys;
			// analyze each existing key
			foreach ($keys as $key) {
				if (preg_match('/[\s]RACKMAP_STATIC$/', $key, $matches) > 0) {
					// static keys are required by RackMap to access remote servers
					$objkeys .= $key.K_NEWLINE;
				} elseif (preg_match('/[\s]RACKMAP_MANAGED$/', $key, $matches) > 0) {
					// existing keys managed by rackmap are removed (unless $keep is true)
					if ($keep) {
						$objkeys .= $key.K_NEWLINE;
					}
				} elseif (!$overwrite) {
					// keys unmanaged by RackMap are removed only in overwrite mode, otherwise are preserved
					$objkeys .= $key.K_NEWLINE;
				}
			}
			// replace temp file with new keys
			if (file_put_contents($tmpfile, $objkeys) !== false) {
				// upload file
				$cmd = 'scp -q -o StrictHostKeyChecking=no '.$tmpfile.' root@'.$host.':/root/.ssh/authorized_keys';
				exec($cmd);
				unlink($tmpfile);
				return true;
			}
		} // end if file exist
	}
	return false;
}

//============================================================+
// END OF FILE
//============================================================+
