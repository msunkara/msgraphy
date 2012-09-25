<?php 

if (!defined('WP_WIZIAPP_BASE')) 
    exit();
/**
* This file contains functions that handle the content from the CMS
* 
* @package WiziappWordpressPlugin
* @subpackage Core
* @author comobix.com plugins@comobix.com
* 
*/
/**
* This function allow us to have unique actions for 
* saving posts content. It warps the wiziapp_save_content
* 
* @param integer $post_id
*/
//function wiziapp_save_post($post_id){
function wiziapp_save_post($post){
    if (!is_object($post)){
        $post_id = $post;
    } else {
        $post_id = $post->ID;    
    }
    
    wiziapp_updateCacheTimestampKey(); 

    if ($post->post_type == 'page') {
        wiziapp_save_content($post_id, 'page');
    } else {
        wiziapp_save_content($post_id, 'post');
    }
}

function wiziapp_delete_post($post_id){
    wiziapp_updateCacheTimestampKey();
    $GLOBALS['WiziappDB']->delete_content_media($post_id, "post");
}

function wiziapp_recover_post($post_id){
    /**
    * @todo If the recovered post is published we need to rescan it...
    */
    $status = get_post_status($post_id);
    if ( $status == 'publish' ){
        wiziapp_updateCacheTimestampKey(); 
    
        wiziapp_save_content($post_id, 'post');
    }
}

/**
* This function allow us to have unique actions for 
* saving comments content. It warps the wiziapp_save_content
* 
* @param integer $comment_id
*/
function wiziapp_save_comment($comment_id){
    wiziapp_save_content($comment_id, 'comment');
}

/**
* This function allow us to have unique actions for 
* saving pages content. It wraps the wiziapp_save_content
* 
* @param integer $page_id
*/
function wiziapp_save_page($page_id){
    wiziapp_save_content($page_id, 'page');
}                                

/**
* This function prepare the content for processing
* it will receive the content we need to process and will force running
* wordpress filters on it. In addition it will call a filter: 'wiziapp_before_the_content'
* so that unknown 'the_content' filters can be remove before processing to make the content parsing simpler.
* 'wiziapp_before_the_content' takes one string parameter containing the $content itself and should return it
* 
* @param string $content the content to process
* @return string $content the content after processing
*/
function wiziapp_process_content($content){
    /* 
    * Some of the filters just echo content instead of adding it to the string...
    * avoid those issues by buffering the output
    */
    ob_start();
    
    wiziapp_removeKnownfilters();
    // Remove the theme settings for now, 
    $contentWidth = isset($GLOBALS['content_width']) ? $GLOBALS['content_width'] : null;
    $GLOBALS['content_width'] = 0;
    // We might need to remove some filters to be able to parse the content, if that is the case:
    $content = apply_filters('wiziapp_before_the_content', $content);
    $content = apply_filters('the_content', $content);
    
    $filteredContent = ob_get_contents(); 
    
    ob_end_clean(); 
    
    $content = $filteredContent . $content; 
    $content = str_replace(']]>', ']]&gt;', $content);

    // Restore the theme settings
    if ($contentWidth != null){
        $GLOBALS['content_width'] = $contentWidth;           
    }
    return $content;
}

function wiziapp_removeKnownfilters(){
    remove_filter('the_content', 'addthis_social_widget');
    remove_filter('the_content', 'A2A_SHARE_SAVE_to_bottom_of_content', 98);
}

/**
* Saves media found in the requested content in a special media table
* for later retrieval
* 
* @param integer $id the content id
* @param string $type can be post/comment/page
*/
function wiziapp_save_content($id, $type="post"){
    global $more;

    $more = 1;

    $content = '';

    if ($type == 'post'){
        $postslist = get_posts("include={$id}&numberposts=1");
        // For posts we need to force the loop
        global $post, $wp_query;
        $wp_query->in_the_loop = true;
        foreach ($postslist as $post){
            setup_postdata($post);
            $content .= get_the_content();       
        }
    } elseif ($type == 'comment'){
        $content_item = get_comment($id);
        // Only processed approved comments
        if ($content_item->comment_approved){
            $content = $content_item->comment_content;    
        } else {
            return FALSE;
        }
    } elseif ($type == 'page'){
        $content_item = get_page($id);
        $content = $content_item->post_content;
    }

    // Handle the special content processing 
    $content = wiziapp_process_content($content);
    
    // Remove the existing media related to this post to avoid having duplicates and unrelated leftovers
    $GLOBALS['WiziappDB']->delete_content_media($id, $type);                 
    
    // Extract the media items with the media extractor
    $extractor = new WiziappMediaExtractor($content);
    
    // Save the images
    $images = $extractor->getImages();
    wiziapp_saveMediaDetails('image', $images, $id, $type);
    
    // Save the videos
    $videos = $extractor->getVideos();
    wiziapp_saveSpecialMediaDetails('video', $videos, $id, $type);
    
    // Save the audios
    $audios = $extractor->getAudios();
    wiziapp_saveSpecialMediaDetails('audio', $audios, $id, $type);
    
    // Mark the content as processed to avoid processing when already processed
    if ($type == 'post' || $type == 'page'){
        add_post_meta($id, 'wiziapp_processed', true, true);
    } 
}

