<?php
/**
 * All-In-One front page theme.
 *
 * @package WordPress
 * @subpackage Expand2Web SmallBiz
 * @since Expand2Web SmallBiz 3.3
 */
 
 $hideSidebar = true;
?>

<!--[if IE 7]>
<style type="text/css">
#homepage-left{margin-top:10px;}
#homepage-middle{margin-top:10px;}
#homepage-right{margin-top:10px;}
}

</style>
<![endif]--> 
    
<div id="business" style="color:#<?php echo biz_option('smallbiz_main_text_color') ?>;">

<div style="float: left; padding-right:15px;margin-top:-1em;"><?php echo biz_option('smallbiz_allinone_business_video')?></div>

<?php echo do_shortcode(biz_option('smallbiz_allinone_main_text'))?>

		</div>

<div id="home">


<div id="homepage-left">

<?php echo do_shortcode(biz_option('smallbiz_allinone_left_text'))?>

</div>

<div id="homepage-middle">

<?php echo do_shortcode(biz_option('smallbiz_allinone_middle_text'))?>

</div>

<div id="homepage-right">

<?php echo do_shortcode(biz_option('smallbiz_allinone_right_text'))?>

</div>
</div>

</div>


