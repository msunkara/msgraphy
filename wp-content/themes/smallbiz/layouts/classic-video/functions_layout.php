<?php
/**
 * Functions for Classic-Video
 *
 * @package WordPress
 * @subpackage Expand2Web SmallBiz
 * @since Expand2Web SmallBiz 3.5
 */ 

/* Defaults overrides for Layout */
function smallbiz_defaults_for_layout(){
  global $smallbiz_defaults_for_layout, $smallbiz_cur_version;
  if($smallbiz_defaults_for_layout){
      return $smallbiz_defaults_for_layout;
  }

  $smallbiz_defaults_for_layout = array(
	"classic_video_text" =>  '<h2>Welcome To My Business!</h2>
<p class="p">If you are looking for first-class service, you have come to the right place! We aim to be friendly and approachable.</p>
<p class="p">We are here to serve you and answer any questions you may have.</p>
<h2>Call us today: 1.192.555.1212</h2>
<p class="p">We put our customers first. We listen to you and help you find what you need. Come visit to see what we are all about.</p><p class="p">We are the only certified PSCBA Center in your Region. Our Staff is consists of industry experts. All of our products are manufactured in our own shop and  come with a lifetime warranty for materials and labor. </p><p class="p">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
 tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim 
veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea 
commodo consequat. Duis aute irure dolor in reprehenderit in voluptate 
velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint 
occaecat cupidatat non proident, sunt in culpa qui officia deserunt 
mollit anim id est laborum</p>
<ul>
	<li>Industry Leading Products</li>
	<li>Quick Turnaround</li>
	<li>Friendly and Approachable</li>
	<li>And much, much more!</li>
</ul>
',

"classic_video_code" => '<iframe width="300" height="255" src="http://www.youtube.com/embed/r15S62FuOS4?rel=0" frameborder="0" allowfullscreen></iframe>',

	"layout_title" =>  'Classic-Video',
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
    if(!get_option('smallbiz_classic_video_text')){
        update_option('smallbiz_classic_video_text', $layout_defaults['classic_video_text']);
    }
      if(!get_option('smallbiz_classic_video_code')){
        update_option('smallbiz_classic_video_code', $layout_defaults['classic_video_code']);
    }
}
/* Extra options for layout must also be defined here: */
function smallbiz_layout_extra_options()
{
    $options = array(
    'classic_video_text',
	'classic_video_code',
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
<div id="homepageimage">


            <?php echo tinyMCE_HTMLarea('classic_video_text',get_option("smallbiz_classic_video_text")); ?>
			
		   <br />
	 <p><input type="submit" value="Save Changes" name="sales_update" /></p>
			
</div> <!--mainpagetext-->
			
			</div> <!--outerbox-->
			
			
<div id="outerbox"> 
<h6>Home Page Video (left side)</h6>
<div id="homepageimage">

	<p>Get your video embedd code from Youtube, Vimeo etc. & paste the supplied code into the field below. </p>
	<p>We recommend choosing the "iFrame" embed code option from YouTube or Vimeo which is HTML5 compatible.</p>
<p>
			<textarea name="classic_video_code" cols="60" rows="10"><?php echo get_option('smallbiz_classic_video_code')?></textarea>
		</p>
		<br />
		 <p class="userguide">Is your Sub-Menu showing or covered behind the video? Read this <a href="http://userguide.expand2web.com/sub-menu-is-behind-and-covered-by-youtube-video/" target="_blank">User Guide post on how to adjust your embed code</a>.</p>
			
			   <br />
	 <p><input type="submit" value="Save Changes" name="sales_update" /></p>
			
</div> <!--homepageimage-->
			
			</div> <!--outerbox-->
<?php } ?>