<?php
//============================================================+
// File name   : tce_auth.php
// Begin       : 2002-09-02
// Last Update : 2012-12-13
//
// Description : Define access levels for each admin page
//               Note:
//                0 = Anonymous user (uregistered user)
//                1 = registered user
//               10 = System Administrator
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
 * Configuration file: define access levels for each admin page.
 * @package net.rackmap.admin.cfg
 * @author Nicola Asuni
 * @since 2002-09-02
 */

// ************************************************************
// SECURITY WARNING :
// SET THIS FILE AS READ ONLY AFTER MODIFICATIONS
// ************************************************************

/**
 * Administrator level.
 */
define ('K_AUTH_ADMINISTRATOR', 10);

/**
 * Advanced operator level.
 */
define ('K_AUTH_ADV_OPERATOR', 8);

/**
 * Operator level.
 */
define ('K_AUTH_OPERATOR', 6);

/**
 * Viewer level.
 */
define ('K_AUTH_VIEWER', 2);

/**
 * Required user's level to access index page.
 */
define ('K_AUTH_INDEX', 1);

/**
 * Required user's level to access "user editor".
 */
define ('K_AUTH_ADMIN_USERS', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to delete users.
 */
define ('K_AUTH_DELETE_USERS', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to access information about this application.
 */
define ('K_AUTH_ADMIN_INFO', 0);

/**
 * Required user's level to display online users.
 */
define ('K_AUTH_ADMIN_ONLINE_USERS', K_AUTH_OPERATOR);

/**
 * Required user's level to upload images.
 */
define ('K_AUTH_ADMIN_UPLOAD_IMAGES', K_AUTH_OPERATOR);

/**
 * Required user's level to import questions.
 */
define ('K_AUTH_BACKUP', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to access file manager for multimedia files.
 */
define ('K_AUTH_ADMIN_FILEMANAGER', K_AUTH_OPERATOR);

/**
 * Required user's level to create and delete directories.
 */
define ('K_AUTH_ADMIN_DIRS', K_AUTH_ADV_OPERATOR);

/**
 * Required user's level to delete multimedia files.
 */
define ('K_AUTH_DELETE_MEDIAFILE', K_AUTH_ADV_OPERATOR);

/**
 * Required user's level to rename multimedia files.
 */
define ('K_AUTH_RENAME_MEDIAFILE', K_AUTH_ADV_OPERATOR);

/**
 * Required user's level to access "group editor".
 */
define ('K_AUTH_ADMIN_GROUPS', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to delete user groups.
 */
define ('K_AUTH_DELETE_GROUPS', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to move users from one group to another.
 */
define ('K_AUTH_MOVE_GROUPS', K_AUTH_ADMINISTRATOR);

/**
 * Configuration of object types and attributes.
 */
define ('K_AUTH_ADMIN_CONFIG', K_AUTH_OPERATOR);

/**
 * Required user's level to access various tools
 */
define ('K_AUTH_ADMIN_TOOLS', K_AUTH_OPERATOR);

/**
 * Datacenter Administration.
 */
define ('K_AUTH_ADMIN_DATACENTERS', K_AUTH_OPERATOR);

/**
 * Datacenter Suites Administration.
 */
define ('K_AUTH_ADMIN_SUITES', K_AUTH_OPERATOR);

/**
 * Racks Administration.
 */
define ('K_AUTH_ADMIN_RACKS', K_AUTH_OPERATOR);

/**
 * Object Administration.
 */
define ('K_AUTH_ADMIN_OBJECTS', K_AUTH_OPERATOR);

/**
 * Object Type Administration.
 */
define ('K_AUTH_ADMIN_OBJECT_TYPES', K_AUTH_OPERATOR);

/**
 * Attributes Administration.
 */
define ('K_AUTH_ADMIN_ATTRIBUTES', K_AUTH_OPERATOR);

/**
 * Manufcturers Administration.
 */
define ('K_AUTH_ADMIN_MANUFACTURERS', K_AUTH_OPERATOR);

/**
 * Import DHCP data.
 */
define ('K_AUTH_IMPORT_DHCP', K_AUTH_ADMINISTRATOR);

/**
 * Import GetOS data.
 */
define ('K_AUTH_IMPORT_GETOS', K_AUTH_ADMINISTRATOR);

/**
 * Configuration templates.
 */
define ('K_AUTH_ADMIN_TEMPLATES', K_AUTH_OPERATOR);

/**
 * Scripts generator.
 */
define ('K_AUTH_SCRIPT_GENERATOR', K_AUTH_OPERATOR);

/**
 * Bulk change attributes.
 */
define ('K_AUTH_ADMIN_BULK_ATTRIBUTES', K_AUTH_ADV_OPERATOR);

/**
 * Required user's level to send ILO commands.
 */
define ('K_AUTH_ILO', K_AUTH_ADV_OPERATOR);

/**
 * Required user's level to view datacenter info.
 */
define ('K_AUTH_VIEW_DATACENTER', K_AUTH_VIEWER);

/**
 * Required user's level to view suite map.
 */
define ('K_AUTH_VIEW_SUITE', K_AUTH_VIEWER);

/**
 * Required user's level to view rack stack.
 */
define ('K_AUTH_VIEW_RACK', K_AUTH_VIEWER);

/**
 * Required user's level to view object details.
 */
define ('K_AUTH_VIEW_OBJECT', K_AUTH_VIEWER);

/**
 * Required user's level for view menu item.
 */
define ('K_AUTH_VIEW', K_AUTH_VIEWER);

/**
 * Required user's level for edit menu item.
 */
define ('K_AUTH_EDIT', K_AUTH_OPERATOR);

/**
 * Required user's level for edit connections between objects.
 */
define ('K_AUTH_ADMIN_CONNECTIONS', K_AUTH_ADV_OPERATOR);

/**
 * Required user's level to use SSH commander.
 */
define ('K_AUTH_SSH_COMMANDER', K_AUTH_ADV_OPERATOR);

/**
 * Required user's level to view network map
 */
define ('K_AUTH_VIEW_NETWORK_MAP', K_AUTH_OPERATOR);

/**
 * Required user's level to edit cable types
 */
define ('K_AUTH_EDIT_CABLE_TYPES', K_AUTH_OPERATOR);

/**
 * Required user's level to add child objects in bulk
 */
define ('K_AUTH_ADMIN_BULK_OBJECTS', K_AUTH_OPERATOR);

/**
 * Required user's level to add child objects in bulk
 */
define ('K_AUTH_BULK_ACCESS_GROUPS', K_AUTH_ADMINISTRATOR);

/**
 * Required user's level to export data
 */
define ('K_AUTH_EXPORT', K_AUTH_VIEWER);

/**
 * Required user's level to search for objects
 */
define ('K_AUTH_SEARCH', K_AUTH_VIEWER);

//============================================================+
// END OF FILE
//============================================================+
