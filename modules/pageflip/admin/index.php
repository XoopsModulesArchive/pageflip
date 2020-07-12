<?php
// $Id: index.php,v 1.25 2004/09/02 17:04:07 hthouzard Exp $
// ------------------------------------------------------------------------ //
// XOOPS - PHP Content Management System  				                    //
// Copyright (c) 2000 XOOPS.org                         					//
// <http://www.xoops.org/>                             						//
// ------------------------------------------------------------------------ //
// This program is free software; you can redistribute it and/or modify     //
// it under the terms of the GNU General Public License as published by     //
// the Free Software Foundation; either version 2 of the License, or        //
// (at your option) any later version.                                      //
// 																			//
// You may not change or alter any portion of this comment or credits       //
// of supporting developers from this source code or any supporting         //
// source code which is considered copyrighted (c) material of the         	 //
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
include_once XOOPS_ROOT_PATH.'/class/xoopslists.php';
include_once XOOPS_ROOT_PATH.'/modules/pageflip/class/class.pageflipcategories.php';
include_once XOOPS_ROOT_PATH.'/modules/pageflip/class/class.pageflipbrochure.php';
include_once XOOPS_ROOT_PATH.'/modules/pageflip/class/class.pageflippages.php';
include_once XOOPS_ROOT_PATH.'/modules/pageflip/class/class.sfiles.php';
include_once XOOPS_ROOT_PATH.'/class/uploader.php';
include_once XOOPS_ROOT_PATH.'/class/pagenav.php';
include_once XOOPS_ROOT_PATH.'/class/file/folder.php';
include_once XOOPS_ROOT_PATH.'/modules/pageflip/admin/functions.php';
include_once XOOPS_ROOT_PATH.'/modules/pageflip/include/functions.php';
include_once XOOPS_ROOT_PATH.'/class/tree.php';

$dateformat=pageflip_getmoduleoption('dateformat');

$myts =& MyTextSanitizer::getInstance();
$brochurescount=0;
    
$PDFPath = XOOPS_ROOT_PATH . '/uploads/pageflip/pdfs/';
$ImagePath = XOOPS_ROOT_PATH . '/uploads/pageflip/images/';
$BinderPath = XOOPS_ROOT_PATH . '/uploads/pageflip/binder/';

if ($windowsos == true){
	$GSpthConvert = '"C:\Program Files\gs\gs8.63\bin\gswin32.exe "';
	$IMpthConvert = '"C:\Program Files\ImageMagick-6.4.8-Q16\convert.exe "';
}else{
	$GSpthConvert = '/usr/bin/gs ';
	$IMpthConvert = '/usr/bin/convert ';
}


/*
* Brochures manager
*
* It's from here that you can list, add, modify an delete Brochures
* At first, you can see a list of all the Brochures in your databases. This list contains the topic's ID, its name,
* its parent Brochures
* Below this list you find the form used to create and edit the topics.
* use this form to :
* - Type the topic's title
* - Enter its description
* - Select its parent topic
* - Choose a color
* - And finally you ca select an image to represent the brochure
*/



