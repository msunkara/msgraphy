<?php
/**
 * Fader front page theme.
 *
 * @package WordPress
 * @subpackage Expand2Web SmallBiz
 * @since Expand2Web SmallBiz 3.3
 */
 
 $hideSidebar = true;
?>

<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/layouts/slider/jquery.js"></script>
	
<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/layouts/slider/easySlider1.7.js"></script>

	<script type="text/javascript">
		$(document).ready(function(){	
			$("#slider").easySlider({
				auto: true, 
				continuous: true,
				numeric: true
			});
		});	
	</script>

<div id="homewrap">

<div id="slider">
 		<ul>
 			<li><a href="<?php echo biz_option('smallbiz_slider_lks1')?>">
 			<img src="<?php echo biz_option('smallbiz_slider_imgs1')?>" /></a></li>
 			
 			<li><a href="<?php echo biz_option('smallbiz_slider_lks2')?>">
 			<img src="<?php echo biz_option('smallbiz_slider_imgs2')?>" /></a></li>
 			
 			<li><a href="<?php echo biz_option('smallbiz_slider_lks3')?>">
 			<img src="<?php echo biz_option('smallbiz_slider_imgs3')?>" /></a></li>
 			
 			<li><a href="<?php echo biz_option('smallbiz_slider_lks4')?>">
 			<img src="<?php echo biz_option('smallbiz_slider_imgs4')?>" /></a></li>
 			</li>
 			
 			<li><a href="<?php echo biz_option('smallbiz_slider_lks5')?>">
 			<img src="<?php echo biz_option('smallbiz_slider_imgs5')?>" /></a></li>
 			</li>
 			
 		</ul>
 	</div>
</div>

<div id="home">
<div id="homepage-main-text">

<?php echo do_shortcode(biz_option('smallbiz_slider_main_text'))?>

</div>
</div>
</div>