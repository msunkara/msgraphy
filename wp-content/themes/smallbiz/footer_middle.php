<div id="footer-middle-sidebar">
<ul>
	<?php if ( function_exists('dynamic_sidebar') && dynamic_sidebar('Footer Middle') ) : else : ?>
		<li class="first featured">
		
		<h2><?php $page = get_page_(biz_option('smallbiz_feature_page_2')); echo $page->post_title?></h2>
				<p style="color:#<?php echo biz_option('smallbiz_page_summary_2_color')?>;">
				<?php echo biz_option('smallbiz_feature_page_summary_2');?></p>
				<p><a class="readon" href="<?php bloginfo('url'); ?>/?page_id=<?php echo $page->ID ?>">continue reading...</a>
				</p>
				
				</li>
<?php endif; ?>
</ul>
</div>