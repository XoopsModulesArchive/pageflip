<?php
// $Id: groupperms.php,v 1.7 2004/07/26 17:51:25 hthouzard Exp $
// ------------------------------------------------------------------------ //
// XOOPS - PHP Content Management System            				        //
// Copyright (c) 2000 XOOPS.org                           					//
// <http://www.xoops.org/>                             						//
// ------------------------------------------------------------------------ //
// This program is free software; you can redistribute it and/or modify     //
// it under the terms of the GNU General Public License as published by     //
// the Free Software Foundation; either version 2 of the License, or        //
// (at your option) any later version.                                      //
// 																			//
// You may not change or alter any portion of this comment or credits       //
// of supporting developers from this source code or any supporting         //
// source code which is considered copyrighted (c) material of the          //
// original comment or credit authors.                                      //
// 																			//
// This program is distributed in the hope that it will be useful,          //
// but WITHOUT ANY WARRANTY; without even the implied warranty of           //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
// GNU General Public License for more details.                             //
// 																			//
// You should have received a copy of the GNU General Public License        //
// along with this program; if not, write to the Free Software              //
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------ //
include_once '../../../include/cp_header.php';
include_once XOOPS_ROOT_PATH.'/modules/pageflip/class/class.pageflipbrochure.php';
include_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
include_once XOOPS_ROOT_PATH . '/class/xoopsform/grouppermform.php';
include_once XOOPS_ROOT_PATH . '/modules/pageflip/admin/functions.php';

xoops_cp_header();

adminmenu(4);
echo '<br /><br /><br />';
$permtoset= isset($_POST['permtoset']) ? intval($_POST['permtoset']) : 1;
$selected=array('','','');
$selected[$permtoset-1]=' selected';
echo "<form method='post' name='fselperm' action='groupperms.php'><select name='permtoset' onChange='javascript: document.fselperm.submit()'><option value='3'".$selected[2].">"._AM_VIEWFORM."</option></select> <input type='submit' name='go'></form>";
$module_id = $xoopsModule->getVar('mid');

switch($permtoset)
{
	case 3:
		$title_of_form = _AM_VIEWFORM;
		$perm_name = 'pageflip_view';
		$perm_desc = _AM_VIEWFORM_DESC;
		break;
}

$permform = new XoopsGroupPermForm($title_of_form, $module_id, $perm_name, $perm_desc);
$xt = new PageflipBrochure( $xoopsDB -> prefix( 'pageflip_brochures' ));

$allbrochures =& $xt->getBrochuresList();

foreach ($allbrochures as $brochure_id => $brochure) {
    $permform->addItem($brochure_id, $brochure['title'], $brochure['pid']);
}
echo $permform->render();
echo "<br /><br /><br /><br />\n";
unset ($permform);

xoops_cp_footer();
?>
