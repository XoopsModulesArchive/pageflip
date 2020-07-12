<?php

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
	die("XOOPS root path not defined");
}

include_once XOOPS_ROOT_PATH."/class/xoopstree.php";
include_once XOOPS_ROOT_PATH."/class/module.errorhandler.php";
include_once XOOPS_ROOT_PATH."/modules/pageflip/include/functions.php";

class PageflipPages 
{
	var $menu;
	var $table;
	var $page_id;
	var $brochure_id;
	var $page_title;
	var $page_description;
	var $page_imgurl;
	var $page_sound;
	var $page_views;	
	var $page_number;
	var $prefix; // only used in topic tree
	var $use_permission=false;
	var $mid; // module id used for setting permission

	function PageflipPages($pageid=0)
	{
		$this->db =& Database::getInstance();
		$this->table = $this->db->prefix("pageflip_pages");
		if ( is_array($pageid) ) {
			$this->makepage($pageid);
		} elseif ( $pageid != 0 ) {
			$this->getpage(intval($pageid));
		} else {
			$this->page_id = $pageid;
		}
	}
	
	function getVar($var) {
		if(method_exists($this, $var)) {
			return call_user_func(array($this,$var));
		} else {
	    	return $this->$var;
	    }
	}
	function getAllpages( $brochure_id=0)
	{
	    $pages_arr = array();
	    $db =& Database::getInstance();
	    $table = $db->prefix('pageflip_pages');
        $sql = "SELECT * FROM ".$table;
        if ($brochure_id!=0) {
			$sql .= " WHERE brochure_id = ".$brochure_id."";
		}
		$sql .= " ORDER BY page_number";
		$result = $db->query($sql);
		while ($row = $this->db->fetchArray($result)) {
			$ret[$row["page_number"]]=array("page_image"=>($row["page_image"]), "page_number"=>($row["page_number"]));
		}

	
		return $ret;
	}



	function setMenu($value)
	{
		$this->menu=$value;
	}

	function setpage_color($value)
	{
		$this->page_color=$value;
	}

	function setpage_pages($value)
	{
		$this->page_pages=$value;
	}

	function makepage($array)
	{
		if(is_array($array)) {
			foreach($array as $key=>$value){
				$this->$key = $value;
			}
		}
	}

	function store()
	{
		$myts =& MyTextSanitizer::getInstance();
		$title = "";
		$imgurl = "";
		$page_pages = $this->page_pages;
		$page_description=$myts->censorString($this->page_description);
		$page_description= $myts->addSlashes($page_description);
		$brochure_id=$myts->$this->brochure_id;
		$page_color=$myts->addSlashes($this->page_color);
		$page_sound=$myts->addSlashes($this->page_sound);
		$page_views=$myts->$this->views;
		$page_pages=$myts->$this->pages;
		

		if ( isset($this->page_title) && $this->page_title != "" ) {
			$title = $myts->addSlashes($this->page_title);
		}
		if ( isset($this->page_imgurl) && $this->page_imgurl != "" ) {
			$imgurl = $myts->addSlashes($this->page_imgurl);
		}
		//echo "page_pages1:".$this->page_pages."<P>";
		
		if ( isset($this->page_pages) && $this->page_pages != "" ) {
			$page_pages = $this->page_pages;
		}


		$insert=false;
		if ( empty($this->page_id) ) {
			$insert=true;
			$this->page_id = $this->db->genId($this->table."_page_id_seq");
			$sql = sprintf("INSERT INTO %s (page_id, brochure_id, page_number, page_imgurl, page_title, page_description, page_sound, page_views) VALUES (%u, %u, %u, '%s', '%s', '%s', '%s','%u')", $this->table, intval($this->page_id), intval($this->brochure_id), intval($this->page_number), $imgurl, $title, $page_description, $page_sound, $page_views);
		} else {
			$sql = sprintf("UPDATE %s SET brochure_id = %u, page_number = %u, page_imgurl = '%s', page_title = '%s', page_description='%s', page_sound='%s', page_views='%s' WHERE page_id = %u", $this->table, intval($this->brochure_id), intval($this->page_number), $imgurl, $title, $page_description,$page_sound,$page_views, intval($this->page_id));
		}
		if ( !$result = $this->db->query($sql) ) {
			// TODO: Replace with something else
			ErrorHandler::show('0022');
		} else {
			if($insert) {
				$this->page_id = $this->db->getInsertId();
			}
		}

		return true;
	}


	function DeleteAllByBrochureID($brochure_id){
		
		if ($brochure_id>0){
			$sql="DELETE FROM ".$this->table." WHERE brochure_id=".$brochure_id.";";
			$result = $this->db->query($sql) ;
		}
		
	}

	function brochure_id()
	{
		return $this->brochure_id;
	}

	function page_number()
	{
		return $this->page_number;
	}
	function page_sound()
	{
		return $this->page_sound;
	}
	function page_views()
	{
		return $this->page_views;
	}



