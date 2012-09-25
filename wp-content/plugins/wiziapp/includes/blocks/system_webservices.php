<?php
/**
* @package WiziappWordpressPlugin
* @subpackage AdminWebServices
* @author comobix.com plugins@comobix.com
*/

/**
* Authenticate the request against the plugin token. 
* if the authentication fail, throw 404.
* 
*/
function wiziapp_checkSystemAuth(){
    // Verify the plugin token against our app_token
    $token = $_SERVER['HTTP_PLUGIN'];

    if ( $token != WiziappConfig::getInstance()->app_token ){
        header("HTTP/1.0 404 Not Found");
        exit;
    }
    return TRUE;
}

function wiziapp_checkInstalledPlugin(){
    $header = array(
        'action' => 'checkInstalledPlugin',
        'status' => TRUE,
        'code' => 200,
        'message' => '',
    );
            
    echo json_encode(array('header' => $header, 'version' => WIZIAPP_P_VERSION));
    exit;   
}

function wiziapp_updateThumbsConfiguration(){
    wiziapp_checkSystemAuth();

    $thumbsJson = stripslashes($_POST['settings']);
    $thumbs = json_decode($thumbsJson, TRUE);
    $message = '';
    
    $GLOBALS['WiziappLog']->write('info', "The params are ".print_r($_POST, TRUE), 
                                        "system_webservices.wiziapp_updateThumbsConfiguration");
                                        
    if ( !$thumbs ){
        $status = FALSE;
        $message = 'Unable to decode thumbs configuraiton: '.$thumbsJson;
    } else {
        WiziappConfig::getInstance()->startBulkUpdate();
        // The request must be with the exact keys
        WiziappConfig::getInstance()->full_image_height = $thumbs['full_image_height'];
        WiziappConfig::getInstance()->full_image_width = $thumbs['full_image_width'];
        
        WiziappConfig::getInstance()->images_thumb_height = $thumbs['images_thumb_height'];
        WiziappConfig::getInstance()->images_thumb_width = $thumbs['images_thumb_width'];
        
        WiziappConfig::getInstance()->posts_thumb_height = $thumbs['posts_thumb_height'];
        WiziappConfig::getInstance()->posts_thumb_width = $thumbs['posts_thumb_width'];
        
        WiziappConfig::getInstance()->featured_post_thumb_height = $thumbs['featured_post_thumb_height'];
        WiziappConfig::getInstance()->featured_post_thumb_width = $thumbs['featured_post_thumb_width'];
        
        WiziappConfig::getInstance()->mini_post_thumb_height = $thumbs['mini_post_thumb_height'];
        WiziappConfig::getInstance()->mini_post_thumb_width = $thumbs['mini_post_thumb_width'];
        
        WiziappConfig::getInstance()->comments_avatar_height = $thumbs['comments_avatar_height'];
        WiziappConfig::getInstance()->comments_avatar_width = $thumbs['comments_avatar_width'];
        
        WiziappConfig::getInstance()->album_thumb_width = $thumbs['album_thumb_width'];
        WiziappConfig::getInstance()->album_thumb_height = $thumbs['album_thumb_height'];
        
        WiziappConfig::getInstance()->video_album_thumb_width = $thumbs['video_album_thumb_width'];
        WiziappConfig::getInstance()->video_album_thumb_height = $thumbs['video_album_thumb_height'];
        
        WiziappConfig::getInstance()->audio_thumb_width = $thumbs['audio_thumb_width'];
        WiziappConfig::getInstance()->audio_thumb_height = $thumbs['audio_thumb_height'];
        
        //$status = update_option('wiziapp_settings', $options);
        $status = WiziappConfig::getInstance()->bulkSave();
        if ( !$status )  {
            $message = 'Unable to update thumbs settings';
        } else {
            wiziapp_updateCacheTimestampKey();
        }
    }
    
    $header = array(
        'action' => 'thumbs',
        'status' => $status,
        'code' => ($status) ? 200 : 4004,
        'message' => $message,
    );
            
    echo json_encode(array('header' => $header));
    exit;
}

