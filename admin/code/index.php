<?php
//============================================================+
// File name   : index.php
// Begin       : 2004-04-29
// Last Update : 2012-12-13
//
// Description : Main page of RackMap system.
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
 * Main page of RackMap system.
 * @package net.rackmap.admin
 * @brief Main page of RackMap system.
 * @author Nicola Asuni
 * @since 2004-04-20
 */

/**
 */

require_once('../config/tce_config.php');
$pagelevel = K_AUTH_INDEX;
require_once('../../shared/code/tce_authorization.php');
require_once('tce_page_header.php');

require_once('../../shared/code/tce_functions_form.php');
require_once('tce_functions_objects.php');
require_once('tce_functions_group_permissions.php');

$user_id = intval($_SESSION['session_user_id']);
//$userip = $_SESSION['session_user_ip'];
$userlevel = intval($_SESSION['session_user_level']);

echo '<div class="container">'.K_NEWLINE;

echo '<div class="tceformbox">'.K_NEWLINE;

if ($userlevel >= K_AUTH_VIEWER) {
	// select datacenter
	$sqldc = 'SELECT * FROM '.K_TABLE_DATACENTERS.' ORDER BY dcn_name ASC';
	if ($rdc = F_db_query($sqldc, $db)) {
		while ($mdc = F_db_fetch_array($rdc)) {
			
			$dcn_permission = F_getUserPermission($user_id, K_TABLE_DATACENTER_GROUPS, $mdc['dcn_id']);
			if ($dcn_permission > 0) {
				
				$datacenter = '<a href="tce_view_datacenter.php?dcn_id='.$mdc['dcn_id'].'" title="'.$l['t_view_datacenter'].'">'.$mdc['dcn_name'].'</a>';
				// select suite
				$sqlst = 'SELECT * FROM '.K_TABLE_SUITES.' WHERE sts_dcn_id='.$mdc['dcn_id'].' ORDER BY sts_name ASC';
				if ($rst = F_db_query($sqlst, $db)) {
					while ($mst = F_db_fetch_array($rst)) {
						
						$sts_permission = F_getUserPermission($user_id, K_TABLE_SUITE_GROUPS, $mst['sts_id']);
						if ($sts_permission > 0) {

							$suite = '<a href="tce_view_suite.php?dcn_id='.$mdc['dcn_id'].'&amp;sts_id='.$mst['sts_id'].'" title="'.$l['t_view_suite'].'">'.$mst['sts_name'].'</a>';
							
							echo '<h2 style="text-align:left;">&bull; '.$datacenter.' &rarr; '.$suite.'</h2>';

							// *** GET RACK INFO
							$rack_pos = array(); // store rack positions
							$rack_name = array(); //store rack names
							$sql = 'SELECT * FROM '.K_TABLE_RACKS.' WHERE rck_sts_id='.$mst['sts_id'].' ORDER BY rck_name ASC';
							if ($r = F_db_query($sql, $db)) {
								while ($m = F_db_fetch_array($r)) {
									$rack_pos[$m['rck_position_x']][$m['rck_position_y']] = $m['rck_id'];
									$rack_name[$m['rck_id']] = $m['rck_name'].' - '.$m['rck_label'].' - '.$m['rck_tag'];
									$rck_permission[$m['rck_id']] = F_getUserPermission($user_id, K_TABLE_RACK_GROUPS, $m['rck_id']);
								}
							} else {
								F_display_db_error();
							}

							// *** MAP OF RACKS ON SELECTED SUITE
							echo '<div class="row">'.K_NEWLINE;
							echo '<table class="suite">'.K_NEWLINE;
							for ($y = $mst['sts_height']; $y >= 0 ; --$y) {
								echo '<tr>';
								echo '<td style="text-align:center;">'.$y.'</td>';
								for ($x = 1; $x <= $mst['sts_width']; ++$x) {
									if ($y == 0) {
										echo '<td style="text-align:center;">'.$x.'</td>';
									} else {
										if (isset($rack_pos[$x][$y]) AND (($rck_permission[$rack_pos[$x][$y]] & 1) == 1)) {
											echo '<td><a href="tce_view_rack.php?dcn_id='.$mdc['dcn_id'].'&amp;sts_id='.$mst['sts_id'].'&amp;rck_id='.$rack_pos[$x][$y].'#rack" title="['.$x.' - '.$y.'] '.$rack_name[$rack_pos[$x][$y]].'" class="rackbutton">&nbsp;</a></td>'.K_NEWLINE;
										} elseif ($sts_permission > 1) {
											echo '<td><a href="tce_edit_racks.php?dcn_id='.$mdc['dcn_id'].'&amp;sts_id='.$mst['sts_id'].'&amp;rck_id=0&amp;rck_position_x='.$x.'&amp;rck_position_y='.$y.'" title="['.$x.' - '.$y.'] '.$l['w_add_new_rack'].'" class="newrackbutton">&nbsp;</a></td>'.K_NEWLINE;
										} else {
											echo '<td>&nbsp;</td>'.K_NEWLINE;
										}
									}
								}
								echo '</tr>'.K_NEWLINE;
							}
							echo '</table>'.K_NEWLINE;
							echo '</div>'.K_NEWLINE;
							
						} // end of suite permission
					}
				} else {
					F_display_db_error();
				}
				
			} // end of datacenter permission
		}
	} else {
		F_display_db_error();
	}
}
echo '</div>'.K_NEWLINE;

echo '<div class="pagehelp">'.$l['hp_main_index'].'</div>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;


require_once('tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
