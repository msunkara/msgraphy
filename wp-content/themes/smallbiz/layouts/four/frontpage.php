<?php
/**
 * Foursquare front page theme.
 *
 * @package WordPress
 * @subpackage Expand2Web SmallBiz
 * @since Expand2Web SmallBiz 3.3
 */
 
 $hideSidebar = true;
?>

<div id="home">


<div id="homepage-top-left">

<?php echo do_shortcode(biz_option('smallbiz_four_business_video'))?>

</div>

<div id="homepage-top-right">

<?php echo do_shortcode(biz_option('smallbiz_four_right_text'))?>

</div>

<div id="homepage-bottom-left">

<?php echo do_shortcode(biz_option('smallbiz_four_left_bottom_text'))?>

</div>

<div id="homepage-bottom-right">

<?php echo do_shortcode(biz_option('smallbiz_four_right_bottom_text'))?>


</div>

</div>
</div>

