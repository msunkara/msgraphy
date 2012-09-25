<?php
/**
 * Rotator-Video front page theme.
 *
 * @package WordPress
 * @subpackage Expand2Web SmallBiz
 * @since Expand2Web SmallBiz 3.5.1
 */

 $hideSidebar = true;
?> 

<div id="homewrap">
	<div id="business" style="color:#<?php echo get_option('smallbiz_main_text_color') ?>;">
	
	<div id="rotator-video">

<?php echo do_shortcode(biz_option('smallbiz_rotator_video'))?>

</div>
		
<?php echo do_shortcode(biz_option('smallbiz_rotator_main_text1'))?>

	
<div style="clear: both;"></div>

</div>
</div>

<div id="devider">
</div>
<div id="home">

<div id="homepage-box1">


<?php echo do_shortcode(biz_option('smallbiz_rotator_boxe1'))?>


</div>

<div id="homepage-box2">

<?php echo do_shortcode(biz_option('smallbiz_rotator_boxe2'))?>


</div>

<div id="homepage-box3">

<?php echo do_shortcode(biz_option('smallbiz_rotator_boxe3'))?>



</div>

<div id="homepage-box4">

<?php echo do_shortcode(biz_option('smallbiz_rotator_boxe4'))?>


</div>
</div>
</div>

