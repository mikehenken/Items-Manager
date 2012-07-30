<?php

/*
Plugin Name: Item Manager
Description: Full Featured Items Manager.
Version: 1.6
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
  '1.6',
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
require_once(GSPLUGINPATH.'items/inc/common.php');
$im_customfield_def = null;

//Begin Items Manager Class
include(GSPLUGINPATH.'items/class/IMclass.php');


//Begin Display Items Manager Class
include(GSPLUGINPATH.'items/class/IMclassDisplay.php');


///////////////////
//ADMIN FUNCTIONS//
///////////////////

function item_manager() 
{
	//Main Navigation For Admin Panel
	$ImClass = new ItemsManager;
	admin_header();

	if (isset($_GET['edit'])) 
	{
		$id = empty($_GET['edit']) ? uniqid() : $_GET['edit'];
		showEditItem($id);
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
		showEditCategories();    
	} 	
	elseif (isset($_GET['deletecategory']))
	{     
		$ImClass->processImSettings();   
		showEditCategories();   
	}		
	elseif (isset($_GET['category'])) 
	{       
		showEditCategories();	 
	}
	elseif (isset($_GET['settings_edit']))
	{
		$ImClass->processImSettings();
		showImSettings();
	}
	elseif (isset($_GET['settings'])) 
	{     
		showImSettings();
	}
	elseif (isset($_POST['submit'])) 
	{
		$ImClass->processItem();
		showItemsAdmin();
	} 
	elseif (isset($_GET['fields']))
	{
		items_customfields_configure();
	}
	else 
	{
		showItemsAdmin();
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
	//$CustomFields = new DisplayImItems;
	global $SITEURL;
	if(file_exists(ITEMSFILE))
	{
		$category_file = getXML(ITEMSFILE);
		foreach($category_file->categories->category as $the_fed)
		{	
			$category = $the_fed;
			$url = $SITEURL.$item_file_url."/?category=$category";
			echo "<li><a href=\"$url\">$category</a></li>";
		}
		echo '<br/><li><a href="'.$SITEURL.$item_file_url.'/">View All Categories</a></li>';
	}
}


// prints a list of products / categories checks / returns a list of results
function get_items($content) 
{ 
	$CustomFields = new DisplayImItems;
	$url = strval(get_page_slug(FALSE));
	if(isset($_GET['sort']))
	{
		$sort = $_GET['sort'];
	}
	else
	{
		$sort = null;
	}
	if ($url == ITEMSLISTPAGE) 
	{
		$CustomFields->getAllItems($sort);
	}
	else 
	{
		return $content;
	}
}


	function showItemsAdmin()
	{
		$ImClass = new ItemsManager;
		$items = $ImClass->getItemsAdmin();
		if(!isset($_GET['item_type']) || $item_type == "view")
		{
			if (!empty($items)) 
			{
				echo '<h3>All '.IMTITLE.'</h3><table class="highlight">';
				foreach ($items as $item) 
				{
					$id = basename($item, ".xml");
					$file = ITEMDATA . $item;
					$data = @getXML($file);
					$date = $data->date;
					$title = html_entity_decode($data->title, ENT_QUOTES, 'UTF-8');
					?>
					<tr>
						<td>
							<a href="load.php?id=item_manager&edit=<?php echo $id; ?>" title="Edit <?php echo IMTITLE; ?>: <?php echo $title; ?>">
							<?php echo $title; ?>
							</a>
							<span style="font-size:9px; color:#a0a0a0; margin-left:5px"><?php echo $data->category;?></span>
						</td>
						<td style="text-align: right;">
							<span><?php echo $date; ?></span>
						</td>
						<td class="switch_visible">
							<a href="load.php?id=item_manager&visible=<?php echo $id; ?>" class="switch_visible" style="text-decoration:none" title="Visible <?php echo IMTITLE; ?>: <?php echo $title; ?>?">
							<?php 
							if (!isset($data->visible) || $data->visible == true)
							{ 
								echo '<font color="#333333">V</font>';
							}
							else
							{
								echo '<font color="#acacac">V</font>';
							}
							?>
							</a>
						</td>
						<td class="switch_promo">
							<a href="load.php?id=item_manager&promo=<?php echo $id; ?>" class="switch_promo" style="text-decoration:none" title="Promo <?php echo IMTITLE; ?>: <?php echo $title; ?>?">
							<?php 
							if (!isset($data->promo) || $data->promo == true)
							{  
								echo '<font color="#333333">P</font>';
							}
							else
							{
								echo '<font color="#acacac">P</font>';
							}
							?>
							</a>
						</td>
						<td class="delete">
							<a href="load.php?id=item_manager&delete=<?php echo $id; ?>" class="delconfirm" title="Delete <?php echo IMTITLE; ?>: <?php echo $title; ?>?">
							X
							</a>
						</td>
					</tr>
					<?php
				}
				echo '</table>';
			}
		}
		echo '<p><b>' . count($items) . '</b> '.IMTITLE.'</p>';
	}

	function admin_header()
	{
		?>
		<div style="width:100%;margin:0 -15px -15px -10px;padding:0px;">
			<h3 class="floated"><?php echo IMTITLE; ?></h3>  
			<div class="edit-nav clearfix" style="">
				<a href="load.php?id=item_manager&settings" <?php if (isset($_GET['settings'])) { echo 'class="current"'; } ?>>Settings</a>
				<a href="load.php?id=item_manager&fields" <?php if (isset($_GET['fields'])) { echo 'class="current"'; } ?>>Custom Fields</a>
				<a href="load.php?id=item_manager&category" <?php if (isset($_GET['category'])) { echo 'class="current"'; } ?>>Manage Categories</a>
				<a href="load.php?id=item_manager&edit" <?php if (isset($_GET['edit']) && $_GET['edit'] == "") { echo 'class="current"'; } ?>>Add New</a>
				<a href="load.php?id=item_manager&view" <?php if (isset($_GET['view'])) { echo 'class="current"'; } ?>>View All</a>
			</div> 
		</div>
		</div>
		<div class="main" style="margin-top:-10px;">
		<?php
	}
	
	function showEditItem($id)
	{
		  $ImClass = new ItemsManager;
		  $file = ITEMDATA . $id . '.xml';
		  $data = @getXML($file);
		  $title = @stripslashes($data->title);
		  $category = @stripslashes($data->category);
		  $content = @stripslashes($data->content);
		  $excerpt = @stripslashes($data->excerpt);
		  ?>
		<h3><?php if (empty($data)) echo 'Create New'; else echo 'Edit'; echo IMTITLE;?><?php //$the = im_customfield_def(); foreach ($the as $thee) {echo $thee[type];}  ?></h3>
		<form class="largeform" action="load.php?id=item_manager" method="post" accept-charset="utf-8">
			<input name="id" type="hidden" value="<?php echo $id; ?>" />
			<p>
				<input class="text title" name="post-title" type="text" value="<?php if($title != "") { echo $title; } else { echo "Title"; } ?>" onFocus="if(this.value == 'Title') {this.value = '';}" onBlur="if (this.value == '') {this.value = 'Title';}" style="width:350px;float:left;"/>
				<select class="text" style="width:250px;float:left;margin-left:20px;padding:5px;font-size:14px;" name="category">
					 <?php
						if($category == "")
						{
							echo "<option value=\"\">Choose Category..</option>";
						}
						$category_file = getXML(ITEMDATAFILE);
						
						foreach($category_file->categories->category as $the_fed)
						{
							if($category == $the_fed)
							{
								$select_box = "selected";
							}
							else { 
								$select_box = ""; 
							}
							echo "<option value=\"$the_fed\" $select_box>$the_fed</option>";
						}
					 ?>
				</select>
			</p>
			<div style="clear:both">&nbsp;</div>
			<link href="../plugins/items/uploader/client/fileuploader.css" rel="stylesheet" type="text/css">
			<script src="../plugins/items/uploader/client/fileuploader.js" type="text/javascript"></script>
			<?php $ImClass->showCustomFieldsAdmin(); ?>
			<p>
				<input name="submit" type="submit" class="submit" value="Save <?php echo IMTITLE; ?>" />
				&nbsp;&nbsp;or&nbsp;&nbsp;
				<a href="load.php?id=item_manager" class="cancel" title="Cancel">Cancel</a>
			</p>
		</form>
  <?php
	}
	
	function showEditCategories()
	{
		global $PRETTYURLS;
		$ImClass = new ItemsManager;
		if(file_exists(ITEMDATAFILE))
			{
				$category_file = getXML(ITEMDATAFILE);
			}
		?>
		<h3>Add &amp; Manage Categories</h3>
		<form class="largeform" action="load.php?id=item_manager&category&category_edit" method="post" accept-charset="utf-8">
		  <div class="leftsec">
			<p>
			  <label for="page-url">Add New Category:</label>
			  <input class="text" type="text" name="new_category" value="" />
			</p>
		  </div>
		  <div class="clear"></div>
		  <table class="highlight">
		  <tr>
		  <th>Category Name</th><th>Delete Category</th>
		  </tr>
		  <?php
		if(file_exists(ITEMDATAFILE))
		{
			foreach($category_file->categories->category as $the_fed)
			{
				echo '
				<tr><td>'.$the_fed.'</td><td><a href="load.php?id=item_manager&category&deletecategory='.$the_fed.'">X</a></td></tr>
			';
			}
		}
		  ?>
		  </table>
		  <p>
			<span>
			  <input class="submit" type="submit" name="category_edit" value="Add Category" />
			</span>
		  </p>
		</form>
		<?php
	}

	function showImSettings()
	{
		$ImClass = new ItemsManager;
		if(file_exists(ITEMDATAFILE))
		{
		  $category_file = getXML(ITEMDATAFILE);
			$file_url = $category_file->item->pageurl;
			$file_title = $category_file->item->title;
			$file_page = $category_file->item->pageurl;
			$file_page_details = $category_file->item->detailspage;
			$file_results_page = $category_file->item->resultspage;
		}
		?>
		<h3>Item Manager Settings</h3>
		<form class="largeform" action="load.php?id=item_manager&settings&settings_edit" method="post" accept-charset="utf-8">
			<div class="leftsec">
				<p>
					<label for="page-url">Choose Item Manager Title</label>
					<input type="text" class="text" name="item-title" value="<?php echo $file_title; ?>" />
				</p>
			</div>
		 <div class="rightsec">
			<p>
			  <label for="page-url">Choose Page To Display Results</label>
			  
			  <select class="text" name="page-url">

			  <?php
			  $pages = get_available_pages();
			  foreach ($pages as $page) {
				$slug = $page['slug'];
				if ($slug == $file_url)
				  echo "<option value=\"$slug\" selected=\"selected\">$slug</option>\n";
				else
				  echo "<option value=\"$slug\">$slug</option>\n";
			  }
			  ?>
			  </select>
			</p>
		  </div>
		 <div class="leftsec">
			<p>
			  <label for="page-url">Choose Page To Display <strong>Details Page</strong></label>
			  
			  <select class="text" name="detailspage">

			  <?php
			  $pages = get_available_pages();
			  foreach ($pages as $page) {
				$slug = $page['slug'];
				if ($slug == $file_page_details)
				  echo "<option value=\"$slug\" selected=\"selected\">$slug</option>\n";
				else
				  echo "<option value=\"$slug\">$slug</option>\n";
			  }
			  ?>
			  </select>
			</p>
		  </div>  
		  <div class="clear"></div>
		  <h2 style="margin-bottom:0px"><strong>Advanced Settings</strong></h2>
		 <div class="leftsec">
			<p style="margin-top:0px">
			  <h3>Results Page Coding</h3>
			  <p style="width:600px;"><strong>This Feature Should Be Used By Experianced Users Only<br/><br />
			  1. You can use any html, css, javascript, or php in this textarea<br/><br />
			  2. The Title Field Can Be Retrieved By Typing <?php highlight_string('<?php echo $data->title; ?>'); ?><br /><br />
			  3. The Category Field Can Be Retrieved By Typing <?php highlight_string('<?php echo $data->category; ?>'); ?><br /><br />
			  4. Custom Fields Can Be Retrieved By Tpying <?php highlight_string('<?php echo $data->nameofcustomfield; ?>'); ?><br /><br />
			  5. The Category Field Can Be Retrieved By Typing <?php highlight_string('<?php echo $data->category; ?>'); ?><br /><br />
			  6. The CONTENT Of The Post Can Be Retrieved By Typing <?php highlight_string('<?php echo $content; ?>'); ?><br /><br />
			  7. The URL Of The Post Can Be Retrieved By Typing <?php highlight_string('<?php echo $url; ?>'); ?><br /><br />
			  </strong></p>
		 <textarea name="resultspage">
		  <?php
		  echo stripcslashes($file_results_page);
		  ?>
		 </textarea>
			</p>
		  </div>  
			<div class="clear"></div>
		  <p>
			<span>
			  <input class="submit" type="submit" name="settings_edit" value="Submit Settings" />
			</span>
		  </p>
		</form>
		<?php
	}
	
?>
