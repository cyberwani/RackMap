<?php
//============================================================+
// File name   : tce_menu_view.php
// Begin       : 2004-04-20
// Last Update : 2011-11-15
//
// Description : Output View submenu.
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
 * Output View submenu.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-10-31
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_VIEW;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['w_view'];
require_once('../code/tce_page_header.php');

echo '<div class="container">'.K_NEWLINE;

// print submenu
echo '<ul>'.K_NEWLINE;
foreach ($menu['tce_menu_view.php']['sub'] as $link => $data) {
	echo F_menu_link($link, $data, 1);
}
echo '</ul>'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

require_once('../code/tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
