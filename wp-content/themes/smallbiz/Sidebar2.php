<?php
/*
Template Name: Optional Sidebar-2
*/
if (is_front_page() && (get_option('smallbiz_mobile-layout-enabled') && $GLOBALS["smartphone"])){
    include("page.php");
} else { 
?>
<?php get_header(); ?>
<style>.entry-title{padding-left:0px;}</style>
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<div class="post" id="post-<?php the_ID(); ?>">
		<div class="entry">
		
		<?php if(!is_page('home')) :?><h2 class="entry-title"><?php the_title(); ?></h2><?php endif; ?>
							<?php global $more; $more = false; ?>
<?php the_content('...Continue Reading'); ?>
<?php $more = true; ?>
				<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>
			</div>
		</div>
		<?php endwhile; endif; ?>
	
<?php include(TEMPLATEPATH."/right-sidebar-2.php");?>
<?php get_footer(); ?>
<?php } ?>