	function page_description($format="S")
	{
		$myts =& MyTextSanitizer::getInstance();
		switch($format){
			case "S":
				$page_description = $myts->displayTarea($this->page_description,1);
				break;
			case "P":
				$page_description = $myts->previewTarea($this->page_description);
				break;
			case "F":
			case "E":
				$page_description = $myts->htmlSpecialChars($myts->stripSlashesGPC($this->page_description));
				break;
		}
		return $page_description;
	}

	function page_imgurl($format="S")
	{
		if(trim($this->page_imgurl)=='') {
			$this->page_imgurl='blank.png';
		}
		$myts =& MyTextSanitizer::getInstance();
		switch($format){
			case "S":
				$imgurl= $myts->htmlSpecialChars($this->page_imgurl);
				break;
			case "E":
				$imgurl = $myts->htmlSpecialChars($this->page_imgurl);
				break;
			case "P":
				$imgurl = $myts->stripSlashesGPC($this->page_imgurl);
				$imgurl = $myts->htmlSpecialChars($imgurl);
				break;
			case "F":
				$imgurl = $myts->stripSlashesGPC($this->page_imgurl);
				$imgurl = $myts->htmlSpecialChars($imgurl);
				break;
		}
		return $imgurl;
	}


	
	function getpageTitleFromId($page,&$pagestitles)
	{
		$myts =& MyTextSanitizer::getInstance();
		$sql="SELECT page_id, page_title, page_imgurl FROM ".$this->table." WHERE ";
	    if (!is_array($page)) {
        	$sql .= " page_id=".intval($page);
	    } else {
	    	if(count($page)>0) {
	        	$sql .= " page_id IN (".implode(',', $page).")";
	    	} else {
	    		return null;
	    	}
	    }
	    $result = $this->db->query($sql);
		while ($row = $this->db->fetchArray($result)) {
			$pagestitles[$row["page_id"]]=array("title"=>$myts->displayTarea($row["page_title"]),"picture"=>XOOPS_URL.'/modules/pageflip/images/pages/'.$row["page_imgurl"]);
		}
		return $pagestitles;
	}


	function &getpagesList($frontpage=false,$perms=false)
	{
		$sql='SELECT page_id, page_pid, page_title, page_color FROM '.$this->table." WHERE 1 ";
		if($frontpage) {
			$sql .= " AND page_frontpage=1";
		}
		if($perms) {
			$pagesids=array();
			$pagesids=pages_MygetItemIds();
            if (count($pagesids) == 0) {
            	return '';
            }
            $pages = implode(',', $pagesids);
            $sql .= " AND page_id IN (".$pages.")";
		}
		$result = $this->db->query($sql);
		$ret = array();
		$myts =& MyTextSanitizer::getInstance();
		while ($myrow = $this->db->fetchArray($result)) {
			$ret[$myrow['page_id']] = array('title' => $myts->displayTarea($myrow['page_title']), 'pid' => $myrow['page_pid'], 'color'=> $myrow['page_color']);
		}
		return $ret;
	}




	function setpageTitle($value)
	{
		$this->page_title = $value;
	}

	function setbrochure_id($value)
	{
		$this->brochure_id = $value;
	}
	function setpage_number($value)
	{
		$this->page_number = $value;
	}

	function setpage_sound($value)
	{
		$this->page_sound = $value;
	}
	function setpage_views($value)
	{
		$this->page_views = $value;
	}
	
	function setpageImgurl($value)
	{
		$this->page_imgurl = $value;
	}


	function getpage($pageid)
	{
		$pageid = intval($pageid);
		$sql = "SELECT * FROM ".$this->table." WHERE page_id=".$pageid."";
		$array = $this->db->fetchArray($this->db->query($sql));
		$this->makepage($array);
	}



	function usePermission($mid)
	{
		$this->mid = $mid;
		$this->use_permission = true;
	}



	function setpageDescription($value)
	{
		$this->page_description = $value;
	}

	function page_frontpage()
	{
		return $this->page_frontpage;
	}

	function setpageFrontpage($value)
	{
		$this->page_frontpage=intval($value);
	}
	
	
	function pageExists($pid, $title) {
		//echo $this->table."<P>";
		$sql = "SELECT COUNT(*) from ".$this->table." WHERE  page_title = '".trim($title)."'";
		$rs = $this->db->query($sql);
        list($count) = $this->db->fetchRow($rs);
		if ($count > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	function page_id()
	{
		return $this->page_id;
	}



	function page_title($format="S")
	{
		$myts =& MyTextSanitizer::getInstance();
		switch($format){
			case "S":
			case "E":
				$title = $myts->htmlSpecialChars($this->page_title);
				break;
			case "P":
			case "F":
				$title = $myts->htmlSpecialChars($myts->stripSlashesGPC($this->page_title));
				break;
		}
		return $title;
	}



	function prefix()
	{
		if ( isset($this->prefix) ) {
			return $this->prefix;
		}
	}



	
	
}
?>