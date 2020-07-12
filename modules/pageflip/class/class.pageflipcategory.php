<?php

//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                       
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

class PageflipCategory 
{
	var $menu;
	var $table;
	var $category_id;
	var $category_title;
	var $category_description;
	var $category_imgurl;

	var $prefix; // only used in topic tree
	var $use_permission=true;
	var $mid; // module id used for setting permission

	function PageflipCategory($categoryid=0)
	{

		$this->db =& Database::getInstance();
		$this->table = $this->db->prefix("pageflip_categories");

		if ( is_array($categoryid) ) {
			$this->makeCategory($categoryid);
		} elseif ( $categoryid === "latest" ) {
			$this->getLastCategory();
		} elseif ( $categoryid != 0 ) {
			$this->getCatgegory(intval($categoryid));
		} else {
			$this->category_id = $categoryid;
		}
//echo "BROCH1:".$this->brochure_id;
	}




	function getChildTreeArray($sel_id=0, $order='', $perms='',$parray = array(), $r_prefix='')
	{
		$sql = "SELECT * FROM ".$this->table." WHERE (topic_pid=".$sel_id.")".$perms;
		if ( $order != "" ) {
			$sql .= " ORDER BY $order";
		}
		$result = $this->db->query($sql);
		$count = $this->db->getRowsNum($result);
		if ( $count == 0 ) {
			return $parray;
		}
		while ( $row = $this->db->fetchArray($result) ) {
			$row['prefix'] = $r_prefix.".";
			array_push($parray, $row);
			$parray = $this->getChildTreeArray($row['category_id'],$order,$perms,$parray,$row['prefix']);
		}
		return $parray;
	}
	
	
	function getVar($var) {
		if(method_exists($this, $var)) {
			return call_user_func(array($this,$var));
		} else {
	    	return $this->$var;
	    }
	}

	/**
 	* Get the total number of brochures in the base
 	*/
	function getAllCategoryCount($checkRight = true)
	{

		global $xoopsDB;
		$table = $xoopsDB->prefix('pageflip_categories');


	    $perms='';
	    if ($checkRight) {
	        global $xoopsUser;
	        $module_handler =& xoops_gethandler('module');
	        $pageflipModule=& $module_handler->getByDirname('pageflip');
	        $groups = is_object($xoopsUser) ? $xoopsUser->getGroups() : XOOPS_GROUP_ANONYMOUS;
	        $gperm_handler =& xoops_gethandler('groupperm');
	        $categories = $gperm_handler->getItemIds('pageflip_view', $groups, $pageflipModule->getVar('mid'));
	        if(count($brochures)>0) {
	        	$brochures = implode(',', $categories);

	        	$perms = " WHERE category_id IN (".$categories.") ";

	        } else {

	        	return null;
	        }
	    }

		$sql = "SELECT count(category_id) as cpt FROM ".$table.$perms;

		// echo $sql."<P>";
		$result = $xoopsDB->query($sql);

		$array  = $xoopsDB->fetchArray($result );

		return($array['cpt']);
	}



	function getAllCategories($checkRight = true, $permission = "pageflip_view")
	{


		global $xoopsUser;

		// CHECK IF USER IS WEBMASTER
		$in_group = is_object($xoopsUser) && in_array(1, $xoopsUser->getGroups());
		if ($in_group==1){
			$UserID = "";
		}else{
			$UserID = $xoopsUser->getVar('uid');
		}

	    $categgories_arr = array();
	    $db =& Database::getInstance();
	    $table = $db->prefix('pageflip_categories');
       	 $sql = "SELECT * FROM ".$table;
        	if ($checkRight) {
			$brochures = pageflip_MygetItemIds($permission);
			if (count($categories) == 0) {
				return array();
			}

			$categories = implode(',', $categories);
			$sql .= " WHERE category_id IN (".$categories.")";
		}else{
			$sql .= " WHERE (category_pid=0)";
		}
		if ( $UserID != "" ) {
			$sql .= " AND user_id =".$UserID;
		}

		$sql .= " ORDER BY category_title";
//echo "SQL:".$sql."<P>";
		$result = $db->query($sql);
		while ($array = $db->fetchArray($result)) {
			$category = new PageflipBrochure();
			$category->makeCategory($array);
			$categories_arr[$array['category_id']] = $category;
			unset($category);
		}
		return $categories_arr;
	}




	function setMenu($value)
	{
		$this->menu=$value;
	}

