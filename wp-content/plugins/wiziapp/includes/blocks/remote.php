<?php if (!defined('WP_WIZIAPP_BASE')) exit();
/**
* This class handles incoming request to the CMS.
* It will check if the request is ours and handle the 
* related web services
* 
* @package WiziappWordpressPlugin
* @subpackage Core
* @author comobix.com plugins@comobix.com
* 
*/
class WiziappRequestHandler {
    /**
    * Simple PHP4 style constructor to add the required actions
    */
    function WiziappRequestHandler() {
        add_action('parse_request', array(&$this, 'handleRequest'));
        add_action('init', array(&$this, 'logInitRequest'), 1);
    }
    
    function logInitRequest(){
//        $GLOBALS['WiziappLog']->write('info', "Got a request for the blog: ", "remote.WiziappRequestHandler.logInitRequest");    
    }
    
    /*
     * Intercept any incoming request to the blog, if the request is for our web services
     * which are identified by the wiziapp prefix pass it on to processing, if not 
     * do nothing with it and let wordpress handle it
     * 
     * @see WiziappRequestHandler::_routeRequest
     * @params WP object  the main wordpress object is passed by reference
     */
    function handleRequest($wp){
    //function handleRequest(){
        //$request = $_SERVER['REQUEST_URI'];
        $request = $wp->request;
        if (empty($request)){
            // doesn't rewrite the requests, try to get the query string
            $request = urldecode($_SERVER['QUERY_STRING']);
        }

        $GLOBALS['WiziappLog']->write('info', "Got a request for the blog: ".print_r($request, TRUE), 
                                        "remote.WiziappRequestHandler.handleRequest");
        
        //if (strpos($request, 'wiziapp/') === 0){
        if (($pos = strpos($request, 'wiziapp/')) !== FALSE){
            if ($pos != 0){
                $request = substr($request, $pos);
            }            
            
            $request = str_replace('?', '&', $request);
            
            $this->_routeRequest($request);
        } 
    }
    
