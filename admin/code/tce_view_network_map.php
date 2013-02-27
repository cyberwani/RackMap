<?php
//============================================================+
// File name   : tce_view_network_map.php
// Begin       : 2011-11-24
// Last Update : 2011-11-24
//
// Description : Display network map.
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
//    Copyright (C) 2011-2011 Fubra Limited
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
 * Display network map.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2011-11-24
 */

/**
 */

require_once('../config/tce_config.php');
$pagelevel = K_AUTH_VIEW_NETWORK_MAP;
require_once('../../shared/code/tce_authorization.php');
require_once('tce_functions_netgraph.php');
require_once('tce_functions_objects.php');
$thispage_title = $l['t_view_network_map'];
require_once('tce_page_header.php');

//$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
$userlevel = intval($_SESSION['session_user_level']);

if (isset($_REQUEST['cbt_id'])) {
	$cbt_id = intval($_REQUEST['cbt_id']);
} else {
	$cbt_id = 0;
}

echo '<form action="'.$_SERVER['SCRIPT_NAME'].'" method="post" enctype="multipart/form-data" id="form_editor">'.K_NEWLINE;
echo F_select_connection_type($cbt_id, false, true);
echo '</form>'.K_NEWLINE;

echo F_get_network_svg_map($cbt_id);

require_once('tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
