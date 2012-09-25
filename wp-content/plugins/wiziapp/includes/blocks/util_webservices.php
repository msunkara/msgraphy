<?php


function wiziapp_getCommentsCount($post_id){
    $comments = get_approved_comments($post_id); 
    
    /**
    * @todo check for failures
    */
    $status = TRUE;
    $message = '';
    
    $header = array(
        'action' => 'commentsCount',
        'status' => $status,
        'code' => ($status) ? 200 : 4004,
        'message' => $message,
    );
            
    echo json_encode(array('header' => $header, 'count' => count($comments)));
    exit;
}