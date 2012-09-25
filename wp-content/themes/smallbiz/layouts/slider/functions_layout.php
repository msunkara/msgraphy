<?php
/**
 * Functions for slider.
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
  
  
"slider_imgs1" => 'http://cdn4.expand2web.com/slider01.png',

"slider_imgs2" => 'http://cdn4.expand2web.com/slider02.png',

"slider_imgs3" => 'http://cdn4.expand2web.com/slider03.png',

"slider_imgs4" => 'http://cdn4.expand2web.com/slider04.png',

"slider_imgs5" => 'http://cdn4.expand2web.com/slider05.png',


"slider_lks1" => 'http://userguide.expand2web.com/',

"slider_lks2" => 'http://www.expand2web.com/',

"slider_lks3" => 'http://www.smallbiztheme.com/affiliates/',

"slider_lks4" => '#',

"slider_lks5" => '#',

    "slider_main_text" =>  '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>',

	"layout_title" =>  'slider',
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
	if(!get_option('smallbiz_slider_main_text')){
	    //update_option('smallbiz_slider_main_text', $layout_defaults['slider_main_text']);
	}
}
/* Extra options for layout must also be defined here: */
function smallbiz_layout_extra_options()
{
    $options = array(
    'slider_imgs1',
          'slider_imgs2',
            'slider_imgs3',
              'slider_imgs4',
                'slider_imgs5',
         'slider_lks1',
         'slider_lks2',
         'slider_lks3',
         'slider_lks4',
         'slider_lks5',
			'slider_main_text',
			'slider_page_image'
			
	);
	return $options;
}

/* Section on the options page for layout */
function smallbiz_theme_option_page_layout_options()
{
	global $wpdb, $smallbiz_cur_version ;	
?>


<div id="outerbox"> 
<h6>Home Page Slider</h6>
<div id="homepageimage">

<p><strong>Slider Instructions</strong></p>
 
 <p class="userguide">1) Create 5 images (with image editing software of your choice - If you don't have image editing software - <a href="http://userguide.expand2web.com/free-and-online-based-image-editors/ target="_blank">look here</a>) that are 960px Wide by 240px Height.</p>
<p>2) Upload your images using Wordpress "Media" -> "Add Media" (upper-left Wordpress Dashboard sidebar).</p>
<p>3) Copy the image URL(s) into the field(s) below.</p>
<p>4) You can link each image to a page or any URL you want. Links must start with <em>http://</em> or <em>https://</em>. Leave field blank if you do not want to link the image.  </p>

<br />

<p>Image URL 1:<br /> <input style="width:600px" type="text" name="slider_imgs1" value="<?php echo get_option("smallbiz_slider_imgs1")?>" /></p>

<p>Optional: Link Image 1 to the following page...<br /> <input style="width:400px" type="text" name="slider_lks1" value="<?php echo get_option("smallbiz_slider_lks1")?>" /></p>

<br />

<p>Image URL 2:<br /> <input style="width:600px" type="text" name="slider_imgs2" value="<?php echo get_option("smallbiz_slider_imgs2")?>" /></p>

<p>Optional: Link Image 2 to the following page...<br /> <input style="width:400px" type="text" name="slider_lks2" value="<?php echo get_option("smallbiz_slider_lks2")?>" /></p>

<br />

<p>Image URL 3:<br /> <input style="width:600px" type="text" name="slider_imgs3" value="<?php echo get_option("smallbiz_slider_imgs3")?>" /></p>

<p>Optional: Link Image 3 to the following page...<br /> <input style="width:400px" type="text" name="slider_lks3" value="<?php echo get_option("smallbiz_slider_lks3")?>" /></p>

<br />

<p>Image URL 4:<br /> <input style="width:600px" type="text" name="slider_imgs4" value="<?php echo get_option("smallbiz_slider_imgs4")?>" /></p>

<p>Optional: Link Image 4 to the following page...<br /> <input style="width:400px" type="text" name="slider_lks4" value="<?php echo get_option("smallbiz_slider_lks4")?>" /></p>

<br />

<p>Image URL 5:<br /> <input style="width:600px" type="text" name="slider_imgs5" value="<?php echo get_option("smallbiz_slider_imgs5")?>" /></p>

<p>Optional: Link Image 5 to the following page...<br /> <input style="width:400px" type="text" name="slider_lks5" value="<?php echo get_option("smallbiz_slider_lks5")?>" /></p>

<br />

<p><em>We restricted the slider to 5 images to keep your page load times fast. Google does check for it.</em></p>
 <p class="userguide"><em>Advanced Users: Visit the SmallBiz UserGuide if you would like to have <a href="http://userguide.expand2web.com/more-or-less-thn-5-slides/" target="_blanK">more or less then 5 Slides</a>.</em></p>

               <br />
<p><input type="submit" value="Save Changes" name="sales_update" /></p>
            
</div> <!--homepageimage-->    
<div id="protip">
<p>ProTip: Advanced User option <a href="http://userguide.expand2web.com/slider-controls" target="_blank">custom slider control settings</a>.</p>
</div>
</div> <!--outerbox-->


<div id="outerbox">             
<h6>Home Page Text Box</h6>
<div id="mainpagetext">

            <?php echo tinyMCE_HTMLarea('slider_main_text',get_option("smallbiz_slider_main_text")); ?>
            

            <?php

            $pages = $wpdb->get_results('select * from '. $wpdb->prefix .'posts where post_type="page" and post_status="publish"');

            ?>
            
               <br />
	 <p><input type="submit" value="Save Changes" name="sales_update" /></p>
            
</div> <!--mainpagetext-->
  <div id="protip">
<p>ProTip: How to add an Image into the Homepage Text Box. <a href="http://userguide.expand2web.com/adding-images-to-the-4-bottom-boxes/" target="_blank">Click here to learn how</a>.</p>
</div>
</div> <!--outerbox-->
            

<?php } ?>