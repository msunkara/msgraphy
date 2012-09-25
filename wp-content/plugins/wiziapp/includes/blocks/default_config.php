<?php
/**
* @package WiziappWordpressPlugin
* @subpackage Configuration
* @author comobix.com plugins@comobix.com
* 
* for changes to take effect, we must re-install the plugin
*/

function wiziapp_getDefaultConfig(){
    $envSettings = array();
    require_once('conf/' . WIZIAPP_ENV . '_config.inc.php');
    
    $settings = array(
        // Push notifications
        'show_badge_number' => 1,
        'trigger_sound' => 1,
        'show_notification_text' => 1,
        'notify_on_new_post' => 1,
        'aggregate_notifications' => 0,
        'aggregate_sum' => 1,
        'notify_periods' => 'day',
        
        // Rendering
        'main_tab_index' => 't1',
        'sep_color' => '#bbbbbbff',
/**        'med_thumb_height' => 84,
        'med_thumb_width' => 112,
        'small_thumb_height' => 55,
        'small_thumb_width' => 73,
        'comments_avatar_size' => 50,*/
        
        'full_image_height' => 480,
        'full_image_width' => 320,
        'multi_image_height' => 320, // 350-30 pixels for the scroller and sorrounding space
        'multi_image_width' => 298, //300-2 pixels for the rounded border
        'images_thumb_height' => 55,
        'images_thumb_width' => 73,
        'posts_thumb_height' => 55,
        'posts_thumb_width' => 73,
        'featured_post_thumb_height' => 55,
        'featured_post_thumb_width' => 73,
        'limit_post_thumb_height' => 135,
        'limit_post_thumb_width' => 135,
        'comments_avatar_height' => 58,
        'comments_avatar_width' => 58,
        'album_thumb_width' => 64,
        'album_thumb_height' => 51,
        'video_album_thumb_width' => 64,
        'video_album_thumb_height' => 51,
        'audio_thumb_width' => 60,
        'audio_thumb_height' => 60,
        
        
        'thumb_size' => 80,
        'use_post_preloading' => TRUE,
        'comments_list_limit' => 20,
        'links_list_limit' => 20,
        'pages_list_limit' => 20,
        'posts_list_limit' => 10,
        'categories_list_limit' => 20,
        'tags_list_limit' => 20,
        'videos_list_limit' => 20,
        'audios_list_limit' => 20,
        
        'max_thumb_check' => 2,
        'count_minimum_for_appear_in_albums' => 5,
        //'minimum_width_for_appear_in_albums' => 90,
//        'minimum_height_for_appear_in_albums' => 90,
        
        // API
        'app_token' => '',
        'app_id' => 0,
        
        // Theme
        'allow_grouped_lists' => FALSE,
        'zebra_lists' => TRUE,
        'theme_name' => 'iphone',
        'wiziapp_theme_name' => 'default',
        
        // app
        'app_description' => 'Here you will see the description about your app. You will be able to provide the description in the app store information form (step 3).',
        'app_name' => get_bloginfo('name'),
        'app_icon' => '',
        'version' => '0.2',
        'icon_url' => '',
        
        // Screens titles
        'categories_title' => 'Categories',
        'tags_title' => 'Tags',
        'albums_title' => 'Albums',
        'videos_title' => 'Videos',
        'audio_title' => 'Audio',
        'links_title' => 'Links',
        'pages_title' => 'Pages',
        'favorites_title' => 'Favorites',
        'about_title' => 'About',
        'search_title' => 'Search Results',
        'archive_title' => 'Archives',
        
        // General 
        'last_recorded_save' => time(),
        'reset_settings_on_uninstall' => TRUE,
        'search_limit' => 50,
        'search_limit_pages' => 20,
        'post_processing_batch_size' => 3,
        'finished_processing' => FALSE,
        'configured' => FALSE,
        'app_live' => FALSE,
        'appstore_url' => '',
        'appstore_url_timeout' => 1, //How many days will pass before we will show the user the "download app from appstore" confirmation alert again, 0 will make it not display at all
        'email_verified' => FALSE,
        'show_email_verified_msg' => TRUE,
        'wiziapp_showed_config_once' => FALSE,
    );
    
    return array_merge($settings, $envSettings);
}