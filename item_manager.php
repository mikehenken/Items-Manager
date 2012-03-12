<?php

/*
Plugin Name: Item Manager
Description: Full Featured Items Manager.
Version: 1.1
Author: PyC
Author URI: http://profileyourcity.com/
*/

# get correct id for plugin
$thisfile = basename(__FILE__, '.php');

# definitions
define('ITEMDATA', GSDATAPATH  . 'items/');
define('ITEMDATAFILE', GSDATAOTHERPATH  . 'item_manager.xml');
$item_manager_file = getXML(GSDATAOTHERPATH.'item_manager.xml');
	if(file_exists(ITEMDATAFILE))
	{
		$item_manager_file = getXML(GSDATAOTHERPATH.'item_manager.xml');
		$item_title = $item_manager_file->item->title;
		$item_file_url = $item_manager_file->item->pageurl;
		$item_details_url = $item_manager_file->item->detailspage;
	}
	else
	{
		$item_title = "Items";
		$item_file_url = "index";
		$item_details_url = '';
	}
define('ITEMPAGE', $item_details_url);
define('ITEMSLISTPAGE', $item_file_url);
define('IMTITLE', $item_title);


# register plugin
register_plugin(
  $thisfile,
  'Item Manager',
  '1.2',
  'PyC',
  'http://profileyourcity.com/',
  'Full Featured Item Manager',
  'pages',
  'item_manager'
);


# hooks
add_action('pages-sidebar', 'createSideMenu', array($thisfile, IMTITLE.' Manager'));
add_action('sitemap-additem', 'im_sitemap_include');
add_filter('content', 'get_items');

//For Custom Fields
i18n_merge('i18n_customfields') || i18n_merge('i18n_customfields', 'en_US');
require_once(GSPLUGINPATH.'items/common.php');
$im_customfield_def = null;

//Begin Items Manager Class
include(GSPLUGINPATH.'items/class/IMclass.php');


//Begin Display Items Manager Class
include(GSPLUGINPATH.'items/class/IMclassDisplay.php');


///////////////////
//ADMIN FUNCTIONS//
///////////////////

function item_manager() {

//Main Navigation For Admin Panel
$ImClass = new ItemsManager;
$ImClass->admin_header();

if (isset($_GET['edit'])) 
			{
				$id = empty($_GET['edit']) ? uniqid() : $_GET['edit'];
				$ImClass->showEditItem($id);
			} 
			elseif (isset($_GET['delete'])) 
			{
				$ImClass->deleteItem($_GET['delete']);
			} 
			elseif (isset($_GET['visible'])) 
			{
				$id = $_GET['visible'];
				$ImClass->switchVisibleItem($id);
			}
			elseif (isset($_GET['promo'])) 
			{
				$id = $_GET['promo'];
				$ImClass->switchPromotedItem($id);
			}
			elseif (isset($_POST['category_edit'])) 
			{      
				$ImClass->processImSettings();   
				$ImClass->showEditCategories();    
			} 	
			elseif (isset($_GET['deletecategory']))
			{     
				$ImClass->processImSettings();   
				$ImClass->showEditCategories();   
			}		
			elseif (isset($_GET['category'])) 
			{       
				$ImClass->showEditCategories();	 
			}
			elseif (isset($_GET['settings_edit']))
			{
				$ImClass->processImSettings();
				$ImClass->showImSettings();
			}
			elseif (isset($_GET['settings'])) 
			{     
				$ImClass->showImSettings();
			}
			elseif (isset($_POST['submit'])) {
				$ImClass->processItem();
			} 
			elseif (isset($_GET['fields']))
			{
				items_customfields_configure();
			}
			else {
				$ImClass->showItemsAdmin();
			}

}

function getTheField($tag)
{
	$CustomFields = new DisplayImItems;
	echo $CustomFields->GetField($tag);
}


//////////
function returnTheField($tag)
{
	$CustomFields = new DisplayImItems;
	return $CustomFields->GetField($tag);
}


function im_list_categories() 
{  
	$CustomFields = new DisplayImItems;
	$CustomFields->showCategories();
}


// prints a list of products / categories checks / returns a list of results
function get_items($content) 
{ 
	$CustomFields = new DisplayImItems;
	$url = strval(get_page_slug(FALSE));
	if ($url == ITEMSLISTPAGE) 
	{
		$CustomFields->getAllItems();
	}
	else 
	{
		return $content;
	}
}


?>
