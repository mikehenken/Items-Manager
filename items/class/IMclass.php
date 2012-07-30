<?php 

class ItemsManager
{
	public function __construct()
	{
			//Path for uploaded images/files to be placed
			$end_path = GSDATAUPLOADPATH.'items';
			
			//Alert Admin If Items Manager Settings XML File Is Directory Does Not Exist
			if (!file_exists(ITEMDATA)) 
			{
				mkdir(GSDATAPATH.'items', 0755);
				$ourFileName = GSDATAPATH.'items/.htaccess';
				$ourFileHandle = fopen($ourFileName, 'w') or die("can't open file");
				$stringData = "Allow from all";
				fwrite($ourFileHandle, $stringData);
				fclose($ourFileHandle);
				if (!file_exists(ITEMDATA)) 
				{
					echo '<h3>'.IMTITLE.' Manager</h3><p>The directory "<i>'.GSDATAPATH.'items</i>"
					does not exist. It is required for this plugin to function properly.
					Please create it manually and make sure it is writable.</p>';
				}
				else
				{
					echo '<div class="updated"><strong>The below directory has been succesfully created:</strong><br/>"'.ITEMDATA.'"</div>';
				}
			}
			if(!file_exists($end_path))
			{
				mkdir($end_path, 0755);
				$ourFileName = $end_path.'/.htaccess';
				$ourFileHandle = fopen($ourFileName, 'w') or die("can't open file");
				$stringData = "Allow from all";
				fwrite($ourFileHandle, $stringData);
				fclose($ourFileHandle);
				if (!file_exists($end_path)) 
				{
					echo '<h3>'.IMTITLE.' Manager</h3><p>The directory "<i>'.$end_path.'</i>"
					does not exist. It is required for this plugin to function properly.
					Please create it manually and make sure it is writable.</p><p>You will also need to create a .htaccess document and place it in the "'.$end_path.'" folder. The .htaccess file needs to contain the following line of code:<br/>Allow from all</p>';
				}
				else
				{
					echo '<div class="updated"><strong>The below directory has been succesfully created:</strong><br/>"'.$end_path.'"</div>';
				}
			}
			if(!file_exists(ITEMDATAFILE))
			{
				$this->processImSettings();
			}
	}
	
	public function getItemsAdmin()
	{
		$items = array();
		$files = getFiles(ITEMDATA);
		foreach ($files as $file) 
		{
			if (is_file(ITEMDATA . $file) && preg_match("/.xml$/", $file)) 
			{
				$items[] = $file;
			}
		}
		sort($items);
		return array_reverse($items);
	}
	
	
	public function showCustomFieldsAdmin()
	{
		include(GSPLUGINPATH.'items/inc/edit-2.php');
	}
	
	public function processItem()
	{ 
		$id = clean_urls(to7bits($_POST['post-title'], "UTF-8"));
		$file = ITEMDATA . $id . '.xml';
		$orig_file = ITEMDATA . $_POST['id'] . '.xml';
		if(file_exists($orig_file) && $id != $_POST['id'])
		{
			unlink($orig_file);
		}
		$title = $_POST['post-title'];
		$category = $_POST['category'];
		$content = safe_slash_htmll($_POST['post-content']);  

		if (!file_exists($file)) 
		{
			$date = date('j M Y');
		} 
		else 
		{
			$data = @getXML($file);
			$date = $data->date;
		}

		$xml = @new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><item></item>');
		$xml->addChild('title', empty($title) ? '(no title)' : $title);
		$xml->addChild('slug', $id);
		$xml->addChild('visible', true);
		$xml->addChild('date', $date);
		$xml->addChild('category', $category);
		$note = $xml->addChild('content');  
		$note->addCData($content); 
		$newse = im_customfield_def();

		foreach ($newse as $thes) 
		{
			$keys = $thes['key'];
			if(isset($_POST['post-'.$keys])) 		
			{
				if($keys != "content" && $keys != "excerpt")			
				{	
					$tmp = $xml->addChild($keys);
					$tmp->addCData($_POST['post-'.$keys]);
				}
			}
		}
		XMLsave($xml, $file);

		if (!is_writable($file))
		{
			echo '<div class="error">Unable to write '.IMTITLE.' data to file</div>';
		}
		else
		{
			echo '<div class="updated">The '.IMTITLE.' has been succesfully saved</div>';
		}
	}
	
	public function switchVisibleItem($id)
	{
		$file = ITEMDATA . $id . '.xml';
		if (!file_exists($file))
		{
			echo 'file dont exist';
		}
		else
		{
			$data = @getXML($file);
			if (!isset($data->visible) || $data->visible == false)
			{
				$data->visible = true;
				$action = 'unhidden';
			}
			else
			{
				$data->visible = false;
				$action = 'hidden';
			}
			XMLsave($data, $file);

			if (!is_writable($file))
			{
				echo '<div class="error">Unable to write '.IMTITLE.' data to file</div>';
			}
			else
			{
				echo '<div class="updated">The '.IMTITLE.' has been succesfully '.$action.'</div>';
			}
		}
		$this->showItemsAdmin();
	}
	
	public function switchPromotedItem($id)
	{
		$file = ITEMDATA . $id . '.xml';
		if (!file_exists($file))
		{
			echo 'file dont exist';
		}
		else
		{
			$data = @getXML($file);
			if (!isset($data->promo) || $data->promo == false)
			{
				$data->promo = true;
				$action = 'promoted';
			}
			else
			{
				$data->promo = false;
				$action = 'unpromoted';
			 }
			XMLsave($data, $file);
		 
			if (!is_writable($file))
			{
				echo '<div class="error">Unable to write '.IMTITLE.' data to file</div>';
			}
			else
			{
				echo '<div class="updated">The '.IMTITLE.' has been succesfully '.$action.'</div>';
			}
		}
		$this->showItemsAdmin();	
	}
	
