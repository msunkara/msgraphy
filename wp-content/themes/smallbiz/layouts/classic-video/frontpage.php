<?php
/**
 * Classic-Video front page theme.
 *
 * @package WordPress
 * @subpackage Expand2Web SmallBiz
 * @since Expand2Web SmallBiz 3.3
 */
?>
    <div id="homewrap">
	<div id="classic-home-video">
        <?php echo do_shortcode(biz_option('smallbiz_classic_video_code'))?>

	</div>
	
	<div id="classic-home-text">  
        <?php echo do_shortcode(biz_option('smallbiz_classic_video_text'))?>
    </div>
    </div>