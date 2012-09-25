<?php  
/**
* Handles the display of the application, checks if the request for the blog came from a 
* supported known application and if so directs it to the CMS Plugin theme.
* When displaying posts inside our templates makes sure to convert what is needed
* 
* @package WiziappWordpressPlugin
* @subpackage ContentDisplay
* @author comobix.com plugins@comobix.com
*/
class WiziappContentHandler {
    var $mobile;
    var $inApp;
    var $debug = FALSE;
    
    static $shouldDisplayAppstoreLinkFlag = TRUE;

    /**
    * Apply all of the classes hooks to the right requests,
    * we don't need to start this request every time, just when it is possibly needed
    */
    function WiziappContentHandler() {
        $this->mobile = false;
        $this->inApp = false;

        add_action('plugins_loaded', array(&$this, 'detectAccess'), 99);
        if ( strpos($_SERVER['REQUEST_URI'], '/wp-admin') === false 
            && strpos($_SERVER['REQUEST_URI'], 'xmlrpc') === false) {
            // Don't change the template directory when in the admin panel
            add_filter('stylesheet', array(&$this, 'get_stylesheet'), 99);
            add_filter('theme_root', array(&$this, 'theme_root'), 99);
            add_filter('theme_root_uri', array(&$this, 'theme_root_uri'), 99);
            add_filter('template', array(&$this, 'get_template'), 99);    
            //add_filter('wp_head', array(&$this, 'do_head_section'), 99);
            add_filter('the_content', array(&$this, 'trigger_before_content'), 1);
            add_filter('the_content', array(&$this, 'convert_content'), 999);
            add_filter('the_category', array(&$this, 'convert_categories_links'), 99);  
        } else {
            if (strpos($_SERVER['REQUEST_URI'], 'wiziapp') !== false){
                // Avoid cache in the admin
                header("Cache-Control: no-store, no-cache, must-revalidate");
                header("Expires: " . gmdate("D, d M Y H:i:s", time() - 3600) . " GMT");
                add_filter('admin_head', array(&$this, 'do_admin_head_section'), 99);
            }
        }            
    }
    
    /**
    * We are doing some of the functionality ourselves so reduce the overhead...
    */
    function _removeKnownFilters(){
        remove_filter('the_content', 'addthis_social_widget');
        remove_filter('the_content', 'A2A_SHARE_SAVE_to_bottom_of_content', 98);
        remove_filter("gettext", "ws_plugin__s2member_translation_mangler", 10, 3);
        remove_filter('the_content', 'shrsb_position_menu');   
    }
    
    /**
    * Detect if we have been access from the application, the application uses a pre-defined protocol for it's 
    * requests, so if something is not there its not the application.
    * 
    */
    function detectAccess(){
        $GLOBALS['WiziappLog']->write('debug', "Detecting access type", "WiziappContentHandler");
        $appToken = isset($_SERVER['HTTP_APPLICATION']) ? $_SERVER['HTTP_APPLICATION'] : '';
        $udid = isset($_SERVER['HTTP_UDID']) ? $_SERVER['HTTP_UDID'] : '';
        
        $GLOBALS['WiziappLog']->write('debug', "The headers are: {$appToken} and {$udid}", "WiziappContentHandler");
        
        if (strpos($_SERVER['REQUEST_URI'], 'wiziapp/') !== FALSE){
            $this->inApp = TRUE;
        } 
        
        if ((!empty($appToken) && !empty($udid)) || $this->inApp){
            $GLOBALS['WiziappLog']->write('debug', "In the application display", "WiziappContentHandler");
            
            $this->mobile = TRUE;
            $this->inApp = TRUE;
            $this->_removeKnownFilters();
        } else {
            $this->mobile = FALSE;
            $this->inApp = FALSE;

            if (strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'], 'iPod') || strstr($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
                if (WiziappContentHandler::$shouldDisplayAppstoreLinkFlag && WiziappConfig::getInstance()->appstore_url != '') {
                    if (!isset($_COOKIE['WIZI_SHOW_APPSTORE_URL']) || $_COOKIE['WIZI_SHOW_APPSTORE_URL'] == 0){
                        add_action('wp_head', array(&$this, 'displayAppstoreAppURL'), 1);
                        WiziappContentHandler::$shouldDisplayAppstoreLinkFlag = FALSE;
                        $timeout = time() + (60 * 60 * 24);
                        setcookie("WIZI_SHOW_APPSTORE_URL", WiziappConfig::getInstance()->appstore_url_timeout, $timeout, "/");
                    }
                }
            }
                
            $GLOBALS['WiziappLog']->write('debug', "Didn't recognize the headers, normal browsing", "WiziappContentHandler");
        } 
    }
    
