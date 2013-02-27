<?php
//============================================================+
// File name   : tce_authorization.php
// Begin       : 2001-09-26
// Last Update : 2012-01-16
//
// Description : Check user authorization level.
//               Grants / deny access to pages.
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com
//               www.tecnick.com
//               info@tecnick.com
//
// License:
//    Copyright (C) 2004-2013 Nicola Asuni - Tecnick.com
//    Tecnick.com has granted the right for this file to be used for free only as a part of the RackMap software.
//    The code contained in this file can not be used for other purposes without explicit permission from Tecnick.com
//============================================================+

/**
 * @file
 * This script handles user's sessions.
 * Just the registered users granted with a username and a password are entitled to access the restricted areas (level > 0) of RackMap and the public area to perform the tests.
 * The user's level is a numeric value that indicates which resources (pages, modules, services) are accessible by the user.
 * To gain access to a specific resource, the user's level must be equal or greater to the one specified for the requested resource.
 * RackMap has 10 predefined user's levels:<ul>
 * <li>0 = anonymous user (unregistered).</li>
 * <li>1 = basic user (registered);</li>
 * <li>2-9 = configurable/custom levels;</li>
 * <li>10 = administrator with full access rights</li>
 * </ul>
 * @package net.rackmap.shared
 * @brief RackMap Shared Area
 * @author Nicola Asuni
 * @since 2001-09-26
 */

/**
 */

require_once('../config/tce_config.php');
require_once('../../shared/code/tce_functions_authorization.php');
require_once('../../shared/code/tce_functions_session.php');
require_once('../../shared/code/tce_functions_otp.php');

$logged = false; // the user is not yet logged in

// --- read existing user's session data from database
$PHPSESSIDSQL = F_escape_sql($PHPSESSID);
$session_hash = md5($PHPSESSID.getClientFingerprint());
$sqls = 'SELECT * FROM '.K_TABLE_SESSIONS.' WHERE cpsession_id=\''.$PHPSESSIDSQL.'\'';
if ($rs = F_db_query($sqls, $db)) {
	if ($ms = F_db_fetch_array($rs)) { // the user's session already exist
		// decode session data
		session_decode($ms['cpsession_data']);
		// check for possible session hijacking
		if (K_CHECK_SESSION_FINGERPRINT AND ((!isset($_SESSION['session_hash'])) OR ($_SESSION['session_hash'] != $session_hash))) {
			// display login form
			session_regenerate_id();
			F_login_form();
			exit();
		}
		// update session expiration time
		$expiry = date(K_TIMESTAMP_FORMAT, (time() + K_SESSION_LIFE));
		$sqlx = 'UPDATE '.K_TABLE_SESSIONS.' SET cpsession_expiry=\''.$expiry.'\' WHERE cpsession_id=\''.$PHPSESSIDSQL.'\'';
		if (!$rx = F_db_query($sqlx, $db)) {
			F_display_db_error();
		}
	} else { // session do not exist so, create new anonymous session
		$_SESSION['session_hash'] = $session_hash;
		$_SESSION['session_user_id'] = 1;
		$_SESSION['session_user_name'] = '- ['.substr($PHPSESSID, 12, 8).']';
		$_SESSION['session_user_ip'] = getNormalizedIP($_SERVER['REMOTE_ADDR']);
		$_SESSION['session_user_level'] = 0;
		$_SESSION['session_user_firstname'] = '';
		$_SESSION['session_user_lastname'] = '';
		// read client cookie
		if (isset($_COOKIE['LastVisit'])) {
			$_SESSION['session_last_visit'] = intval($_COOKIE['LastVisit']);
		} else {
			$_SESSION['session_last_visit'] = 0;
		}
		// track when user request logout
		if (isset($_REQUEST['logout'])) {
			$_SESSION['logout'] = true;
		}
		// set client cookie
		$cookie_now_time = time(); // note: while time() function returns a 32 bit integer, it works fine until year 2038.
		$cookie_expire_time = $cookie_now_time + K_COOKIE_EXPIRE; // set cookie expiration time
		setcookie('LastVisit', $cookie_now_time, $cookie_expire_time, K_COOKIE_PATH, K_COOKIE_DOMAIN, K_COOKIE_SECURE);
		setcookie('PHPSESSID', $PHPSESSID, $cookie_expire_time, K_COOKIE_PATH, K_COOKIE_DOMAIN, K_COOKIE_SECURE);
	}
} else {
	F_display_db_error();
}