/**
* Preparing the media information to be saved in the database and then triggers the database saving
* 
* @param string $type the type of media we are saving. can be audio/image/video
* @param array $items the array of media items we are saving
* @param id $content_id the id of the content itself (post/page)
* @param string $content_type the content type, can be post / page
*/
function wiziapp_saveMediaDetails($type, $items, $content_id, $content_type){
    if (count($items) == 0) {
        return FALSE;
    }
    $result = FALSE;
    /**
    * We now do 1 sql insert per post instead of 1 per media
    */
    //for($a = 0, $total = count($items); $a < $total; ++$a){
//        $obj = $items[$a]['obj'];
//        $html = $items[$a]['html'];
//        if ($content_type == 'post'){
//            $result = $GLOBALS['WiziappDB']->update_post_media($content_id, $type, $obj, $html);                 
//        } elseif ($content_type == 'page'){
//            $result = $GLOBALS['WiziappDB']->update_page_media($content_id, $type, $obj, $html);                 
//        }
//    }
    
    $result = $GLOBALS['WiziappDB']->add_content_medias($type, $items, $content_id, $content_type);
    
    return $result;
}

function wiziapp_saveSpecialMediaDetails($type, $items, $content_id, $content_type){
    if (count($items) == 0) {
        return FALSE;
    }
    $result = FALSE;
    
    for($a = 0, $total = count($items); $a < $total; ++$a){
        $obj = $items[$a]['obj'];
        $html = $items[$a]['html'];
        if ($content_type == 'post'){
            $result = $GLOBALS['WiziappDB']->update_post_media($content_id, $type, $obj, $html);                 
        } elseif ($content_type == 'page'){
            $result = $GLOBALS['WiziappDB']->update_page_media($content_id, $type, $obj, $html);                 
        }
    }
    
    return $result;
}

/**
* Called from the install method, to install the base content
* that will be used at first for the simulator and for building the cms
* profile
* 
* After the initial processing the user will be able to trigger the processing
* via his plugin control panel or when a post is requested for the first time
*/
function wiziapp_generate_latest_content(){
    global $wpdb;
    $done = false;
    
    $GLOBALS['WiziappLog']->write('info', "Parsing the latest content", "content");
    // Parse the latest posts
    $number_recents_posts = WiziappConfig::getInstance()->post_processing_batch_size;
    $recent_posts = wp_get_recent_posts($number_recents_posts);
    $last_post = -1;
    foreach($recent_posts as $post){
        $post_id = $post['ID'];
        $GLOBALS['WiziappLog']->write('info', "Processing post: {$post_id}", 
            'content.wiziapp_generate_latest_content');
        wiziapp_save_post($post_id);
        
        $last_post = $post_id;
    }
    
    $GLOBALS['WiziappLog']->write('info', "Processing all pages", 'content');
    $pages = get_all_page_ids(); 
    for($p = 0, $total = count($pages); $p < $total; ++$p){
        wiziapp_save_page($pages[$p]);
    }
    
    // Save the fact that we processed  $number_recents_posts and if the number of posts 
    // in the blog is bigger, we need to continue
    $numposts = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'publish'");
    if ($numposts <= $number_recents_posts){
        $last_post = -1;
    }
    add_option("wiziapp_last_processed", $last_post);    
    $GLOBALS['WiziappLog']->write('info', "Finished parsing initial content", 'content');
    
    return $done;
}

function wiziapp_triggerCacheUpdateByProfile($user, $old_user_data){
    wiziapp_updateCacheTimestampKey();         
}

function wiziapp_triggerCacheUpdate($option, $oldvalue, $_newvalue){
    // Once per request...
    if (strpos($option, '_') !== 0 && strpos($option, 'wiziapp') === FALSE){
        remove_action('update_option', 'wiziapp_triggerCacheUpdate');
        wiziapp_updateCacheTimestampKey();
    }
}

function wiziapp_updateCacheTimestampKey(){
    WiziappConfig::getInstance()->last_recorded_save = time();   
}

function wiziapp_getCacheTimestampKey(){
    return WiziappConfig::getInstance()->last_recorded_save;
}