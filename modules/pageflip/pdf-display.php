<?php
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                  Copyright (c) 2005-2006 Instant Zero                     //
//                     <http://xoops.instant-zero.com/>                      //
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

include_once '../../mainfile.php';
global $xoopsOption,$xoopsUser,$xoopsTpl;
include_once XOOPS_ROOT_PATH.'/modules/pageflip/class/class.pageflipbrochure.php';
include_once XOOPS_ROOT_PATH.'/modules/pageflip/class/class.pageflippages.php';
include_once XOOPS_ROOT_PATH.'/modules/pageflip/include/functions.php';
include_once XOOPS_ROOT_PATH.'/class/uploader.php';
include_once XOOPS_ROOT_PATH.'/class/pagenav.php';
include_once XOOPS_ROOT_PATH.'/class/file/folder.php';

$xoopsOption['template_main'] = 'display_pdf.html';
include_once XOOPS_ROOT_PATH.'/header.php';

$perms = '';

$brochure_id = intval($_GET['brochure_id']);
$pdf = $_GET['pdfname'];

$xt = new PageflipBrochure ($brochure_id);

//echo "pdf :".$pdf ;

$xoopsTpl->assign('brochure_id',$xt->brochure_id);
$xoopsTpl->assign('brochure_title',$xt->brochure_title);
$xoopsTpl->assign('brochure_pdf',$pdf);


$xoopsTpl->assign('xoops_pagetitle', $xt->brochure_title);
$meta_description = $xt->brochure_description;
if(isset($xoTheme) && is_object($xoTheme)) {
	$xoTheme->addMeta( 'meta', 'description', $meta_description);
} else {	// Compatibility for old Xoops versions
	$xoopsTpl->assign('xoops_meta_description', $meta_description);
}


//break;
include_once XOOPS_ROOT_PATH.'/footer.php';
?>