    function displayAppstoreAppURL () {
        echo '<script type="text/javascript">
                var res = confirm("Download our free app from the AppStore");
                if (res == true) { location.replace("' . WiziappConfig::getInstance()->appstore_url . '");}</script>';
    }

    /**
    * Handle the links converting, will convert images and post links according to
    * the app protocol.
    *     
    * @param array $matches the array returned from preg_replace_callback
    * @return string the link found after converting to the app format
    */
    function _handle_links_converting($matches){
        $link = $matches[0];
        $url = $matches[2];
        $post_id = url_to_postid($url);         
        if ($post_id){     
            $newUrl = wiziapp_buildPostLink($post_id);
            $link = str_replace($url, $newUrl, $link);
        } else {
            // If it is an image, convert to open image
            if (    strpos($url, '.png') !== FALSE || 
                    strpos($url, '.gif') !== FALSE || 
                    strpos($url, '.jpg') !== FALSE || 
                    strpos($url, '.jpeg') !== FALSE ){ 
                $newUrl = wiziapp_buildLinkToImage($url);
                $partLink = substr($link, 0, strpos($link, '>'));
                $secondPartLink = substr($link, strpos($link, '>'));
                
                $link = str_replace($url, $newUrl, $partLink) . $secondPartLink;
            }
        }
        return $link;
    }
    /**
     *
     * @param int $post_id the content id we are processing
     * @param string $content
     * @return array $replacements the array with the instructions for str_replace
     */
    function _getGaleriesReplacementCode($post_id, $content){
        if ($content != "") { // This might happen in cases like an empty nextgen album (with no galleries)
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            @$dom->loadHTML($content); // Hide the errors

            $loadingErrors = libxml_get_errors();
            if ( count($loadingErrors) > 0 ){
                $GLOBALS['WiziappLog']->write('warning', "After loading the DOM for post: {$post_id} the errors are:".print_r($loadingErrors, TRUE), "DomLoader");
            }
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);
    //        $images = $xpath->query('//img/parent::*[not(text()) and count(*)=1]/parent::*[not(text()) and count(*)=1]/parent::*[not(text()) and count(*)=1]/parent::*[id]/child::*/child::*/child::*/child::img');
            $images = $xpath->query('//p[not(text()) and count(*)=1]/a[not(text()) and count(*)=1]/img');
            $galleries = array();
            $nb = $images->length;
            if($nb > 0){
                for($pos=0; $pos<$nb; $pos++){
                    $image = $images->item($pos);
                    $grand_parent = $image->parentNode->parentNode;
                    $prev_image = ($pos>0)?$images->item($pos-1):null;
                    if($prev_image!= null && $grand_parent->isSameNode($prev_image->parentNode->parentNode->nextSibling)) {
                        $galleries[count($galleries)][]=$image;
                    } else {
                        $galleries[count($galleries)+1] = array($image);
                    }

                }
                foreach($galleries as $gallery){
                    $first_image = array_shift($gallery);
                    foreach ($gallery as $image){
                        $ancor = $image->parentNode;
                        $first_image->parentNode->parentNode->appendChild($ancor);
    //                    $image->parentNode->parentNode->removeChild($ancor);
                    }
                }
            }
            /**$content = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>',
                                    "\n", "\r"), array('', '', '', '', '', ''), $dom->saveHTML()));*/
        }
    }

    /**
    * Get the images we scanned for this post and it's replacement code
    * 
    * @param int $post_id the content id we are processing
    * @return array $replacements the array with the instructions for str_replace
    */
    function _getImagesReplacementCode($post_id){
        $imagesElements = $GLOBALS['WiziappDB']->get_content_images($post_id);
        $replacements = array(                                                  
            'find' => array(),
            'replace' => array(),
        );

        if ($imagesElements !== FALSE){
            foreach($imagesElements as $image){
                $GLOBALS['WiziappLog']->write('info', ">>> About to replace :: {$image['original_code']}", '_getImagesReplacementCode');
                $replacements['find'][] = $image['original_code'];
                
                $newImage = '<img ';
                $GLOBALS['WiziappLog']->write('info', ">>> The image info :: " . print_r($image, TRUE), '_getImagesReplacementCode');
                $attachInfo = json_decode($image['attachment_info'], TRUE);
                foreach($attachInfo['attributes'] as $attrName => $attrValue){
                    $value = str_replace('\\', '/', $attrValue);
                    $newImage .= " {$attrName}=\"{$value}\"";
                }
                
                /**
                * @todo Fix (remove) this once ticket 710 is fixed
                */
                global $thumbSize;
                if (count($imagesElements) >= WiziappConfig::getInstance()->count_minimum_for_appear_in_albums) {
                    $thumb = new WiziappImageHandler($attachInfo['attributes']['src']); 
                    $thumbSize = WiziappConfig::getInstance()->getImageSize('album_thumb');
                    $url = $thumb->getResizedImageUrl($attachInfo['attributes']['src'], $thumbSize['width'], $thumbSize['height']);
                    $newImage .= " data-image-thumb=\"" . $url . "\"";   
                }
                
//                $json = json_encode($image);
                $id_code = " data-wiziapp-id=\"{$image['id']}\" ";
                $newImage .= $id_code . ' />';
                $GLOBALS['WiziappLog']->write('info', ">>> with this:: {$newImage}", '_getImagesReplacementCode');
                
                // Wordpress save the image without closing /> so let's check this too
                $replacements['find'][] = str_replace(' />', '>', $image['original_code']);
                $replacements['replace'][] = $newImage;
                // We have 2 find elements per image, so we need to replace, otherwise things gets buggy... :P
                $replacements['replace'][] = $newImage;
                
                //$replacements['replace'][] = str_replace('/>', "{$id_code}/><!-- {$json} -->", $image['original_code']);
            }
        }
        return $replacements;
    }
    
    /**
    * Get the videos and audio we scanned for this post and it's replacement code
    * 
    * @param int $post_id the content id we are processing
    * @return array $replacements the array with the instructions for str_replace
    */
    function _getSpecialComponentsCode($post_id){
        $replacements = array(
            'find' => array(),
            'replace' => array(),
        );

        $specialElements = $GLOBALS['WiziappDB']->get_content_special_elements($post_id);
        if ($specialElements !== FALSE){
            $ve = new WiziappVideoEmbed();
            foreach($specialElements as $element){
                $info = json_decode($element['attachment_info'], TRUE);
                if ($element['attachment_type'] == $GLOBALS['WiziappDB']->media_types['video']){
                    $replaceCode = $ve->getCode($info['actionURL'], $element['id'], $info['bigThumb']);
                } else {
                    // audio should convert to a component
                    // @todo Add single component export from the simulator engine
                    $info['actionURL'] = str_replace('audio', 'video', $info['actionURL']);
                    $style = '';
                    if (!empty($info['imageURL'])){
                        $style = "background-image: url({$this->_getAdminImagePath()}{$info['imageURL']}.png);";
                    }

                    if (strlen($info['title']) > 35) {
                        $title = substr($info['title'], 0, 35) . '...';
                    }
                    
                    $replaceCode = "<a href='" . $info['actionURL'] . "'><div class='audioCellItem'>
                            <div class='col1'>
                                <div class='imageURL' style='{$style}'></div>
                            </div>
                            <div class='col2'>
                                <p class='title'>{$title}</p>
                                <p class='duration'>{$info['duration']}</p>
                            </div>
                            <div class='col3'>
                                <div class='playButton'></div>
                            </div>
                        </div></a>";
                }
                
                if ($replaceCode) {
                    $replacements['find'][] = $element['original_code'];
                    $replacements['replace'][] = $replaceCode;
                }

                if ( isset($_GET['sim']) && $_GET['sim'] == 1){
                    // The original code might have been altered a bit to be valid xhtml, check it
                    if (strpos($element['original_code'], '=""') !== FALSE){
                        $replacements['find'][] = str_replace('=""', '', $element['original_code']);
                        $replacements['replace'][] = $replaceCode;
                    }
                }

                /**
                 * The content might have been inserted with ='' instead of =""
                 */
                if (!empty($element['original_code'])){
                    $replacements['find'][] = str_replace('"', "'", $element['original_code']);
                    $replacements['replace'][] = $replaceCode;
                }


                if (strpos($element['original_code'], '<iframe') === FALSE || (isset($_GET['sim']) && $_GET['sim'] == 1)){
                    if (strpos($element['original_code'], '&') !== FALSE){
                        $replacements['find'][] = str_replace('&', '&amp;', $element['original_code']);   
                        $replacements['replace'][] = $replaceCode;   
                    }
                    
                    if (strpos($element['original_code'], '<img') !== FALSE){
                        $replacements['find'][] = str_replace(' />', '>', $element['original_code']);
                        $replacements['replace'][] = $replaceCode;   
                    }
                    
                    /**
                    * The following is indeed ugly but for some reason sometimes we are getting </param> which mess up everything
                    */
                    if ( strpos($element['original_code'], '</param>') !== FALSE ){
                        $replacements['find'][] = str_replace('</param>', '', $element['original_code']);
                        $replacements['replace'][] = $replaceCode;   
                    }
                }
            }
        }
        $GLOBALS['WiziappLog']->write('info', ">>> The replacement code is:" . print_r($replacements, TRUE), '_getSpecialComponentsCode');
        return $replacements;
    }

    function _getAdminImagePath(){
        return 'http://' . WiziappConfig::getInstance()->getCdnServer() . '/images/app/themes/' . WiziappConfig::getInstance()->wiziapp_theme_name . '/';
    }
    
    function trigger_before_content($content){
        if ($this->inApp === TRUE) {   
            $GLOBALS['WiziappLog']->write('info', "Triggering before the content", 'trigger_before_content');
            $content = apply_filters('wiziapp_before_the_content', $content);
        }
        return $content;
    }
    
    /**
    * Convert the known content to a predefined format used bu the application
    * called from 'the_content' filter of wordpress, running last.
    * 
    * @see self::_handle_links_converting
    * @see self::_getImagesReplacementCode
    * @see self::_getSpecialComponentsCode
    * 
    * @param string $content the initial content
    * @return string $content the processed content
    */
    function convert_content($content){
        global $post;
        $GLOBALS['WiziappLog']->write('info', "In the_content filter callback the contentHandler", "convert_content");  
        
        if ($this->inApp === TRUE) {   
            $GLOBALS['WiziappLog']->write('info', "Converting content like we are inside the app", "convert_content");  
            // Get the html for the content after processing it with the DOM Loader
            //$encoding = get_bloginfo('charset');

            /**$GLOBALS['WiziappProfiler']->write("Loading the content to the DOM", "convert_content");
            $dom = new WiziappDOMLoader($content, $encoding);
            $body = $dom->getBody();
            $content = $dom->getNodeAsHTML($body);
            $GLOBALS['WiziappProfiler']->write("Got the content from the DOM", "convert_content");*/
            
            //Change Galleries to new view
            /**$GLOBALS['WiziappProfiler']->write("Getting the galleries code for post {$post->ID}", "convert_content");
            $this->_getGaleriesReplacementCode($post->ID, &$content);
            $GLOBALS['WiziappProfiler']->write("Done Getting the galleries code for post {$post->ID}", "convert_content");   
//            $content = str_replace($galeriesCode['find'], $galeriesCode['replace'], $content);
            */
            $GLOBALS['WiziappProfiler']->write("Getting the images code for post {$post->ID}", "convert_content");   
            // Add the content id to images
            $imagesCode = $this->_getImagesReplacementCode($post->ID);
            $content = str_replace('&amp;', '&', $content);
            $content = str_replace($imagesCode['find'], $imagesCode['replace'], $content);
            
            $GLOBALS['WiziappProfiler']->write("Done Getting the images code for post {$post->ID}", "convert_content");   
            //$GLOBALS['WiziappLog']->write('info', "The content::" . $content, "convert_content");  
            
            // Handle special tags: video and audio
            $GLOBALS['WiziappProfiler']->write("Getting the special elements code for post {$post->ID}", "convert_content");   
            $specialCode = $this->_getSpecialComponentsCode($post->ID);
            $content = str_replace($specialCode['find'], $specialCode['replace'], $content);
            $GLOBALS['WiziappProfiler']->write("Done Getting the special elements code for post {$post->ID}", "convert_content");   
            
            // Remove the remaining flash tags which are not supported on the iphone
            
            // Handle links
            $GLOBALS['WiziappProfiler']->write("Handling links for post {$post->ID}", "convert_content");   
            $content = preg_replace_callback('/<a\s[^>]*href\s*=\s*([\"\']??)([^\" >]*?)\\1[^>]*>(.*)<\/a>/siU',
                                             array(&$this, "_handle_links_converting"), $content);            
            $GLOBALS['WiziappProfiler']->write("Done Handling links for post {$post->ID}", "convert_content");   
        }
        
        $GLOBALS['WiziappLog']->write('info', "Returning the converted content", "convert_content");  
        return $content;
    }
    
    function convert_categories_links($data1){
        return $data1;    
    }
    
    function do_head_section() {
        // Add our style sheets - no need anymore, or is there a need?
    }

    function do_admin_head_section() {
        $cssFile = dirname(__FILE__) . '/../../themes/admin/style.css';
        if ( file_exists($cssFile) ){
            $css = file_get_contents($cssFile);
            /* remove comments */
            $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
            /* remove tabs, spaces, newlines, etc. */
            $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);

            $cdnServer = WiziappConfig::getInstance()->getCdnServer();

            $css = str_replace('@@@WIZIAPP_CDN@@@', $cdnServer, $css);
            echo '<style type="text/css">'. $css . '</style>';
        }
    }

    function get_stylesheet( $stylesheet ) {
        if ($this->inApp === TRUE) {
            $stylesheet = 'default';
        } 
        return $stylesheet;
    }
          
    function get_template( $template ) {
        $this->detectAccess();

        if ($this->inApp) {
            $template = 'iphone';
        } 

        return $template;
    }
          
    function get_template_directory( $value ) {
        $this->detectAccess();

        $theme_root = $this->_get_plugin_dir();
        if ($this->inApp) {
            $value = $theme_root . '/themes'; 
        } 

        return $value;
    }
          
    function theme_root( $path ) {
        $this->detectAccess();

        $theme_root = $this->_get_plugin_dir();
        if ($this->inApp) {
            $path = $theme_root . '/themes';
        } 

        return $path;
    }
          
    function theme_root_uri( $url ) {
        $this->detectAccess();

        if ($this->inApp) {
            $url = $this->_get_plugin_dir() . "/themes";
        } 

        return $url;
    }
    
    function _get_plugin_dir(){
        return dirname(__FILE__) . '/../../';
    }
}

/**
* @global WiziappContentHandler $GLOBALS['WiziappContentHandler']
*/
if (!isset($GLOBALS['WiziappContentHandler'])) {
    /**
     * Initiate the Wiziapp Content Handler Object
     */
    $GLOBALS['WiziappContentHandler'] = new WiziappContentHandler() ;
}      
