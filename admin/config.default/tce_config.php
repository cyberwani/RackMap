<?php
//============================================================+
// File name   : tce_config.php
// Begin       : 2001-09-02
// Last Update : 2011-11-24
//
// Description : Configuration file for administration section.
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
 * Configuration file for administration section.
 * @package net.rackmap.admin.cfg
 * @brief Configuration file for administration section.
 * @author Nicola Asuni
 * @since 2001-09-02
 */

/**
 */

// --- INCLUDE FILES -----------------------------------------------------------

require_once('../config/tce_auth.php');
require_once('../../shared/config/tce_config.php');

// --- OPTIONS / COSTANTS ------------------------------------------------------

/**
 * Max memory limit.
 */
define ('K_MAX_MEMORY_LIMIT', '512M');

/**
 * Max number of rows to display in tables.
 */
define ('K_MAX_ROWS_PER_PAGE', 50);

/**
 * Max size to be uploaded in bytes.
 */
define ('K_MAX_UPLOAD_SIZE', 32000000);

/**
 * List of allowed file types for upload (remove all extensions to disable upload).
 * FOR SERVER SECURITY DO NOT ADD EXECUTABLE FILE TYPES HERE
 */
define ('K_ALLOWED_UPLOAD_EXTENSIONS', serialize(array('csv', 'xml', 'txt', 'png', 'gif', 'jpg', 'jpeg', 'svg', 'mp3', 'mid', 'oga', 'ogg', 'wav', 'wma', 'avi', 'flv', 'm2v', 'mpeg', 'mpeg4', 'mpg', 'mpg2', 'mpv', 'ogm', 'ogv', 'vid')));

// -- DEFAULT META and BODY Tags --

/**
 * RackMap title.
 */
define ('K_RACKMAP_TITLE', 'RackMap');

/**
 * RackMap description.
 */
define ('K_RACKMAP_DESCRIPTION', 'RackMap');

/**
 * RackMap Author.
 */
define ('K_RACKMAP_AUTHOR', 'Nicola Asuni');

/**
 * Reply-to meta tag.
 */
define ('K_RACKMAP_REPLY_TO', '');

/**
 * Default html meta keywords.
 */
define ('K_RACKMAP_KEYWORDS', 'RackMap, racks, inventory');

/**
 * Relative path to html icon.
 */
define ('K_RACKMAP_ICON', '../../favicon.ico');

/**
 * Full path to CSS stylesheet.
 */
define ('K_RACKMAP_STYLE', K_PATH_STYLE_SHEETS.'default.css');

/**
 * Full path to CSS stylesheet for RTL languages.
 */
define ('K_RACKMAP_STYLE_RTL', K_PATH_STYLE_SHEETS.'default_rtl.css');

/**
 * Full path to CSS stylesheet for help file.
 */
define ('K_RACKMAP_HELP_STYLE', K_PATH_STYLE_SHEETS.'help.css');

/**
 * If true display admin clock in UTC (GMT).
 */
define ('K_CLOCK_IN_UTC', false);

/**
 * Max number of chars to display on a selection box.
 */
define ('K_SELECT_SUBSTRING', 40);

/**
 * If true enable the backup download.
 */
define ('K_DOWNLOAD_BACKUPS', true);

/**
 * Path to graphviz dot executable (/usr/bin/dot).
 * On Debian/Ubuntu you can easily install graphviz using the following command:
 * "sudo apt-get install graphviz"
 */
define ('K_PATH_GRAPHVIZDOT', '/usr/bin/fdp');

// --- INCLUDE FILES -----------------------------------------------------------

require_once('../../shared/config/tce_db_config.php');
require_once('../../shared/code/tce_db_connect.php');
require_once('../../shared/code/tce_functions_general.php');

// --- PHP SETTINGS -----------------------------------------------------------

ini_set('memory_limit', K_MAX_MEMORY_LIMIT); // set PHPmemory limit
ini_set('upload_max_filesize', K_MAX_UPLOAD_SIZE); // set max upload size
ini_set('post_max_size', K_MAX_UPLOAD_SIZE); // set max post size
ini_set('session.use_trans_sid', 0); // if =1 use PHPSESSID

//============================================================+
// END OF FILE
//============================================================+
