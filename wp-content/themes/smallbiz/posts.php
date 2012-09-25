<?php
/*
Template Name: List Posts
 */
 
if (is_front_page() && (get_option('smallbiz_mobile-layout-enabled') && $GLOBALS["smartphone"])){
    include("page.php");
} else { 
?>
<?php get_header(); ?>
<?php
if(!$paged){
    $paged = get_query_var( 'paged' );
}
// Occurs when we are set as the homepage in Smallbiz:
if(!$paged){
    if($_REQUEST["paged"]){
        $paged = $_REQUEST["paged"];
    } else if(strpos($_SERVER['REQUEST_URI'], "/page/") !== false){
        // Permlink structure not set to default, unique way of figuring it out.
        // Figuring out why get_query_var fails would be better.
        $page_url = $_SERVER['REQUEST_URI'];
        $page_url = substr($page_url, strrpos($_SERVER['REQUEST_URI'], "/page/")+6);
        $page_url = substr($page_url, 0, strpos($page_url, "/") );
        $paged = intVal($page_url);        
    }
}
?>
<?php query_posts("posts_per_page=".get_option("posts_per_page")."&paged=$paged");?>
		<?php if (have_posts()) : ?>
		<?php while (have_posts()) : the_post(); ?>
		<div id="blogpost">
		
				<div id="post-title"><h2 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2></div>
				
				<div id="post-date"><small><?php the_time('l, F jS, Y') ?></small></div>
				<div class="entry">
							<?php global $more; $more = false; ?>
<?php the_content('Continue Reading...'); ?>
<?php $more = true; ?>
				</div>
				<p class="postmetadata"><?php the_tags('Tags: ', ', ', '<br />'); ?> Posted in <?php the_category(', ') ?> | <?php edit_post_link('Edit', '', ' | '); ?>  <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?></p>
			</div>
		<?php endwhile; ?>
		<div class="navigation">
			<div class="alignleft"><?php next_posts_link('Older Entries »', 0); ?>
</div>
			<div class="alignright"><?php previous_posts_link('« Newer Entries', 0) ?>
</div>
		</div>
	<?php else : ?>
		<h2 class="center">You don't have any blog posts yet. Create one and it will display here!</h2>
		<?php include (TEMPLATEPATH . '/searchform.php'); ?>
	<?php endif; ?>
<?php include(TEMPLATEPATH."/sidebar-blog.php");?>
<?php get_footer(); ?>
<?php } ?>