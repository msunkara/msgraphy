<?php
/**
* @package WiziappWordpressPlugin
* @subpackage AdminDisplay
* @author comobix.com plugins@comobix.com
*/

// Create the function use in the action hook
function wiziapp_add_dashboard_widgets() {
    add_meta_box('wiziapp_dashboard_widget', 'WiziApp Widget' , 'wiziapp_dashboard_widget_function', 'dashboard', 'side', 'core' );
    // Globalize the metaboxes array, this holds all the widgets for wp-admin
    global $wp_meta_boxes;
    // Get the regular dashboard widgets array 
    // (which has our new widget already but at the end)

    $side_dashboard = $wp_meta_boxes['dashboard']['side']['core'];
    // Backup and delete our new dashbaord widget from the end of the array
    $wiziapp_widget_backup = array('wiziapp_dashboard_widget' => $side_dashboard['wiziapp_dashboard_widget']);
    unset($side_dashboard['wiziapp_dashboard_widget']);
    
    // Merge the two arrays together so our widget is at the beginning
    $sorted_dashboard = array_merge($wiziapp_widget_backup, $side_dashboard);
    
    // Save the sorted array back into the original metaboxes 
    $wp_meta_boxes['dashboard']['side']['core'] = $sorted_dashboard;
} 

function wiziapp_dashboard_widget_function(){
    if ( empty(WiziappConfig::getInstance()->app_token) ){
        echo __("Error activating the wiziapp plugin", 'wiziapp');
    } else {
        global $current_user;
        get_currentuserinfo();
        $perms = wiziapp_dirs_perms();
        echo __('Needed dirs writable: ', 'wiziapp'). (($perms['cache'] && $perms['logs'])?'<span style="color:green;">'.__('ok', 'wiziapp').'</span>':'<span style="color:red;">'.__('error', 'wiziapp').'</spna>').'<br />';
        
        echo __('Cache time: ', 'wiziapp').date('F j, Y H:i:s', WiziappConfig::getInstance()->last_recorded_save);
    }
}