    /*
    * serves as a routing table, if the incoming request has our 
    * prefix, check if we can handle the requested method, if so
    * call the method.
    * 
    * This is the first routing table function, it will separate the
    * content requests from the webservices requests.
    * 
    * One major difference between the webservices and the content requests
    * is that the content requests are getting cached on the server side and webservices requests 
    * shouldn't ever be cached as a whole.
    */
    function _routeRequest($request){
        error_reporting(0);
        
        $fullReq = explode('&', $request);
        $req = explode('/', $fullReq[0]);
    
        $service = $req[1];
        $action = $req[2];

        if ($service == 'user'){
            if ($action == 'check' || $action == 'login'){
                wiziapp_check_login();
            } elseif ($action == 'track'){
                wiziapp_user_push_subscription($req[3], $req[4]);
            /**} elseif($action == 'register'){  - Disabled for now
                $message = wiziapp_user_registration(); 
                wiziapp_buildRegisterForm($message); */
            } elseif($action == 'forgot_pass'){ 
                $message = wiziapp_user_forgot_password(); 
                wiziapp_buildForgotPassForm($message); 
            }   
        } elseif($service == 'content' || $service == 'search') {
            // Content requests should trigger a the caching
            $cache = new WiziappCache;
            $key = str_replace('/', '_', $request);
            $qs = str_replace('&', '_', $_SERVER['QUERY_STRING']);
            $qs = str_replace('=', '', $qs);
            $key .= $qs;

            $key .= wiziapp_getCacheTimestampKey();
            
            global $wiziappLoader;
            $key .= $wiziappLoader->getVersion();
            
            // Added the accept encoding headers, so we won't try to return zip when we can't
            $httpXcept = isset($_SERVER['HTTP_X_CEPT_ENCODING'])?$_SERVER['HTTP_X_CEPT_ENCODING']:'';
            $httpAccept = isset($_SERVER['HTTP_ACCEPT_ENCODING'])?$_SERVER['HTTP_ACCEPT_ENCODING']:'';
            $etagHeader = isset($_SERVER['HTTP_IF_NONE_MATCH'])?$_SERVER['HTTP_IF_NONE_MATCH']:'';
            $key .= str_replace(', ', '_', "{$httpXcept}_{$httpAccept}_{$etagHeader}");
            $key .= str_replace(',', '', $key);
            //if ( $cache->beginCache(md5($key)) ){
            if ($cache->beginCache(md5($key), array('duration'=>30))){
                $output = $this->_routeContent($req);
                                
                $cache->endCache($output);    
                
                if (!$output){
                    $GLOBALS['WiziappLog']->write('info', "Nothing to output the app should use the cache",  
                        "remote.WiziappRequestHandler._routeRequest");
                    /**
                    * IIS needs us to be very specific
                    */
                    header ('Content-Length: 0');
                    $GLOBALS['WiziappLog']->write('info', "Sent the content-length",  
                        "remote.WiziappRequestHandler._routeRequest"); 
                    
                    header("HTTP/1.1 304 Not Modified");
                    $GLOBALS['WiziappLog']->write('info', "sent 304 Not Modified for the app",  
                        "remote.WiziappRequestHandler._routeRequest");
                }
            }
            
            /**
            * The content services are the only thing that will expose themselves and 
            * do a clean exit, the rest of the services will pass the handling to wordpress 
            * if they weren't able to process the request due to missing parameters and such
            */
            exit();    
//        } elseif( $service == 'rate' ) {
//            wiziapp_rate_content($req);
        } elseif($service == 'getrate') {
            wiziapp_the_rating_wrapper($req);
        } elseif ($service == "getimage"){
            wiziapp_getImageUrl();
            exit();
        } elseif ($service == "getthumb"){
            wiziapp_doPostThumbnail($req[2], array('width'=>$_GET['width'], 'height'=>$_GET['height']), array('width'=>$_GET['limitWidth'], 'height'=>$_GET['limitHeight']));
            exit();
        } elseif($service == 'post') {
            $GLOBALS['WiziappLog']->write('info', "Need to do something with post..:" . print_r($req, TRUE),
                                                "remote.WiziappRequestHandler._routeRequest");
            if ($req[3] == "comments") {
                wiziapp_getCommentsCount($req[2]);
            }
        } elseif($service == 'comment') {
            wiziapp_add_comment($request);
        /**} elseif ( $service == 'search' ){
            $this->_routeContent($req); */
        } elseif ($service == 'keywords'){
            wiziapp_get_search_keywords();
        } elseif ($service == 'system'){
            if ($action == 'screens'){
                wiziapp_updateScreenConfiguration();
            } else if ($action == 'components'){
                wiziapp_updateComponentsConfiguration();
            } else if ($action == 'pages'){
                wiziapp_updatePagesConfiguration();
            } else if ($action == 'frame'){
                wiziapp_getCrossFrameHandler();
            } else if ($action == 'settings'){
                wiziapp_updateConfiguration();
            } else if ($action == 'thumbs'){
                wiziapp_updateThumbsConfiguration();
            } else if ($action == 'check'){
                wiziapp_checkInstalledPlugin();
            } else if ( $action == 'logs' ){
                wiziapp_listLogsWS();
            } else if ( $action == 'getLog' ){
                wiziapp_getLogFileWS($req[3]);
            }
        }
    }
    
