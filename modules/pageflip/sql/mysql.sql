
CREATE TABLE `pageflip_categories` (
  `category_id` int(11) NOT NULL auto_increment,
  `category_pid` int(11) NOT NULL default '0',
  `category_title` varchar(100) NOT NULL,
  `category_description` varchar(2000) default NULL,
  `category_imgurl` varchar(500) default NULL,
  `DateCreated` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`category_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;

INSERT INTO `pageflip_categories` (`category_id` ,`category_title` ,`category_description` ,`category_imgurl` ,`DateCreated`) VALUES ('1', 'default', 'default category - please edit', NULL ,CURRENT_TIMESTAMP);

CREATE TABLE `pageflip_pages` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `brochure_id` int(11) NOT NULL,
  `page_number` int(11) NOT NULL,
  `page_title` varchar(100) NOT NULL,
  `page_imgurl` varchar(500) NOT NULL,
  `page_description` varchar(50) NOT NULL,
  `page_image` varchar(50) NOT NULL,
  `page_sound` varchar(50) NOT NULL,
  `page_views` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`page_id`),	
  UNIQUE KEY `page_id` (`page_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;





CREATE TABLE `pageflip_brochures` (
  `brochure_id` int(11) NOT NULL auto_increment,
  `brochure_pid` int(11) NOT NULL default '1',
  `user_id` int(11) NOT NULL,
  `brochure_orderid` int(11) NOT NULL default '0',
  `brochure_title` varchar(100) NOT NULL,
  `brochure_description` varchar(500) NOT NULL,
  `brochure_imgurl` varchar(500) NOT NULL,
  `brochure_color` varchar(6) NOT NULL,
  `brochure_pages` int(4) NOT NULL,
  `brochure_pagewidth` int(4) NOT NULL,
  `brochure_pageheight` int(4) NOT NULL,
  `brochure_addpars` varchar(500) NOT NULL,
  `brochure_cropconfig` varchar(500) NOT NULL,
  `brochure_pageprefix` varchar(25) NOT NULL,
  `brochure_resolution` int(4) NOT NULL,
  `DateCreated` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`brochure_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

