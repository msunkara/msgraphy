<?php

/*
Plugin Name: bilobaFLICKR Widget
Plugin URI: http://www.best-plugins.de
Description: Import photos from a flickr rss feed to a wordpress widget.
Version: 2.2
Author: Maik Balleyer (Biloba IT)
Author URI: http://www.biloba-it.de
License: GPL2

Copyright 2011 Maik Balleyer (Biloba IT)  (email : balleyer@biloba-it.de)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
    
*/

add_action("init", "bilobaflickr_init");
add_action('wp_head', 'bilobaflickr_addCssToHeader');

function bilobaflickr_init() {
	register_widget_control('bilobaFLICKR', 'bilobaflickr_control', 200, 200);
	register_sidebar_widget('bilobaFLICKR', 'bilobaflickr_widget');	
}

function bilobaflickr_control() {
	
	//Get all options from the bilobaflickr plugin
	$aryOptions = bilobaflickr_getPluginOptions();
	
	//Load/save options for the bilobaflickr plugin 
	if(array_key_exists('bilobaflickr_submit', $_POST)) {
		$aryPostOptions['title'] = strip_tags(stripslashes($_POST["bilobaflickr_title"]));
		$aryPostOptions['items'] = strip_tags(stripslashes($_POST["bilobaflickr_items"]));
		$aryPostOptions['rss'] = strip_tags(stripslashes($_POST["bilobaflickr_rss"]));
		$aryPostOptions['lightbox'] = strip_tags(stripslashes($_POST["bilobaflickr_lightbox"]));
		$aryPostOptions['size'] = strip_tags(stripslashes($_POST["bilobaflickr_size"]));
		
		update_option('bilobaflickr_widget', $aryPostOptions);
		$aryOptions = $aryPostOptions;
	}

	$strTitle = wp_specialchars($aryOptions['title']);
	$intItems = wp_specialchars($aryOptions['items']);
  $strRss = wp_specialchars($aryOptions['rss']);
  $blnLightbox = wp_specialchars($aryOptions['lightbox']);
  $intSize = wp_specialchars($aryOptions['size']);
	
  //Set default value (since 2.1 implemented)
  if($intSize == '' or $intSize == NULL) {
  	$intSize = 1;
  }
  
	//HTML Output  
	bilobaflickr_getPluginOptionsWww($strTitle, $intItems, $strRss, $blnLightbox, $intSize);
}

function bilobaflickr_widget($args) {
	extract($args);
	
	//Get all options from the bilobaflickr plugin
	$aryOptions = bilobaflickr_getPluginOptions();
	
	//Set variables for procedure
	$strTitle = $aryOptions['title'];
	$intItems = $aryOptions['items'];
	$strFlickrRss = $aryOptions['rss'];
	$blnLightbox = $aryOptions['lightbox'];
	$intSize = $aryOptions['size'];
	
	//Get all photos from the rss feed
	$aryPhotos = bilobaflickr_getFlickrPhotosRss($strFlickrRss, $intItems, $intSize);
	
	//HTML Output
	echo $before_widget;
	echo $before_title . $strTitle . $after_title;
	bilobaflickr_getWidgetWww($aryPhotos, $blnLightbox, $intSize);
	echo $after_widget;
}

function bilobaflickr_getFlickrPhotosRss($strFlickrRss, $intItems, $intSize) {
	$aryPhotos = array();
	
	if( file_exists(ABSPATH . WPINC . '/rss.php') ) {
		require_once(ABSPATH . WPINC . '/rss.php');
	} else {
		require_once(ABSPATH . WPINC . '/rss-functions.php');
	}	
	
	$aryRss = fetch_rss($strFlickrRss);
	
	if(is_array($aryRss->items)) {
		$aryItems = array_slice($aryRss->items, 0, $intItems );
		$intCounter = 0;
		while(list($strKey, $strPhoto) = each($aryItems)) {
			preg_match_all("/<IMG.+?SRC=[\"']([^\"']+)/si",$strPhoto['description'],$aryResult, PREG_SET_ORDER);
			$strPhotoUrl = $aryResult[0][1];
			$strPhotoUrlBig = str_replace( "_m.jpg", "_b.jpg", $strPhotoUrl);
			
			switch($intSize) {
				case 0:
					$strPhotoUrl = str_replace( "_m.jpg", "_t.jpg", $strPhotoUrl);
					break;
				case 1:
					//Do nothing
					break;
				default:
					//Do nothing
			}      

      $aryPhotos[$intCounter]['url'] = $strPhotoUrl;
      $aryPhotos[$intCounter]['url_big'] = $strPhotoUrlBig;
      $aryPhotos[$intCounter]['alt'] = wp_specialchars($strPhoto['title'], true);
      $aryPhotos[$intCounter]['title'] = wp_specialchars($strPhoto['title'], true);
      $aryPhotos[$intCounter]['link'] = wp_specialchars($strPhoto['link'], true);
      $aryPhotos[$intCounter]['morelink'] = $aryRss->channel['link'];
      
      $intCounter++;
		}
	}
	
	return $aryPhotos;
}

