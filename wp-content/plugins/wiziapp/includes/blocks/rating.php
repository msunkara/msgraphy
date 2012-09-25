<?php
/**
* Allow the application to rate content on the CML
* 
* This webservice allows the application to rate content on the CMS according the CMS rating support
* 
* @package WiziappWordpressPlugin
* @subpackage WebServices
* @author comobix.com plugins@comobix.com
* 
* @param array $request the request object, contains the post_id
* @return array the usual header to indicate success or failure with the appropiated message
* 
* @todo Adds real rating integration
*/
function wiziapp_rate_content($request){
    @header('Content-Type: application/json'); 
    $post_id = $request[3];
    $rating = $_POST['rating'];
    $cms_user_id = $_POST['user_id'];
    $appToken = $_SERVER['HTTP_APPLICATION'];
    $udid = $_SERVER['HTTP_UDID'];

    $GLOBALS['WiziappLog']->write('info', "Sending a rating request with {$post_id}::{$rating}::{$cms_user_id} the post was: ".print_r($_REQUEST, TRUE)." the headers are: ".print_r(apache_request_headers(), TRUE), "wiziapp_rate_content");
    do_action('wiziapp_do_rating', $post_id, $rating, $cms_user_id);
    
    /**
    * @todo add a plugin method to report errors from actions, maybe a global?
    * 
    */
    $status = TRUE;
    
    // if the request doesn't contain all that we need - leave
    if ( !empty($post_id) && !empty($appToken) && !empty($udid) ){
        $header = array(
            'action' => 'rate',
            'status' => $status,
            'code' => ($status) ? 200 : 4004,
            'message' => ($status) ? '' : __('Problem updating the rating', 'wiziapp'),
        );
        
        echo json_encode($header);
        exit;
    } else {
        $GLOBALS['WiziappLog']->write('error', "Something in the request was missing: !empty($post_id) && !empty($appToken) && !empty($udid)", "remote");
    }
}

function wiziapp_the_rating_wrapper($request){
    global $post;
    $post_id = intval($request[3]);
    $post = get_post($post_id);
    wiziapp_the_rating();
}

add_action('wiziapp_do_rating', 'wiziapp_do_actual_rating', 1, 3);
/**
 * check witch rating plugin exist in wp and rate post
 * @param int $postId
 * @param int $rating
 * @param int $user_id
 * @return bool
 */
function wiziapp_do_actual_rating($postId, $rating=0, $user_id=0) {
    $GLOBALS['WiziappLog']->write('info', "Got a rating request with {$postId}::{$rating}::{$user_id}", "wiziapp_do_rating");
    $postId = intval($postId);
    $rating = intval($rating);
    if(filter_var($user_id, FILTER_VALIDATE_IP)){
        $ip = $user_id; $user_id = 0;
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
        $user_id = intval($user_id);
    }
    if ($rating < 1 && $rating > 5)return false;
    //polldaddy rating
    $id = get_option('pd-rating-posts-id');
    if(function_exists('polldaddy_show_rating_comments') &&  $id>0){
        $url = 'http://polldaddy.com/ratings/rate.php?';
        $url_query = array();
        $url_query['cmd'] = 'get';
        $url_query['id'] = get_option('pd-rating-posts-id');
        $url_query['uid'] = 'wp-post-'.$postId;
        $url_query['item_id'] = '_post_'.$postId;


        $link = $url . http_build_query($url_query);
        $matches = array();
        $get_content = wiziapp_general_http_request('',$link,'GET');
        $get_content = $get_content['body'];
        preg_match("/\.token='([a-z0-9]*)/", $get_content, $matches);
        $url_query['token'] = $matches[1];
        preg_match("/\.avg_rating = ([a-z0-9]*)/", $get_content, $matches);
        $url_query['avg'] = $matches[1];
        preg_match("/\.votes = ([a-z0-9]*)/", $get_content, $matches);
        $url_query['votes'] = $matches[1];

        $post = get_post($postId);
        $url_query['title'] = str_replace('&amp;', '&', $post->post_title);
        $url_query['permalink'] = $post->guid;
        $url_query['type'] = 'stars';
        $url_query['cmd'] = 'rate';
        $url_query['r'] = $rating;

        $link = $url . http_build_query($url_query);

        wiziapp_general_http_request('',$link,'GET');
        return true;
    }
    //GD Star rating
    global $gdsr;
    if(is_object($gdsr) && get_class($gdsr)== 'GDStarRating' ){
        $ua = $_SERVER["HTTP_USER_AGENT"];
        gdsrBlgDB::save_vote($postId, $user_id, $ip, $ua, $rating);
        gdsrFrontHelp::save_cookie($postId);
        do_action("gdsr_vote_rating_article", $postId, $user_id, $rating);
        return true;
    }
    //WP-PostRatings
    if (function_exists('process_ratings') && $postId>0 && $user_id>0){
        $_GET['rate'] = $rating;
        $_GET['pid'] = $postId;
        global $user_ID;
        $user_ID = $user_id;
        process_ratings();
        return true;
    }
    return false;
}

/**
 * check witch rating plugin exist in wp and return rating of post
 * @param int $postId - the post id
 * @return floatval - rating of post
 */
function wiziapp_get_rating($postId){
    $postId = intval($postId);
    //polldaddy rating
    $id = get_option('pd-rating-posts-id');
    if(function_exists('polldaddy_show_rating_comments') &&  $id>0){
        $url = 'http://polldaddy.com/ratings/rate.php?';
        $url_query = array();
        $url_query['cmd'] = 'get';
        $url_query['id'] = get_option('pd-rating-posts-id');
        $url_query['uid'] = 'wp-post-'.$postId;
        $url_query['item_id'] = '_post_'.$postId;


        $link = $url . http_build_query($url_query);
        $matches = array();
        $get_content = wiziapp_general_http_request('',$link,'GET');
        $get_content = $get_content['body'];
        preg_match("/\.avg_rating = ([a-z0-9]*)/", $get_content, $matches);
        return wiziapp_convert_rating($matches[1],5);
    }
    //GD Star rating
    global $gdsr;
    if(is_object($gdsr) && get_class($gdsr)== 'GDStarRating' && $postId>0 ){
        $rating = $gdsr->get_article_rating($postId);
        return wiziapp_convert_rating($rating[1]/$rating[0],$gdsr->o['stars']);
    }
    //WP-PostRatings
    if (function_exists('process_ratings') && function_exists('get_post_custom') && $postId>0){
        $post_ratings_data = get_post_custom($postId);
        return wiziapp_convert_rating($post_ratings_data['ratings_average'][0],intval(get_option('postratings_max')));
    }
}

function wiziapp_convert_rating($rating,$max){
    $k = $max/10;//coefficient
    $tmp_rating = $rating/$k;
    $converted_rating = round($tmp_rating)/2;
    return floatval($converted_rating);
}