	public function deleteItem($id)
	{
		$file = ITEMDATA . $id . '.xml';
		if (file_exists($file))
			unlink($file);
		if (file_exists($file))
			echo '<div class="error">Unable to delete the '.IMTITLE.'</div>';
		else
			echo '<div class="updated">The '.IMTITLE.' has been deleted</div>';
		$this->showItemsAdmin();
	}
	
	public function processImSettings()
	{
		$category_file = getXML(ITEMDATAFILE);
		//Page URL
		if(isset($_POST['page-url']))
		{
			$file_url = $_POST['page-url'];
		}
		elseif(isset($category_file->item->pageurl))
		{
			$file_url = $category_file->item->pageurl;
		}
		else
		{
			$file_url = ITEMSLISTPAGE;
		}
		
		//Item Title
		if(isset($_POST['item-title']))
		{
			$file_title = $_POST['item-title'];
		}
		elseif(isset($category_file->item->title))
		{
			$file_title = $category_file->item->title;
		}
		else
		{
			$file_title = IMTITLE;
		}
		
		//Details Page
		if(isset($_POST['detailspage']))
		{
			$file_page_details = $_POST['detailspage'];
		}
		elseif(isset($category_file->item->detailspage))
		{
			$file_page_details = $category_file->item->detailspage;
		}
		else
		{
			$file_page_details = ITEMPAGE;
		}
		
		//Results Page
		if(isset($_POST['resultspage']))
		{
			$file_results_page = safe_slash_html($_POST['resultspage']);
		}
		elseif(isset($category_file->item->resultspage))
		{
			$file_results_page = $category_file->item->resultspage;
		}
		else 
		{
			$file_results_page = '
			<style>
				.m_pic {
					width:160px;
					float:left;
					border:1px solid white;
					padding:1px;margin-top:0px;
				}
				.thatable tr td h2 {
					margin:5px;
					font-size:15px;
					margin-toP:6px;
					margin-top:0px;
					padding-top:0px;
				}
				.thetable {
					margin-bottom:30px;
				}
				.thetable td h2{
					font-size:17px;
				}
			</style>
			<table width="100%" class="thetable">
				<tr>
					<td class="resize_img" width="175" valign="top">
						<div><img src="<?php echo $SITEURL; ?>/data/uploads/items/<?php echo $data->image1; ?>" class="m_pic"/></div>
					</td>
					<td valign="top">
						<h2 style=""><?php echo $data->title; ?> - <span class="title_development"><?php echo $data->category; ?></span> - <a href="<?php echo $url; ?>" style="font-size:13px;">View Details</a></h2>
						<p style="margin:0px;margin-left:4px;text-align:left;">
							&nbsp;
						</p>
						<p style="margin:0px;margin-left:4px;text-align:left;">
							<?php echo $content; ?>.. <a href="<?php echo $url; ?>">Read more</a>
						</p>
					</td>
				</tr>
			</table>
			';
		}
		if(file_exists(ITEMDATAFILE))
		{	
			$category_file = getXML(ITEMDATAFILE);
		}
		$xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><channel></channel>');
		
			$item_xml = $xml->addChild('item');
			
			//Set Title Variable And And Write To XML FIle
			$item_xml->addChild('title', $file_title);
			
			//Set Page URL Variable And Write To XML FIle
			$item_xml->addChild('pageurl', $file_url);
			
			//Set Details Page And Write To XML File
			$item_xml->addChild('detailspage', $file_page_details);
			
			//Set Results Page Coding And Write To XML File
			$note = $item_xml->addChild('resultspage');  
			$note->addCData($file_results_page); 
			
			//Add Categories
			$category = $xml->addChild('categories');
			if(file_exists(ITEMDATAFILE))
			{		
				foreach($category_file->categories->category as $the_fed)
				{
					$category_uri = $the_fed;
					if(isset($_GET['deletecategory']) && $category_uri == $_GET['deletecategory'])
					{
					
					}
					else
					{
						$category->addChild('category', $category_uri);
					}
				}
			}
			if(isset($_POST['new_category']) && $_POST['new_category'] != "")
			{
				$category->addChild('category', $_POST['new_category']);
			}	
			
		//Save XML File
		XMLsave($xml, ITEMDATAFILE);
	}
}

//Clean URL For Slug
function clean_urls($text)  {
	$text = strip_tags(lowercase($text));
	$code_entities_match = array(' ?',' ','--','&quot;','!','@','#','$','%','^','&','*','(',')','+','{','}','|',':','"','<','>','?','[',']','\\',';',"'",',','/','*','+','~','`','=','.');
	$code_entities_replace = array('','-','-','','','','','','','','','','','','','','','','','','','','','','','','');
	$text = str_replace($code_entities_match, $code_entities_replace, $text);
	$text = urlencode($text);
	$text = str_replace('--','-',$text);
	$text = rtrim($text, "-");
	return $text;
}

function to7bits($text,$from_enc="UTF-8") {
	if (function_exists('mb_convert_encoding')) {
		$text = mb_convert_encoding($text,'HTML-ENTITIES',$from_enc);
	}
	$text = preg_replace(
	array('/&szlig;/','/&(..)lig;/','/&([aouAOU])uml;/','/&(.)[^;]*;/'),array('ss',"$1","$1".'e',"$1"),$text);
	return $text;
}

//Function To Clean Posted Content
function safe_slash_htmll($text) {
		if (get_magic_quotes_gpc()==0) 
		{		
			$text = addslashes(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
		}
		else 
		{		
			$text = htmlentities($text, ENT_QUOTES, 'UTF-8');	
		}
		return $text;
}
?>