function assignpages(){


    global $xoopsDB, $xoopsConfig, $xoopsModule, $myts,$xoopsModuleConfig, $PDFPath, $ImagePath, $GSpthConvert, $IMpthConvert,$BinderPath ;

    include_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
    xoops_cp_header();
    adminmenu(4);
   
echo _AM_DBUPDATED."<P>";
	//echo "-1-operation:".$_POST['operation']."<P>";
	$operation= isset($_POST['operation']) ? $_POST['operation'] : "";

	$imagesPath='/uploads/pageflip/images/';

	$brochure_id = isset($_POST['allbrochures']) ? intval($_POST['allbrochures']) : 0;
	if(intval($brochure_id)==0) {
		$brochure_id = isset($_GET['brochure_id']) ? intval($_GET['brochure_id']) : 0;
	}
	$readytoAssign = isset($_GET['ready']) ? intval($_GET['ready']) : 0;

	$sform = new XoopsThemeForm(_AM_SELECT_BROCHURE, 'selectbrochureform', XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/admin/index.php?op=assign', 'post');
	$sform->setExtra('enctype="multipart/form-data"');

    		$xt = new PageflipBrochure();
		$allBrochures = $xt->getBrochuresList();
		//print_r($allBrochures);
		$select_brochures = "<select name='allbrochures'>";
		foreach($allBrochures as $brochure_value) {
			if ($brochure_value['brochure_id']==$brochure_id){
				$selected="selected='selected'";
			}else{$selected="";}
			

			$select_brochures .="<option"." value='".$brochure_value['brochure_id']."'".$selected.">".$brochure_value['title']."</option>";
		}
		$select_brochures .= "</select>";
		$sform->addElement(new XoopsFormLabel( "Select a Brochure", $select_brochures ) );

	
	// Submit buttons
	$button_tray = new XoopsFormElementTray('' ,'');
	$submit_btn = new XoopsFormButton('', 'post', "Select", 'submit');
	$button_tray->addElement($submit_btn);
	$sform->addElement($button_tray);
	$sform->display();
	// Copy Files ready to create the page flip brochure
	$from=XOOPS_ROOT_PATH . '/modules/pageflip/setup/template-megazine/';
	$dest=$BinderPath.$brochure_id.'/';
	$lowrespath=$BinderPath.$brochure_id.'/megazine/src/lowres/';

	$xfh= new XoopsFolderHandler( $dest);
	$res=$xfh->create($dest,"777");
	$res=$xfh->chmod($dest,"777");
	unset($xfh);
	$xfh= new XoopsFolderHandler( $from );
	$res=$xfh->chmod($from ,"777");
	$res=$xfh->chmod($dest,"777");
	$res=$xfh->copy(array('to'=> $dest, 'from'=> $from));
	unset($xfh);
//echo "FROM:".$from."<P>";
//echo "TO:".$dest ."<p >";


	//exec("cp ".$from. " -R ".$dest ); // Execute the command IMpthConvert 
	//echo "CP:"."cp ".$from. " -R ".$dest ."<p >";

		
	// READY TO ASSIGN Images to Pages
	if($brochure_id > 0 and $readytoAssign == 1) {
		$pagesOBJ = new PageflipPages();
		$xtmodbrochure = new PageflipBrochure($brochure_id);
		$brochure_pages=$xtmodbrochure->brochure_pages();
		if ($operation ==_AM_BROCHURE_SAVEPAGESANDBUILD){	
			$pagesOBJ->DeleteAllByBrochureID($brochure_id);
		}

		// Save information to Tables (and copy files), so that the relationship can be easily ammended later if needed
		$sql = "INSERT INTO ".$xoopsDB->prefix("pageflip_pages")." (page_id, brochure_id, page_number, page_image, page_title, page_description, page_sound, page_views) VALUES ";
		// Get ready to copy images to the binder folder
		for ( $counter = 1; $counter <= $brochure_pages; $counter += 1) {
		    if ($operation ==_AM_BROCHURE_BUILD || $operation ==_AM_BROCHURE_SAVEPAGESANDBUILD){	
			$destOrig=$BinderPath.$brochure_id.'/';
			$values="";
			if ($counter>1){$values=", ";}
			$postimage='ImageSelect'.$counter;
			$ImageSelect = isset($_POST[$postimage]) ? $_POST[$postimage] : "";
			$values.="(0, ".$brochure_id.",".$counter.", '".$ImageSelect."', '".$ImageSelect."', '".$ImageSelect."', '', 0)";
			$sql.=$values;
			// Copy file to Binder folder for this brochure

			$fromimagepath=XOOPS_ROOT_PATH.$imagesPath.$brochure_id.'/';

                   	$fromimagepath = addPathElement($fromimagepath, $ImageSelect);
                     $dest = addPathElement($destOrig."megazine/src/", $ImageSelect);
                    	//echo "fromimagepath:".$fromimagepath."<P>";
                    //	echo "dest:".$dest."<P>";	
//sleep(0.25);					
			copy($fromimagepath, $dest);
//sleep(0.25);

			// ************************** CREATE LOW RES VERSION OF IMAGE - BEGIN ***************************************
			$IMexecCMD="";
//$IMexecCMD= $IMpthConvert.  " -resize ".$xtmodbrochure->brochure_pagewidth()."x".$xtmodbrochure->brochure_pageheight(). " ".$dest." ". $lowrespath.$ImageSelect." ";
			
			$IMexecCMD= $IMpthConvert.  " -resize ".$xtmodbrochure->brochure_pagewidth()."". " ".$dest." ". $lowrespath.$ImageSelect." ";

			//$IMexecCMD= " -resize ". $dest."/".$ImageSelect." ". $dest."/lowres/".$ImageSelect." ";

			//$IMexecCMD.= $outputImagePath."/".$PageNames.$counter.".jpg " . $pdfFilePath ." -c quit";
			if ($windowsos == true){
				$IMexecCMD= str_replace("/", "\\", $IMexecCMD);		
			}

 			//echo "IMexecCMD:".$IMexecCMD."<P>";
			exec($IMexecCMD); // Execute the command 
sleep(0.25);
			// ************************** CREATE LOW RES VERSION OF IMAGE END ***************************************
		   }


			unset($xfh_template);
		}


			if ($operation ==_AM_BROCHURE_SAVEPAGES || $operation ==_AM_BROCHURE_SAVEPAGESANDBUILD){
				$result = $xoopsDB->queryf($sql);
			}
			if ($operation ==_AM_BROCHURE_BUILD || $operation ==_AM_BROCHURE_SAVEPAGESANDBUILD){
				unlink($destOrig."megazine/megazine.xml");
				$domres=NewDOMDocument($destOrig."megazine/src/", $destOrig,$brochure_id);
			}
				
		unset($xtmodbrochure);
				
	}
	

	/* Populate select boxes with the images, ready to be assigned. */
		
	if($brochure_id>0) {
		$sform = new XoopsThemeForm("Assign images", 'imageuploadform', XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/admin/index.php?op=assign&brochure_id='.$brochure_id.'&ready=1', 'post');
		$sform->setExtra('enctype="multipart/form-data"');

		//echo "brochure_id:".$brochure_id."<P>";

		$xfh = new XoopsFolderHandler($ImagePath.$brochure_id.'/');
		$pdfArray=$xfh->read(); // All images for corresponding brochure in the uploads directory for images
		//echo "PATH:".$ImagePath.$brochure_id.'/'."<P>";
		
		$xtmod = new PageflipBrochure($brochure_id);
		$brochure_pages=$xtmod->brochure_pages();
		
		$pagesOBJ = new PageflipPages();
		$imagesarr=array();
		$imagesarr=$pagesOBJ->getAllpages($brochure_id); // All pages file names and page numbers for corresponding brochure from Table

		// LOOP For each page in Brochure (No of pages as defined in the brochure table)
		for ( $counter = 1; $counter <= $xtmod->brochure_pages(); $counter += 1) {
			//echo "PAGE:".$imagesarr[$counter]['page_image']."<P>";
			// For each page create a select box containing all the pictures in the image folder
			$select_image = "<select name='ImageSelect".$counter."'>";
			$selected="";
			// Loop through each image (from pdf export directory())
			foreach($pdfArray[1] as $pdf_value) {
				//echo "NOMATCH-".$imagesarr[$counter]['page_image']."-".$pdf_value."-".$counter."-".$imagesarr[$counter]['page_number']."<P>";
				// If we find an image file that matches the name of the file corresponding to this page number (counter) then
				// we must make sure that the select box shows that value to be SELECTED
				if (($imagesarr[$counter]['page_image']==$pdf_value) and ($counter==$imagesarr[$counter]['page_number'])){
					//echo "YESMATCH-".$imagesarr[$counter]['page_image']."-".$pdf_value."-".$counter."-".$imagesarr[$counter]['page_number']."<P>";
					$selected="SELECTED";
				}
				// Create Options for the page number in process
				$select_pdfs_options.="<option"." value='".$pdf_value."' ".$selected." >".$pdf_value."</option>";
				$selected="";
			}			
			// Complete select box for this page number
			$select_image .= $select_pdfs_options;
			$select_pdfs_options="";
			$select_image .= "</select>";
			// Write to form for this Page
			$sform->addElement( new XoopsFormLabel( "Page ".$counter, $select_image) );
		}
		unset($pdfArray);   

		// Submit buttons
		$button_tray = new XoopsFormElementTray('' ,'');
		
		//$submit_btn = new XoopsFormButton('', 'operation', _AM_BROCHURE_SAVEPAGES, 'submit');
		//$button_tray->addElement($submit_btn);

		$submit_btn2 = new XoopsFormButton('', 'operation', _AM_BROCHURE_BUILD, 'submit');
		$button_tray->addElement($submit_btn2);

		$submit_btn3 = new XoopsFormButton('', 'operation', _AM_BROCHURE_SAVEPAGESANDBUILD, 'submit');
		$button_tray->addElement($submit_btn3);

		$sform->addElement($button_tray);
		$sform->display();
	}	      
}

	
function imageuploads(){
    global $xoopsDB, $xoopsConfig, $xoopsModule, $myts,$xoopsModuleConfig;
    include_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
    xoops_cp_header();
    adminmenu(2);
    $uploadpath=XOOPS_ROOT_PATH . '/uploads/pageflip/images/';
    $ImagePath='uploads/pageflip/images/';

	$brochure_id = isset($_POST['allbrochures']) ? intval($_POST['allbrochures']) : 0;
	if(intval($brochure_id)==0) {
		$brochure_id = isset($_GET['brochure_id']) ? intval($_GET['brochure_id']) : 0;
	}


	// CREATE DIRECTORY IF NOT EXISTS
	//echo "PATH:".$uploadpath.$brochure_id."<P>";
	$xfh = new XoopsFolderHandler( $uploadpath.$brochure_id.'/' );
	$res=$xfh->create($uploadpath.$brochure_id , "777");
	$res=$xfh->chmod($uploadpath.$brochure_id ,"777");




	$ImageFile = isset($_POST['xoops_upload_file']) ? $_POST['xoops_upload_file'] : "";
	// READY TO UPLOAD
				// DELETE SELECTED FILES
				if(isset($_POST['delupload']) && count($_POST['delupload'])>0) {
					foreach ($_POST['delupload'] as $onefile) {
						//echo "-3	-".XOOPS_ROOT_PATH .$onefile."<P>";
						unlink(XOOPS_ROOT_PATH .$onefile);

					}
				}

	if($ImageFile!="" and $brochure_id!=0) {
		$xt = new PageflipBrochure($brochure_id);

		$fldname = $_FILES[$_POST['xoops_upload_file'][0]];
		$fldname = (get_magic_quotes_gpc()) ? stripslashes($fldname['name']) : $fldname['name'];
		if(xoops_trim($fldname!='')) {
			$sfiles = new sFiles();
			$dstpath = $uploadpath.$brochure_id.'/' ;
			$destname=$sfiles->createUploadName($dstpath ,$fldname, true);
			$permittedtypes = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/x-png', 'image/png');
			$uploader = new XoopsMediaUploader($dstpath, $permittedtypes, 9999999999);
			//echo "DESTNAME:".$destname."<P>";
			//echo "UPFILE:".$fldname."<P>";
			$uploader->setTargetFileName($fldname);

			if ($uploader->fetchMedia($_POST['xoops_upload_file'][0])) {
				if ($uploader->upload()) {
					$xt->setBrochureImgurl(basename($destname));
				} else {
					echo _AM_UPLOAD_ERROR . ' ' . $uploader->getErrors();
				}
			} else {
				echo $uploader->getErrors();
			}


		}
	}	
	
	$sform = new XoopsThemeForm("Select Brochure", 'selectbrochureform', XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/admin/index.php?op=upload', 'post');
	$sform->setExtra('enctype="multipart/form-data"');


/*
       $xt = new PageflipBrochure();
	$allBrochures = $xt->getAllBrochures($xoopsModuleConfig['restrictindex']);
	$brochure_tree = new XoopsObjectTree($allBrochures, 'brochure_id', 'brochure_pid');
	$brochure_select = $brochure_tree->makeSelBox('allbrochures', 'brochure_title', 'pageflip_brochures', $brochure_id, true);

	$sform->addElement(new XoopsFormLabel("Select Brochure", $brochure_select));
*/

    		$xt = new PageflipBrochure();
		$allBrochures = $xt->getBrochuresList();
		//print_r($allBrochures);
		$select_brochures = "<select name='allbrochures'>";
		foreach($allBrochures as $brochure_value) {
			if ($brochure_value['brochure_id']==$brochure_id){
				$selected="selected='selected'";
			}else{$selected="";}
			

			$select_brochures .="<option"." value='".$brochure_value['brochure_id']."'".$selected.">".$brochure_value['title']."</option>";
		}
		$select_brochures .= "</select>";
		$sform->addElement(new XoopsFormLabel( "Select a Brochure", $select_brochures ) );

	
	// Submit buttons
	$button_tray = new XoopsFormElementTray('' ,'');
	$submit_btn = new XoopsFormButton('', 'post', "Select", 'submit');
	$button_tray->addElement($submit_btn);
	$sform->addElement($button_tray);
	$sform->display();
	
	if($brochure_id>0) {
		//$brochure_id = isset($_GET['brochure_id']) ? intval($_GET['brochure_id']) : 0;
		$sform = new XoopsThemeForm("Upload images", 'imageuploadform', XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/admin/index.php?op=upload&brochure_id='.$brochure_id, 'post');
		$sform->setExtra('enctype="multipart/form-data"');
		
		$uploadirectory=$imagePath.$brochure_id.	'/' ;

		// ************************************************
		// All images with option to delete
		// *************************************************

		$filesarr=array();
		$filesarr=$xfh->read();
		//print_r($filesarr);

		if(count($filesarr)>0) {
			$upl_tray = new XoopsFormElementTray(_AM_UPLOAD_ATTACHFILE,'<br />');
			$upl_checkbox=new XoopsFormCheckBox('', 'delupload[]');
			$ImageUploadPath="/".$ImagePath.$brochure_id.'/';

			$i=0;
			foreach ($filesarr[1] as $onefile)
			{
				$i=0;
				$link = sprintf("<a href='%s/%s' target='_blank'>%s</a>\n",XOOPS_URL,$ImagePath.$brochure_id."/".$onefile,$onefile);
				$upl_checkbox->addOption($ImageUploadPath.$onefile,$link);
				$i++;
			}
			$upl_tray->addElement($upl_checkbox,false);
			$dellabel=new XoopsFormLabel(_AM_DELETE_SELFILES,'');
			$upl_tray->addElement($dellabel,false);
			$sform->addElement($upl_tray);
		}


		$brochureimage='xoops.gif';
		$imgtray = new XoopsFormElementTray("Upload images",'<br />');
	

	    $uploadfolder=sprintf(_AM_UPLOAD_WARNING,XOOPS_URL . $uploadirectory );
	    $fileseltray= new XoopsFormElementTray('','<br />');
	    $fileseltray->addElement(new XoopsFormFile(_AM_BROCHURE_PICTURE , 'attachedfile', 99999999999), false);
	    $fileseltray->addElement(new XoopsFormLabel($uploadfolder ), false);
	    $imgtray->addElement($fileseltray);
	    
		$imgpath=sprintf("<br /><br />"._AM_IMGNAEXLOC, '' . $uploadirectory );
		$imageselect= new XoopsFormSelect($imgpath, 'brochure_imgurl',$brochureimage);
	    $brochures_array = XoopsLists :: getImgListAsArray( XOOPS_ROOT_PATH . $uploadirectory );
	    foreach( $brochures_array as $image ) {
	        $imageselect->addOption("$image", $image);
	    }
/*
	    $imageselect->setExtra( "onchange='showImgSelected(\"image3\", \"brochure_imgurl\", \"" . $uploadirectory . "\", \"\", \"" . XOOPS_URL . "\")'" );
	    $imgtray->addElement($imageselect,false);
	    $imgtray -> addElement( new XoopsFormLabel( '', "<br /><img src='" . XOOPS_URL . "/" . $uploadirectory . "/" . $brochureimage . "' name='image3' id='image3' alt='' />" ) );
*/		    
	    $sform->addElement($imgtray);	    
	
		    
		// Submit buttons
		$button_tray = new XoopsFormElementTray('' ,'');
		$submit_btn = new XoopsFormButton('', 'post', "Go", 'submit');
		$button_tray->addElement($submit_btn);
		$sform->addElement($button_tray);
		$sform->display();
	}	    
}


function pdfconvert(){

    global $xoopsDB, $xoopsConfig, $xoopsModule, $myts,$xoopsModuleConfig, $PDFPath, $ImagePath, $GSpthConvert, $IMpthConvert  ;
    include_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
    xoops_cp_header();
    adminmenu(3);  
	$PDFPath=XOOPS_ROOT_PATH.'/uploads/pageflip/pdfs/';
	$ImagePath=XOOPS_ROOT_PATH.'/uploads/pageflip/images/';
	$windowsos=false;
	
	$brochure_id = isset($_POST['allbrochures']) ? intval($_POST['allbrochures']) : 0;
	if(intval($brochure_id)==0) {
		$brochure_id = isset($_GET['brochure_id']) ? intval($_GET['brochure_id']) : 0;
	}	
	$PDFFile = isset($_POST['PDFSelect']) ? $_POST['PDFSelect'] : "";
	$Action = isset($_GET['action']) ? $_GET['action'] : "";

 	$brochure_assignpages= isset($_POST['brochure_assignpages']) ? $_POST['brochure_assignpages'] : 1;
 	$brochure_pageorderstart= isset($_POST['brochure_pageorderstart']) ? $_POST['brochure_pageorderstart'] : 1;
	$brochure_page_suffix=intval($brochure_pageorderstart);
	if ($brochure_page_suffix==0){$brochure_page_suffix=1;}
	//echo "brochure_pageorderstart:".$brochure_page_suffix."<P>";
	$brochure_pagesinPDF = isset($_POST['brochure_pagesinPDF']) ? intval($_POST['brochure_pagesinPDF']) : 1;
	//echo "brochure_pagesinPDF :".$brochure_pagesinPDF ."<P>";
	if (intval($brochure_id!=0)){
		$outputPath = $PDFPath.$brochure_id;
		// CREATE DIRECTORY IF NOT EXISTS
		$xfh = new XoopsFolderHandler( $outputPath);
		//echo "out1:".$outputPath."<P>";
		$res=$xfh->create($outputPath , "777");
		$res=$xfh->chmod($outputPath ,"777");
		unset($xfh);

		$outputImagePath= $ImagePath.$brochure_id;
		//echo "out2:".$outputImagePath."<P>";

		// CREATE DIRECTORY IF NOT EXISTS
		$xfh = new XoopsFolderHandler( $outputImagePath);
		$res=$xfh->create($outputImagePath, "777");
		$res=$xfh->chmod($outputImagePath,"777");
		unset($xfh);

	}


	// READY TO CONVERT
	if($PDFFile!="" and $brochure_id!=0) {
		//$watermark_path = '../path/to/watermark.png';
	
		$imageMagickPath = '/usr/bin/convert'; 
		
		$escape = substr($imageMagickPath, 1, 1) == ':' ? '' : '\\'; // Linux likes us to escape brackets, but Windows not			
		$pdfFilePath = $PDFPath .$brochure_id.'/'.$PDFFile;

		
		// GET NUMBER OF PAGES IN Brochure
		$xtmod = new PageflipBrochure($brochure_id);
		$brochure_pages=$xtmod->brochure_pages();
		unset($xtmod);

		$PDFPars = isset($_POST['brochure_addpars']) ? $_POST['brochure_addpars'] : "";
		$PDFCrop = isset($_POST['brochure_cropconfig']) ? $_POST['brochure_cropconfig'] : "";
		$PageNames = isset($_POST['brochure_pageprefix']) ? $_POST['brochure_pageprefix'] : "page_";
		$Resolution = isset($_POST['brochure_resolution']) ? $_POST['brochure_resolution'] : "140";


		/* AUTO order of Page numbering - Setup (Start step 1)*/
		if ($brochure_assignpages==1){
			$pagesOBJ = new PageflipPages();
			$pagesOBJ->DeleteAllByBrochureID($brochure_id);
			$sql = "INSERT INTO ".$xoopsDB->prefix("pageflip_pages")." (page_id, brochure_id, page_number, page_image, page_title, page_description, page_sound, page_views) VALUES ";
			$values="";
		}
		/* AUTO order of Page numbering - Setup (End step 1)*/



		// RUN CONVERT PDF COMMANDS
		for ( $counter = 1; $counter <= $brochure_pagesinPDF; $counter += 1) {

			// Convert a single PDF to JPG file
			$GSexecCMD="";
			$GSexecCMD = $GSpthConvert . "-q -sDEVICE=jpeg -dBATCH -dNOPAUSE -dFirstPage=".$counter." -dLastPage=".$counter." -r".$Resolution." -sOutputFile=";
			$GSexecCMD .= $outputImagePath."/".$PageNames.$brochure_page_suffix.".jpg '" . $pdfFilePath ."' -c quit";
			if ($windowsos == true){
				$GSexecCMD = str_replace("/", "\\", $GSexecCMD);		
			}

			$GSexecCMD=$GSexecCMD;
			//echo "GSexecCMD:".$GSexecCMD."<P>";
			exec($GSexecCMD); // Execute the command IMpthConvert 
			//echo "brochure_cropconfig:".$_POST['brochure_cropconfig']."<P>";
sleep(0.5);
			// Crop file
			$crop = $_POST['brochure_cropconfig'];	
			if ($crop != "") {
				$execCMDImageMagick="";
				//1567x2387+462+599
				$outputPathFile=$outputImagePath."/".$PageNames.$brochure_page_suffix.".jpg ";
				echo "outputPathFile:".$outputPathFile."<P>";
				$outputPathFileCrop=$outputImagePath."/".$PageNames.$brochure_page_suffix.".jpg ";
				echo "outputPathFileCrop:".$outputPathFileCrop."<P>";
			 	$execCMDImageMagick .= $IMpthConvert.$outputPathFile." -crop " . $crop . " ".$outputPathFileCrop;

			 	
				if ($windowsos == true){
					$execCMDImageMagick = str_replace("/", "\\", $execCMDImageMagick);		
				}
				echo "execCMDImageMagick:".$execCMDImageMagick."<P>";
			 	exec($execCMDImageMagick); // Execute the command
sleep(0.5);
			}	
			/* AUTO order of Page numbering - Assign (start- step 2)*/	
			if ($brochure_assignpages==1){
				$ImageSelect = $PageNames.$brochure_page_suffix.".jpg";
				$sql.="(0, ".$brochure_id.",".$brochure_page_suffix.", '".$ImageSelect."', '".$ImageSelect."', '".$ImageSelect."', '', 0)";	
				//echo "CP:".$counter."--".$brochure_pages."<P>";
				if ($counter < $brochure_pages){
					$sql.=", ";
				}
			}
			/* AUTO order of Page numbering - Assign (end step 2)*/			
 			
			$brochure_page_suffix++;
		}
		

	}


	/* AUTO order of Page numbering - Write to DB (start- step 3)*/
	if ($brochure_assignpages==1){
		//echo "ASSIGN-SQL:".$sql."<P>";	
		$result = $xoopsDB->queryf($sql);	
	}	
	/* AUTO order of Page numbering - Assign (end step 3)*/

	
	// UPLOAD PDF FILE
	if($Action=="uploadpdf" and $brochure_id!=0) {

				// Manage upload(s)
				//$PDFUploadPath="/".$PDFPath .$brochure_id.'/';
				$PDFUploadPath=$PDFPath .$brochure_id;
				//echo "PDFUploadPath:".$PDFUploadPath."<P>";


					
				if(isset($_POST['delupload']) && count($_POST['delupload'])>0) {
					foreach ($_POST['delupload'] as $onefile) {
						unlink($onefile);

					}
				}
				if(isset($_POST['xoops_upload_file'])) {
					
					$fldname = $_FILES[$_POST['xoops_upload_file'][0]];
					$fldname = (get_magic_quotes_gpc()) ? stripslashes($fldname['name']) : $fldname['name'];
					if(xoops_trim($fldname!='')) {
						$sfiles = new sFiles();
						$destname=$sfiles->createUploadName($PDFUploadPath,$fldname);

						$uploader = new XoopsMediaUploader( $PDFUploadPath,array("application/pdf") , 9657565533);
						$uploader->setTargetFileName($fldname);

						if ($uploader->fetchMedia($_POST['xoops_upload_file'][0])) {
							if ($uploader->upload()) {
							} else {
								echo _AM_UPLOAD_ERROR. ' ' . $uploader->getErrors();
							}
						} else {
							echo $uploader->getErrors();
						}
					}
				}
			}

				
	$sform = new XoopsThemeForm("Select Brochure", 'selectbrochureform', XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/admin/index.php?op=convert', 'post');
	$sform->setExtra('enctype="multipart/form-data"');




    		$xt = new PageflipBrochure();
		$allBrochures = $xt->getBrochuresList();
		//print_r($allBrochures);
		$select_brochures = "<select name='allbrochures'>";
		foreach($allBrochures as $brochure_value) {
			if ($brochure_value['brochure_id']==$brochure_id){
				$selected="selected='selected'";
			}else{$selected="";}
			

			$select_brochures .="<option"." value='".$brochure_value['brochure_id']."'".$selected.">".$brochure_value['title']."</option>";
		}
		$select_brochures .= "</select>";
		$sform->addElement(new XoopsFormLabel( "Select a Brochure", $select_brochures ) );


	
	// Submit buttons
	$button_tray = new XoopsFormElementTray('' ,'');
	$submit_btn = new XoopsFormButton('', 'post', "Select", 'submit');
	$button_tray->addElement($submit_btn);
	$sform->addElement($button_tray);
	$sform->display();
	
	//echo "brochure_id:".$brochure_id."<P>";

	//************************************************************************
	//
	//	Get any PDFS that are already uploaded - pdfArray
	//
	//
	//
	//************************************************************************
	$xfh = new XoopsFolderHandler( XOOPS_ROOT_PATH . '/uploads/pageflip/pdfs/'.$brochure_id.'/', 'brochure_id', 'brochure_pid');
	$pdfArray=$xfh->read();

	if($brochure_id>0) {
		// UPLOAD PDF FORM
		$sform = new XoopsThemeForm("Upload a PDF", 'uploadpdfform', XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/admin/index.php?op=convert&action=uploadpdf&brochure_id='.$brochure_id, 'post');
		$sform->setExtra('enctype="multipart/form-data"');
	
	//	$xfh = new XoopsFolderHandler( XOOPS_ROOT_PATH . $PDFPath.$brochure_id.'/', 'brochure_id', 'brochure_pid');
		$filesarr=array();
		$filesarr=$xfh->read();
		//print_r($filesarr);
		if(count($filesarr)>0) {
			$upl_tray = new XoopsFormElementTray(_AM_UPLOAD_ATTACHFILE,'<br />');
			$upl_checkbox=new XoopsFormCheckBox('', 'delupload[]');
			$PDFUploadPath="/".$PDFPath .$brochure_id.'/';


			$i=0;
			foreach ($filesarr[1] as $onefile)
			{
				$i=0;
				$link = sprintf("<a href='%s/%s' target='_blank'>%s</a>\n",XOOPS_URL,$PDFPath.$brochure_id."/".$onefile,$onefile);
				$upl_checkbox->addOption($PDFUploadPath.$onefile,$link);
				$i++;
			}
			$upl_tray->addElement($upl_checkbox,false);
			$dellabel=new XoopsFormLabel(_AM_DELETE_SELFILES,'');
			$upl_tray->addElement($dellabel,false);
			$sform->addElement($upl_tray);
		}
		$sform->addElement(new XoopsFormFile(_AM_SELFILE, 'attachedfile', $xoopsModuleConfig['maxuploadsize']), false);
		
		// Submit buttons
		$button_tray = new XoopsFormElementTray('' ,'');
		$submit_btn = new XoopsFormButton('', 'post', "Go", 'submit');
		$button_tray->addElement($submit_btn);
		$sform->addElement($button_tray);
		$sform->display();

	}
	
	
	//echo XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/admin/index.php?op=convert&brochure_id='.$brochure_id;
	/* START PDF CONVERT TRAY*/
	if($brochure_id>0) {
		//$brochure_id = isset($_GET['brochure_id']) ? intval($_GET['brochure_id']) : 0;
			
		//$sform = new XoopsThemeForm("Convert PDF", 'selectpdfform', XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/admin/index.php?op=convert&brochure_id='.$brochure_id, 'post');
		  $sform = new XoopsThemeForm("Convert PDF",   'convertpdfform', XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/admin/index.php?op=convert&amp;brochure_id='.$brochure_id, 'post');
		 // $sform = new XoopsThemeForm("Upload a PDF", 'uploadpdfform', XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/admin/index.php?op=convert&action=uploadpdf&brochure_id='.$brochure_id, 'post');
		$sform->setExtra('enctype="multipart/form-data"');

		$xt = new XoopsTree($xoopsDB->prefix('pageflip_brochures'), 'brochure_id', 'brochure_pid');
		
		$brochures_arr = $xt->getChildTreeArray(0,'brochure_title');
		//print_r($brochures_arr);
		//break;
		$totalbrochures = count($brochures_arr);
		$class='';
	


		$select_pdfs = "<select name='PDFSelect'>";
		foreach($pdfArray[1] as $pdf_value) {
			$select_pdfs.="<option"." value='".$pdf_value."'>".$pdf_value."</option>";
		}
		$select_pdfs .= "</select>";
	
		$sform->addElement(new XoopsFormLabel( "Select PDF to convert", $select_pdfs) );

		//$sform->addElement(new XoopsFormText("Additional conversion parameters", 'PDFPars', 50, 255, $xoopsModuleConfig['AddConfig']), false);
		//$sform->addElement(new XoopsFormText("Crop config", 'PDFCrop', 50, 255, $xoopsModuleConfig['Crop']), false);		
		//$sform->addElement(new XoopsFormText("Page Prefix", 'PageNames', 50, 255, "pageno_"), false);
		//$sform->addElement(new XoopsFormText("Resolution", 'Resolution', 50, 255, "150"), false);	

		$xtmod = new PageflipBrochure($brochure_id);
		$brochure_addpars =$xtmod->brochure_addpars();
		$brochure_cropconfig =$xtmod->brochure_cropconfig();
		$brochure_pageprefix =$xtmod->brochure_pageprefix();
		$brochure_resolution =$xtmod->brochure_resolution();
		$brochure_pagesinPDF =$xtmod->brochure_pages();

		$sform->addElement(new XoopsFormText(_AM_BROCHURE_ADD_PARS, 'brochure_addpars', 50, 255, $brochure_addpars), false);
		$sform->addElement(new XoopsFormText(_AM_BROCHURE_CROP_CONFIG, 'brochure_cropconfig', 50, 255, $brochure_cropconfig), false);		
		$sform->addElement(new XoopsFormText(_AM_BROCHURE_PAGE_PREFIX, 'brochure_pageprefix', 50, 255, $brochure_pageprefix), false);
		$sform->addElement(new XoopsFormText(_AM_BROCHURE_RESOLUTION, 'brochure_resolution', 50, 255, $brochure_resolution), false);	
		$sform->addElement(new XoopsFormRadioYN(_AM_BROCHURE_ASSIGNPAGES, 'brochure_assignpages', $brochure_assignpages, _YES, _NO),true);
		$sform->addElement(new XoopsFormText(_AM_BROCHURE_PAGE_NUMBERING_START, 'brochure_pageorderstart', 50, 255, $brochure_pageorderstart), true);	
		$sform->addElement(new XoopsFormText(_AM_BROCHURE_PAGE_IN_PDF, 'brochure_pagesinPDF', 50, 255, $brochure_pagesinPDF), true);	


	/* START PDF CONVERT TRAY*/
	$imgtray = new XoopsFormElementTray(_AM_TOPICIMG,'<br />');


    	 
    /* END PDF CONVERT TRAY*/
    
		// Submit buttons
		$button_tray = new XoopsFormElementTray('' ,'');
		$submit_btn = new XoopsFormButton('', 'post', "Convert PDF", 'submit');
		$button_tray->addElement($submit_btn);
		$sform->addElement($button_tray);
		$sform->display();
		}
}




// Save a $brochure after it has been modified
function modcategories()
{

    global $xoopsDB, $xoopsModule, $xoopsModuleConfig;
    $xt = new PageflipCategory(intval($_POST['category_id']));

	//echo "modcategories-CATID:".intval($_POST['category_id'])."<P>";

    if (empty($_POST['category_title'])) {
        redirect_header( 'index.php?op=catmanager', 2, _AM_ERRORTOPICNAME );
    }
    if(isset($_SESSION['items_count'])) {
    	$_SESSION['items_count'] = -1;
    }

    $xt -> setCategoryTitle($_POST['category_title']);

   	if(isset($_POST['category_description'])) {
   		$xt->setCategoryDescription($_POST['category_description']);
   	} else {
   		$xt->setCategoryDescription('');
   	}


/*
    if (isset($_POST['brochure_imgurl']) && $_POST['brochure_imgurl']!= '') {
        $xt -> setBrochureImgurl($_POST['brochure_imgurl']);
    }

	if(isset($_POST['xoops_upload_file'])) {
		$fldname = $_FILES[$_POST['xoops_upload_file'][0]];
		$fldname = (get_magic_quotes_gpc()) ? stripslashes($fldname['name']) : $fldname['name'];

		if(xoops_trim($fldname!='')) {
			$sfiles = new sFiles();
			$dstpath = XOOPS_ROOT_PATH . '/uploads/' . $xoopsModule->dirname() . '/covers';
			$destname=$sfiles->createUploadName($dstpath ,$fldname, true,"binder_cover_uploads");
			$permittedtypes = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/x-png', 'image/png');
			$uploader = new XoopsMediaUploader($dstpath, $permittedtypes, 9999999999);
			$uploader->setTargetFileName($destname);


			if ($uploader->fetchMedia($_POST['xoops_upload_file'][0])) {


				if ($uploader->upload()) {
					$xt->setBrochureImgurl(basename($destname));
				} else {
					echo _AM_UPLOAD_ERROR . ' ' . $uploader->getErrors();
				}
			} else {
				echo $uploader->getErrors();
			}
		}
   	}

*/





    $xt->store();

	// Permissions
	$gperm_handler = &xoops_gethandler('groupperm');
	$criteria = new CriteriaCompo();
	$criteria->add(new Criteria('gperm_itemid', $xt->category_id(), '='));
	$criteria->add(new Criteria('gperm_modid', $xoopsModule->getVar('mid'),'='));
	$criteria->add(new Criteria('gperm_name', 'pageflip_view', '='));
	$gperm_handler->deleteAll($criteria);


	if(isset($_POST['groups_pageflip_can_view'])) {
		foreach($_POST['groups_pageflip_can_view'] as $onegroup_id) {
			$gperm_handler->addRight('pageflip_view', $xt->category_id(), $onegroup_id, $xoopsModule->getVar('mid'));
		}
	}

    redirect_header( 'index.php?op=catmanager', 1, _AM_DBUPDATED );
    exit();
}



// Add a new Category
function addCategory()
{

    global $xoopsDB, $xoopsModule, $xoopsModuleConfig;

    $xt = new PageflipCategory(0);
    if (!$xt->categoryExists(0, $_POST['category_title'])) {

        if (empty($_POST['category_title']) || xoops_trim($_POST['category_title'])=='') {
            redirect_header( 'index.php?op=catmanager', 2, _AM_ERRORBROCHURENAME );
        }

        $xt->setCategoryTitle($_POST['category_title']);

		if(isset($_POST['category_description'])) {
			$xt->setCategoryDescription($_POST['category_description']);
		} else {
			$xt->setCategoryDescription('');
		}
		$xt->setCategoryDescription('');
		$xt->store();

		// Permissions
/*
		$gperm_handler = &xoops_gethandler('groupperm');
		if(isset($_POST['groups_pageflip_can_view'])) {
			foreach($_POST['groups_pageflip_can_view'] as $onegroup_id) {
				$gperm_handler->addRight('pageflip_view', $xt->brochure_id(), $onegroup_id, $xoopsModule->getVar('mid'));
			}
		}
*/
        $notification_handler = & xoops_gethandler('notification');
        redirect_header('index.php?op=catmanager', 1, _AM_DBUPDATED);
    } else {
        redirect_header('index.php?op=catmanager', 2, _AM_ADD_BROCHURE_ERROR);
    }
    exit();
}



// Delete a category_id 
function delCategory()
{
    global $xoopsDB, $xoopsModule;

    $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
    $xt = new PageflipCategory ($category_id);

      $xt -> deleteSingleCategory($category_id);
      
	// Delete also the notifications and permissions
       xoops_notification_deletebyitem( $xoopsModule -> getVar( 'mid' ), 'category', $category_id );
	 xoops_groupperm_deletebymoditem($xoopsModule->getVar('mid'), 'pageflip_view', $category_id);

        redirect_header( 'index.php?op=catmanager', 1, _AM_DBUPDATED );
 

}



function catmanager()
{
    global $xoopsDB, $xoopsConfig, $xoopsModule, $myts;
    include_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
    xoops_cp_header();
    adminmenu(0);

    $uploadfolder=sprintf(_AM_UPLOAD_WARNING,XOOPS_URL . '/modules/' . $xoopsModule->dirname().'/images/categories');
    $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
    $action = isset($_GET['action']) ? $_GET['action'] : "";
	$xt = new PageflipCategory ();
	$categories_arr = $xt->getChildTreeArray(0);
//print_r($categories_arr);
	$totalcategories = count($categories_arr);
	$class='';

	pageflip_collapsableBar('categoriesmanager', 'topcategoriesmanager');
	echo "<img onclick=\"toggle('toptable'); toggleIcon('toptableicon');\" id='topcategoriesmanager' name='topcategoriesmanager' src='" . XOOPS_URL . "/modules/pageflip/images/close12.gif' alt='' /></a>&nbsp;"._AM_CATEGORIESMNGR . ' (' . $totalcategories . ')'."</h4>";
	echo "<div id='categoriesmanager'>";
	echo '<br />';
	echo "<a href=\"index.php?op=catmanager\"\"><span>"."New Category" ."</span></a>";
	echo '<br />';echo '<br />';

       echo "<div style='text-align: center;'>";
       echo "<table width='100%' cellspacing='1' cellpadding='3' border='0' class='outer'><tr class='bg3'><td align='center'>" . _AM_CATEGORY . "</td><td align='left'>" . _AM_CATEGORYNAME . "</td><td align='center'>" . _AM_ACTION . "</td></tr>";

	if(is_array($categories_arr) && $totalcategories) {
		$cpt=1;
		$tmpcpt=$start;
		$ok=true;
		$output='';
		while($ok) {
			if($tmpcpt < $totalcategories) {
				$linkedit = XOOPS_URL . '/modules/'.$xoopsModule->dirname() . '/admin/index.php?op=catmanager&amp;action=editCategory&amp;category_id=' . $categories_arr[$tmpcpt]['category_id'];
				$linkdelete = XOOPS_URL . '/modules/'.$xoopsModule->dirname() . '/admin/index.php?op=catmanager&amp;action=delCategory&amp;category_id=' . $categories_arr[$tmpcpt]['category_id'];
				$linkview = XOOPS_URL . '/modules/'.$xoopsModule->dirname() . '/index.php?cat_id='. $categories_arr[$tmpcpt]['category_id'];

				$action=sprintf("<a href='%s'>%s</a> - <a href='%s'>%s</a> - <a href='%s'>%s</a>",$linkedit,_AM_EDIT , $linkdelete, _AM_DELETE, $linkview , "View");
				$parent='&nbsp;';
				if($categories_arr[$tmpcpt]['category_pid']>0)	{
					$xttmp = new XoopsTopic($xoopsDB->prefix('categories'),$categories_arr[$tmpcpt]['category_pid']);
					$parent = $xttmp->category_title();
					unset($xttmp);
				}
				if($categories_arr[$tmpcpt]['category_pid']!=0) {
					$categories_arr[$tmpcpt]['prefix'] = str_replace('.','-',$categories_arr[$tmpcpt]['prefix']) . '&nbsp;';
				} else {
					$categories_arr[$tmpcpt]['prefix'] = str_replace('.','',$categories_arr[$tmpcpt]['prefix']);
				}
				$submenu=$categories_arr[$tmpcpt]['menu'] ? _YES : _NO;
				$class = ($class == 'even') ? 'odd' : 'even';
				$output  = $output . "<tr class='".$class."'><td>" . $categories_arr[$tmpcpt]['category_id'] . "</td><td align='left'>" . $categories_arr[$tmpcpt]['prefix'] . $myts->displayTarea($categories_arr[$tmpcpt]['category_title']) . "</td><td>" . $action . "</td></tr>";
			} else {
				$ok=false;
			}
			if($cpt>=5) {
				$ok=false;
			}
			$tmpcpt++;
			$cpt++;
		}
		echo $output;
	}
	$pagenav = new XoopsPageNav( $totalcategories, 5, $start, 'start', 'op=catmanager');
	echo "</table><div align='right'>".$pagenav->renderNav().'</div><br />';
	echo "</div></div><br />\n";

	$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

	if($category_id>0) {
unset($xtmod );
		$xtmod = new PageflipCategory ($category_id);
				//print_r($xtmod);
		$category_title=$xtmod->category_title('E');
		$category_description=$xtmod->category_description('E');

		$op='modcategories';
/*
		if(xoops_trim($xtmod->category_imgurl())!='') {
			$categoryimage=$xtmod->category_imgurl();
		} else {
			$categoryimage='blank.png';
		}
*/


		$btnlabel=_AM_MODIFY;
		$parent=$xtmod->category_pid();
		$formlabel=_AM_MODIFYCATEGORY;
		$submenu=$xtmod->menu();
		unset($xtmod);
	} else {
		$brochure_title='';
		$brochure_description='';
		
		$op='addCategory';
		$brochureimage='xoops.gif';
		$btnlabel=_AM_ADD;
		$parent=-1;
		$submenu=0;
		$formlabel=_AM_ADD_CATEGORY;
		$brochure_color='000000';
	}
	//echo "-1-<P>";
	$sform = new XoopsThemeForm($formlabel, 'categoryform', XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/admin/index.php', 'post');
	$sform->setExtra('enctype="multipart/form-data"');
	$sform->addElement(new XoopsFormText(_AM_CATEGGORYNAME, 'category_title', 50, 255, $category_title), true);
	$editor=new XoopsFormTextArea(_AM_CATEGORY_DESCR,'category_description', $category_description, 15, 60, 'hometext_hidden');
	if($editor) {
		$sform->addElement($editor,false);
	}
	
	$sform->addElement(new XoopsFormHidden('op', $op), false);
	$sform->addElement(new XoopsFormHidden('category_id', $category_id), false);



	// ********** Picture
	//$imgtray = new XoopsFormElementTray(_AM_BROCHUREIMG,'<br />');
	//$imgpath=sprintf(_AM_IMGNAEXLOC, 'uploads/' . $xoopsModule -> dirname() . '/covers/' );
	
/*
    $imageselect= new XoopsFormSelect($imgpath, 'brochure_imgurl',$brochureimage);

    $imgtray -> addElement( new XoopsFormLabel( '', "<br /><img src='" . XOOPS_URL . "/" . $uploadirectory . "/" . $brochureimage . "' name='image3' id='image3' alt='' />" ) );
    $uploadfolder=sprintf(_AM_UPLOAD_WARNING,XOOPS_URL . '/uploads/' . $xoopsModule -> dirname().'/covers');

    $imgtray -> addElement( new XoopsFormLabel( '', "<br /><img src='" . XOOPS_URL . "/" . $uploadirectory . "/" . $topicimage . "' name='image3' id='image3' alt='' />" ) );
    $uploadfolder=sprintf(_AM_UPLOAD_WARNING,XOOPS_URL . '/modules/' . $xoopsModule -> dirname().'/covers');

    $fileseltray= new XoopsFormElementTray('','<br />');
    $fileseltray->addElement(new XoopsFormFile(_AM_BROCHURE_PICTURE , 'attachedfile', 999999999999), false);
    $fileseltray->addElement(new XoopsFormLabel($uploadfolder ), false);
    $imgtray->addElement($fileseltray);
    $sform->addElement($imgtray);
*/


	// Permissions
    $member_handler = & xoops_gethandler('member');
    $group_list = &$member_handler->getGroupList();
    $gperm_handler = &xoops_gethandler('groupperm');
    $full_list = array_keys($group_list);


	$groups_ids = array();
    if($category_id > 0) {		// Edit mode
    	$category_ids = $gperm_handler->getGroupIds('pageflip_view', $category_id, $xoopsModule->getVar('mid'));
    	$category_ids = array_values($groups_ids);
    	$groups_pageflip_can_view_checkbox = new XoopsFormCheckBox(_AM_VIEWFORM, 'groups_pageflip_can_view[]', $groups_ids);
    } else {	// Creation mode
    	$groups_pageflip_can_view_checkbox = new XoopsFormCheckBox(_AM_VIEWFORM, 'groups_pageflip_can_view[]', $full_list);
    }
    $groups_pageflip_can_view_checkbox->addOptionArray($group_list);
    $sform->addElement($groups_pageflip_can_view_checkbox);
    
    

	// Submit buttons
	$button_tray = new XoopsFormElementTray('' ,'');
	$submit_btn = new XoopsFormButton('', 'post', $btnlabel, 'submit');
	$button_tray->addElement($submit_btn);
	$sform->addElement($button_tray);
	$sform->display();

}



function brochuresmanager()
{
    global $xoopsDB, $xoopsConfig, $xoopsModule, $myts;
    include_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
    xoops_cp_header();
    adminmenu(1);
    $uploadfolder=sprintf(_AM_UPLOAD_WARNING,XOOPS_URL . '/modules/' . $xoopsModule->dirname().'/images/brochures');
    $uploadirectory='/uploads/' . $xoopsModule -> dirname().'/covers';
    $start = isset($_GET['start']) ? intval($_GET['start']) : 0;

	//$xt = new XoopsTree ($xoopsDB->prefix('pageflip_brochures'), 'brochure_id', 'brochure_pid');

	$xt = new PageflipBrochure ();
	//$brochures_arr = $xt->getAllBrochures($xoopsModuleConfig['restrictindex']);
	$brochures_arr = $xt->getChildTreeArray(0);
//print_r($brochures_arr);
	//break;
	$totalbrochures = count($brochures_arr);
	$class='';

   // echo '<h4>' . _AM_CONFIG . '</h4>';
	pageflip_collapsableBar('brochuresmanager', 'topbrochuresmanager');
	echo "<img onclick=\"toggle('toptable'); toggleIcon('toptableicon');\" id='topbrochuresmanager' name='topbrochuresmanager' src='" . XOOPS_URL . "/modules/pageflip/images/close12.gif' alt='' /></a>&nbsp;"._AM_BROCHURESMNGR . ' (' . $totalbrochures . ')'."</h4>";
	echo "<div id='brochuresmanager'>";
	echo '<br />';
	echo "<a href=\"index.php?op=brochuresmanager\"\"><span>"."New Brochure" ."</span></a>";
	echo '<br />';echo '<br />';
    echo "<div style='text-align: center;'>";
    echo "<table width='100%' cellspacing='1' cellpadding='3' border='0' class='outer'><tr class='bg3'><td align='center'>" . _AM_BROCHURE . "</td><td align='left'>" . _AM_BROCHURENAME . "</td><td align='center'>" . _AM_ACTION . "</td></tr>";
	if(is_array($brochures_arr) && $totalbrochures) {
		$cpt=1;
		$tmpcpt=$start;
		$ok=true;
		$output='';
		while($ok) {

			if($tmpcpt < $totalbrochures) {

				$linkedit = XOOPS_URL . '/modules/'.$xoopsModule->dirname() . '/admin/index.php?op=brochuresmanager&amp;brochure_id=' . $brochures_arr[$tmpcpt]['brochure_id'];
				$linkdelete = XOOPS_URL . '/modules/'.$xoopsModule->dirname() . '/admin/index.php?op=delBrochure&amp;brochure_id=' . $brochures_arr[$tmpcpt]['brochure_id'];
				$linkview = XOOPS_URL . '/uploads/'.$xoopsModule->dirname() . '/binder/' . $brochures_arr[$tmpcpt]['brochure_id'];
				$action=sprintf("<a href='%s'>%s</a> - <a href='%s'>%s</a> - <a href='%s'>%s</a>",$linkedit,_AM_EDIT , $linkdelete, _AM_DELETE, $linkview , "View");

/*
				$parent='&nbsp;';
				if($brochures_arr[$tmpcpt]['brochure_pid']>0)	{
					$xttmp = new XoopsTopic($xoopsDB->prefix('brochures'),$brochures_arr[$tmpcpt]['brochure_pid']);
					$parent = $xttmp->brochure_title();
					unset($xttmp);
				}
*/

				if($brochures_arr[$tmpcpt]['brochure_pid']!=0) {
					$brochures_arr[$tmpcpt]['prefix'] = str_replace('.','-',$brochures_arr[$tmpcpt]['prefix']) . '&nbsp;';
				} else {
					$brochures_arr[$tmpcpt]['prefix'] = str_replace('.','',$brochures_arr[$tmpcpt]['prefix']);
				}
				$submenu=$brochures_arr[$tmpcpt]['menu'] ? _YES : _NO;
				$class = ($class == 'even') ? 'odd' : 'even';
				$output  = $output . "<tr class='".$class."'><td>" . $brochures_arr[$tmpcpt]['brochure_id'] . "</td><td align='left'>" . $brochures_arr[$tmpcpt]['prefix'] . $myts->displayTarea($brochures_arr[$tmpcpt]['brochure_title']) . "</td><td>" . $action . "</td></tr>";
			} else {
				$ok=false;
			}
			if($cpt>=5) {
				$ok=false;
			}
			$tmpcpt++;
			$cpt++;
		}
		echo $output;
	}
	$pagenav = new XoopsPageNav( $totalbrochures, 5, $start, 'start', 'op=brochuresmanager');
	echo "</table><div align='right'>".$pagenav->renderNav().'</div><br />';
	echo "</div></div><br />\n";

	$brochure_id = isset($_GET['brochure_id']) ? intval($_GET['brochure_id']) : 0;
	if($brochure_id>0) {
		$xtmod = new PageflipBrochure($brochure_id);
				//print_r($xtmod);
		$brochure_title=$xtmod->brochure_title('E');
		$brochure_description=$xtmod->brochure_description('E');
		$brochure_pages=$xtmod->brochure_pages();
		$op='modbrochures';
		if(xoops_trim($xtmod->brochure_imgurl())!='') {
			$brochureimage=$xtmod->brochure_imgurl();
		} else {
			$brochureimage='blank.png';
		}

		$brochure_pagewidth=$xtmod->brochure_pagewidth();
		$brochure_pageheight=$xtmod->brochure_pageheight();
		$brochure_addpars =$xtmod->brochure_addpars();
		$brochure_cropconfig =$xtmod->brochure_cropconfig();
		$brochure_pageprefix =$xtmod->brochure_pageprefix();
		$brochure_resolution =$xtmod->brochure_resolution();
		$brochure_orderid =$xtmod->brochure_orderid();
		$brochure_catid =$xtmod->brochure_pid();


		$btnlabel=_AM_MODIFY;
		$parent=$xtmod->brochure_pid();
		$formlabel=_AM_MODIFYBROCHURE;
		$submenu=$xtmod->menu();
		unset($xtmod);
	} else {
		$brochure_title='';
		$brochure_description='';
		$brochure_pages='';
		$op='addBrochure';
		$brochureimage='xoops.gif';
		$btnlabel=_AM_ADD;
		$parent=-1;
		$submenu=0;
		$formlabel=_AM_ADD_BROCHURE;
		$brochure_color='000000';
		$brochure_catid =1;

		$brochure_pagewidth=300;
		$brochure_pageheight=425;
		$brochure_addpars = '';
		$brochure_cropconfig = '';
		$brochure_pageprefix = '';
		$brochure_resolution = '72';
		$brochure_orderid = 0;
	}
	//echo "-1-<P>";
	$sform = new XoopsThemeForm($formlabel, 'brochureform', XOOPS_URL.'/modules/'.$xoopsModule->getVar('dirname').'/admin/index.php', 'post');
	$sform->setExtra('enctype="multipart/form-data"');
	$sform->addElement(new XoopsFormText(_AM_BROCHURENAME, 'brochure_title', 50, 255, $brochure_title), true);
	$editor=new XoopsFormTextArea(_AM_BROCHURE_DESCR,'brochure_description', $brochure_description, 15, 60, 'hometext_hidden');
	if($editor) {
		$sform->addElement($editor,false);
	}



//echo "brochure_catid:".$brochure_catid ."<P>";

	$catlist=new XoopsFormSelect("Category", 'cat_list',$brochure_catid,1,false);
	$xt = new PageflipCategory ();
	$allCats = $xt->getCategoriesList(false);				
	if(count($allCats )) {
		foreach ($allCats as $onecat) {
			//echo "POO-2:".$onecat["cat_id"]."<P>";
			//$catlist->addOption($onecat->cat_id(),$onecat->cat_title());
			$catlist->addOption($onecat["cat_id"] , $onecat["cat_title"]);
		}
	}

	$catlist->setDescription("Select a category");
	$sform->addElement($catlist, false);

	$sform->addElement(new XoopsFormText(_AM_BROCHUREORDERID, 'brochure_orderid', 5, 3, $brochure_orderid), true);
	$sform->addElement(new XoopsFormText(_AM_BROCHUREPAGES, 'brochure_pages', 5, 3, $brochure_pages), true);
	
	$sform->addElement(new XoopsFormHidden('op', $op), false);
	$sform->addElement(new XoopsFormHidden('brochure_id', $brochure_id), false);

		$sform->addElement(new XoopsFormText(_AM_BROCHURE_PAGEWIDTH, 'brochure_pagewidth', 50, 255, $brochure_pagewidth), true);
		$sform->addElement(new XoopsFormText(_AM_BROCHURE_PAGEHEIGHT, 'brochure_pageheight', 50, 255, $brochure_pageheight), true);

		$sform->addElement(new XoopsFormText(_AM_BROCHURE_ADD_PARS, 'brochure_addpars', 50, 255, $brochure_addpars), false);
		$sform->addElement(new XoopsFormText(_AM_BROCHURE_CROP_CONFIG, 'brochure_cropconfig', 50, 255, $brochure_cropconfig), false);		
		$sform->addElement(new XoopsFormText(_AM_BROCHURE_PAGE_PREFIX, 'brochure_pageprefix', 50, 255, $brochure_pageprefix), false);
		$sform->addElement(new XoopsFormText(_AM_BROCHURE_RESOLUTION, 'brochure_resolution', 50, 255, $brochure_resolution), false);	



	// ********** Picture
/*
	$imgtray = new XoopsFormElementTray(_AM_BROCHUREIMG,'<br />');

$cover_path='uploads/' . $xoopsModule -> dirname() . '/covers/';
	$imgpath=sprintf(_AM_IMGNAEXLOC, $cover_path );
	

    $imageselect= new XoopsFormSelect($imgpath, 'brochure_imgurl',$brochureimage);
    $brochures_array = XoopsLists :: getImgListAsArray( XOOPS_ROOT_PATH . '/uploads/' . $xoopsModule -> dirname().'/covers' );
    foreach( $brochures_array as $image ) {
        $imageselect->addOption("$image", $image);
    }
    $imageselect->setExtra( "onchange='showImgSelected(\"image3\", \"brochures_imgurl\", \"" . $uploadirectory . "\", \"\", \"" . XOOPS_URL . "\")'" );
    $imgtray->addElement($imageselect,false);
    $imgtray -> addElement( new XoopsFormLabel( '', "<br /><img src='" . XOOPS_URL . "/" . $uploadirectory . "/" . $brochureimage . "' name='image3' id='image3' alt='' />" ) );
    $uploadfolder=sprintf(_AM_UPLOAD_WARNING,XOOPS_URL . '/uploads/' . $xoopsModule -> dirname().'/covers');


    $fileseltray= new XoopsFormElementTray('','<br />');
    $fileseltray->addElement(new XoopsFormFile(_AM_BROCHURE_PICTURE , 'attachedfile', 9999999999), false);
	//echo "UPLOADF:".$uploadfolder ."<P>";
    $fileseltray->addElement(new XoopsFormLabel($uploadfolder ), false);
    $imgtray->addElement($fileseltray);
    $sform->addElement($imgtray);
*/




	// ********** Picture
	$imgtray = new XoopsFormElementTray(_AM_BROCHUREIMG,'<br />');
	$imgpath=sprintf(_AM_IMGNAEXLOC, 'uploads/' . $xoopsModule -> dirname() . '/covers/' );
	


    $imageselect= new XoopsFormSelect($imgpath, 'brochure_imgurl',$brochureimage);

    $imgtray -> addElement( new XoopsFormLabel( '', "<br /><img src='" . XOOPS_URL . "/" . $uploadirectory . "/" . $brochureimage . "' name='image3' id='image3' alt='' />" ) );
    $uploadfolder=sprintf(_AM_UPLOAD_WARNING,XOOPS_URL . '/uploads/' . $xoopsModule -> dirname().'/covers');




    $imgtray -> addElement( new XoopsFormLabel( '', "<br /><img src='" . XOOPS_URL . "/" . $uploadirectory . "/" . $topicimage . "' name='image3' id='image3' alt='' />" ) );
    $uploadfolder=sprintf(_AM_UPLOAD_WARNING,XOOPS_URL . '/modules/' . $xoopsModule -> dirname().'/covers');

    $fileseltray= new XoopsFormElementTray('','<br />');
    $fileseltray->addElement(new XoopsFormFile(_AM_BROCHURE_PICTURE , 'attachedfile', 999999999999), false);
    $fileseltray->addElement(new XoopsFormLabel($uploadfolder ), false);
    $imgtray->addElement($fileseltray);
    $sform->addElement($imgtray);





	// Permissions

    $member_handler = & xoops_gethandler('member');
    $group_list = &$member_handler->getGroupList();
    $gperm_handler = &xoops_gethandler('groupperm');
    $full_list = array_keys($group_list);


	$groups_ids = array();
    if($brochure_id > 0) {		// Edit mode
    	$groups_ids = $gperm_handler->getGroupIds('pageflip_view', $brochure_id, $xoopsModule->getVar('mid'));
    	$groups_ids = array_values($groups_ids);
    	$groups_pageflip_can_view_checkbox = new XoopsFormCheckBox(_AM_VIEWFORM, 'groups_pageflip_can_view[]', $groups_ids);
    } else {	// Creation mode
    	$groups_pageflip_can_view_checkbox = new XoopsFormCheckBox(_AM_VIEWFORM, 'groups_pageflip_can_view[]', $full_list);
    }
    $groups_pageflip_can_view_checkbox->addOptionArray($group_list);
    $sform->addElement($groups_pageflip_can_view_checkbox);
    
    

	// Submit buttons
	$button_tray = new XoopsFormElementTray('' ,'');
	$submit_btn = new XoopsFormButton('', 'post', $btnlabel, 'submit');
	$button_tray->addElement($submit_btn);
	$sform->addElement($button_tray);
	$sform->display();

}


// Save a $brochure after it has been modified
function modbrochures()
{
    global $xoopsDB, $xoopsModule, $xoopsModuleConfig;
    $xt = new PageflipBrochure(intval($_POST['brochure_id']));
    if (intval($_POST['brochure_pid']) == intval($_POST['brochure_id'])) {
        redirect_header( 'index.php?op=brochuresmanager', 2, _AM_ADD_BROCHURE_ERROR1 );
    }
    $xt->setBrochurePid(intval($_POST['cat_list']));
    if (empty($_POST['brochure_title'])) {
        redirect_header( 'index.php?op=brochuresmanager', 2, _AM_ERRORTOPICNAME );
    }
    if(isset($_SESSION['items_count'])) {
    	$_SESSION['items_count'] = -1;
    }

    $xt -> setBrochureTitle($_POST['brochure_title']);
    if (isset($_POST['brochure_imgurl']) && $_POST['brochure_imgurl']!= '') {
        $xt -> setBrochureImgurl($_POST['brochure_imgurl']);
    }

   	$xt->setMenu(intval($_POST['submenu']));
   	$xt->setBrochureFrontpage(intval($_POST['brochure_frontpage']));
   	if(isset($_POST['brochure_description'])) {
   		$xt->setBrochureDescription($_POST['brochure_description']);
   	} else {
   		$xt->setBrochureDescription('');
   	}
   	//$xt->Setbrochure_rssurl($_POST['brochure_rssfeed']);
   	$xt->setBrochure_color($_POST['brochure_color']);
   	$xt->setBrochure_pages($_POST['brochure_pages']);

   	$xt->setBrochure_pagewidth($_POST['brochure_pagewidth']);
   	$xt->setBrochure_pageheight($_POST['brochure_pageheight']);
   	$xt->setBrochure_addpars ($_POST['brochure_addpars']);
   	$xt->setBrochure_cropconfig ($_POST['brochure_cropconfig']);
   	$xt->setBrochure_pageprefix ($_POST['brochure_pageprefix']);
   	$xt->setBrochure_resolution ($_POST['brochure_resolution']);

   	$xt->setBrochureOrderID ($_POST['brochure_orderid']);  	





	if(isset($_POST['xoops_upload_file'])) {
		$fldname = $_FILES[$_POST['xoops_upload_file'][0]];
		$fldname = (get_magic_quotes_gpc()) ? stripslashes($fldname['name']) : $fldname['name'];

		if(xoops_trim($fldname!='')) {
			$sfiles = new sFiles();
			$dstpath = XOOPS_ROOT_PATH . '/uploads/' . $xoopsModule->dirname() . '/covers';
			$destname=$sfiles->createUploadName($dstpath ,$fldname, true,"binder_cover_uploads");
			$permittedtypes = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/x-png', 'image/png');
			$uploader = new XoopsMediaUploader($dstpath, $permittedtypes, 9999999999);
			$uploader->setTargetFileName($destname);


			if ($uploader->fetchMedia($_POST['xoops_upload_file'][0])) {


				if ($uploader->upload()) {
					$xt->setBrochureImgurl(basename($destname));
				} else {
					echo _AM_UPLOAD_ERROR . ' ' . $uploader->getErrors();
				}
			} else {
				echo $uploader->getErrors();
			}
		}
   	}









    $xt->store();

	// Permissions
	$gperm_handler = &xoops_gethandler('groupperm');
	$criteria = new CriteriaCompo();
	$criteria->add(new Criteria('gperm_itemid', $xt->brochure_id(), '='));
	$criteria->add(new Criteria('gperm_modid', $xoopsModule->getVar('mid'),'='));
	$criteria->add(new Criteria('gperm_name', 'pageflip_view', '='));
	$gperm_handler->deleteAll($criteria);
/*

	if(isset($_POST['groups_pageflip_can_view'])) {
		foreach($_POST['groups_pageflip_can_view'] as $onegroup_id) {
			$gperm_handler->addRight('pageflip_view', $xt->brochure_id(), $onegroup_id, $xoopsModule->getVar('mid'));
		}
	}
*/
    redirect_header( 'index.php?op=brochuresmanager', 1, _AM_DBUPDATED );
    exit();
}

// Delete a brochure_id and its subbrochures and its stories and the related stories
function delBrochure()
{
    global $xoopsDB, $xoopsModule;
	//echo "delBrochure-1-<P>";
    $brochureid = isset($_GET['brochure_id']) ? intval($_GET['brochure_id']) : 0;
    $xt = new PageflipBrochure(0);

        $xt->deleteBrochure($brochureid);

	unset($xt);

        redirect_header( 'index.php?op=brochuresmanager', 1, _AM_DBUPDATED );
        exit();
    
}

// Add a new brochure
function addBrochure()
{

	global $xoopsDB, $xoopsModule, $xoopsModuleConfig;
    $brochurepid = isset($_POST['cat_list']) ? intval($_POST['cat_list']) : 1;
    $xt = new PageflipBrochure(0);
    if (!$xt->brochureExists(pageflip, $_POST['brochure_title'])) {
        $xt->setBrochurePid($brochurepid);
        if (empty($_POST['brochure_title']) || xoops_trim($_POST['brochure_title'])=='') {
            redirect_header( 'index.php?op=brochuresmanager', 2, _AM_ERRORBROCHURENAME );
        }
        $xt->setBrochureTitle($_POST['brochure_title']);
        //$xt->Setbrochure_rssurl($_POST['brochure_rssfeed']);
        $xt->setBrochure_pages($_POST['brochure_pages']);
		$xt->setBrochure_color($_POST['brochure_color']);


   	$xt->setBrochure_pagewidth($_POST['brochure_pagewidth']);
   	$xt->setBrochure_pageheight($_POST['brochure_pageheight']);
   	$xt->setBrochure_addpars ($_POST['brochure_addpars']);
   	$xt->setBrochure_cropconfig ($_POST['brochure_cropconfig']);
   	$xt->setBrochure_pageprefix ($_POST['brochure_pageprefix']);
   	$xt->setBrochure_resolution ($_POST['brochure_resolution']);

   	$xt->setBrochureOrderID ($_POST['brochure_orderid']);
        if (isset($_POST['brochure_imgurl'] ) && $_POST['brochure_imgurl'] != '') {
            $xt->setBrochureImgurl($_POST['brochure_imgurl'] );
        }
		$xt->setMenu(intval($_POST['submenu']));

	    //if(isset($_SESSION['items_count'])) {
    	//	$_SESSION['items_count'] = -1;
    	//}




	if(isset($_POST['xoops_upload_file'])) {
		$fldname = $_FILES[$_POST['xoops_upload_file'][0]];
		$fldname = (get_magic_quotes_gpc()) ? stripslashes($fldname['name']) : $fldname['name'];

		if(xoops_trim($fldname!='')) {
			$sfiles = new sFiles();
			$dstpath = XOOPS_ROOT_PATH . '/uploads/' . $xoopsModule->dirname() . '/covers';
			$destname=$sfiles->createUploadName($dstpath ,$fldname, true,"binder_cover_uploads");
			$permittedtypes = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/x-png', 'image/png');
			$uploader = new XoopsMediaUploader($dstpath, $permittedtypes, 9999999999);
			$uploader->setTargetFileName($destname);

			if ($uploader->fetchMedia($_POST['xoops_upload_file'][0])) {


				if ($uploader->upload()) {
					$xt->setBrochureImgurl(basename($destname));
				} else {
					echo _AM_UPLOAD_ERROR . ' ' . $uploader->getErrors();
				}
			} else {
				echo $uploader->getErrors();
			}
		}
   	}



		if(isset($_POST['brochure_description'])) {
			$xt->setBrochureDescription($_POST['brochure_description']);
		} else {
			$xt->setBrochureDescription('');
		}
		$xt->setBrochureDescription('');
		$xt->store();
		// Permissions
		$gperm_handler = &xoops_gethandler('groupperm');
		if(isset($_POST['groups_pageflip_can_view'])) {
			foreach($_POST['groups_pageflip_can_view'] as $onegroup_id) {
				$gperm_handler->addRight('pageflip_view', $xt->brochure_id(), $onegroup_id, $xoopsModule->getVar('mid'));
			}
		}
        $notification_handler = & xoops_gethandler('notification');
        redirect_header('index.php?op=brochuresmanager', 1, _AM_DBUPDATED);
    } else {
        redirect_header('index.php?op=brochuresmanager', 2, _AM_ADD_BROCHURE_ERROR);
    }
    exit();
}

// **********************************************************************************************************************************************
// **** Main
// **********************************************************************************************************************************************
$op = 'default';
if(isset($_POST['op'])) {
 $op=$_POST['op'];
} elseif(isset($_GET['op'])) {
	$op=$_GET['op'];
}

if(isset($_POST['action'])) {
 $action=$_POST['action'];
} elseif(isset($_GET['action'])) {
	$action=$_GET['action'];
}

//echo "OP:".$op;   

switch ($op) {

    case 'catmanager':
	 if ($action=="delCategory"){
		delCategory();
	}
	else{
        catmanager();
	}
        break;

    case 'addCategory':
        addCategory();
        break;

    case 'modcategories':
        modcategories();
        break;
            
    case 'assign':
        assignpages();
        break;
        
    case 'upload':
        imageuploads();
        break;
        
    case 'convert':
        pdfconvert();
        break;

    case 'brochuresmanager':
        brochuresmanager();
        break;

    case 'addBrochure':
        addBrochure();
        break;

    case 'delBrochure':
        delBrochure();
        break;

    case 'modbrochures':
        modbrochures();
        break;

    case 'edit':
		if (file_exists(XOOPS_ROOT_PATH.'/modules/pageflip/language/'.$xoopsConfig['language'].'/main.php')) {
			include_once XOOPS_ROOT_PATH.'/modules/pageflip/language/'.$xoopsConfig['language'].'/main.php';
		} else {
			include_once XOOPS_ROOT_PATH.'/modules/pageflip/language/english/main.php';
		}
		include_once XOOPS_ROOT_PATH.'/modules/pageflip/submit.php';
		break;


    case 'verifydb':
    	xoops_cp_header();
    	adminmenu();
		$tbllist = $xoopsDB->prefix('stories').','.$xoopsDB->prefix('brochures').','.$xoopsDB->prefix('stories_files').','.$xoopsDB->prefix('stories_votedata');
		$xoopsDB->queryF("OPTIMIZE TABLE ".$tbllist);
		$xoopsDB->queryF("CHECK TABLE ".$tbllist);
		$xoopsDB->queryF("ANALYZE TABLE ".$tbllist);
		redirect_header( 'index.php', 3, _AM_DBUPDATED);
		exit;
    	break;

    case 'default':
    default:
        xoops_cp_header();
        adminmenu(0);

        echo '<h4>' . _AM_CONFIG . '</h4>';
        echo"<table width='100%' border='0' cellspacing='1' class='outer'><tr><td class=\"odd\">";
        echo " - <b><a href='index.php?op=catmanager'>" . _AM_CATMNGR . '</a></b>';
        echo "<br /><br />\n";

        echo " - <b><a href='index.php?op=brochuresmanager'>" . _AM_BROCHURESMNGR . '</a></b>';
        echo "<br /><br />\n";
        
        echo " - <b><a href='index.php?op=upload'>" . _MI_PAGEFLIP_UPLOAD . "</a></b>\n";
        echo "<br /><br />\n";

        echo " - <b><a href='index.php?op=convert'>" . _MI_PAGEFLIP_CONVERTPDF . "</a></b>\n";
        echo "<br /><br />\n";

        echo " - <b><a href='index.php?op=assign'>" . _MI_PAGEFLIPS_ASSIGN . "</a></b>\n";
        echo "<br /><br />\n";
        
                echo " - <b><a href='" . XOOPS_URL . '/modules/system/admin.php?fct=preferences&amp;op=showmod&amp;mod=' . $xoopsModule -> getVar( 'mid' ) . "'>" . _AM_GENERALCONF . "</a></b>";
        echo "<br /><br />\n";
                echo " - <b><a href='groupperms.php'>" . _AM_GROUPPERM . "</a></b>\n";
        echo "<br /><br />\n";
        echo"</td></tr></table>";
        break;
}

xoops_cp_footer();


    function addPathElement($path, $element)
    {
        return slashTerm($path) . $element;
    }
    

    function slashTerm($path)
    {
        if (XoopsFolderHandler::isSlashTerm($path)) {
            return $path;
        }
        return $path . XoopsFolderHandler::correctSlashFor($path);
     }



	function NewDOMDocument($dir, $pthOutput, $brochure_id){

	
		$doc = new DOMDocument();
		$doc->formatOutput = true;
		$first = true;
		//echo "dir:".$dir."<P>";
		
		//echo "brochure_id:".$brochure_id."<P>";
		$xtmod = new PageflipBrochure($brochure_id);
		$brochure_pages=$xtmod->brochure_pages();
		$pagesOBJ = new PageflipPages();
		$imagesarr=array();
		$imagesarr=$pagesOBJ->getAllpages($brochure_id); // All pages file names and page numbers for corresponding brochure from Table
		//echo "brochure_pages:".$xtmod->brochure_pages()."<P>";

		// LOOP For each page in Brochure (No of pages as defined in the brochure table)
		for ( $counter = 1; $counter <= $xtmod->brochure_pages(); $counter += 1) {
		//echo "loopz<P>";
		       if ($first) {

				$file = $dir."lowres/".$imagesarr[$counter]['page_image'];
		            $size = getimagesize($file);
//echo "SIZE:";print_r($size);echo "<P>";
//echo "FILE:".$file."<P>";
//echo "HEIGHT:".$xtmod->brochure_pageheight."<P>";
//echo "REALHEIGHT:".$size[1]."<P>";
		            $r = $doc->createElement( "book" );
		            $r->setAttribute("pagewidth", $xtmod->brochure_pagewidth);
		            $r->setAttribute("pageheight", $size[1]);
		            $r->setAttribute("bgcolor","cccccc");
		            $r->setAttribute("pageoffset","1");

		            $doc->appendChild( $r );
		            $chapter = $doc->createElement( "chapter" );
		            $r->appendChild($chapter); // Add chapter to root element
		            		            
		            $first = false;   
		        }
					//echo "_".$imagesarr[$counter]['page_image']."<P>";
					//echo "_".$xtmod->brochure_pageheight."<P>";
		        $page = $doc->createElement( "page" );
		        $img = $doc->createElement( "img" );
			 if (file_exists($dir."lowres/".$imagesarr[$counter]['page_image'])) {
		        	$img->setAttribute("src", "src/lowres/" . $imagesarr[$counter]['page_image']);
		        	$img->setAttribute("hires", "src/" . $imagesarr[$counter]['page_image']);	
			 }else{
		       	 $img->setAttribute("src", "src/" . $imagesarr[$counter]['page_image']);	
			 }

	          
		        $chapter->appendChild( $page );  // Add page to Chapter element
		        $page->appendChild( $img );  // Add page to Chapter element
		        // print_r($page);
		          
		 } 
		$pthOutput=$pthOutput."megazine/megazine.xml";
		//$pthOutput = str_replace("/", "\\", $pthOutput);
		//echo "pthOutput:".$pthOutput."<P>";
		$doc->save($pthOutput); 

	} 
	
         
?>