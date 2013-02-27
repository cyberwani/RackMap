<?php
//============================================================+
// File name   : tce_config.php
// Begin       : 2002-02-24
// Last Update : 2012-01-09
//
// Description : Shared configuration file.
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
 * Shared configuration file.
 * @package net.rackmap.shared.cfg
 * @brief Shared configuration file.
 * @author Nicola Asuni
 * @since 2002-02-24
 */

/**
 * RackMap version (do not change).
 */
define ('K_RACKMAP_VERSION', '1.0.043');

/**
 * 2-letters code for default language.
 */
define ('K_LANGUAGE', 'en');

/**
 * If true, display a language selector.
 */
define ('K_LANGUAGE_SELECTOR', true);

/**
 * Defines a serialized array of available languages.
 * Each language is indexed using a 2-letters code (ISO 639).
 */
define ('K_AVAILABLE_LANGUAGES', serialize(array(
	'en' => 'English'
)));

ini_set('zend.ze1_compatibility_mode', false); // disable PHP4 compatibility mode

// -- INCLUDE files --
require_once('../../shared/config/tce_paths.php');
require_once('../../shared/config/tce_general_constants.php');

/**
 * If true enable One Time Password authentication on login.
 */
define ('K_OTP_LOGIN', true);

/**
 * User's session life time in seconds.
 */
define ('K_SESSION_LIFE', K_SECONDS_IN_HOUR);

/**
 * Define timestamp format using PHP notation (do not change).
 */
define ('K_TIMESTAMP_FORMAT', 'Y-m-d H:i:s');

/**
 * If true, check for possible session hijacking (set to false if you have login problems).
 */
define ('K_CHECK_SESSION_FINGERPRINT', true);

// Client Cookie settings

/**
 * Cookie domain.
 */
define ('K_COOKIE_DOMAIN', '');

/**
 * Cookie path.
 */
define ('K_COOKIE_PATH', '/');

/**
 * If true use secure cookies.
 */
define ('K_COOKIE_SECURE', false);

/**
 * Expiration time for cookies.
 */
define ('K_COOKIE_EXPIRE', K_SECONDS_IN_DAY);

/**
 * Various pages redirection modes after login (valid values are 1, 2, 3 and 4).
 * 1 = relative redirect.
 * 2 = absolute redirect.
 * 3 = html redirect.
 * 4 = full redirect.
 */
define ('K_REDIRECT_LOGIN_MODE', 3);

// Error settings

/**
 * Define error reporting types for debug.
 */
define ('K_ERROR_TYPES', E_ALL | E_STRICT);
//define ('K_ERROR_TYPES', E_ERROR | E_WARNING | E_PARSE);

/**
 * Enable error logs (../log/tce_errors.log).
 */
define ('K_USE_ERROR_LOG', false);

/**
 * If true display messages and errors on Javascript popup window.
 */
define ('K_ENABLE_JSERRORS', false);

/**
 * Set your own timezone here.
 * Possible values are listed on:
 * http://php.net/manual/en/timezones.php
 */
define ('K_TIMEZONE', 'Europe/London');


// ---------- * ---------- * ---------- * ---------- * ----------


/**
 * Error handlers.
 */
require_once('../../shared/code/tce_functions_errmsg.php');

// load language resources

// set user's selected language or default language
if(isset($_REQUEST['lang'])
	AND (strlen($_REQUEST['lang']) == 2)
	AND (array_key_exists($_REQUEST['lang'],unserialize(K_AVAILABLE_LANGUAGES)))) {
	/**
	 * Use requested language.
	 * @ignore
	 */
	define ('K_USER_LANG', $_REQUEST['lang']);
	// set client cookie
	setcookie('SessionUserLang', K_USER_LANG, time() + K_COOKIE_EXPIRE, K_COOKIE_PATH, K_COOKIE_DOMAIN, K_COOKIE_SECURE);
} elseif (isset($_COOKIE['SessionUserLang'])
	AND (strlen($_COOKIE['SessionUserLang']) == 2)
	AND (array_key_exists($_COOKIE['SessionUserLang'],unserialize(K_AVAILABLE_LANGUAGES)))) {
	/**
	 * Use session language.
	 * @ignore
	 */
	define ('K_USER_LANG', $_COOKIE['SessionUserLang']);
} else {
	/**
	 * Use default language.
	 * @ignore
	 */
	define ('K_USER_LANG', K_LANGUAGE);
}

// TMX class
require_once('../../shared/code/tce_tmx.php');
// istantiate new TMXResourceBundle object
$lang_resources = new TMXResourceBundle(K_PATH_TMX_FILE, K_USER_LANG, K_PATH_LANG_CACHE.basename(K_PATH_TMX_FILE, '.xml').'_'.K_USER_LANG.'.php');
$l = $lang_resources->getResource(); // language array

ini_set('arg_separator.output', '&amp;');
//date_default_timezone_set(K_TIMEZONE);

if(!defined('PHP_VERSION_ID')) {
	$version = PHP_VERSION;
	define('PHP_VERSION_ID', (($version{0} * 10000) + ($version{2} * 100) + $version{4}));
}
if (PHP_VERSION_ID < 50300) {
	@set_magic_quotes_runtime(false); //disable magic quotes
	ini_set('magic_quotes_gpc', 'On');
	ini_set('magic_quotes_runtime', 'Off');
	ini_set('register_long_arrays', 'On');
	//ini_set('register_globals', 'On');
}

// --- get 'post', 'get' and 'cookie' variables
foreach ($_REQUEST as $postkey => $postvalue) {
	if (($postkey{0} != '_') AND (!preg_match('/[A-Z]/', $postkey{0}))) {
		if (!get_magic_quotes_gpc() AND !is_array($postvalue)) {
			// emulate magic_quotes_gpc
			$postvalue = addslashes($postvalue);
			$_REQUEST[$postkey] = $postvalue;
			if (isset($_GET[$postkey])) {
				$_GET[$postkey] = $postvalue;
			} elseif (isset($_POST[$postkey])) {
				$_POST[$postkey] = $postvalue;
			} elseif (isset($_COOKIE[$postkey])) {
				$_COOKIE[$postkey] = $postvalue;
			}
		}
		$$postkey = $postvalue;
	}
}

//============================================================+
// END OF FILE
//============================================================+
