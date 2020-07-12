<?php
// $Id: xoops_version.php,v 1.34 2004/09/01 17:48:07 hthouzard Exp $
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
if (!defined('XOOPS_ROOT_PATH')) {
	die('XOOPS root path not defined');
}

$modversion['name'] = _MI_PAGEFLIP_NAME;
$modversion['version'] = 0.1;
$modversion['description'] = _MI_PAGEFLIP_DESC;
$modversion['credits'] = "EVUCAN";
$modversion['author'] = "EVUCAN";
$modversion['help'] = "";
$modversion['license'] = "GPL see LICENSE";
$modversion['official'] = 1;
$modversion['image'] = "images/pageflip_slogo.png";
$modversion['dirname'] = "pageflip";

$modversion['sqlfile']['mysql'] = "sql/mysql.sql";

// Tables created by sql file (without prefix!)
$modversion['tables'][0] = 'pageflip_brochures';
$modversion['tables'][1] = 'pageflip_pages';
$modversion['tables'][2] = 'pageflip_categories';

// Admin things
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = "admin/index.php";
$modversion['adminmenu'] = "admin/menu.php";

// Templates
$modversion['templates'][1]['file'] = 'all_brochures.html';
$modversion['templates'][1]['description'] = '';
$modversion['templates'][2]['file'] = 'latest_brochure.html';
$modversion['templates'][2]['description'] = '';
$modversion['templates'][3]['file'] = 'display_pdf.html';
$modversion['templates'][3]['description'] = '';
$modversion['templates'][4]['file'] = 'display_brochure.html';
$modversion['templates'][4]['description'] = '';

// Blocks
$modversion['blocks'][1]['file'] = "pageflip_brochures.php";
$modversion['blocks'][1]['name'] = _MI_PAGEFLIP_BNAME1;
$modversion['blocks'][1]['description'] = "Shows latest brochures";
$modversion['blocks'][1]['show_func'] = "b_pageflip_brochures_show";
$modversion['blocks'][1]['template'] = 'brochures_top.html';


// Menu
$modversion['hasMain'] = 1;

$cansubmit = 0;



/*
 * 
 */
$modversion['config'][1]['name'] = 'noofpages';
$modversion['config'][1]['title'] = '_MI_PAGEFLIP_PAGES';
$modversion['config'][1]['description'] = '_MI_PAGEFLIP_PAGES_DESC';
$modversion['config'][1]['formtype'] = 'textbox';
$modversion['config'][1]['valuetype'] = 'text';
$modversion['config'][1]['default'] = 3;


/*
 * 
 */
$modversion['config'][2]['name'] = 'Crop';
$modversion['config'][2]['title'] = '_MI_PAGEFLIP_CROP';
$modversion['config'][2]['description'] = '_MI_PAGEFLIP_CROP_DESC';
$modversion['config'][2]['formtype'] = 'textbox';
$modversion['config'][2]['valuetype'] = 'text';
$modversion['config'][2]['default'] = "";

/*
 * 
 * 
 */
$modversion['config'][3]['name'] = 'AddConfig';
$modversion['config'][3]['title'] = '_MI_PAGEFLIP_ADDCONFIG';
$modversion['config'][3]['description'] = '_MI_PAGEFLIP_ADDCONFIG_DESC';
$modversion['config'][3]['formtype'] = 'textbox';
$modversion['config'][3]['valuetype'] = 'text';
$modversion['config'][3]['default'] = "-colorspace RGB";

/**
 * Format of the date to use in the module, if you don't specify anything then the default date's format will be used
 */

$modversion['config'][4]['name'] = 'restrictindex';
$modversion['config'][4]['title'] = '_MI_PAGEFLIP_RESTRICTINDEX';
$modversion['config'][4]['description'] = '_MI_PAGEFLIP_RESTRICTINDEXDSC';
$modversion['config'][4]['formtype'] = 'yesno';
$modversion['config'][4]['valuetype'] = 'int';
$modversion['config'][4]['default'] = 0;

$modversion['config'][5]['name'] = 'maxuploadPDFsize';
$modversion['config'][5]['title'] = '_MI_UPLOADPDFFILESIZE';
$modversion['config'][5]['description'] = '_MI_UPLOADPDFFILESIZE_DESC';
$modversion['config'][5]['formtype'] = 'textbox';
$modversion['config'][5]['valuetype'] = 'int';
$modversion['config'][5]['default'] = 120048576;
/**
 * MAX Filesize Upload in kilo bytes
 */
$modversion['config'][6]['name'] = 'maxuploadsize';
$modversion['config'][6]['title'] = '_MI_UPLOADFILESIZE';
$modversion['config'][6]['description'] = '_MI_UPLOADFILESIZE_DESC';
$modversion['config'][6]['formtype'] = 'textbox';
$modversion['config'][6]['valuetype'] = 'int';
$modversion['config'][6]['default'] = 10048576;

/**
 * MAX Filesize Upload in kilo bytes
 */
$modversion['config'][7]['name'] = 'resolution';
$modversion['config'][7]['title'] = '_MI_RESOLUTION';
$modversion['config'][7]['description'] = '_MI_RESOLUTION_DESC';
$modversion['config'][7]['formtype'] = 'textbox';
$modversion['config'][7]['valuetype'] = 'int';
$modversion['config'][7]['default'] = 72;

/**
 * MAX Filesize Upload in kilo bytes
 */
$modversion['config'][8]['name'] = 'default_image_prefix';
$modversion['config'][8]['title'] = '_MI_IMAGEPREFIX';
$modversion['config'][8]['description'] = '_MI_IMAGEPREFIX_DESC';
$modversion['config'][8]['formtype'] = 'textbox';
$modversion['config'][8]['valuetype'] = 'text';
$modversion['config'][8]['default'] = "page_";

?>