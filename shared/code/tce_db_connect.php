<?php
//============================================================+
// File name   : tce_db_connect.php
// Begin       : 2001-09-02
// Last Update : 2009-10-09
//
// Description : open connection with active database
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
 * Open a connection to a MySQL Server and select a database.
 * @package net.rackmap.shared
 * @author Nicola Asuni
 * @since 2001-09-02
 */

/**
 */

require_once('../../shared/code/tce_db_dal.php'); // Database Abstraction Layer for selected DATABASE type

if(!$db = @F_db_connect(K_DATABASE_HOST, K_DATABASE_PORT, K_DATABASE_USER_NAME, K_DATABASE_USER_PASSWORD, K_DATABASE_NAME)) {
	die('<h2>'.F_db_error().'</h2>');
}

//============================================================+
// END OF FILE
//============================================================+