	function setCategory_title($value)
	{
		$this->category_title=$value;
	}

	function setCategory_description($value)
	{
		$this->category_description=$value;
	}

	function setCategory_img_url($value)
	{
		$this->category_img_url=$value;
	}
	
	
	function makeCatgegory($array)
	{
		if(is_array($array)) {
			foreach($array as $key=>$value){
//echo "KEY:".$key." VAL:".$value."<P>";
				$this->$key = $value;
			}
		}
	}

	function store()
	{
		global $xoopsUser;
		$myts =& MyTextSanitizer::getInstance();
		$title = "";
		$imgurl = "";

		$category_description=$this->category_description;

		if ( isset($this->category_title) && $this->category_title != "" ) {
			$title = $myts->addSlashes($this->category_title);
		}
		if ( isset($this->category_imgurl) && $this->category_imgurl != "" ) {
			$imgurl = $myts->addSlashes($this->category_imgurl);
		}
		if ( !isset($this->category_pid) || !is_numeric($this->category_pid) ) {
			$this->category_pid = 0;
		}
		if (is_object($xoopsUser)) {
			$user_id = $xoopsUser->getVar('uid');
		}
		$insert=false;
		if ( empty($this->category_id) ) {

			//echo "BID2:".$this->brochure_id."<P>";
			$insert=true;
			$this->category_id = $this->db->genId($this->table."_category_id_seq");
			$sql = sprintf("INSERT INTO %s (category_id, category_pid, category_title, category_description, category_imgurl) VALUES (%u, %u,'%s', '%s', '%s')", $this->table, intval($this->category_id), intval($this->category_pid), $title, $category_description);
		} else {
//echo "STORE-2<P>";
			//echo "BID3:".$this->brochure_id."<P>";
			$sql = sprintf("UPDATE %s SET category_pid = %u, category_title = '%s', category_description='%s', category_imgurl = '%s' WHERE category_id = %u", $this->table, intval($this->category_pid), $title, $category_description, $imgurl, intval($this->brochure_id));
		}

		//echo "SQLY:".$sql."<P>";

		if ( !$result = $this->db->query($sql) ) {
			// TODO: Replace with something else

			ErrorHandler::show('0022');
		} else {
			if($insert) {
				$this->brochure_id = $this->db->getInsertId();
			}
		}

		if ( $this->use_permission == true ) {
			$xt = new XoopsTree($this->table, "brochure_id", "brochure_pid");
			$parent_brochures = $xt->getAllParentId($this->brochure_id);
			if ( !empty($this->m_groups) && is_array($this->m_groups) ){
				foreach ( $this->m_groups as $m_g ) {
					$moderate_brochures = XoopsPerms::getPermitted($this->mid, "ModInBrochure", $m_g);
					$add = true;
					// only grant this permission when the group has this permission in all parent brochures of the created brochure
					foreach($parent_brochures as $p_brochure){
						if ( !in_array($p_brochure, $moderate_brochures) ) {
							$add = false;
							continue;
						}
					}
					if ( $add == true ) {
						$xp = new XoopsPerms();
						$xp->setModuleId($this->mid);
						$xp->setName("ModInBrochure");
						$xp->setItemId($this->brochure_id);
						$xp->store();
						$xp->addGroup($m_g);
					}
				}
			}
			if ( !empty($this->s_groups) && is_array($this->s_groups) ){
				foreach ($this->s_groups as $s_g ) {
					$submit_brochures = XoopsPerms::getPermitted($this->mid, "SubmitInBrochure", $s_g);
					$add = true;
					foreach($parent_brochures as $p_brochure){
						if ( !in_array($p_brochure, $submit_brochures) ) {
							$add = false;
							continue;
						}
					}
					if ( $add == true ) {
						$xp = new XoopsPerms();
						$xp->setModuleId($this->mid);
						$xp->setName("SubmitInBrochure");
						$xp->setItemId($this->brochure_id);
						$xp->store();
						$xp->addGroup($s_g);
					}
				}
			}
			if ( !empty($this->r_groups) && is_array($this->r_groups) ){
				foreach ( $this->s_groups as $r_g ) {
					$read_brochures = XoopsPerms::getPermitted($this->mid, "ReadInBrochure", $r_g);
					$add = true;
					foreach($parent_brochures as $p_brochure){
						if ( !in_array($p_brochure, $read_brochures) ) {
							$add = false;
							continue;
						}
					}
					if ( $add == true ) {
						$xp = new XoopsPerms();
						$xp->setModuleId($this->mid);
						$xp->setName("ReadInBrochure");
						$xp->setItemId($this->brochure_id);
						$xp->store();
						$xp->addGroup($r_g);
					}
				}
			}
		}
		return true;
	}



