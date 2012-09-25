<?php
/**
 * Functions for Classic (default layout)
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
	"main_text" =>  '<h2>Welcome To My Business!</h2>
<p class="p">If you are looking for first-class service, you have come to the right place! We aim to be friendly and approachable.</p>
<p class="p">We are here to serve you and answer any questions you may have.</p>

<h2>Call us today: 1.800.800.8888</h2>

<p class="p">We put our customers first. We listen to you and help you find what you need. Come visit to see what we are all about:</p>

<ul>
	<li>Industry Leading Products</li>
	<li>Quick Turnaround</li>
	<li>Friendly and Approachable</li>
	<li>And much, much more!</li>
</ul>
',
	"layout_title" =>  'Classic',
	);
    return $smallbiz_defaults_for_layout;
}

/* Extra options for layout */
/* Not sure this is needed -- check. */ 
function smallbiz_on_layout_activate()
{
	global $wpdb;
	$smallbiz_defaults = smallbiz_defaults();
}
/* Extra options for layout must also be defined here: */
function smallbiz_layout_extra_options()
{
    $options = array(
	);
	return $options;
}

/* Section on the options page for layout */
function smallbiz_theme_option_page_layout_options()
{
	global $wpdb, $smallbiz_cur_version ;
?>
<div id="outerbox"> 			
<h6>Home Page Text</h6>
<div id="mainpagetext">

            <?php echo tinyMCE_HTMLarea('main_text',get_option("smallbiz_main_text")); ?>
			
		   <br />
	 <p><input type="submit" value="Save Changes" name="sales_update" /></p>
			
</div> <!--mainpagetext-->
			
			</div> <!--outerbox-->
			
			
<div id="outerbox"> 
<h6>Home Page Image (left side of text)</h6>
<div id="homepageimage">

<p><strong>Upload your own image to replace the default image on the homepage.</strong></p>
<p>Simply repeat the upload to replace your current image if desired.</p>
<p>The SmallBiz Theme will size the image for you - however for fast page load times we encourage you to resize your image before uploading.</p> 
<br />
			<p>Ideal size: 313px x 203px : <input type="file" class="fileinput" name="page_image"/></p>
			
			   <br />
	 <p><input type="submit" value="Save Changes" name="sales_update" /></p>
			
</div> <!--homepageimage-->
			
			</div> <!--outerbox-->
<?php } ?>