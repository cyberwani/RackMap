<?php
//============================================================+
// File name   : tce_db_dal.php
// Begin       : 2003-10-12
// Last Update : 2010-02-08
//
// Description : Load the functions for the selected database
//               type (Database Abstraction Layer).
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
 * Database Abstraction layer (DAL).
 * Loads the Database functions for the selected DATABASE type.
 * The database type is defined by K_DATABASE_TYPE constant on /shared/config/tce_db_config.php configuration file.
 * @package net.rackmap.shared
 * @author Nicola Asuni
 * @since 2003-10-12
 */

/**
 */

require_once('../../shared/code/tce_db_dal_mysql.php');

//============================================================+
// END OF FILE
//============================================================+