	function menu()
	{
		return $this->menu;
	}

	function category_description($format="S")
	{
		$myts =& MyTextSanitizer::getInstance();
		switch($format){
			case "S":
				$category_description = $myts->displayTarea($this->category_description,1);
				break;
			case "P":
				$category_description = $myts->previewTarea($this->category_description);
				break;
			case "F":
			case "E":
				$category_description = $myts->htmlSpecialChars($myts->stripSlashesGPC($this->category_description));
				break;
		}
		return $category_description;
	}

	function category_imgurl($format="S")
	{
		if(trim($this->category_imgurl)=='') {
			$this->category_imgurl='blank.png';
		}
		$myts =& MyTextSanitizer::getInstance();
		switch($format){
			case "S":
				$imgurl= $myts->htmlSpecialChars($this->category_imgurl);
				break;
			case "E":
				$imgurl = $myts->htmlSpecialChars($this->category_imgurl);
				break;
			case "P":
				$imgurl = $myts->stripSlashesGPC($this->category_imgurl);
				$imgurl = $myts->htmlSpecialChars($imgurl);
				break;
			case "F":
				$imgurl = $myts->stripSlashesGPC($this->category_imgurl);
				$imgurl = $myts->htmlSpecialChars($imgurl);
				break;
		}
		return $imgurl;
	}



	function &getCategoriesList($frontpage=false,$perms=false)
	{
		$sql='SELECT category_id, category_pid, category_title FROM '.$this->table." WHERE 1 ";

		if($perms) {
			$categoryids=array();
			$categoryids=categories_MygetItemIds();
            if (count($categoryids) == 0) {
            	return '';
            }
            $$categories = implode(',', $categoryids);
            $sql .= " AND brochure_id IN (".$$categories.")";
		}
		$result = $this->db->query($sql);
		$ret = array();
		$myts =& MyTextSanitizer::getInstance();
		while ($myrow = $this->db->fetchArray($result)) {
			$ret[$myrow['category_id']] = array('title' => $myts->displayTarea($myrow['category_title']), 'pid' => $myrow['category_pid']);
		}
		return $ret;
	}




	function setCategoryTitle($value)
	{
		$this->category_title = $value;
	}

	function setCategoryImgurl($value)
	{
		$this->category_imgurl = $value;
	}

	function setCategoryPid($value)
	{
		$this->category_pid = $value;
	}
	function setCategoryOrderID($value)
	{
		$this->category_orderid = $value;
	}

	function getBrochure($brochureid)
	{
		$categoryid = intval($categoryid);
		$sql = "SELECT * FROM ".$this->table." WHERE category_id=".$categoryid."";
		$array = $this->db->fetchArray($this->db->query($sql));
		$this->makeCategory($array);
	}


	function getLastBrochure()
	{
		$categoryid = intval($categoryid);
		$sql = "SELECT * FROM ".$this->table;
		$sql .= ' ORDER BY DateCreated DESC';
		$sql .= ' LIMIT 1';
//echo "LATEST:".$sql."<P>";
		$array = $this->db->fetchArray($this->db->query($sql));
		$this->makeBrochure($array);
	}



	function usePermission($mid)
	{
		$this->mid = $mid;
		$this->use_permission = true;
	}


	function setCategoryDescription($value)
	{
		$this->category_description = $value;
	}


	
	
	function categoryExists($pid, $title) {
		//echo $this->table."<P>";
		$sql = "SELECT COUNT(*) from ".$this->table." WHERE category_pid = ".intval($pid)." AND category_title = '".trim($title)."'";
		$rs = $this->db->query($sql);
        list($count) = $this->db->fetchRow($rs);
		if ($count > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	function category_id()
	{
		return $this->category_id;
	}

	function category_pid()
	{
		return $this->category_pid;
	}


	function category_title($format="S")
	{
		$myts =& MyTextSanitizer::getInstance();
		switch($format){
			case "S":
			case "E":
				$title = $myts->htmlSpecialChars($this->category_title);
				break;
			case "P":
			case "F":
				$title = $myts->htmlSpecialChars($myts->stripSlashesGPC($this->category_title));
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