<?php
/**
* @package WiziappWordpressPlugin
* @subpackage PushNotifications
* @author comobix.com plugins@comobix.com
*/
/*
 * Happens every time a post is published (also when it is updated after being published)
 */
//function wiziapp_publish_post($post_id){
function wiziapp_publish_post($post){
    $post_id = $post->ID;
    // @todo Get this from the saved options
    $tabId = WiziappConfig::getInstance()->main_tab_index;
    
    if ( !WiziappConfig::getInstance()->notify_on_new_post ){
        $GLOBALS['WiziappLog']->write('info', "We are set not to notify on new post...", 
                                        "notifications_events.wiziapp_publish_post");
        return;
    }
    $GLOBALS['WiziappLog']->write('info', "Notifying on new post", 
                                        "notifications_events.wiziapp_publish_post");
    $request = null;
    if ( WiziappConfig::getInstance()->aggregate_notifications ){
        $GLOBALS['WiziappLog']->write('info', "We need to aggregate the messages", 
                                        "notifications_events.wiziapp_publish_post");
        // We might need to send this later...
        // let's check
        if (!isset(WiziappConfig::getInstance()->counters)) {
            WiziappConfig::getInstance()->counters = array('posts'=>0);
        }
        // Increase the posts count
        WiziappConfig::getInstance()->counters['posts'] += 1;
        
        // If the sum is set and not 0 we need to aggragate by posts count
        if ( WiziappConfig::getInstance()->aggregate_sum ){
            // Have we reached or passed our trashhold
            if ( WiziappConfig::getInstance()->counters['posts'] >= WiziappConfig::getInstance()->aggregate_sum ){
                // We need to notify on all the new posts
                $sound = WiziappConfig::getInstance()->trigger_sound;
                $badge = (WiziappConfig::getInstance()->show_badge_number) ? WiziappConfig::getInstance()->counters['posts']: 0;
                $users = 'all';
                $request = array(
                    'type'=>1,
                    'sound'=>$sound,
                    'badge'=>$badge,
                    'users'=>$users,
                );
                if ( WiziappConfig::getInstance()->show_notification_text ){
                    $request['content'] = urlencode(stripslashes(WiziappConfig::getInstance()->counters['posts'].' new posts published'));
                    $request['params'] = "{\"tab\": \"{$tabId}\"}";
                }
                // reset the counter
                WiziappConfig::getInstance()->counters['posts'] = 0;
            } 
        }
        
    } else { // We are not aggragating the message
        $sound = WiziappConfig::getInstance()->trigger_sound;
        $badge = WiziappConfig::getInstance()->show_badge_number;
        $users = 'all';
        $request = array(
            'type'=>1,
            'sound'=>$sound,
            'badge'=>$badge,
            'users'=>$users,
        );
        if ( WiziappConfig::getInstance()->show_notification_text ){
            $request['content'] = urlencode(stripslashes(__('New Post Published', 'wiziapp')));
            //$request['params'] = "{tab: \"{$tabId}\"}";
            $request['params'] = "{\"tab\": \"{$tabId}\"}";
        }
    }
    // Done setting up what to send, now send it..
    
    // Make sure we have a reason to even send this message
    if ( $request == null || (!$request['sound'] && !$request['badge'] && !$request['content'] )){
        return;
    }
    // We have something to send
    $GLOBALS['WiziappLog']->write('info', "About to send a single notification event...", 
                                        "notifications_events.wiziapp_publish_post");
    $response = wiziapp_http_request($request, '/push', 'POST');
}

function wiziapp_push_interval_function($period, $period_text){
    if ( !WiziappConfig::getInstance()->notify_on_new_post ){
        return;
    }
    $request = null;
    $tabId = WiziappConfig::getInstance()->main_tab_index;
    if ( WiziappConfig::getInstance()->aggregate_notifications && WiziappConfig::getInstance()->notify_periods == $period){
        if (!isset(WiziappConfig::getInstance()->counters)) {
            // We don't have any counters in place yet, no need to run
            return;
        }
        if ( WiziappConfig::getInstance()->counters['posts'] > 0 ){
            $sound = WiziappConfig::getInstance()->trigger_sound;
            $badge = (WiziappConfig::getInstance()->show_badge_number) ? WiziappConfig::getInstance()->counters['posts']: 0;
            $users = 'all';
            $request = array(
                'type'=>1,
                'sound'=>$sound,
                'badge'=>$badge,
                'users'=>$users,
            );
            if ( WiziappConfig::getInstance()->show_notification_text ){
                $request['content'] = urlencode(stripslashes(WiziappConfig::getInstance()->counters['posts'].__(' new posts published ', 'wiziapp').$period_text));
                $request['params'] = "{\"tab\": \"{$tabId}\"}";
            }
            // reset the counter
            WiziappConfig::getInstance()->counters['posts'] = 0;
        }
    }
}

function wiziapp_push_daily_function(){
    wiziapp_push_interval_function('day', __('today', 'wiziapp'));
}
function wiziapp_push_weekly_function(){
    wiziapp_push_interval_function('week', __('this week', 'wiziapp'));
}
function wiziapp_push_monthly_function(){
    wiziapp_push_interval_function('month', __('this month', 'wiziapp'));
}