// --- check if login information has been submitted
if ((isset($_POST['logaction'])) AND ($_POST['logaction'] == 'login')) {
	// check login attempt from the current client device to avoid brute force attack
	$bruteforce = true;
	$fingerprintkey = md5(getClientFingerprint());
	$sqlt = 'SELECT * FROM '.K_TABLE_SESSIONS.' WHERE cpsession_id=\''.$fingerprintkey.'\' LIMIT 1';
	if ($rt = F_db_query($sqlt, $db)) {
		if ($mt = F_db_fetch_array($rt)) {
			// check the expiration time
			if (strtotime($mt['cpsession_expiry']) < time()) {
				$bruteforce = false;
			}
			// update
			$wait = intval($mt['cpsession_data']);
			if ($wait < 86400) {
				$wait *= 2;
			}
			$sqlup = 'UPDATE '.K_TABLE_SESSIONS.' SET
				cpsession_expiry=\''.date(K_TIMESTAMP_FORMAT, (time() + $wait)).'\',
				cpsession_data=\''.$wait.'\'
				WHERE cpsession_id=\''.$fingerprintkey.'\'';
			if (!F_db_query($sqlup, $db)) {
				F_display_db_error();
			}
		} else {
			// add new record
			$wait = 1; // number of seconds to wait for the second attempt
			$sqls = 'INSERT INTO '.K_TABLE_SESSIONS.' (
				cpsession_id,
				cpsession_expiry,
				cpsession_data
				) VALUES (
				\''.$fingerprintkey.'\',
				\''.date(K_TIMESTAMP_FORMAT, (time() + $wait)).'\',
				\''.$wait.'\'
				)';
			if (!F_db_query($sqls, $db)) {
				F_display_db_error();
			}
			$bruteforce = false;
		}
	}
	if ($bruteforce) {
		F_print_error('WARNING', $l['m_login_brute_force'].' '.$wait);
	} else {
		$xuser_password = getPasswordHash($_POST['xuser_password']); // one-way password encoding
		// check if submitted login information are correct
		$sql = 'SELECT * FROM '.K_TABLE_USERS.' WHERE user_name=\''.F_escape_sql($_POST['xuser_name']).'\' AND user_password=\''.$xuser_password.'\'';
		if ($r = F_db_query($sql, $db)) {
			if ($m = F_db_fetch_array($r)) {
				// check One Time Password
				$otp = false;
				if (K_OTP_LOGIN) {
					$mtime = microtime(true);
					if ((isset($_POST['xuser_otpcode'])) AND !empty($_POST['xuser_otpcode']) 
						AND (($_POST['xuser_otpcode'] == F_getOTP($m['user_otpkey'], $mtime))
							OR ($_POST['xuser_otpcode'] == F_getOTP($m['user_otpkey'], ($mtime - 30)))
							OR ($_POST['xuser_otpcode'] == F_getOTP($m['user_otpkey'], ($mtime + 30))))) {
						// check if this OTP token has been alredy used
						$sqlt = 'SELECT cpsession_id FROM '.K_TABLE_SESSIONS.' WHERE cpsession_id=\''.$_POST['xuser_otpcode'].'\' LIMIT 1';
						if ($rt = F_db_query($sqlt, $db)) {
							if (!F_db_fetch_array($rt)) {
								// Store this token on the session table to mark it as invalid for 5 minute (300 seconds)
								$sqltu = 'INSERT INTO '.K_TABLE_SESSIONS.' (
									cpsession_id,
									cpsession_expiry,
									cpsession_data
									) VALUES (
									\''.$_POST['xuser_otpcode'].'\',
									\''.date(K_TIMESTAMP_FORMAT, (time() + 300)).'\',
									\'300\'
									)';
								if (!F_db_query($sqltu, $db)) {
									F_display_db_error();
								}
								$otp = true;
							}
						}
					}
				}
				if (!K_OTP_LOGIN OR $otp) {
					// sets some user's session data
					$_SESSION['session_user_id'] = $m['user_id'];
					$_SESSION['session_user_name'] = $m['user_name'];
					$_SESSION['session_user_ip'] = getNormalizedIP($_SERVER['REMOTE_ADDR']);
					$_SESSION['session_user_level'] = $m['user_level'];
					$_SESSION['session_user_firstname'] = urlencode($m['user_firstname']);
					$_SESSION['session_user_lastname'] = urlencode($m['user_lastname']);
					// read client cookie
					if (isset($_COOKIE['LastVisit'])) {
						$_SESSION['session_last_visit'] = intval($_COOKIE['LastVisit']);
					} else {
						$_SESSION['session_last_visit'] = 0;
					}
					$logged = true;
				} else {
					$login_error = true;
				}
			} elseif (!F_check_unique(K_TABLE_USERS, 'user_name=\''.F_escape_sql($_POST['xuser_name']).'\'')) {
				// the user name exist but the password is wrong
				//F_print_error('WARNING', $l['m_login_wrong']);
				$login_error = true;
			} else {
				// this user doesn't exist on RackMap database
				$login_error = true;
			}
		} else {
			F_display_db_error();
		}
	}
}

