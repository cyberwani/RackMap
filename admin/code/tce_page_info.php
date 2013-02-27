<?php
//============================================================+
// File name   : tce_page_info.php
// Begin       : 2004-05-21
// Last Update : 2013-02-27
//
// Description : Outputs Information page.
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
 * Outputs Information page.
 * @package net.rackmap.admin
 * @author Nicola Asuni
 * @since 2004-05-21
 */

/**
 */

require_once('../config/tce_config.php');

$pagelevel = K_AUTH_ADMIN_INFO;
require_once('../../shared/code/tce_authorization.php');

$thispage_title = $l['t_page_info'];
require_once('../code/tce_page_header.php');

require_once('tce_page_header.php');

echo '<div class="container">'.K_NEWLINE;

echo ''.$l['d_rackmap_desc'].'<br />'.K_NEWLINE;

echo '<ul class="credits">'.K_NEWLINE;
echo '<li><strong>'.$l['w_author'].':</strong> Nicola Asuni</li>'.K_NEWLINE;
echo '<li><strong>Copyright:</strong> This application is based on a software framework copyrighted by Nicola Asuni - <a href="http://www.tecnick.com" title="Tecnick.com website">Tecnick.com</a> (2004-2013) and includes new source code copyrighted by <a href="http://www.fubra.com" title="Fubra website">Fubra LTD</a> (2011-2012). Full copyright notices are stated on each file. <a href="mailto:support@rackmap.net">support@rackmap.net</a> - ';
echo '<a href="http://www.rackmap.net" title="RackMap project website">http://www.rackmap.net</a>.</li>'.K_NEWLINE;
echo '<li><strong>'.$l['w_license'].':</strong><br />
RackMap IS SUBJECT TO THE AGPLv3 LICENSE WITH THE FOLLOWING EXCEPTION:<br />
This software contains files copyrighted by Tecnick.com. Tecnick.com has granted the right for these files to be used for free only as a part of the RackMap software. The code contained on these Tecnick.com files can not be used for other purposes without explicit permission from Tecnick.com.<br />
With this exception,<br />
This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.<br />This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more details.<br />You should have received a copy of the GNU Affero General Public License along with this program. If not, see <a href="http://www.gnu.org/licenses/" title="GNU Licenses">http://www.gnu.org/licenses/</a>.<br />
See <a href="../../LICENSE.TXT" title="'.$l['m_new_window_link'].'">LICENSE.TXT</a> file for more information.</li>'.K_NEWLINE;
echo '</ul>'.K_NEWLINE;

echo '<h2>'.$l['t_third_parties'].'</h2>'.K_NEWLINE;

echo '<ul class="credits">'.K_NEWLINE;

echo '<li><strong>TCPDF</strong><br />
PHP class for generating PDF documents.<br />
Author: Nicola Asuni (<a href="mailto:info@tcpdf.org">info@tcpdf.org</a>)<br />
Homepage: <a href="http://www.tcpdf.org/">http://www.tcpdf.org/</a><br />
License: <a href="http://www.tcpdf.org/license.php" title="TCPDF License">LGPLv3 (GNU LESSER GENERAL PUBLIC LICENSE VERSION 3)</a><br />
Location: /shared/code/<br />
Note: some fonts released with TCPDF are subject to different licenses, please consult the documents on the fonts subfolders.<br /><br />
</li>'.K_NEWLINE;

echo '<li><strong>PHPMailer</strong><br />
Full Featured Email Transfer Class for PHP.<br />
Author: Brent R. Matzelle (<a href="mailto:bmatzelle@yahoo.com">bmatzelle@yahoo.com</a>)<br />
Homepage: <a href="http://phpmailer.sourceforge.net/">http://phpmailer.sourceforge.net/</a><br />
License: <a href="http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html" title="GNU Lesser General Public License v2">LGPL (GNU LESSER GENERAL PUBLIC LICENSE VERSION 2.1)</a><br />
Location: /shared/phpmailer/<br /><br />
</li>'.K_NEWLINE;

echo '<li><strong>The DHTML Calendar</strong><br />
Calendar widget written in Javascript.<br />
Author: Mihai Bazon (<a href="mailto:mihai_bazon@yahoo.com">mihai_bazon@yahoo.com</a>)<br />
Homepage: <a href="http://dynarch.com/mishoo/">http://dynarch.com/mishoo/</a><br />
License: <a href="http://www.gnu.org/licenses/lgpl.html" title="GNU Lesser General Public License">LGPL (GNU LESSER GENERAL PUBLIC LICENSE VERSION)</a><br />
Location: /shared/jscripts/jscalendar/<br /><br />
</li>'.K_NEWLINE;

echo '</ul>'.K_NEWLINE;

echo '<h2>'.$l['t_translations'].'</h2>'.K_NEWLINE;

echo '<ul class="credits">'.K_NEWLINE;
echo '<li>[EN] English : Nicola Asuni</li>'.K_NEWLINE;
echo '</ul>'.K_NEWLINE;

echo '</div>'.K_NEWLINE;

echo '<br />'.K_NEWLINE;

// display credit logos
echo '<div class="creditslogos">'.K_NEWLINE;
echo '<a href="../../LICENSE.TXT"><img src="../../images/credits/agplv3-88x31.png" alt="License" width="88" height="31" style="border:none;" /></a>'.K_NEWLINE;
echo '<a href="http://validator.w3.org/check?uri='.K_PATH_HOST.$_SERVER['SCRIPT_NAME'].'" title="This Page Is Valid XHTML 1.0 Strict!"><img src="../../images/credits/w3c_xhtml10_88x31.png" alt="Valid XHTML 1.0!" height="31" width="88" style="border:none;" /></a>'.K_NEWLINE;
echo '<a href="http://jigsaw.w3.org/css-validator/" title="This document validates as CSS!"><img src="../../images/credits/w3c_css_88x31.png" alt="Valid CSS1!" height="31" width="88" style="border:none;" /></a>'.K_NEWLINE;
echo '<a href="http://www.w3.org/WAI/WCAG1AAA-Conformance" title="Explanation of Level Triple-A Conformance"><img src="../../images/credits/w3c_wai_aaa_88x31.png" alt="Level Triple-A conformance icon, W3C-WAI Web Content Accessibility Guidelines 1.0" width="88" height="31" style="border:none;" /></a>'.K_NEWLINE;
echo '</div>'.K_NEWLINE;

require_once('tce_page_footer.php');

//============================================================+
// END OF FILE
//============================================================+
