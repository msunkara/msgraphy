<?php
/**
 * SmallBiz default page theme. Drawing the page/post contents is done in page_while.php.
 *
 * @package WordPress
 * @subpackage Expand2Web SmallBiz
 * @since Expand2Web SmallBiz 3.3
 */
 
 // shared functions to help repair things that get broken:
 include("pre-header.inc.php");
 
 // If we have a custom homepage, the "active page" may be incorrect -- so load a different template instead: 
 $real_template = get_post_meta($real_id, '_wp_page_template', true);
 $loaded_template = false;
 if (is_front_page() && (get_option('smallbiz_mobile-layout-enabled') && $GLOBALS["smartphone"])){
    // Don't try to load another template if this is the mobile front page.     
 } else {
     if(is_front_page() &&  $wp_query->get_queried_object()->ID != (get_option('smallbiz_page_on_front'))){
        $real_id = (get_option('smallbiz_page_on_front'));    
        $real_template = get_post_meta($real_id, '_wp_page_template', true);
        if($real_template && $real_template != "default"){
            include_once $real_template;
            $loaded_template=  true;
        }
     } 
 }
 
 if(!$loaded_template){ // page template check start
   $real_template = get_post_meta($real_page_id, '_wp_page_template', true); 
?>
<?php get_header(); ?>
<?php 
 $preview_special = false;
 if(isset($_REQUEST['preview'])){
    /* If we are in a preview (hack)... */
    if(isset($_REQUEST['blog'])) { 
        include("posts.php");
        $preview_special = true;
    } else if(isset($_REQUEST['findus'])) {
        include("findeus_page.php");
        $preview_special = true;
    } else if(isset($_REQUEST['contact'])) {
        include("contact_page.php");
        $preview_special = true;
    }
 } 
 // If we loaded a special page, do nothing. Otherwise...
 if ( $preview_special ) {
    ?>
<?php } else if (is_front_page()) {
    /* If this is the front page, check if the the homepage is the smallbiz one...  */
    if( (get_option('smallbiz_mobile-layout-enabled') && $GLOBALS["smartphone"]) ||
        (get_option('smallbiz_page_on_front') == get_option('smallbiz_homepage_id'))){
        include('layouts/'.smallbiz_get_current_layout().'/frontpage.php');
    } else {   
        /* ... or if the homepage is set to something else; if so, load that page and display it: */    
        $id = get_option('smallbiz_page_on_front');
        $recent = new WP_Query("page_id=$id"); while($recent->have_posts()) : $recent->the_post();?>
            <?php
               $pageClass = preg_replace("/[^a-z\d]/i", "", strtolower($recent->post->post_title));
               include("page_while.php"); 
            ?>
        <?php endwhile; ?>        
     <?php } ?>
<?php } else {    
    /* ... or, if it isn't a home page, draw the page normally. */
    ?>
    <?php
        // Do we want this to change depending on the page, or to be the "blog" class for everything? used as a prefix:
        $pageClass = preg_replace("/[^a-z\d]/i", "", strtolower(wp_title(false,false))); 
    ?>
   <?php if (have_posts()) : while (have_posts()) : the_post(); 
            include("page_while.php"); 
         endwhile; endif; 
   ?>
<?php } ?>
<?php
// Sidebar checks -- the first should not be needed.
if(!$hideSidebar && $real_template != "no-sidebar.php"){
    if($wp_query->is_posts_page=="1" || $real_template == ('posts.php') ){
        include(TEMPLATEPATH."/sidebar-blog.php");
    } else {
        get_sidebar();
    }
}
?>
<?php get_footer(); 
}// end of page template check
?>
