<?php
/**
 * Functions for Rotator.
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

"rotator_main_text" =>  '<h2>Welcome to my Business!</h2>
<p>If you are looking for first-class service, you have come to the right place! We aim to be friendly and approachable.</p>
<h2>Call us today: 1.192.555.1212 </h2>
<p>We are here to serve you and answer any questions you may have. We are the only Certified and NRT Approved Center in the Pacific Region.</p><p>We specialize is exactly what you need - New customers and inquiries are always welcome.</p><p>Fast - Reliable - Local.</p>',

"rotator_imgs1" => 'http://cdn4.expand2web.com/Building.jpg',

"rotator_imgs2" => 'http://cdn4.expand2web.com/Cast.jpg',

"rotator_imgs3" => 'http://cdn4.expand2web.com/Dentist.jpg',

"rotator_imgs4" => 'http://cdn4.expand2web.com/Shipping.jpg',

"rotator_imgs5" => '',


"rotator_lks1" => 'http://www.expand2web.com/',

"rotator_lks2" => '#',

"rotator_lks3" => '#',

"rotator_lks4" => '#',

"rotator_lks5" => '#',


"rotator_box1" => '<h2>Blog</h2><hr /> <a href="#"><img src="http://cdn4.expand2web.com/newyear.png" alt="Expand2Web Example Image" /></a>',

"rotator_box2" => '<h2>About</h2><hr /><a href="#"><img src="http://cdn4.expand2web.com/crew.png" alt="Expand2Web Example Image" /></a>',

"rotator_box3" => '<h2>Articles</h2><hr /><a href="#"><img src="http://cdn4.expand2web.com/xray.png" alt="Expand2Web Example Image" /></a>',

"rotator_box4" => '<h2>Find Us</h2><hr /><a href="#"><img src="http://cdn4.expand2web.com/maps.png" alt="Expand2Web Example Image" /></a>',

	"layout_title" =>  'Rotator',
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
	if(!get_option('smallbiz_rotator_box1')){
	    update_option('smallbiz_rotator_box1', $layout_defaults['rotator_box1']);
	}
    if(!get_option('smallbiz_rotator_box2')){
        update_option('smallbiz_rotator_box2', $layout_defaults['rotator_box2']);
    }
    if(!get_option('smallbiz_rotator_box3')){
        update_option('smallbiz_rotator_box3', $layout_defaults['rotator_box3']);
    }
    if(!get_option('smallbiz_rotator_box4')){
        update_option('smallbiz_rotator_box4', $layout_defaults['rotator_box4']);
    }
     if(!get_option('smallbiz_rotator_lks1')){
        update_option('smallbiz_rotator_lks1', $layout_defaults['rotator_lks1']);
    }
}
/* Extra options for layout must also be defined here: */
function smallbiz_layout_extra_options()
{
    $options = array(
        'rotator_page_image',
        'rotator_main_text',
        'rotator_imgs1',
          'rotator_imgs2',
            'rotator_imgs3',
              'rotator_imgs4',
                'rotator_imgs5',
         'rotator_lks1',
         'rotator_lks2',
         'rotator_lks3',
         'rotator_lks4',
         'rotator_lks5',
			'rotator_box1',
			'rotator_box2',
			'rotator_box3',
			'rotator_box4',
	);
	return $options;
}

/* Section on the options page for layout */
function smallbiz_theme_option_page_layout_options()
{
	global $wpdb, $smallbiz_cur_version ;
?>

<div id="outerbox">             
<h6>Home Page Main Text Box</h6>
<div id="mainpagetext">

            <?php echo tinyMCE_HTMLarea('rotator_main_text',get_option("smallbiz_rotator_main_text")); ?>
            

            <?php

            $pages = $wpdb->get_results('select * from '. $wpdb->prefix .'posts where post_type="page" and post_status="publish"');

            ?>
            
               <br />
	 <p><input type="submit" value="Save Changes" name="sales_update" /></p>
            
</div> <!--mainpagetext-->
</div> <!--outerbox-->
       
<div id="outerbox">             
<h6>Rotating Images - Slideshow on Homepage</h6>
<div id="mainpagetext">
<p><strong>Image Slideshow Instructions</strong></p>
 
 <p class="userguide">1) Create images (with image editing software of your choice - <a href="http://userguide.expand2web.com/free-and-online-based-image-editors/" target="_blank">look here for free options</a>) that are 320px Wide by 215px Height.</p>
<p>2) Upload your images using Wordpress "Media" -> "Add Media" (upper-left Wordpress Dashboard sidebar).</p>
<p>3) Copy the image URL(s) into the field(s) below.</p>
<p>4) Leave fields empty if you want less than 5 images.</p>
<p>5) You can link each image to a page or any URL you want. Insert "#" if you don't want to link. </p>

<br />

<p>Image URL 1:<br /> <input style="width:600px" type="text" name="rotator_imgs1" value="<?php echo get_option("smallbiz_rotator_imgs1")?>" /></p>

<p>Link Image 1 to the following page or use # for no link:<br /> <input style="width:400px" type="text" name="rotator_lks1" value="<?php echo get_option("smallbiz_rotator_lks1")?>" /></p>

<br />

<p>Image URL 2:<br /> <input style="width:600px" type="text" name="rotator_imgs2" value="<?php echo get_option("smallbiz_rotator_imgs2")?>" /></p>

