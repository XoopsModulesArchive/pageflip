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

class PageflipBrochure 
{
	var $menu;
	var $brochure_description;
	var $table;
	var $brochure_id;
	var $brochure_pid;
	var $brochure_orderid;
	var $brochure_title;
	var $brochure_imgurl;
	var $brochure_pages;

	var $brochure_pagewidth;
	var $brochure_pageheight;
	var $brochure_addpars;
	var $brochure_cropconfig;
	var $brochure_pageprefix;
	var $brochure_resolution;

	var $prefix; // only used in topic tree
	var $use_permission=false;
	var $mid; // module id used for setting permission

	function PageflipBrochure($brochureid=0, $cat_id=0)
	{
//echo "BROCH0:".$brochureid;
		$this->db =& Database::getInstance();
		$this->table = $this->db->prefix("pageflip_brochures");

		if ( is_array($brochureid) ) {
			$this->makeBrochure($brochureid);
		} elseif ( $brochureid === "latest" ) {
			$this->getLastBrochure($cat_id);
		} elseif ( $brochureid != 0 ) {
			$this->getBrochure(intval($brochureid));
		} else {
			$this->brochure_id = $brochureid;
		}
//echo "BROCH1:".$this->brochure_id;
	}


	/**
 	* makes a nicely ordered selection box
 	*
 	* @param	int	$preset_id is used to specify a preselected item
 	* @param	int	$none set $none to 1 to add a option with value 0
 	*/
	function makeMySelBox($title,$order="",$preset_id=0, $none=0, $sel_name="brochure_id", $onchange="", $perms)
	{
		$myts =& MyTextSanitizer::getInstance();
		$outbuffer='';
		$outbuffer = "<select name='".$sel_name."'";
		if ( $onchange != "" ) {
			$outbuffer .= " onchange='".$onchange."'";
		}
		$outbuffer .= ">\n";
		$sql = "SELECT brochure_id, ".$title." FROM ".$this->table." WHERE (brochure_pid=0)".$perms;
		if ( $order != "" ) {
			$sql .= " ORDER BY $order";
		}
		$result = $this->db->query($sql);
		if ( $none ) {
			$outbuffer .= "<option value='0'>----</option>\n";
		}
		while ( list($catid, $name) = $this->db->fetchRow($result) ) {
			$sel = "";
			if ( $catid == $preset_id ) {
				$sel = " selected='selected'";
			}
			$name=$myts->displayTarea($name);
			$outbuffer .= "<option value='$catid'$sel>$name</option>\n";
			$sel = "";
			$arr = $this->getChildTreeArray($catid, $order, $perms);
			foreach ( $arr as $option ) {
				$option['prefix'] = str_replace(".","--",$option['prefix']);
				$catpath = $option['prefix']."&nbsp;".$myts->displayTarea($option[$title]);

				if ( $option['brochure_id'] == $preset_id ) {
					$sel = " selected='selected'";
				}
				$outbuffer .= "<option value='".$option['brochure_id']."'$sel>$catpath</option>\n";
				$sel = "";
			}
		}
		$outbuffer .= "</select>\n";
		return $outbuffer;
	}