function bilobaflickr_getPluginOptions() {
	$aryOptions = array();
	
	//Get all options from the bilobaflickr plugin
	$aryOptions = get_option('bilobaflickr_widget');
	
	//Set default values if something went wrong
	if($aryOptions == false) {
		$aryOptions['title'] = 'bilobaFLICKR';
		$aryOptions['items'] = 5;
		$aryOptions['rss'] = 'http://api.flickr.com/services/feeds/photos_public.gne?id=41068918@N05&lang=de-de&format=rss_200';		
		$aryOptions['lightbox'] = '0';
		$aryOptions['size'] = '1';
	}	
	
	return $aryOptions;
}

function bilobaflickr_getPluginOptionsWww($strTitle, $intItems, $strRss, $blnLightbox, $intSize) {
	load_plugin_textdomain('bilobaflickr','/wp-content/plugins/flickr-wp-widget/location-of-mo-po-files/');
	
	//Output html code for option box
	?>
	<link rel="stylesheet" type="text/css" href="/wp-content/plugins/bilobaflickr/bilobaflickr.css">
	<p class="bilobaflickr_text">
		Import photos from flickr to this widget.
	</p>	
	<p class="bilobaflickr_input">
		<label for="bilobaflickr_title">
			<?php _e('Title:'); ?>
		</label>
		<input size="15" id="bilobaflickr_title" name="bilobaflickr_title" type="text" value="<?=$strTitle?>">
	</p>
	<p class="bilobaflickr_input">
		<label for="bilobaflickr_items">
			<?php _e('Amount of photos:'); ?>
		</label>
		<input size="5" id="bilobaflickr_items" name="bilobaflickr_items" type="text" value="<?=$intItems?>">
	</p>
	<p class="bilobaflickr_input">
		<label for="bilobaflickr_size">
			<?php _e('Size of photos:'); ?>
		</label>
		<?
		//Set size to drop-down box
		switch($intSize) {
			case 0:
				$strSelThumb = ' selected="selected"';
				$strSelSmall = ' ';					
				break;
			case 1:
				$strSelThumb = ' ';
				$strSelSmall = ' selected="selected"';				
				break;
			default:
				$strSelThumb = ' ';
				$strSelSmall = ' selected="selected"';
		}
		?>
		<select size="1" id="bilobaflickr_size" name="bilobaflickr_size">
			<option value="0" <?=$strSelThumb?>>Thumbnail (100x67)</option>
			<option value="1" <?=$strSelSmall?>>Small (240x160)</option>
		</select>
	</p>
	<p class="bilobaflickr_input">
		<label for="bilobaflickr_rss">
			<?php _e('RSS Url:'); ?>
		</label>
		<input size="25" id="bilobaflickr_rss" name="bilobaflickr_rss" type="text" value="<?=$strRss?>">
	</p>
	<p class="bilobaflickr_input">
		<label for="bilobaflickr_lightbox">
			<?php _e('Use lightbox (needs to be installed):'); ?>
		</label>
		<input id="bilobaflickr_lightbox" name="bilobaflickr_lightbox" type="checkbox" value="lightbox"
		<?
		if($blnLightbox != '') {
			echo ' checked="checked"';
		}
		?>		
		>
	</p>	
	<p class="bilobaflickr_text">
		You can find the <strong>rss url</strong> at the bottom of the flickr photostream you want to show.
	</p>
	<input type="hidden" id="bilobaflickr_submit" name="bilobaflickr_submit" value="1">
	
	<?
}

function bilobaflickr_getWidgetWww($aryPhotos, $blnLightbox, $intSize) {
	echo '<div class="bilobaflickr_item_box">';
	foreach($aryPhotos as $aryPhoto) {
		$strMoreLink = $aryPhoto['morelink'];
		
		switch($intSize) {
			case "0":
				echo '<div class="bilobaflickr_item_thumb">';
				break;
			case "1":
				echo '<div class="bilobaflickr_item_small">';
				break;
			default:
				echo '<div class="bilobaflickr_item_small">';
		}		
		$strImage = '<img alt="' . $aryPhoto['alt'] . '" title="' . $aryPhoto['alt'] . '" src="' . $aryPhoto['url'] . '" border="0" />';
		if($blnLightbox != '') {
			echo '<a rel="lightbox[bilobaflickr]" href="' . $aryPhoto['url_big'] . '" title="' . $aryPhoto['alt'] . '">';
			echo $strImage;
			echo '</a>';		
		} else {
			echo '<a title="' . $aryPhoto['alt'] . '" href="'.$aryPhoto['link'].'">';
			echo $strImage;
			echo '</a>';
		}
		echo '</div>';		
	}
	echo '<a id="bilobaflickr_morelink" title="Open flickr photostream" href="'.$strMoreLink.'">More photos</a><br />';
	
	//Please donate this plugin at best-plugins.de when removing this link
	echo '<a id="bilobaflickr_homepage" title="Go to plugin homepage" href="http://www.best-plugins.de">Plugin Homepage</a>';
	
	echo '</div>';
}

function bilobaflickr_addCssToHeader() {
	echo '<link rel="stylesheet" type="text/css" href="/wp-content/plugins/flickr-wp-widget/bilobaflickr.css" />' . "\n"; 
}

?>