<p>Link Image 2 to the following page or use # for no link:<br /> <input style="width:400px" type="text" name="rotator_lks2" value="<?php echo get_option("smallbiz_rotator_lks2")?>" /></p>

<br />

<p>Image URL 3:<br /> <input style="width:600px" type="text" name="rotator_imgs3" value="<?php echo get_option("smallbiz_rotator_imgs3")?>" /></p>

<p>Link Image 3 to the following page or use # for no link:<br /> <input style="width:400px" type="text" name="rotator_lks3" value="<?php echo get_option("smallbiz_rotator_lks3")?>" /></p>

<br />

<p>Image URL 4:<br /> <input style="width:600px" type="text" name="rotator_imgs4" value="<?php echo get_option("smallbiz_rotator_imgs4")?>" /></p>

<p>Link Image 4 to the following page or use # for no link:<br /> <input style="width:400px" type="text" name="rotator_lks4" value="<?php echo get_option("smallbiz_rotator_lks4")?>" /></p>

<br />

<p>Image URL 5:<br /> <input style="width:600px" type="text" name="rotator_imgs5" value="<?php echo get_option("smallbiz_rotator_imgs5")?>" /></p>

<p>Link Image 5 to the following page or use # for no link:<br /> <input style="width:400px" type="text" name="rotator_lks5" value="<?php echo get_option("smallbiz_rotator_lks5")?>" /></p>

<br />

<p><em>(Note: We restricted the slideshow to 5 images to keep your page load times fast. Google does check for it)</em></p>

               <br />
<p><input type="submit" value="Save Changes" name="sales_update" /></p>
    
</div> <!--mainpagetext-->
</div> <!--outerbox-->      

<div id="outerbox">             
<h6>Bottom Row Box 1</h6>
<div id="mainpagetext">

            <?php echo tinyMCE_HTMLarea('rotator_box1',get_option("smallbiz_rotator_box1")); ?>
            

            <?php

            $pages = $wpdb->get_results('select * from '. $wpdb->prefix .'posts where post_type="page" and post_status="publish"');

            ?>
            
               <br />
               
                     <p class="userguide"><strong>Visit the SmallBiz User Guide Post <a href="http://userguide.expand2web.com/adding-images-to-the-4-bottom-boxes/" target="_blank">on how to add your own image</a> to all text boxes.</strong> </p>
               <p>We strongly recommend to resize your images before uploading. Suggested size: 188px by 135px.</p>
               <p>The Theme will attempt to resize your images for you to a width of 188px. The image aspect ratio (width to height) will be maintained. However the quality may degrade. </p>
               
               <br />
	 <p><input type="submit" value="Save Changes" name="sales_update" /></p>
            
</div> <!--mainpagetext-->
<div id="protip">
<p>ProTip: For a fast loading page - resize your images to 188px width before uploading.</p>
</div>
</div> <!--outerbox-->
            
            
<div id="outerbox">             
<h6>Bottom Row Box 2</h6>
<div id="mainpagetext">

            <?php echo tinyMCE_HTMLarea('rotator_box2',get_option("smallbiz_rotator_box2")); ?>
            

            <?php

            $pages = $wpdb->get_results('select * from '. $wpdb->prefix .'posts where post_type="page" and post_status="publish"');

            ?>
            
               <br />
	 <p><input type="submit" value="Save Changes" name="sales_update" /></p>
            
</div> <!--mainpagetext-->
<div id="protip">
<p>ProTip: Need Bigger Boxes or want a different Box Background Colors? <a href="http://userguide.expand2web.com/changing-the-background-color-height-width-of-the-4-boxes/" target="_blank">Read the User Guide Post here</a>.</p>
</div>
</div> <!--outerbox-->
            
<div id="outerbox">             
<h6>Bottom Row Box 3</h6>
<div id="mainpagetext">

            <?php echo tinyMCE_HTMLarea('rotator_box3',get_option("smallbiz_rotator_box3")); ?>
            

            <?php

            $pages = $wpdb->get_results('select * from '. $wpdb->prefix .'posts where post_type="page" and post_status="publish"');

            ?>
            
               <br />
	 <p><input type="submit" value="Save Changes" name="sales_update" /></p>
            
</div> <!--mainpagetext-->
<div id="protip">
<p>ProTip: Change or remove the blue Divider line above the 4 boxes. <a href="http://userguide.expand2web.com/changing-the-blue-devider-line/" target="_blank">Here is how to</a>.</p>
</div>
</div> <!--outerbox-->
            
            

<div id="outerbox">             
<h6>Bottom Row Box 4</h6>
<div id="mainpagetext">

            <?php echo tinyMCE_HTMLarea('rotator_box4',get_option("smallbiz_rotator_box4")); ?>
            

            <?php

            $pages = $wpdb->get_results('select * from '. $wpdb->prefix .'posts where post_type="page" and post_status="publish"');

            ?>
            
               <br />
	 <p><input type="submit" value="Save Changes" name="sales_update" /></p>
            
</div> <!--mainpagetext-->      
<div id="protip">
<p>ProTip: Use an Image for Google maps and link to your Find Us Page. <a href="http://userguide.expand2web.com/adding-google-maps-to-homepage//" target="_blank">Read the tip here.</a>.</p>
</div>
</div> <!--outerbox-->
<?php } ?>