if (!isset($pagelevel)) {
	// set default page level
	$pagelevel = 0;
}

// check user's level
if ($pagelevel) { // pagelevel=0 means access to anonymous user
	// pagelevel >= 1
	if ($_SESSION['session_user_level'] < $pagelevel) { //check user level
		// To gain access to a specific resource, the user's level must be equal or greater to the one specified for the requested resource.
		F_login_form(); //display login form
	}
}

if ($logged) { //if user is just logged in: reloads page
	// html redirect
	$htmlredir = '<'.'?xml version="1.0" encoding="'.$l['a_meta_charset'].'"?'.'>'.K_NEWLINE;
	$htmlredir .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'.K_NEWLINE;
	$htmlredir .= '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$l['a_meta_language'].'" lang="'.$l['a_meta_language'].'" dir="'.$l['a_meta_dir'].'">'.K_NEWLINE;
	$htmlredir .= '<head>'.K_NEWLINE;
	$htmlredir .= '<title>ENTER</title>'.K_NEWLINE;
	$htmlredir .= '<meta http-equiv="refresh" content="0" />'.K_NEWLINE; //reload page
	$htmlredir .= '</head>'.K_NEWLINE;
	$htmlredir .= '<body>'.K_NEWLINE;
	$htmlredir .= '<a href="'.$_SERVER['SCRIPT_NAME'].'">ENTER</a>'.K_NEWLINE;
	$htmlredir .= '</body>'.K_NEWLINE;
	$htmlredir .= '</html>'.K_NEWLINE;
	switch (K_REDIRECT_LOGIN_MODE) {
		case 1: {
			// relative redirect
			header('Location: '.$_SERVER['SCRIPT_NAME']);
			break;
		}
		case 2: {
			// absolute redirect
			header('Location: '.K_PATH_HOST.$_SERVER['SCRIPT_NAME']);
			break;
		}
		case 3: {
			// html redirect
			echo $htmlredir;
			break;
		}
		case 4:
		default: {
			// full redirect
			header('Location: '.K_PATH_HOST.$_SERVER['SCRIPT_NAME']);
			echo $htmlredir;
			break;
		}
	}
	exit;
}

//============================================================+
// END OF FILE
//============================================================+
