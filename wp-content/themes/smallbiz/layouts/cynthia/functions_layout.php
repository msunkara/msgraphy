<?php
/**
 * Functions for Cynthia.
 *
 * @package WordPress
 * @subpackage Expand2Web SmallBiz
 * @since Expand2Web SmallBiz 3.3
 */ 

/* Defaults overrides for Layout */
function smallbiz_defaults_for_layout(){
  global $smallbiz_defaults_for_layout, $smallbiz_cur_version;
  if($smallbiz_defaults_for_layout){
      return $smallbiz_defaults_for_layout;
  }

  $smallbiz_defaults_for_layout = array(
    "cynthia_page_image" =>  'mid.jpg',
    "cynthia_main_text" =>  '<div style="float: left; padding-right:15px; padding-bottom:8px;padding-top:10px;"><img src="http://cdn4.expand2web.com/site1.jpg" style="margin-top: 15px;" alt="Expand2Web Stockimage" height="250" /></div>
    <h2>Call us today: 1.192.555.1212</h2><p class="p">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p><p class="p">Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>',
    
    "cynthia_left_text" => '<h2>About</h2><hr /><p> </p>
    <div style="float: left; padding-right:15px; padding-bottom
    :8px;padding-top:10px;"><img src="http://cdn4.expand2web.com/happy.jpg" alt="Expand2Web Stockimage" /></div><p>This is an example of a Text Box, you could edit this to put information about yourself or your site so readers know where you are coming from. You can rename the box to what you like. This box and all others can be edited with a visual editor inside of WordPress.</p>',
    
	"layout_title" =>  'Cynthia',
	);
    return $smallbiz_defaults_for_layout;
}

/* Extra options for layout */
/* Not sure this is needed -- check. */ 
function smallbiz_on_layout_activate()
{
	global $wpdb;
	$smallbiz_defaults = smallbiz_defaults();
	$layout_defaults   = smallbiz_defaults_for_layout();
	if(!get_option('smallbiz_cynthia_left_text')){
	    //update_option('smallbiz_cynthia_left_text', $layout_defaults['cynthia_left_text']);
	}
}
/* Extra options for layout must also be defined here: */
function smallbiz_layout_extra_options()
{
    $options = array(
    		'cynthia_page_image',
			'cynthia_left_text',
			'cynthia_main_text'
	);
	return $options;
}

/* Section on the options page for layout */
function smallbiz_theme_option_page_layout_options()
{
	global $wpdb, $smallbiz_cur_version ;
	
	if(isset($_POST['sales_update']) )
	{
		if($_FILES['cynthia_page_image']['tmp_name'] != ""){
		    if (file_exists(dirname(__FILE__).'/images/'.get_option('smallbiz_cynthia_page_image'))) {
		        unlink(dirname(__FILE__).'/images/'.get_option('smallbiz_cynthia_page_image'));
			}
			@move_uploaded_file($_FILES['cynthia_page_image']['tmp_name'], dirname(__FILE__). '/../../images/'. $_FILES['cynthia_page_image']['name']);
			update_option('smallbiz_cynthia_page_image', $_FILES['cynthia_page_image']['name']);
			update_option('smallbiz_cynthia_page_image_customized', 'true');
	     }	    
	}
?>

<div id="outerbox"> 
<h6>Home Page Banner (above the two textboxes on the homepage)</h6>
<div id="homepageimage">

<p><strong>Create your own 960px wide banner.</strong></p><p> You can make it as tall as you want the theme will scale accordingly.</p>
 <p class="userguide">If you don't have image editing software - <a href="http://userguide.expand2web.com/free-and-online-based-image-editors/ target="_blank">look here</a>.</p>
<br />

            <p>Ideal size: 960px x 200px : <input type="file" class="fileinput" name="cynthia_page_image"/></p>
            
               <br />
	 <p><input type="submit" value="Save Changes" name="sales_update" /></p>
            
</div> <!--homepageimage-->    
<div id="protip">
<p>ProTip: You can also upload an animated GIF created using image software for cool slideshow style effects.</p>
</div>
</div> <!--outerbox-->

<div id="outerbox">             
<h6>Home Page Text Box - Left Side</h6>
<div id="mainpagetext">

            <?php echo tinyMCE_HTMLarea('cynthia_left_text',get_option("smallbiz_cynthia_left_text")); ?>
            

            <?php

            $pages = $wpdb->get_results('select * from '. $wpdb->prefix .'posts where post_type="page" and post_status="publish"');

            ?>
            
               <br />
	 <p><input type="submit" value="Save Changes" name="sales_update" /></p>
            
</div> <!--mainpagetext--> 
<div id="protip">
<p>ProTip: Add your own Image into the Text Box. <a href="http://userguide.expand2web.com/adding-images-to-the-4-bottom-boxes/" target="_blank">Click here to learn how</a>.</p>
</div>
</div> <!--outerbox-->
            
            

<div id="outerbox">             
<h6>Home Page Text Box - Right Side</h6>
<div id="mainpagetext">

            <?php echo tinyMCE_HTMLarea('cynthia_main_text',get_option("smallbiz_cynthia_main_text")); ?>
            

            <?php

            $pages = $wpdb->get_results('select * from '. $wpdb->prefix .'posts where post_type="page" and post_status="publish"');

            ?>
            
               <br />
	 <p><input type="submit" value="Save Changes" name="sales_update" /></p>
            
</div> <!--mainpagetext-->
<div id="protip">
<p>ProTip: Change the Text Box Height and Background color. <a href="http://userguide.expand2web.com/height-and-color-of-cynthia-textboxes/" target="_blank">Click Here to learn how</a>. </p>
</div>
</div> <!--outerbox-->
            
            


<?php } ?>