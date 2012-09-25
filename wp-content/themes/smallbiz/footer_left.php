<div id="footer-left-sidebar">
<ul>
	<?php if ( function_exists('dynamic_sidebar') && dynamic_sidebar('Footer Left') ) : else : ?>
			<li class="featured">
			
				<h2><?php $page = get_page_(biz_option('smallbiz_feature_page_1')); echo $page->post_title?></h2>
				<p style="color:#<?php echo biz_option('smallbiz_page_summary_1_color')?>;">
				<?php echo biz_option('smallbiz_feature_page_summary_1');?></p>
					<p><a class="readon" href="<?php bloginfo('url'); ?>/?page_id=<?php echo $page->ID ?>">continue reading...</a>
				</p>
				</li>
<?php endif; ?>
</ul>
</div>