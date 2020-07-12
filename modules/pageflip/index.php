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
include_once XOOPS_ROOT_PATH.'/modules/pageflip/include/pagenav.php';
include_once XOOPS_ROOT_PATH.'/class/file/folder.php';

$xoopsOption['template_main'] = 'all_brochures.html';
include_once XOOPS_ROOT_PATH.'/header.php';

$perms = '';

if (isset($_GET['start']) ) {
	$start = intval($_GET['start']);
} else {
	$start = 0;
}

$cat_id = isset($_GET['cat_id']) ? trim($_GET['cat_id']) : 0;
$cat_id = isset($_POST['cat_id']) ? trim($_POST['cat_id']) : $cat_id;



$xt = new PageflipBrochure ();
$brochures_arr = $xt->getChildTreeArray(0,'brochure_id','','','',$cat_id);
$pageflipcountbybrochure = $xt->getPageflipCountByBrochure();

//echo "CNT:".count($brochures_arr)."<P>";
//echo "Cstart:".$start."<P>";
//print_r($brochures_arr);echo "<P>";

$NofToDisplay=5;

	$totalbrochures = count($brochures_arr);
//echo "totalbrochures0 :".$totalbrochures ."<P>";
	if(is_array($brochures_arr) && $totalbrochures) {
//echo "totalbrochures1: :".$totalbrochures ."<P>";
		$cpt=1;
		$tmpcpt=$start;
		$ok=true;
		$output='';
		while($ok) {
			if($tmpcpt < $totalbrochures) {

				//echo "ID:".$brochures_arr[$tmpcpt]['brochure_id']."<P>";

				$select_pdfs_options="";
				$PDFPath = XOOPS_ROOT_PATH . '/uploads/pageflip/pdfs/';
				$xfh = new XoopsFolderHandler($PDFPath.$brochures_arr[$tmpcpt]['brochure_id'].'/');
				$pdfArray=$xfh->read(); // All images for corresponding brochure in the uploads directory for images

				// Loop through each image (from pdf export directory())
				foreach($pdfArray[1] as $pdf_value) {
					$pdfilelocation= XOOPS_URL."/uploads/pageflip/pdfs/".$brochures_arr[$tmpcpt]['brochure_id']."/".$pdf_value;
					$pdflink= XOOPS_URL."/modules/pageflip/pdf-display.php?brochure_id=".$brochures_arr[$tmpcpt]['brochure_id']."&amp;pdfname=".urlencode($pdf_value);
					$select_pdfs_options.="<a href='".$pdflink."'>".$pdf_value."</a> | ";	
				}	
				//echo "TMP:".	$tmpcpt."<P>";			//echo "ID:".$brochures_arr[$tmpcpt]['brochure_id']."<P>";


				 $brochures_arr[$tmpcpt]['pdflist'] =$select_pdfs_options;

				//print_r($brochures_arr[$tmpcpt]);echo "<P>";

				$xoopsTpl->append('Brochures', $brochures_arr[$tmpcpt]);
			} else {
				$ok=false;
			}

			if($cpt>=$NofToDisplay) {
				$ok=false;
			}
			$tmpcpt++;
			$cpt++;
		}
	}


unset($brochures_arr);


	$totalcount = PageflipBrochure ::getAllBrochureCount(true,$cat_id);

	//echo "TC--:".$totalcount."-".$NofToDisplay."<p>";
       if ( $totalcount > $NofToDisplay) {
        	include_once XOOPS_ROOT_PATH.'/modules/pageflip/include/pagenav.php';
		$pagenav = new XoopsPageNav($totalcount, $NofToDisplay, $start, 'start', "cat_id=".$cat_id);
        	$xoopsTpl->assign('pagenav', $pagenav->renderNav($totalcount));
      } else {
        $xoopsTpl->assign('pagenav', '');
       }
	$xoopsTpl->assign('cat_id', $cat_id);


/*
$xoopsTpl->assign('xoops_pagetitle', "Page-flip magazines);
$meta_description = $xt->brochure_description;
if(isset($xoTheme) && is_object($xoTheme)) {
	$xoTheme->addMeta( 'meta', 'description', $meta_description);
} else {	// Compatibility for old Xoops versions
	$xoopsTpl->assign('xoops_meta_description', $meta_description);
}
*/

include_once XOOPS_ROOT_PATH.'/footer.php';
?>
