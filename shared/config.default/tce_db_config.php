<?php
//============================================================+
// File name   : tce_db_config.php
// Begin       : 2001-09-02
// Last Update : 2012-01-24
//
// Description : Database congiguration file.
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
 * Database congiguration file.
 * @package net.rackmap.shared.cfg
 * @author Nicola Asuni
 * @since 2001-09-02
 */

/**
 * database type (MYSQL, POSTGRESQL, ORACLE)
 */
define ('K_DATABASE_TYPE', 'MYSQL');

/**
 * database Host name (eg: localhost)
 */
define ('K_DATABASE_HOST', 'localhost');

/**
 * database port (eg: 3306, 5432, 1521)
 */
define ('K_DATABASE_PORT', '3306');

/**
 * database name (rackmap)
 */
define ('K_DATABASE_NAME', 'rackmapdb');

/**
 * database user name
 */
define ('K_DATABASE_USER_NAME', 'root');

/**
 * database user password
 */
define ('K_DATABASE_USER_PASSWORD', '');

/**
 * prefix for database tables names
 */
define ('K_TABLE_PREFIX', 'rm_');

// -----------------------------------------------------------------------------
// --- DATABASE TABLES NAMES (DO NOT CHANGE) -----------------------------------
// -----------------------------------------------------------------------------

/**
 * This table stores information about users' Web sessions.
 */
define ('K_TABLE_SESSIONS', K_TABLE_PREFIX.'sessions');

/**
 * This table contains all registered users' data, including system administrators and a special 'anonymous' user.
 */
define ('K_TABLE_USERS', K_TABLE_PREFIX.'users');

/**
 * This table contains users' groups. Each user belongs to one of the groups defined in this table.
 */
define ('K_TABLE_GROUPS', K_TABLE_PREFIX.'user_groups');

/**
 * This table contains the list of groups to whom each user belongs.
 */
define ('K_TABLE_USERGROUP', K_TABLE_PREFIX.'usrgroups');

/**
 * List possible attributes for an object.
 */
define ('K_TABLE_ATTRIBUTE_TYPES', K_TABLE_PREFIX.'attribute_types');

/**
 * Values of attributes that belongs to an object.
 */
define ('K_TABLE_ATTRIBUTE_VALUES', K_TABLE_PREFIX.'attribute_values');

/**
 * List of datacenters.
 */
define ('K_TABLE_DATACENTERS', K_TABLE_PREFIX.'datacenters');

/**
 * Map the rack position of object.
 */
define ('K_TABLE_LOCATIONS', K_TABLE_PREFIX.'locations');

/**
 * List of objects manufacturers.
 */
define ('K_TABLE_MANUFACTURES', K_TABLE_PREFIX.'manufactures');

/**
 * List of manufacturers MAC prefixes.
 */
define ('K_TABLE_MANUFACTURES_MAC', K_TABLE_PREFIX.'manufacturer_mac');

/**
 * Generic real or virtual object.
 */
define ('K_TABLE_OBJECTS', K_TABLE_PREFIX.'objects');

/**
 * Map the parent-child relationship between each object.
 */
define ('K_TABLE_OBJECTS_MAP', K_TABLE_PREFIX.'objects_map');

/**
 * Map attributes that belongs to each object type.
 */
define ('K_TABLE_OBJECT_ATTRIBUTES_MAP', K_TABLE_PREFIX.'object_attributes_map');

/**
 * List object types.
 */
define ('K_TABLE_OBJECT_TYPES', K_TABLE_PREFIX.'object_types');

/**
 * List of racks in each suite.
 */
define ('K_TABLE_RACKS', K_TABLE_PREFIX.'racks');

/**
 * List suites on each datacenters.
 */
define ('K_TABLE_SUITES', K_TABLE_PREFIX.'suites');

/**
 * List suites on each datacenters.
 */
define ('K_TABLE_TEMPLATES', K_TABLE_PREFIX.'templates');

/**
 * Connections between objects.
 */
define ('K_TABLE_CABLES', K_TABLE_PREFIX.'cables');

/**
 * Object permission groups.
 */
define ('K_TABLE_OBJECT_GROUPS', K_TABLE_PREFIX.'object_groups');

/**
 * Rack permission groups.
 */
define ('K_TABLE_RACK_GROUPS', K_TABLE_PREFIX.'rack_groups');

/**
 * Suite permission groups.
 */
define ('K_TABLE_SUITE_GROUPS', K_TABLE_PREFIX.'suite_groups');

/**
 * Datacenter permission groups.
 */
define ('K_TABLE_DATACENTER_GROUPS', K_TABLE_PREFIX.'datacenter_groups');


//============================================================+
// END OF FILE
//============================================================+