    function _routeContent($req){   
        ob_start();
        header('Cache-Control: no-cache, must-revalidate');
        $offset = 3600 * 24; // 24 hours
        header('Expires: ' . gmdate("D, d M Y H:i:s", time() + $offset) . ' GMT');
        $type = $req[2];
        $id = $req[3];

        if ( $type != 'video' ){
            header('Content-Type: application/json; charset: utf-8');
        } else {
            header('Content-Type: text/html; charset: utf-8');
        }
                        
        if ($req[1] == 'search'){
            wiziapp_do_search();        
        } else {
            if ($type == "scripts"){
                wiziapp_getContentScripts();
            } else if ($type == "comment"){
                wiziapp_buildCommentPage($id);
            } elseif ($type == 'about'){
                wiziapp_buildAboutScreen();
            } elseif ( $type == 'video' ){
                wiziapp_buildVideoPage($id);
            } elseif ($type == "list"){
                $sub_type = $req[3];
                $GLOBALS['WiziappLog']->write('info', "Listing... The sub type is: {$sub_type}", 
                                                "remote.WiziappRequestHandler._routeContent");
                if ($sub_type == "categories")    {
                    wiziapp_prepareCategoriesPage();
                } elseif($sub_type == "allcategories"){
                    wiziapp_getAllCategories();
                } elseif($sub_type == "tags"){
                    wiziapp_buildTagsPage();
                } elseif($sub_type == "alltags"){
                    wiziapp_getAllTags();
                } elseif($sub_type == "posts"){
                    $show_by = $req[4];
                    if ($show_by == 'recent'){
                        wiziapp_buildRecentPostsPage();
                    }
                } elseif ($sub_type == "pages") {
                    wiziapp_buildPagesListPage();
                } elseif ($sub_type == "allpages") {
                    wiziapp_getAllPages();
                } elseif ($sub_type == "post") {  // list/post/{id}/comments
                    $show = $req[5];
                    if ($show == "comments"){
                        if (isset($req[6]) && $req[6] != 0){
                            wiziapp_buildPostCommentSubCommentsPage($req[4], $req[6]);
                        } else {
                            wiziapp_buildPostCommentsPage($req[4]);
                        }
                    } elseif ($show == "categories"){
                        wiziapp_buildCategoriesByPost($req[4]);
                    } elseif ($show == "tags"){
                        wiziapp_buildTagsByPost($req[4]);
                    } elseif ($show == "images"){
                        if(isset($_GET['ids']) && !empty($_GET['ids'])){
                            wiziapp_buildImagesGalleryByPost($req[4], $_GET['ids']);
                        } else {
                            wiziapp_buildImagesGalleryByPost($req[4]);
                        }
                    }
                } elseif ($sub_type == "category"){
                    wiziapp_buildCategoryPage($req[4]);
                } elseif ($sub_type == "tag"){
                    wiziapp_buildTagPage($req[4]);
                } elseif ($sub_type == "user"){
                    $show = $req[5];
                    if ($show == "comments"){
                        wiziapp_buildMyCommentsPage($req[4]);   
                    } elseif ($show == "commented"){
                        wiziapp_buildCommentedPostsPage($req[4]);   
                    }
                } elseif ($sub_type == 'author'){
                    $authorId = $req[4];
                    if ($req[5] == 'posts'){
                        wiziapp_buildPostsByAuthorPage($authorId);
                    }
                } elseif( $sub_type == 'alllinks' ) {
                        wiziapp_getAllLinks();
                } elseif ($sub_type == 'links') {
                    if (!empty($req[4])){
                        $show = $req[4];   
                        if ($show == 'categories') {
                            wiziapp_buildLinksCategoriesPage();
                        } elseif ($show == 'category'){
                            wiziapp_buildLinksByCategoryPage($req[5]);
                        }
                    } else {
                        wiziapp_buildLinksPage();
                    }
                } elseif ($sub_type == "archive"){
                    $year = $req[4];
                    $month = $req[5];
                    $dayOfMonth = $req[6];
                    // Year
                    if (isset($year)){
                        // Month
                        if (isset($month)){
                            // Day of month
                            if (isset($dayOfMonth)){
                                wiziapp_buildArchiveByDayOfMonthPage($year, $month, $dayOfMonth);
                            } else {
                                wiziapp_buildArchiveByMonthPage($year, $month);
                            } 
                        } else {
                            // Just year, no month
                            wiziapp_buildArchiveMonthsPage($year);
                        }
                    } else {
                        wiziapp_buildArchiveYearsPage();
                    }
                    
                } elseif ($sub_type == "favorites"){
                    $ids = explode(",", $_GET['pids']);
                    wiziapp_buildPostsByIdsPage($ids, __(WiziappConfig::getInstance()->getScreenTitle('favorites'), 'wiziapp'), 'favorites_list');
                } elseif ($sub_type == "media"){
                    $show = $req[4];
                    if ($show == "images"){
                        wiziapp_buildImagesPage();    
                    } elseif($show == 'videos') {
                        wiziapp_buildVideosPage();
                    //} elseif ($show == 'video') {

//                    } elseif ($show == 'videoembed') {
//                        $vid_id = $req[5];
//                        wiziapp_buildVideoEmbedPage($vid_id);
                    } elseif ($show == 'audios'){
                        wiziapp_buildAudioPage();
                    }
                } elseif ($sub_type == "galleries"){
                    wiziapp_buildGalleriesPage();
                } elseif ($sub_type == "gallery"){                
                    $plugin = $req[4];
                    $plugin_item_id = $req[5];
                    if ($plugin == 'videos' && $plugin_item_id == 'all_videos'){
                        wiziapp_buildVideosPage();
                    } else {
                        wiziapp_buildGalleryPluginPage($plugin, $plugin_item_id);
                    }
                } elseif ($sub_type == "attachment"){
                    $attachmentId = $req[4];
                    $show = $req[5];
                    if ($show == "posts"){
                        wiziapp_buildPostsByAttachmentPage($attachmentId);
                    }
                } 
            }     
        }
        
        /**
        * Gzip the output, support weird headers - Moved to the caching class that actually does the output
        */
        /**$encoding = false; 
        if ( isset($_SERVER["HTTP_ACCEPT_ENCODING"]) ){
            $HTTP_ACCEPT_ENCODING = $_SERVER["HTTP_ACCEPT_ENCODING"]; 
            if ( isset($_SERVER["HTTP_X_CEPT_ENCODING"]) ){
                $GLOBALS['WiziappLog']->write('info', "GOT A WEIRD HEADER", "remote.WiziappRequestHandler");
                $HTTP_ACCEPT_ENCODING = $_SERVER["HTTP_X_CEPT_ENCODING"];
            }
            if( headers_sent() ) 
                $encoding = false; 
            else if( strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false ) 
                $encoding = 'x-gzip'; 
            else if( strpos($HTTP_ACCEPT_ENCODING,'gzip') !== false ) 
                $encoding = 'gzip'; 
                
        }  */
        
        $contents = ob_get_contents(); 
        
        $GLOBALS['WiziappLog']->write('info', "BTW the get params were:".print_r($_GET, TRUE), "remote.WiziappRequestHandler._routeContent");
        if (isset($_GET['callback'])){
            $GLOBALS['WiziappLog']->write('debug', "The callback GET param set:".$_GET["callback"] . "(" . $contents . ")", "remote.WiziappRequestHandler._routeContent");
            // Support cross-domain ajax calls for webclients
            // @todo Add a check to verify this is a web client
            header('Content-Type: text/javascript; charset: utf-8');
            $contents = $_GET["callback"] . "({$contents})";  
        } else {
            $GLOBALS['WiziappLog']->write('debug', "The callback GET param is not set", "remote.WiziappRequestHandler._routeContent");
        }
        
        // Check if the content had changed according to the e-tag
        if (isset($GLOBALS['WiziappEtagOverride']) && !empty($GLOBALS['WiziappEtagOverride'])){
            $checksum = md5($GLOBALS['WiziappEtagOverride']);
        } else {
            $checksum = md5($contents);            
        }

        $GLOBALS['WiziappLog']->write('info', "The checksum for the content is: {$checksum}", "remote.WiziappRequestHandler._routeContent");
        
	    $checksum = '"' . $checksum.wiziapp_getCacheTimestampKey() . '"';
        header('ETag: ' . $checksum);    
        $shouldProcess = TRUE;              
        $GLOBALS['WiziappLog']->write('info', "The if not matched header is: {$_SERVER['HTTP_IF_NONE_MATCH']}", "remote.WiziappRequestHandler._routeContent");
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim(stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == $checksum){
            $GLOBALS['WiziappLog']->write('info', "It's a match!!!", "remote.WiziappRequestHandler._routeContent");
            // No change, return 304
            //header ("HTTP/1.0 304 Not Modified"); 
            
            //$shouldProcess = FALSE;
            
            return FALSE;
        } else {
            // The headers do not match
            $GLOBALS['WiziappLog']->write('info', "The headers do not match: " . trim(stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) . 
                                          " and the etag was {$checksum}", "remote.WiziappRequestHandler._routeContent");
        }
        ob_end_clean();
        if ($shouldProcess){
            // Return the content
        //    if($encoding) 
          //  { 
                /**
                * Although gzip encoding is best handled by the zlib.output_compression 
                * Our clients sometimes send a different accpet encoding header like X-cpet-Encoding
                * in that case the only way to catch it is to manually handle the compression 
                * and headers check
                */
            /**    $len = strlen($contents); 
                header('Content-Encoding: '.$encoding); 
                echo "\x1f\x8b\x08\x00\x00\x00\x00\x00"; 
                $contents = gzcompress($contents, 9); 
                $contents = substr($contents, 0, $len); 
            } */
            echo $contents;
        }      
        //ob_end_clean();
        return TRUE;
    }
}

global $wiziappRequestHandler;
$wiziappRequestHandler = new WiziappRequestHandler();
