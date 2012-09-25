<?php if (!defined('WP_WIZIAPP_BASE')) exit();
function wiziapp_user_registration() {
    if(!empty($_POST)) {
        $_REQUEST['action'] = '';
        $username = $_REQUEST['user_login'];
        $email = $_REQUEST['user_email'];
        
        ob_start();
        require_once ABSPATH . 'wp-includes/registration.php';
        require_once ABSPATH . 'wp-login.php';
        ob_end_clean();
        
        $GLOBALS['WiziappLog']->write('info', 'Before register user: ' . $username, 'account.wiziapp_user_registration');
        $user_id = register_new_user($username, $email);
        $GLOBALS['WiziappLog']->write('info', 'After register user: ' . $username, 'account.wiziapp_user_registration');
        
        if (is_int($user_id)) {
//            $status = TRUE;
            $result = __('Registration successfull', 'wiziapp');   
        } else {
            $result = implode('<br>', $user_id->get_error_messages());
//            $status = FALSE;
        }
    } else {
        $result = '';
    }

//    $header = array(
//        'action' => 'register',
//        'status' => $status,
//        'code' => ($status) ? 200 : 4004,
//        'message' => ($status) ? '' : 'Invalid registartion',
//    );
//    echo json_encode(array_merge(array('header' => $header), $result));
//    exit;
    return ($result);
}
function wiziapp_user_forgot_password() {
    if(!empty($_POST)) {
        $_REQUEST['action'] = '';
        $_POST['user_login'] = $_REQUEST['user_login'];
        $_POST['user_email'] = $_REQUEST['user_email'];
        
        ob_start();
        require_once ABSPATH . 'wp-includes/registration.php';
        require_once ABSPATH . 'wp-login.php';
        ob_end_clean();
        
        $status = retrieve_password();
        if($status === true) {
//            $status = TRUE;
            $result = __("Success", 'wiziapp');    
        } else {
            $result = implode('<br>', $status->get_error_messages());
//            $status = FALSE;
        }
    } else {
      $result = '';   
    }

//    $header = array(
//        'action' => 'forgot_password',
//        'status' => $status,
//        'code' => ($status) ? 200 : 4004,
//        'message' => ($status) ? '' : 'Invalid forgot password',
//    );
    return ($result);
}
?>