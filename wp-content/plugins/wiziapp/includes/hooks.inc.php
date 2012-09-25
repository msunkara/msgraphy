<?php if (!defined('WP_WIZIAPP_BASE')) exit();
/**
* our integration with the wordpress CMS.
* this file attaches the plugin to events in wordpress by using filters and actions
* 
* @todo Figure out which method is better, one place of inside the class like contentHandler
* 
* @package WiziappWordpressPlugin
* @author comobix.com plugins@comobix.com
*/

//add_action('init', 'wiziapp_init');
//add_action('plugins_loaded', 'wiziapp_attach_hooks');
function wiziapp_attach_hooks(){
    add_action('admin_menu', 'wiziapp_setup_menu');
//    add_action('admin_init', 'wiziapp_register_settings');
    //add_action('admin_init', 'wiziapptest_admin_init');
    //add_action('activity_box_end', 'wiziapptest_stats_short');
    //add_action('rightnow_end', 'wiziapptest_rightnow');

    // Add the dashboard widget
    //add_action('wp_dashboard_setup', 'wiziapp_add_dashboard_widgets' );

    /* Add a custom column to the users table to indicate that the user 
    * logged in from his mobile device via our app
    * NOTE: Some plugins might not handle other plugins columns very nicely and cause the data not to show...
    */
    add_filter ('manage_users_columns', 'wiziapp_users_list_cols');
    add_filter ('manage_users_custom_column', 'wiziapp_handle_column', 10, 3);

    add_filter('cron_schedules', 'wiziapp_more_reccurences');

    add_action('new_to_publish', 'wiziapp_save_post');
    add_action('pending_to_publish', 'wiziapp_save_post');
    add_action('draft_to_publish', 'wiziapp_save_post');
    add_action('private_to_publish', 'wiziapp_save_post');
    add_action('future_to_publish', 'wiziapp_save_post');
    add_action('publish_to_publish', 'wiziapp_save_post');
    
    add_action('new_to_publish', 'wiziapp_publish_post');
    add_action('pending_to_publish', 'wiziapp_publish_post');
    add_action('draft_to_publish', 'wiziapp_publish_post');
    add_action('private_to_publish', 'wiziapp_publish_post');
    add_action('future_to_publish', 'wiziapp_publish_post');
    
    add_action('deleted_post', 'wiziapp_delete_post');
    add_action('trashed_post', 'wiziapp_delete_post');
    
    add_action('untrashed_post', 'wiziapp_recover_post');
    
    /**
    * @todo add this function to allow updates and no new post was published notifications
    add_action('publish_to_publish', 'wiziapp_publish_updated_post');
    */
    
    /**
    * Notice: publish_post might happen a few times, make sure we are only doing the action once
    * by removing the action once done
    */
    /**add_action('publish_post', 'wiziapp_save_post');
    add_action('publish_post', 'wiziapp_publish_post');*/

    add_action('wiziapp_daily_function_hook', 'wiziapp_push_daily_function');
    add_action('wiziapp_weekly_function_hook', 'wiziapp_push_weekly_function');
    add_action('wiziapp_monthly_function_hook', 'wiziapp_push_monthly_function');

    // Handle installation functions
    register_deactivation_hook(WP_WIZIAPP_BASE, array('WiziappInstaller', 'uninstall'));
    register_activation_hook(WP_WIZIAPP_BASE, array('WiziappInstaller', 'install'));
    
    // Update the cache when the settings are changed
    //add_action('updated_option', 'wiziapp_triggerCacheUpdate');
    //add_action('profile_update', 'wiziapp_triggerCacheUpdateByProfile');
    
    // add custom image size
    /**add_image_size('wiziapp-thumbnail', wiziapp_getThumbSize(), wiziapp_getThumbSize(), true );
    add_image_size('wiziapp-small-thumb', wiziapp_getSmallThumbWidth(), wiziapp_getSmallThumbHeight(), true );
    add_image_size('wiziapp-med-thumb', wiziapp_getMedThumbWidth(), wiziapp_getMedThumbHeight(), true );
    add_image_size('wiziapp-iphone', '320', '480', true);*/
}


if ( !defined('WP_WIZIAPP_HOOKS_ATTACHED') ) {
    define('WP_WIZIAPP_HOOKS_ATTACHED', TRUE);
    wiziapp_attach_hooks();
}