<?php
//============================================================+
// File name   : tce_page_menu.php
// Begin       : 2004-04-20
// Last Update : 2013-01-08
//
// Description : Output XHTML unordered list menu.
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
 * Output XHTML unordered list menu.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2004-04-20
 */

/**
 */

require_once('../config/tce_auth.php');
require_once('../../shared/code/tce_functions_menu.php');

$menu = array(
	'index.php' => array('link' => 'index.php', 'title' => $l['h_index'], 'name' => $l['w_index'], 'level' => K_AUTH_INDEX, 'key' => '', 'enabled' => true),
	'tce_menu_view.php' => array('link' => 'tce_menu_view.php', 'title' => $l['w_view'], 'name' => $l['w_view'], 'level' => K_AUTH_VIEW, 'key' => '', 'enabled' => true),
	'tce_menu_edit.php' => array('link' => 'tce_menu_edit.php', 'title' => $l['w_edit'], 'name' => $l['w_edit'], 'level' => K_AUTH_EDIT, 'key' => '', 'enabled' => true),
	'tce_menu_tools.php' => array('link' => 'tce_menu_tools.php', 'title' => $l['t_tools'], 'name' => $l['w_tools'], 'level' => K_AUTH_VIEW, 'key' => '', 'enabled' => true),
	'tce_menu_users.php' => array('link' => 'tce_menu_users.php', 'title' => $l['w_users'], 'name' => $l['w_users'], 'level' => 1, 'key' => '', 'enabled' => true),
	'tce_page_info.php' => array('link' => 'tce_page_info.php', 'title' => $l['h_info'], 'name' => $l['w_info'], 'level' => K_AUTH_ADMIN_INFO, 'key' => '', 'enabled' => true),
	'tce_logout.php' => array('link' => 'tce_logout.php', 'title' => $l['h_logout_link'], 'name' => $l['w_logout'], 'level' => 1, 'key' => '', 'enabled' => ($_SESSION['session_user_level'] > 0)),
	'tce_login.php' => array('link' => 'tce_login.php', 'title' => $l['h_login_button'], 'name' => $l['w_login'], 'level' => 0, 'key' => '', 'enabled' => ($_SESSION['session_user_level'] < 1))
);

$menu['tce_menu_users.php']['sub'] = array(
	'tce_edit_user.php' => array('link' => 'tce_edit_user.php', 'title' => $l['t_user_editor'], 'name' => $l['w_users'], 'level' => K_AUTH_ADMIN_USERS, 'key' => '', 'enabled' => true),
	'tce_edit_group.php' => array('link' => 'tce_edit_group.php', 'title' => $l['t_group_editor'], 'name' => $l['w_groups'], 'level' => K_AUTH_ADMIN_USERS, 'key' => '', 'enabled' => true),
	'tce_select_users.php' => array('link' => 'tce_select_users.php', 'title' => $l['t_user_select'], 'name' => $l['w_select'], 'level' => K_AUTH_ADMIN_USERS, 'key' => '', 'enabled' => true),
	'tce_show_online_users.php' => array('link' => 'tce_show_online_users.php', 'title' => $l['t_online_users'], 'name' => $l['w_online'], 'level' => K_AUTH_ADMIN_USERS, 'key' => '', 'enabled' => true),
	'tce_edit_bulk_access_groups.php' => array('link' => 'tce_edit_bulk_access_groups.php', 'title' => $l['t_bulk_access_groups'], 'name' => $l['w_bulk_access_groups'], 'level' => K_AUTH_BULK_ACCESS_GROUPS, 'key' => '', 'enabled' => true),
	'tce_user_change_email.php' => array('link' => 'tce_user_change_email.php', 'title' => $l['t_user_change_email'], 'name' => $l['w_change_email'], 'level' => 1, 'key' => '', 'enabled' => true),
	'tce_user_change_password.php' => array('link' => 'tce_user_change_password.php', 'title' => $l['t_user_change_password'], 'name' => $l['w_change_password'], 'level' => 1, 'key' => '', 'enabled' => true)
);

