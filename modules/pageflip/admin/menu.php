<?php
// $Id: menu.php,v 1.3 2004/02/28 01:35:23 mithyt2 Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
//echo "BID:".$_GET['brochure_id'];
//if (intval($_GET['brochure_id']) !=0 ){
//	$get_brochure_id="&brochure_id=".$_GET['brochure_id'];
//}else{
//	$get_brochure_id='234';
//}

$adminmenu[1]['title'] = _MI_PAGEFLIP_CATMANAGER	;
$adminmenu[1]['link'] = 'admin/index.php?op=catmanager';
$adminmenu[2]['title'] = _MI_PAGEFLIP_MANAGER	;
$adminmenu[2]['link'] = 'admin/index.php?op=brochuresmanager';
$adminmenu[3]['title'] = _MI_PAGEFLIP_UPLOAD;
$adminmenu[3]['link'] = 'admin/index.php?op=upload';
$adminmenu[4]['title'] = _MI_PAGEFLIP_CONVERTPDF;
$adminmenu[4]['link'] = 'admin/index.php?op=convert';
$adminmenu[5]['title'] = _MI_PAGEFLIPS_ASSIGN;
$adminmenu[5]['link'] = 'admin/index.php?op=assign';
$adminmenu[6]['title'] = _MI_PAGEFLIP_GROUPPERMS;
$adminmenu[6]['link'] = 'admin/groupperms.php';


?>