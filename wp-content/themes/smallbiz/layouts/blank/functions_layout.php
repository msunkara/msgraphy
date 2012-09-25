<?php
/**
 * Functions for Blank No Sidebar
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
	"noside_text" =>  '<h2>Welcome To My Business!</h2>
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
	"layout_title" =>  'Blank',
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
	if(!get_option('smallbiz_noside_text')){
	    //update_option('smallbiz_nosidek_text', $layout_defaults['noside_text']);
	}
}
/* Extra options for layout must also be defined here: */
function smallbiz_layout_extra_options()
{
    $options = array(
			'noside_text'
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

            <?php echo tinyMCE_HTMLarea('noside_text',get_option("smallbiz_noside_text")); ?>
			
		   <br />
	 <p><input type="submit" value="Save Changes" name="sales_update" /></p>
			
</div> <!--mainpagetext-->
<div id="protip">
<p>ProTip: Add an Image to your text. <a href="http://userguide.expand2web.com/adding-images-to-the-4-bottom-boxes/" target="_blank">Click here to learn how</a>. Or toggle into html mode and paste your YouTube embed code <a href="http://userguide.expand2web.com/adding-a-video-to-pages-and-posts/" target="_blank">See here</a>.</p>
</div>
			
			</div> <!--outerbox-->
			
			
<?php } ?>