	function getChildTreeArray($sel_id=0, $order='brochure_id', $perms='',$parray = array(), $r_prefix='',$cat_id=0)
	{
		global $xoopsUser;

		// CHECK IF USER IS WEBMASTER
		$parray=array();

//echo "getChildTreeArray-5-<P>";
		$sql = "SELECT * FROM ".$this->table." WHERE (brochure_pid>-1)".$perms;

		if ( $UserID != "" ) {
			$sql .= " AND user_id =".$UserID;
		}
		if ( $cat_id >0 ) {
			$sql .= " AND brochure_pid =".$cat_id;
		}
		if ( $order != "" ) {
			$sql .= " ORDER BY $order DESC";
		}

//echo "SQL:".$sql."<P>";
		$result = $this->db->query($sql);

		$count = $this->db->getRowsNum($result);


		if ( $count == 0 ) {
			//echo "getChildTreeArray-0-<P>";
			return $parray;
		}
//echo "getChildTreeArray-1-<P>";
		while ( $row = $this->db->fetchArray($result) ) {

			$row['prefix'] = $r_prefix.".";
			array_push($parray, $row);
			//$parray = $this->getChildTreeArray($row['brochure_id'],$order,$perms,$parray,$row['prefix']);	
			//$parray = $this->getChildTreeArray($row['brochure_id'],$order,$perms,$parray,$row['prefix']);
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
	function getAllBrochureCount($checkRight = true, $cat_id)
	{

		global $xoopsDB;
		$table = $xoopsDB->prefix('pageflip_brochures');


	    $perms='';
	    if ($checkRight) {
	        global $xoopsUser;
	        $module_handler =& xoops_gethandler('module');
	        $pageflipModule=& $module_handler->getByDirname('pageflip');
	        $groups = is_object($xoopsUser) ? $xoopsUser->getGroups() : XOOPS_GROUP_ANONYMOUS;
	        $gperm_handler =& xoops_gethandler('groupperm');
	        $brochures = $gperm_handler->getItemIds('pageflip_view', $groups, $pageflipModule->getVar('mid'));
	        if(count($brochures)>0) {
	        	$brochures = implode(',', $brochures);

	        	$perms = " WHERE brochure_id IN (".$brochures.") ";

	        } else {

	        	return null;
	        }
	    }

		$sql = "SELECT count(brochure_id) as cpt FROM ".$table.$perms;

		if ( $cat_id >0 ) {
			$sql .= " AND brochure_pid =".$cat_id;
		}
		// echo "getAllBrochureCount-SQL:".$sql."<P>";
		$result = $xoopsDB->query($sql);

		$array  = $xoopsDB->fetchArray($result );

		return($array['cpt']);
	}



	function getAllBrochures($checkRight = true, $permission = "pageflip_view")
	{
		global $xoopsUser;

		// CHECK IF USER IS WEBMASTER
		$in_group = is_object($xoopsUser) && in_array(1, $xoopsUser->getGroups());
		if ($in_group==1){
			$UserID = "";
		}else{
			$UserID = $xoopsUser->getVar('uid');
		}

	    $brochures_arr = array();
	    $db =& Database::getInstance();
	    $table = $db->prefix('pageflip_brochures');
       	 $sql = "SELECT * FROM ".$table;
        	if ($checkRight) {
			$brochures = pageflip_MygetItemIds($permission);
			if (count($brochures) == 0) {
				return array();
			}

			$brochures = implode(',', $brochures);
			$sql .= " WHERE brochure_id IN (".$brochures.")";
		}else{
			$sql .= " WHERE (brochure_pid>0)";
		}
		if ( $UserID != "" ) {
			$sql .= " AND user_id =".$UserID;
		}

		$sql .= " ORDER BY brochure_title";
//echo "SQL:".$sql."<P>";
		$result = $db->query($sql);
		while ($array = $db->fetchArray($result)) {
			$brochure = new PageflipBrochure();
			$brochure->makeBrochure($array);
			$brochures_arr[$array['brochure_id']] = $brochure;
			unset($brochure);
//print_r($brochures_arr);
		}
		return $brochures_arr;
	}


	/**
	* Returns the number of published pageflips per brochure
	*/
	function getPageflipCountByBrochure()
	{
		$ret=array();
		$sql="SELECT count(storyid) as cpt, brochureid FROM ".$this->db->prefix('stories')." WHERE (published > 0 AND published <= ".time().") AND (expired = 0 OR expired > ".time().") GROUP BY brochureid";
		$result = $this->db->query($sql);
		while ($row = $this->db->fetchArray($result)) {
			$ret[$row["brochureid"]]=$row["cpt"];
		}
		return $ret;
	}

	/**
	* Returns some stats about a brochure
	*/
	function getBrochureMiniStats($brochureid)
	{
		$ret=array();
		$sql="SELECT count(storyid) as cpt1, sum(counter) as cpt2 FROM ".$this->db->prefix('stories')." WHERE (brochureid=" . $brochureid.") AND (published>0 AND published <= ".time().") AND (expired = 0 OR expired > ".time().")";
		$result = $this->db->query($sql);
		$row = $this->db->fetchArray($result);
		$ret['count']=$row["cpt1"];
		$ret['reads']=$row["cpt2"];
		return $ret;
	}


	function setMenu($value)
	{
		$this->menu=$value;
	}

	function setBrochure_color($value)
	{
		$this->brochure_color=$value;
	}

	function setBrochure_pages($value)
	{
		$this->brochure_pages=$value;
	}

	function setBrochure_pagewidth($value)
	{
		$this->brochure_pagewidth=$value;
	}


	function setBrochure_pageheight($value)
	{
		$this->brochure_pageheight=$value;
	}

	function setBrochure_addpars ($value)
	{
		$this->brochure_addpars =$value;
	}


	function setBrochure_cropconfig ($value)
	{
		$this->brochure_cropconfig =$value;
	}

	function setBrochure_pageprefix ($value)
	{
		$this->brochure_pageprefix =$value;
	}

	function setBrochure_resolution ($value)
	{
		$this->brochure_resolution = $value;
	}

	function makeBrochure($array)
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
//echo "STORE-0<P>";
		$myts =& MyTextSanitizer::getInstance();
		$title = "";
		$imgurl = "";
		$brochure_pages = $this->brochure_pages;
		$brochure_description=$myts->censorString($this->brochure_description);
		$brochure_description= $myts->addSlashes($brochure_description);
		$brochure_color=$myts->addSlashes($this->brochure_color);

		$brochure_pagewidth=$this->brochure_pagewidth;
		$brochure_pageheight=$this->brochure_pageheight;
		$brochure_addpars=$this->brochure_addpars;
		$brochure_cropconfig=$this->brochure_cropconfig;
		$brochure_pageprefix=$this->brochure_pageprefix;
		$brochure_resolution=$this->brochure_resolution;
		$brochure_orderid=$this->brochure_orderid;


		if ( isset($this->brochure_title) && $this->brochure_title != "" ) {
			$title = $myts->addSlashes($this->brochure_title);
		}
		if ( isset($this->brochure_imgurl) && $this->brochure_imgurl != "" ) {
			$imgurl = $myts->addSlashes($this->brochure_imgurl);
		}
		//echo "brochure_pages1:".$this->brochure_pages."<P>";
		
		if ( isset($this->brochure_pages) && $this->brochure_pages != "" ) {
			$brochure_pages = $this->brochure_pages;
		}
		if ( !isset($this->brochure_pid) || !is_numeric($this->brochure_pid) ) {
			$this->brochure_pid = 0;
		}
		if (is_object($xoopsUser)) {
			$user_id = $xoopsUser->getVar('uid');
		}
		$insert=false;
		if ( empty($this->brochure_id) ) {
//echo "STORE-1<P>";
			//echo "BID2:".$this->brochure_id."<P>";
			$insert=true;
			$this->brochure_id = $this->db->genId($this->table."_brochure_id_seq");
			$sql = sprintf("INSERT INTO %s (brochure_id, brochure_pid, user_id, brochure_orderid, brochure_imgurl, brochure_title, brochure_description, brochure_color, brochure_pages, brochure_pagewidth, brochure_pageheight, brochure_addpars, brochure_cropconfig, brochure_pageprefix, brochure_resolution) VALUES (%u, %u, %u, %u, '%s', '%s', '%s', '%d',%u,%u,%u, '%s', '%s', '%s', %u)", $this->table, intval($this->brochure_id), intval($this->brochure_pid), $user_id, $brochure_orderid, $imgurl, $title, $brochure_description, $brochure_color, $brochure_pages, $brochure_pagewidth, $brochure_pageheight, $brochure_addpars, $brochure_cropconfig, $brochure_pageprefix, $brochure_resolution);
		} else {
//echo "STORE-2<P>";
			//echo "BID3:".$this->brochure_id."<P>";
			$sql = sprintf("UPDATE %s SET brochure_pid = %u, brochure_orderid=%u, brochure_imgurl = '%s', brochure_title = '%s', brochure_description='%s', brochure_color='%s', brochure_pages=%u, brochure_pagewidth=%u, brochure_pageheight=%u, brochure_addpars='%s', brochure_cropconfig='%s', brochure_pageprefix='%s', brochure_resolution=%u WHERE brochure_id = %u", $this->table, intval($this->brochure_pid), $brochure_orderid, $imgurl, $title, $brochure_description,$brochure_color,$brochure_pages, $brochure_pagewidth, $brochure_pageheight, $brochure_addpars, $brochure_cropconfig, $brochure_pageprefix, $brochure_resolution, intval($this->brochure_id));
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


	function brochure_color($format='S')
	{
		$myts =& MyTextSanitizer::getInstance();
		switch($format){
			case "S":
				$brochure_color= $myts->displayTarea($this->brochure_color);
				break;
			case "P":
				$brochure_color = $myts->previewTarea($this->brochure_color);
				break;
			case "F":
			case "E":
				$brochure_color = $myts->htmlSpecialChars($this->brochure_color);
				break;
		}
		return $brochure_color;
	}

	function menu()
	{
		return $this->menu;
	}

	function brochure_description($format="S")
	{
		$myts =& MyTextSanitizer::getInstance();
		switch($format){
			case "S":
				$brochure_description = $myts->displayTarea($this->brochure_description,1);
				break;
			case "P":
				$brochure_description = $myts->previewTarea($this->brochure_description);
				break;
			case "F":
			case "E":
				$brochure_description = $myts->htmlSpecialChars($myts->stripSlashesGPC($this->brochure_description));
				break;
		}
		return $brochure_description;
	}

	function brochure_imgurl($format="S")
	{
		if(trim($this->brochure_imgurl)=='') {
			$this->brochure_imgurl='blank.png';
		}
		$myts =& MyTextSanitizer::getInstance();
		switch($format){
			case "S":
				$imgurl= $myts->htmlSpecialChars($this->brochure_imgurl);
				break;
			case "E":
				$imgurl = $myts->htmlSpecialChars($this->brochure_imgurl);
				break;
			case "P":
				$imgurl = $myts->stripSlashesGPC($this->brochure_imgurl);
				$imgurl = $myts->htmlSpecialChars($imgurl);
				break;
			case "F":
				$imgurl = $myts->stripSlashesGPC($this->brochure_imgurl);
				$imgurl = $myts->htmlSpecialChars($imgurl);
				break;
		}
		return $imgurl;
	}

	function brochure_pages($format)
	{
		$brochure_pages=intval($this->brochure_pages);
		return $brochure_pages;
	}

	function brochure_orderid($format)
	{
		$brochure_orderid=intval($this->brochure_orderid);
		return $brochure_orderid;
	}

	function brochure_pagewidth()
	{
		$brochure_pagewidth=intval($this->brochure_pagewidth);
		return $brochure_pagewidth;
	}

	function brochure_pageheight()
	{
		$brochure_pageheight=intval($this->brochure_pageheight);
		return $brochure_pageheight;
	}

	function brochure_addpars()
	{
		$brochure_addpars=$this->brochure_addpars;
		return $brochure_addpars;
	}

	function brochure_cropconfig()
	{
		$brochure_cropconfig=$this->brochure_cropconfig;
		return $brochure_cropconfig;
	}

	function brochure_pageprefix()
	{
		$brochure_pageprefix=$this->brochure_pageprefix;
		return $brochure_pageprefix;
	}
	function brochure_resolution()
	{
		$brochure_resolution=$this->brochure_resolution;
		return $brochure_resolution;
	}


	
	function getBrochureTitleFromId($brochure,&$brochurestitles)
	{
		$myts =& MyTextSanitizer::getInstance();
		$sql="SELECT brochure_id, brochure_title, brochure_imgurl FROM ".$this->table." WHERE ";
	    if (!is_array($brochure)) {
        	$sql .= " brochure_id=".intval($brochure);
	    } else {
	    	if(count($brochure)>0) {
	        	$sql .= " brochure_id IN (".implode(',', $brochure).")";
	    	} else {
	    		return null;
	    	}
	    }
	    $result = $this->db->query($sql);
		while ($row = $this->db->fetchArray($result)) {
			$brochurestitles[$row["brochure_id"]]=array("title"=>$myts->displayTarea($row["brochure_title"]),"picture"=>XOOPS_URL.'/modules/pageflip/images/brochures/'.$row["brochure_imgurl"]);
		}
		return $brochurestitles;
	}


	function &getBrochuresList($frontpage=false,$perms=false)
	{
		$sql='SELECT brochure_id, brochure_pid, brochure_title, brochure_color FROM '.$this->table." WHERE 1 ";
		if($frontpage) {
			$sql .= " AND brochure_frontpage=1";
		}

		if($perms) {
			$brochuresids=array();
			$brochuresids=brochures_MygetItemIds();
           		 if (count($brochuresids) == 0) {
            			return '';
            		}
            		$brochures = implode(',', $brochuresids);
           		 $sql .= " AND brochure_id IN (".$brochures.")";
		}
		$result = $this->db->query($sql);
		$ret = array();
		$myts =& MyTextSanitizer::getInstance();
		while ($myrow = $this->db->fetchArray($result)) {
			$ret[$myrow['brochure_id']] = array('title' => $myts->displayTarea($myrow['brochure_title']), 'pid' => $myrow['brochure_pid'], 'color'=> $myrow['brochure_color'], 'brochure_id'=> $myrow['brochure_id']);
			//$ret[$myrow['brochure_id']] = $this->makeBrochure($myrow);
		}
		return $ret;
	}




	function setBrochureTitle($value)
	{
		$this->brochure_title = $value;
	}

	function setBrochureImgurl($value)
	{
		$this->brochure_imgurl = $value;
	}

	function setBrochurePid($value)
	{
		$this->brochure_pid = $value;
	}
	function setBrochureOrderID($value)
	{
		$this->brochure_orderid = $value;
	}

	function getBrochure($brochureid)
	{
		$brochureid = intval($brochureid);
		$sql = "SELECT * FROM ".$this->table." WHERE brochure_id=".$brochureid."";
		$array = $this->db->fetchArray($this->db->query($sql));
		$this->makeBrochure($array);
	}


	function getLastBrochure($cat_id)
	{
		$brochureid = intval($brochureid);
		$sql = "SELECT * FROM ".$this->table;


		if ( $cat_id >0 ) {
			$sql .= " WHERE brochure_pid =".$cat_id;
		}


		$sql .= ' ORDER BY DateCreated DESC';
		$sql .= ' LIMIT 1';
//echo "LATEST:".$sql."<P>";
		$array = $this->db->fetchArray($this->db->query($sql));
		$this->makeBrochure($array);
	}


	function deleteBrochure($brochure_id)
	{
		//echo "-1-<P>";
		$brochureid = intval($brochureid);
		$sql = "DELETE FROM ".$this->table;
		$sql .= " WHERE brochure_id =".$brochure_id;

		$sql .= ' LIMIT 1';
		//echo "deleteBrochure:".$sql."<P>";
		//$array = $this->db->fetchArray($this->db->query($sql));

		$array = $this->db->queryf($sql);
		return true;
	}




	function usePermission($mid)
	{
		$this->mid = $mid;
		$this->use_permission = true;
	}



	function setBrochureDescription($value)
	{
		$this->brochure_description = $value;
	}

	function brochure_frontpage()
	{
		return $this->brochure_frontpage;
	}

	function setBrochureFrontpage($value)
	{
		$this->brochure_frontpage=intval($value);
	}
	
	
	function brochureExists($pid, $title) {
		//echo $this->table."<P>";
		$sql = "SELECT COUNT(*) from ".$this->table." WHERE brochure_pid = ".intval($pid)." AND brochure_title = '".trim($title)."'";
		$rs = $this->db->query($sql);
        list($count) = $this->db->fetchRow($rs);
		if ($count > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	function brochure_id()
	{
		return $this->brochure_id;
	}

	function brochure_pid()
	{
		return $this->brochure_pid;
	}


	function brochure_title($format="S")
	{
		$myts =& MyTextSanitizer::getInstance();
		switch($format){
			case "S":
			case "E":
				$title = $myts->htmlSpecialChars($this->brochure_title);
				break;
			case "P":
			case "F":
				$title = $myts->htmlSpecialChars($myts->stripSlashesGPC($this->brochure_title));
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

	function getFirstChildTopics()
	{
		$ret = array();
		$xt = new XoopsTree($this->table, "brochure_id", "brochure_pid");
		$brochure_arr = $xt->getFirstChild($this->brochure_id, "brochure_title");
		if ( is_array($brochure_arr) && count($brochure_arr) ) {
			foreach($brochure_arr as $topic){
				$ret[] = new XoopsTopic($this->table, $topic);
			}
		}
		return $ret;
	}

	function getAllChildTopics()
	{
		$ret = array();
		$xt = new XoopsTree($this->table, "brochure_id", "brochure_pid");
		$brochure_arr = $xt->getAllChild($this->brochure_id, "brochure_title");
		if ( is_array($brochure_arr) && count($brochure_arr) ) {
			foreach($brochure_arr as $topic){
				$ret[] = new XoopsTopic($this->table, $topic);
			}
		}
		return $ret;
	}

	function getChildTopicsTreeArray()
	{
		$ret = array();
		$xt = new XoopsTree($this->table, "brochure_id", "brochure_pid");
		$brochure_arr = $xt->getChildTreeArray($this->brochure_id, "brochure_title");
		if ( is_array($brochure_arr) && count($brochure_arr) ) {
			foreach($brochure_arr as $topic){
				$ret[] = new XoopsTopic($this->table, $topic);
			}
		}
		return $ret;
	}

	
	
}
?>