/**
* Used to update the wiziapp_settings option
* 
* Used by the system control services in case of changes done in the account.
* 
* POST /wiziapp/system/settings
* 
* @param string key the option key inside the settings array
* @param string value the value of the key
* 
* @returns regular json status header.
*/
function wiziapp_updateConfiguration(){
    wiziapp_checkSystemAuth();    
    
    $status = FALSE;
    
    $key = $_POST['key'];
    $value = $_POST['value'];
    
    if ( isset(WiziappConfig::getInstance()->$key) ){
        $status = WiziappConfig::getInstance()->saveUpdate($key, $value);
        if ( $status ){
            $message = __('Settings updated', 'wiziapp');
            wiziapp_updateCacheTimestampKey();
        } else {
            $message = __('Unable to update settings', 'wiziapp');
        }
    } else {
        $message = __('Unknown key', 'wiziapp');
    }
    
    $header = array(
        'action' => 'screens',
        'status' => $status,
        'code' => ($status) ? 200 : 5000,
        'message' => $message,
    );
            
    echo json_encode(array('header' => $header));
    exit;
}

/**
* Used to update the wiziapp_screens option
* 
* Used by the system control services in case of changes done in the template the app uses.
* 
* POST /wiziapp/system/screens
* 
* @todo add validation to the content of the screens
* 
* @param string screens A json string holding the screens configuration
* 
* @returns regular json status header.
*/
function wiziapp_updateScreenConfiguration(){
    wiziapp_checkSystemAuth();    
    $screensJson = stripslashes($_POST['screens']);
    $screens = json_decode($screensJson, TRUE);
    $message = '';
    
    if ( !$screens ){
        $status = FALSE;
        $message = __('Unable to decode screens: ', 'wiziapp').$screensJson;
    } else {
        $status = update_option('wiziapp_screens', $screens);    
        if ( !$status )  {
            $message = __('Unable to update screens', 'wiziapp');
        } else {
            wiziapp_updateCacheTimestampKey();
        }
    }
    
    $header = array(
        'action' => 'screens',
        'status' => $status,
        'code' => ($status) ? 200 : 4004,
        'message' => $message,
    );
            
    echo json_encode(array('header' => $header));
    exit;
}

/**
* Used to update the wiziapp_components option
* 
* Used by the system control services in case of changes done in the 
* theme customization the app uses.
* 
* POST /wiziapp/system/components
* 
* @todo add validation to the content of the components
* 
* @param string screens A json string holding the components configuration
* 
* @returns regular json status header.
*/
function wiziapp_updateComponentsConfiguration(){
    wiziapp_checkSystemAuth();    
    $componentsJson = stripslashes($_POST['components']);
    $components = json_decode($componentsJson, TRUE);
    $message = '';
    
    if ( !$components ){
        $status = FALSE;
        $message = __('Unable to decode components: ', 'wiziapp').$componentsJson;
    } else {
        $status = update_option('wiziapp_components', $components);    
        if ( !$status )  {
            $message = __('Unable to update components', 'wiziapp');
        } else {
            wiziapp_updateCacheTimestampKey();
        }
    }
    
    $header = array(
        'action' => 'components',
        'status' => $status,
        'code' => ($status) ? 200 : 4004,
        'message' => $message,
    );
            
    echo json_encode(array('header' => $header));
    exit;
}

/**
* Used to update the wiziapp_pages option
* 
* Used by the system control services to defined which pages we are showing where
* 
* POST /wiziapp/system/pages
* 
* @todo add validation to the content of the pages
* 
* @param string screens A json string holding the pages configuration
* 
* @returns regular json status header.
*/
function wiziapp_updatePagesConfiguration(){
    wiziapp_checkSystemAuth();
    $options = get_option('wiziapp_pages');
    
    $pagesJson = stripslashes($_POST['pages']);
    $pages = json_decode($pagesJson, TRUE);
    
    if ( !$pages ){
        $status = FALSE;
        $message = __('Unable to decode pages: ', 'wiziapp').$pagesJson;
    } else {
        if ( empty($options) ){
            $options = $pages;
            $status = add_option('wiziapp_pages', $options, '', 'no');
             $message = __('Unable to create pages configuration', 'wiziapp');
        } else {
            $options = $pages;
            $status = update_option('wiziapp_pages', $options);        
            $message = __('Unable to update pages configuration', 'wiziapp');
        }        
        
        if ( $status )  {
           wiziapp_updateCacheTimestampKey();
        }
    }
    
    $header = array(
        'action' => 'pages',
        'status' => $status,
        'code' => ($status) ? 200 : 4004,
        'message' => $message,
    );
            
    echo json_encode(array('header' => $header));
    exit;
}

function wiziapp_listLogsWS(){
    wiziapp_checkSystemAuth();           
    WiziappSupport::getInstance()->listLogs();
}

function wiziapp_getLogFileWS($log){
    wiziapp_checkSystemAuth();           
    WiziappSupport::getInstance()->getLog($log);
}