$menu['tce_menu_view.php']['sub'] = array(
	'tce_search.php' => array('link' => 'tce_search.php', 'title' => $l['t_search_object'], 'name' => $l['w_search'], 'level' => K_AUTH_SEARCH, 'key' => '', 'enabled' => true),
	'tce_view_datacenter.php' => array('link' => 'tce_view_datacenter.php', 'title' => $l['t_view_datacenter'], 'name' => $l['w_datacenter'], 'level' => K_AUTH_VIEW_DATACENTER, 'key' => '', 'enabled' => true),
	'tce_view_suite.php' => array('link' => 'tce_view_suite.php', 'title' => $l['t_view_suite'], 'name' => $l['w_suite'], 'level' => K_AUTH_VIEW_SUITE, 'key' => '', 'enabled' => true),
	'tce_view_rack.php' => array('link' => 'tce_view_rack.php', 'title' => $l['t_view_rack'], 'name' => $l['w_rack'], 'level' => K_AUTH_VIEW_RACK, 'key' => '', 'enabled' => true),
	'tce_view_object.php' => array('link' => 'tce_view_object.php', 'title' => $l['t_view_object'], 'name' => $l['w_object'], 'level' => K_AUTH_VIEW_OBJECT, 'key' => '', 'enabled' => true),
	'tce_view_network_map.php' => array('link' => 'tce_view_network_map.php', 'title' => $l['t_view_network_map'], 'name' => $l['w_network_map'], 'level' => K_AUTH_VIEW_NETWORK_MAP, 'key' => '', 'enabled' => true),
);

$menu['tce_menu_edit.php']['sub'] = array(
	'tce_edit_datacenters.php' => array('link' => 'tce_edit_datacenters.php', 'title' => $l['t_datacenter_editor'], 'name' => $l['w_datacenter'], 'level' => K_AUTH_ADMIN_DATACENTERS, 'key' => '', 'enabled' => true),
	'tce_edit_suites.php' => array('link' => 'tce_edit_suites.php', 'title' => $l['t_suite_editor'], 'name' => $l['w_suite'], 'level' => K_AUTH_ADMIN_SUITES, 'key' => '', 'enabled' => true),
	'tce_edit_racks.php' => array('link' => 'tce_edit_racks.php', 'title' => $l['t_rack_editor'], 'name' => $l['w_rack'], 'level' => K_AUTH_ADMIN_RACKS, 'key' => '', 'enabled' => true),
	'tce_edit_objects.php' => array('link' => 'tce_edit_objects.php', 'title' => $l['t_object_editor'], 'name' => $l['w_object'], 'level' => K_AUTH_ADMIN_OBJECTS, 'key' => '', 'enabled' => true),
	'tce_edit_object_types.php' => array('link' => 'tce_edit_object_types.php', 'title' => $l['t_object_type_editor'], 'name' => $l['w_object_types'], 'level' => K_AUTH_ADMIN_OBJECT_TYPES, 'key' => '', 'enabled' => true),
	'tce_edit_attributes.php' => array('link' => 'tce_edit_attributes.php', 'title' => $l['t_attribute_editor'], 'name' => $l['w_attributes'], 'level' => K_AUTH_ADMIN_ATTRIBUTES, 'key' => '', 'enabled' => true),
	'tce_edit_connections.php' => array('link' => 'tce_edit_connections.php', 'title' => $l['t_connection_editor'], 'name' => $l['w_connection'], 'level' => K_AUTH_ADMIN_CONNECTIONS, 'key' => '', 'enabled' => true),
	'tce_edit_cable_types.php' => array('link' => 'tce_edit_cable_types.php', 'title' => $l['t_connection_type_editor'], 'name' => $l['w_connection_type'], 'level' => K_AUTH_EDIT_CABLE_TYPES, 'key' => '', 'enabled' => true),
	'tce_edit_manufacturers.php' => array('link' => 'tce_edit_manufacturers.php', 'title' => $l['t_manufacturer_editor'], 'name' => $l['w_manufacturers'], 'level' => K_AUTH_ADMIN_MANUFACTURERS, 'key' => '', 'enabled' => true),
);

