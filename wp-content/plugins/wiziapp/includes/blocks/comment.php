<?php if (!defined('WP_WIZIAPP_BASE')) exit();
/**
* @package WiziappWordpressPlugin
* @subpackage AppWebServices
* @author comobix.com plugins@comobix.com
*/
/**
* Web service that allows the application to add a comment to another comment or a post
* if the application is sending the cms user id, the web service will connect the comment 
* to the right user, to get the user ID see the WebServices::logic method
* the webservice response is in JSON format and includes the usual header,
* plus a message indicating if the comment entered a moderation queue or submitted successfully
* the application should show the returning message to the user
* 
* @param array $request the array containing the request
*/
function wiziapp_add_comment($request){
    @header('Content-Type: application/json'); 
    $name = $_POST['name'];
    $email = $_POST['email'];
    $content = $_POST['content'];
    $user_id = $_POST['cms_user_id'];
    $post_id = $_POST['post_id'];
    $comment_id = $_POST['comment_id'];
    if ( empty($comment_id) ){
        $comment_id = 0;
    }
    $appToken = $_SERVER['HTTP_APPLICATION'];
    $udid = $_SERVER['HTTP_UDID'];
    //$GLOBALS['WiziappLog']->write('info', 'The server vars are '.print_r($_SERVER, true), 'remote');
    //$GLOBALS['WiziappLog']->write('info', 'The post vars are '.print_r($_POST, true), 'remote');
    // if the request doesn't contain all that we need - leave
    if ( !empty($appToken) && !empty($udid) && !empty($content) && !empty($post_id)){
        //is_wp_error
        $result = array();
        $commentData = array(
            'comment_post_ID'=>$post_id,
            'comment_content'=>$content,
            'comment_author'=>$name,
            'comment_author_email'=>$email,
            'comment_parent'=>$comment_id,
        );
        if ( !empty($user_id) ){
            $commentData['user_ID'] = $user_id;
            $user = get_userdata($user_id);
            $commentData['comment_author_email'] = $user->user_email;
        }
        
        if ( !empty($_POST['parent_id']) ){
            $commentData['comment_parent'] = $_POST['parent_id'];
        }
        
        $header = array(
            'action' => 'add_comment',
        );
        $result = array();
        /** 
        * Wordpress will kill the script if the comment is a duplicated... 
        * so we better perform this check before moving to wordpress
        */
        $dup = wiziapp_simulate_wp_dup_check($commentData);
        if ( !$dup ){
            $comment_id = wp_new_comment($commentData);
            // Check the comment status
            $comment = get_comment($comment_id);
            $moderated = TRUE;
            $message = '';
            if ( $comment->comment_approved == 1 ){
                $moderated = FALSE;
                $message = __('The comment was submitted successfully', 'wiziapp');
            } else {
                $message = __('The comment entered the moderation queue', 'wiziapp');
            }
            
            $result = array("comment" => array(
                //"id" => $comment->comment_ID,
                "commentsURL" => wiziapp_buildPostCommentsLink($post_id),
                "postURL" => wiziapp_buildPostLink($post_id),
                //"moderated" => $moderated,
                "message" => $message,
            ));
            $status = TRUE;                
            $header['message'] = '';
            $header['code'] = 200;    
        } else {
            $status = FALSE;
            $result = array(
                'message' => __('Duplicate comment detected; it looks as though you have already said that!', 'wiziapp'),
            );
            $header['message'] = __('Duplicate comment detected;', 'wiziapp');
            $header['code'] = 5001;
            
        }
        $header['status'] = $status;            
        echo json_encode(array_merge(array('header' => $header), $result));
        exit;
    } else {
        $GLOBALS['WiziappLog']->write('error', "Something in the request was missing: !empty($appToken) && !empty($udid) && !empty($content) && !empty($post_id)", "remote");
    }   
}

/**
* simulates wordpress duplicates comments check
* 
* @param array $commentData and array containing: comment_post_ID, comment_author,comment_author_email, comment_content, comment_parent
* @returns boolean $duplicated
*/
function wiziapp_simulate_wp_dup_check($commentData){
    global $wpdb;
    extract($commentData, EXTR_SKIP);
    $duplicated = FALSE;
    // Simple duplicate check
    // expected_slashed ($comment_post_ID, $comment_author, $comment_author_email, $comment_content)
    $dupe = "SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = '$comment_post_ID' AND comment_approved != 'trash' AND ( comment_author = '$comment_author' ";
    if ( $comment_author_email )
        $dupe .= "OR comment_author_email = '$comment_author_email' ";
    $dupe .= ") AND comment_content = '$comment_content' LIMIT 1";
    if ( $wpdb->get_var($dupe) ) {
        $duplicated = TRUE;
    }
    return $duplicated;
}