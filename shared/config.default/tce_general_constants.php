<?php
//============================================================+
// File name   : tce_general_constants.php
// Begin       : 2002-03-01
// Last Update : 2009-11-10
//
// Description : Configuration file for general constants.
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
 * Configuration file for general constants.
 * @package net.rackmap.shared.cfg
 * @author Nicola Asuni
 * @since 2002-03-01
 */

/**
 * New line character.
 */
define ('K_NEWLINE', "\n");

/**
 * Tabulation character.
 */
define ('K_TAB', "\t");

/**
 * Number of seconds in one minute.
 */
define ('K_SECONDS_IN_MINUTE', 60);

/**
 * Number of seconds in one hour.
 */
define ('K_SECONDS_IN_HOUR', 60 * K_SECONDS_IN_MINUTE);

/**
 * Number of seconds in one day.
 */
define ('K_SECONDS_IN_DAY', 24 * K_SECONDS_IN_HOUR);

/**
 * Number of seconds in one week.
 */
define ('K_SECONDS_IN_WEEK', 7 * K_SECONDS_IN_DAY);

/**
 * Number of seconds in one month.
 */
define ('K_SECONDS_IN_MONTH', 30 * K_SECONDS_IN_DAY);

/**
 * Number of seconds in one year.
 */
define ('K_SECONDS_IN_YEAR', 365 * K_SECONDS_IN_DAY);

/**
 * String used as a seed for some security code generation please change this value and keep it secret.
 */
define ('K_RANDOM_SECURITY', 's8hea03j2');

//============================================================+
// END OF FILE
//============================================================+