$menu['tce_menu_tools.php']['sub'] = array(
	'tce_edit_backup.php' => array('link' => 'tce_edit_backup.php', 'title' => $l['t_backup_editor'], 'name' => $l['w_backup'], 'level' => K_AUTH_BACKUP, 'key' => '', 'enabled' => true),
	'tce_export_data.php' => array('link' => 'tce_export_data.php', 'title' => $l['t_export_data'], 'name' => $l['w_export'], 'level' => K_AUTH_EXPORT, 'key' => '', 'enabled' => true),
	'tce_export_objects_csv.php' => array('link' => 'tce_export_objects_csv.php', 'title' => $l['t_export_objects_csv'], 'name' => $l['w_export_objects_csv'], 'level' => K_AUTH_ADMINISTRATOR, 'key' => '', 'enabled' => true),
	'tce_filemanager.php' => array('link' => 'tce_filemanager.php', 'title' => $l['t_filemanager'], 'name' => $l['w_file_manager'], 'level' => K_AUTH_ADMIN_FILEMANAGER, 'key' => '', 'enabled' => true),
	'tce_import_getos.php' => array('link' => 'tce_import_getos.php', 'title' => $l['t_getos_importer'], 'name' => $l['w_getos_import'], 'level' => K_AUTH_IMPORT_GETOS, 'key' => '', 'enabled' => true),
	'tce_import_dhcp_dump.php' => array('link' => 'tce_import_dhcp_dump.php', 'title' => $l['t_dhcp_importer'], 'name' => $l['w_dhcp_import'], 'level' => K_AUTH_IMPORT_DHCP, 'key' => '', 'enabled' => true),
	'tce_edit_bulk_objects.php' => array('link' => 'tce_edit_bulk_objects.php', 'title' => $l['t_bulk_object_editor'], 'name' => $l['w_bulk_object_editor'], 'level' => K_AUTH_ADMIN_BULK_OBJECTS, 'key' => '', 'enabled' => true),
	'tce_edit_bulk_attributes.php' => array('link' => 'tce_edit_bulk_attributes.php', 'title' => $l['t_bulk_attribute_editor'], 'name' => $l['w_bulk_attribute_editor'], 'level' => K_AUTH_ADMIN_BULK_ATTRIBUTES, 'key' => '', 'enabled' => true),
	'tce_edit_templates.php' => array('link' => 'tce_edit_templates.php', 'title' => $l['t_templates_editor'], 'name' => $l['w_config_templates'], 'level' => K_AUTH_ADMIN_TEMPLATES, 'key' => '', 'enabled' => true),
	'tce_script_generator.php' => array('link' => 'tce_script_generator.php', 'title' => $l['t_script_generator'], 'name' => $l['w_script_generator'], 'level' => K_AUTH_SCRIPT_GENERATOR, 'key' => '', 'enabled' => true),
	'tce_ssh_commander.php' => array('link' => 'tce_ssh_commander.php', 'title' => $l['t_ssh_commander'], 'name' => $l['w_ssh_commander'], 'level' => K_AUTH_SCRIPT_GENERATOR, 'key' => '', 'enabled' => true),
);

echo '<a name="menusection" id="menusection"></a>'.K_NEWLINE;

// link to skip navigation
echo '<div class="hidden">';
echo '<a href="#topofdoc" accesskey="2" title="[2] '.$l['w_skip_navigation'].'">'.$l['w_skip_navigation'].'</a>';
echo '</div>'.K_NEWLINE;

echo '<ul class="menu">'.K_NEWLINE;
foreach ($menu as $link => $data) {
	echo F_menu_link($link, $data, 0);
}
echo '</ul>'.K_NEWLINE; // end of menu

//============================================================+
// END OF FILE
//============================================================+
