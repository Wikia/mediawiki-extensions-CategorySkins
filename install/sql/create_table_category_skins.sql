CREATE TABLE /*_*/category_skins (
  `cs_id` int(11) PRIMARY KEY auto_increment,
  `cs_category` varchar(255) NOT NULL default '',
  `cs_prefix` varchar(255) NOT NULL default '',
  `cs_suffix` varchar(255) NOT NULL default '',
  `cs_logo` varchar(255) NOT NULL default '',
  `cs_logo_link` varchar(255) NOT NULL default '',
  `cs_style` tinyint(1) NOT NULL default '0'
) /*$wgDBTableOptions*/;
CREATE INDEX /*i*/cs_category ON /*_*/category_skins (cs_category);
