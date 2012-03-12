<?php
/*
Plugin Name: Items Custom Fields
Description: Manage Custom Fields For The Item Manager & Displayer
Version: 1.3
Author: PyC
Author URI: http://profileyourcity.com
Modified Version Of Mvlcek's Plugin
*/

# get correct id for plugin
$thisfile = basename(__FILE__, ".php");
if(file_exists('ITEMSFILE'))
{
$item_manager_file = getXML(GSDATAOTHERPATH.'item_manager.xml');
global $item_title;
$item_title = $item_manager_file->item->title;
}
else
{
global $item_title;
$item_title = "Item";
}
# register plugin
register_plugin(
    $thisfile,
  'Custom Fields For Items',
  '1.0',
  'PYC',
  'http://profileyourcity.com/',
  'Manages Custom Fields For The Items Manager - Modified Version of Mvlcek\'s Plugin',
  'pages',
  'items_customfields_configure'
);

i18n_merge('items') || i18n_merge('items', 'en_US');

add_action('header', 'items_customfields_header');            // add hook to create styles for custom field editor.

require_once(GSPLUGINPATH.'items/common.php');

$im_customfield_def = null;

function items_customfields_header() {
      if(!file_exists(GSDATAOTHERPATH.'plugincustomfields.xml'))
		{
		$xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel></channel>');
		$xml->asXML(GSDATAOTHERPATH.'plugincustomfields.xml');
		 return true;
		}
?>
  <style type="text/css">
    form #metadata_window table.formtable td .cke_editor td:first-child { padding: 0; }
    form #metadata_window table.formtable .cke_editor td.cke_top { border-bottom: 1px solid #AAAAAA; }
    form #metadata_window table.formtable .cke_editor td.cke_contents { border: 1px solid #AAAAAA; }
    #customfieldsForm .hidden { display:none; }
    .shorts {
      width:250px !important;
    }
    .checkp {
      margin-top:17px;
    }
     .user_sub_tr {
      border:0px;border-bottom:0px !important; border-bottom-width:0px !important;border-top:0px;border-top-width:0px !important;
   }
    .user_sub_tr td{
      border:0px;border-bottom:0px !important;border-bottom-width:0px !important;padding-top:6px !important; border-top: 0px !important;
   }
      .resize_img {
    max-height:150px;
  }
    

  </style>
<?php
}
function items_customfields_configure(){
  include(GSPLUGINPATH.'items/configure-2.php');
}