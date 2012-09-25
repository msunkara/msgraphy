<?php
/**
* @package WiziappWordpressPlugin
* @subpackage DOMParser
* @author comobix.com plugins@comobix.com
*/
/**
* Gets the html and uses the DOM loader to get an array of objects
* extracts the media elements we can handle
* 
* @todo add plugins support
*/
class WiziappMediaExtractor{   
    // Media holders, separated for easy management
    var $images = array();
    var $videos = array();
    var $audios = array();
        
    function WiziappMediaExtractor($html) {
//        $GLOBALS['WiziappLog']->write('info', ">>>The html for the post is {$html}", 'WiziappMediaExtractor');
        $encoding = get_bloginfo('charset');
        
        if (!empty($html)){
            $dom = new WiziappDOMLoader($html, $encoding);
            $this->_process($dom->getBody());
        }
    }
    
    function _process($dom){
        // Scan all the array elements, top to bottom
        $total = count($dom);
        for($d = 0; $d < $total; ++$d){
            $processed = FALSE;
            $element = $dom[$d];
            if ( !is_array($element) ){
                continue;
            }
            $element_key = key($element);
            if ($element_key != 'text'){
                $element_val = $element[$element_key];
                /**
                * Special elements are: 
                * img, embed, object, a tags are special since they are used as placeholders for 
                * plugins, providing them with a nice fallback, so we are checking them as well
                */
                if (strtolower($element_key) == 'img'){
                    $GLOBALS['WiziappProfiler']->write("Handling IMG", "WiziappMediaExtractor._process");   
                    $processed = $this->_handleImage($element_val, $element);    
                    $GLOBALS['WiziappProfiler']->write("Done Handling IMG", "WiziappMediaExtractor._process");   
                } elseif (strtolower($element_key) == 'object' || strtolower($element_key) == 'embed') {
                    $GLOBALS['WiziappProfiler']->write("Handling Flash Object", "WiziappMediaExtractor._process");   
                    $processed = $this->_handleFlashObjects($element_val, '', $element);   
                    $GLOBALS['WiziappProfiler']->write("Done Handling Flash Object", "WiziappMediaExtractor._process");   
                // vimeo might be loaded as an iframe
                } elseif (strtolower($element_key) == 'iframe') {
//                    $GLOBALS['WiziappLog']->write('info', ">>> Found iframe::" . print_r($element_val, TRUE), '_process');
                    $GLOBALS['WiziappProfiler']->write("Handling Iframe as Flash Object", "WiziappMediaExtractor._process");   
                    $processed = $this->_handleFlashObjects($element_val, '', $element);                        
                    $GLOBALS['WiziappProfiler']->write("Done Handling Iframe as Flash Object", "WiziappMediaExtractor._process");   
                } elseif (strtolower($element_key) == 'a') {
                    /**
                    * Some plugins uses a tags to include sounds and videos, use the built-in
                    * wordpress function to tell us which file it is by it's ext
                    */
                    $GLOBALS['WiziappProfiler']->write("Handling link", "WiziappMediaExtractor._process");   
                    $pos = strrpos($element_val['attributes']['href'], '.');
                    if ( $pos !== FALSE ){
                        $ext = substr($element_val['attributes']['href'], $pos + 1);
                        $type = wp_ext2type($ext);
                        
                        if ( $type == 'audio' ){
                            $GLOBALS['WiziappProfiler']->write("Handling audio link", "WiziappMediaExtractor._process");   
                            $processed = $this->_handleAudio($element_val['attributes']['href'], $element_val, $element);
                            $GLOBALS['WiziappProfiler']->write("Done Handling audio link", "WiziappMediaExtractor._process");   
                        }
                    }
                    $GLOBALS['WiziappProfiler']->write("Done Handling link", "WiziappMediaExtractor._process");   
                    // Use the main parsing method
                    /**if ( !$processed ){ - Removing the support for video embed via links
                        $processed = $this->_handleFlashObjects($element_val, $element_val['attributes']['href'], $element);    
                    }*/
                }
                // The childs of this array can be: text, attributes, childs
                if ( !empty($element_val['childs']) && !$processed ){
                    //$this->_process($element_val['childs']);
                        $childs = $element_val['childs'];
                        $this->_process($childs);
                }
                
            }
        }
    }
    
    function _handleVimeoVideo($src){
        //http://player.vimeo.com/video/16359484?title=1&byline=1&portrait=0&fullscreen=1
        $obj = array();
        // Got a vimeo movie... Need to extract data from vimeo API
        $qs = array();
        parse_str(substr($src, strpos($src, "?") + 1), $qs);
        
        if (!isset($qs['clip_id'])){
            // might be the iframe format:
            $clipId = substr($src, strrpos($src, '/') + 1);
        } else {
            $clipId = $qs['clip_id'];
        }
        
        if (strpos($clipId,'?') !== FALSE){
            $clipId = substr($clipId, 0, strpos($clipId, '?'));
        }
        // Check vimeo for this video
        $vimeo_api = "http://vimeo.com/api/v2/video/{$clipId}.json";
        
        $GLOBALS['WiziappLog']->write('info', ">>> Vimeo API::" . print_r($vimeo_api, TRUE), 
            '_handleVimeoVideo');
        
        $response = wiziapp_general_http_request(array(), $vimeo_api, 'GET', array());
        if (!is_wp_error($response)) {
            $json = $response['body'];
            $vimeo_movies = json_decode($json, TRUE);
            
            if ($vimeo_movies){
                $movie = $vimeo_movies[0];
                $vimeo_id = $clipId;
                
                if (!empty($movie['title'])){
                    $obj = array(
                        'title' => str_replace('&amp;', '&', $movie['title']),
                        'date' => $movie['upload_date'],
                        'thumb' => $movie['thumbnail_medium'],
                        'bigThumb' => array(
                            'url' => $movie['thumbnail_large'],
                            'width'=> 640,
                            'height'=> 320,
                        ),
                        'gotMobile' => !empty($movie['mobile_url']),
                        'description' => str_replace('&amp;', '&', $movie['description']),
                        'duration' => $movie['duration'],
                        'actionURL' => wiziapp_buildVideoLink('vimeo', $vimeo_id, "http://vimeo.com/m/#/{$clipId}"),
                    );
                }
            }
        }
        return $obj;
    }

    function _handleBlipTvVideo($src){
        $obj = array();
        $identifier = substr($src, strrpos($src, '/') + 1);      
        if (strpos($identifier, '?') != FALSE){
            $identifier = substr($identifier, strpos($identifier, '?') + 1);
        }
        $apiUrl = "http://blip.tv/players/episode/{$identifier}?skin=json&no_wrap=1&version=2";
        /**$GLOBALS['WiziappLog']->write('info', ">>> Getting blip.tv data with {$apiUrl} :: {$src} :: {$identifier}", 
            '_handleBlipTvVideo' );*/
        $response = wiziapp_general_http_request(array(), $apiUrl, 'GET', array());
        if (!is_wp_error($response)) {   
            $json = $response['body'];
            $json = trim($json);
            // Remove special chars
            $json = str_replace(array("\t", "\r\n", "\n", "\r"), '', $json);
            // Escape '
            $json = str_replace("'", "\\'", $json);
            $blipResponse = json_decode($json, TRUE);
            if ($blipResponse){
                /**$GLOBALS['WiziappLog']->write('info', ">>> Got the blip data :: ".print_r($blipResponse, TRUE), 
                '_handleBlipTvVideo' );*/
            
//                $blipMpg4 = '';
//                for($a = 0, $total = count($blipResponse['additionalMedia']); $a < $total; ++$a){
//                    if ($blipResponse['additionalMedia'][$a]['role'] == 'Portable (iPod)'){
//                        $blipMpg4 = $blipResponse['additionalMedia'][$a]['url'];
//                    }
//                }
//
//                if ($blipMpg4 == ''){
//                    $blipMpg4 = $blipResponse['url'];
//                }
                
                $obj = array(
                    'title' => str_replace('&amp;', '&', $blipResponse['title']),
                    'date' => $blipResponse['datestampDate'],
                    'thumb' => $blipResponse['thumbnail120Url'],
                    'bigThumb' => array(
                        'url' => $blipResponse['thumbnailUrl'],
                        'width' => 640,
                        'height' => 360,
                    ),
                    'description' => str_replace('&amp;', '&', $blipResponse['description']),
                    'duration' => $blipResponse['media']['duration'],
                    //'actionURL' => wiziapp_buildVideoDetailsLink($blipResponse['itemId']),
                    'actionURL' => wiziapp_buildVideoLink('blip.tv', $blipResponse['itemId'], $blipResponse['url']),
                );                
            }
        }
        return $obj;
    }
    
    function _handleYouTubeVideo($src){
        $obj = array();
        
        // Cut the parameters
        if (strpos($src, '&') !== FALSE){
            $tmp = substr($src, 0, strpos($src, '&'));
            $youTubeId = str_replace('http://www.youtube.com/v/', '', $tmp);    
        } else {
            // can be: http://www.youtube.com/v/SKdyMjsZjj8?fs=1
            //http://www.youtube.com/watch?v=FCXlCkY4Y5g
            if (strpos($src, '/watch?') !== FALSE){
                $qs = array();
                parse_str(substr($src, strpos($src, "?") + 1), $qs);
                $youTubeId = $qs['v'];
            } else {
                $tmp = substr($src, strrpos($src, '/') + 1);
                if (strpos($tmp, '?') !== FALSE){
                    $youTubeId = substr($tmp, 0, strpos($tmp, '?'));
                } else {
                    $youTubeId = $tmp;
                }    
            }
        }
        
        /**
        * Get the information about the video from the formal API
        */
        $apiUrl = "http://gdata.youtube.com/feeds/api/videos/";
        if (strpos($youTubeId, '?') === FALSE){
            $apiUrl .= $youTubeId . "?alt=json";
        } else {
            $apiUrl .= $youTubeId . "&alt=json";
        }
        
        $response = wiziapp_general_http_request(array(), $apiUrl, 'GET', array());
        if (!is_wp_error($response)) {   
            $json = $response['body'];
            $youTubeResponse = json_decode($json, TRUE);
            if (is_array($youTubeResponse)){
                $movie = $youTubeResponse['entry'];
//                $GLOBALS['WiziappLog']->write('info', ">>> Got YouTube response: " . print_r($youTubeResponse, TRUE), "_handleYouTubeVideo");
                  
                //$thumbsCount = count($movie['media$group']['media$thumbnail']);    
//                $bigThumbElement = $movie['media$group']['media$thumbnail'][$thumbsCount - 1];
                $bigThumbElement = $movie['media$group']['media$thumbnail'][0];
                
                $youTubeShortId = $youTubeId;
                if (strpos($youTubeShortId, '?') !== FALSE){
                    $youTubeShortId = substr($youTubeShortId, 0, strpos($youTubeShortId, '?'));
                }
                
                if (isset($movie['media$group']['yt$duration']['seconds'])){
                    $obj = array(
                        'title' => str_replace('&amp;', '&', $movie['title']['$t']),
                        'date' => $movie['published']['$t'],
                        'thumb' => $movie['media$group']['media$thumbnail'][0]['url'],
                        'bigThumb' => array(
                            'url' => $bigThumbElement['url'],
                            'width'=> $bigThumbElement['width'],
                            'height'=> $bigThumbElement['height'],
                        ),
                        'description' => str_replace('&amp;', '&', $movie['content']['$t']),
                        'duration' => $movie['media$group']['yt$duration']['seconds'],
                        'actionURL' => wiziapp_buildVideoLink('youtube', $youTubeShortId,
                                                    'http://www.youtube.com/watch?v=' . $youTubeShortId . '&fmt=18'),
                    );
                }       
            }
        }
        return $obj;
    }
    /**
    * @todo Convert this to a plugin friendly hook.... should use filters
    * 
    * @param array $element
    * @param string $force_src
    */
    function _handleFlashObjects($element, $force_src = '', $full_element=array()){
        $obj = array();
        $src = '';
        if (!empty($force_src)){
            $src = $force_src;
        } elseif (isset($element['attributes']['src'])){
            $src = $element['attributes']['src'];
        } else {
            // Try to find the movie src
            for($e=0,$total=count($element['childs']);$e<$total;++$e){
                // For every param
                if (isset($element['childs'][$e]['param'])){
                    $param = $element['childs'][$e]['param'];
                    if(strtolower($param['attributes']['name']) == 'movie'){
                        $src = $param['attributes']['value'];
                    } else if (strtolower($param['attributes']['name']) == 'src' ){
                        $src = $param['attributes']['value'];
                    }
                }
            }
        }
        $GLOBALS['WiziappLog']->write('info', ">>> The src is {$src}", '_handleFlashObjects');
        if (empty($src)){
            $obj = apply_filters('wiziapp_unknown_flash_content', $element, $full_element);
        } else {
            // We have something to work with, let's see if we support it
            if (strpos($src, 'vimeo.com') !== FALSE){
                $obj = $this->_handleVimeoVideo($src);
                $GLOBALS['WiziappLog']->write('info', ">>> The obj is {$src}::" . print_r($obj, TRUE), '_handleFlashObjects');
            } elseif (strpos($src, 'blip.tv') !== FALSE){
                // we removed support for blip coz of bugs and no time to fix, might come back in the future...
//                $obj = $this->_handleBlipTvVideo($src);
            } elseif (strpos($src, 'youtube.com') !== FALSE){
                $obj = $this->_handleYouTubeVideo($src);
            } else {
                $obj = apply_filters('wiziapp_unknown_flash_content', $element, $full_element);
            }
        } 
        if ($obj == $element){
            $obj = array();
        }

        return $this->_appendToArray($obj, $full_element, 'videos');
    }    
    
    /**
    * _appendToArray
    * 
    * Add the processed object to the right array according to its type
    * there it will wait till we save it
    * 
    * @param array $obj the processed object
    * @param array $element the original element
    * @param string $type the type of the element: videos, images, audios
    */
    function _appendToArray($obj, $element, $type){
        $added = FALSE;
        if (!empty($obj)){
            // We might need to override the type
            if (!empty($obj['type']) && $obj['type'] != $type){
                if (isset($this->{$type}) && is_array($this->{$type})){
                    $type = $obj['type'];    
                }
            }
            
            $dom = new WiziappDOMLoader;
            $html = $dom->getNodeAsHTML(array($element));
            $this->{$type}[] = array(
                'obj' => $obj,
                'html' => $html,
            );
            
            $added = TRUE;
        } 
        return $added;
    }
    
    /**
    * Images are saved as is and returned later for processing
    * 
    * images supports using the data-wiziapp- to add metadata for the images like special plugin ids and such.
    * 
    * @uses Self::_appendToArray
    * 
    * @todo move the image element building from the screens to here
    * @param array $element
    * @param array $full_element
    */
    function _handleImage($element, $full_element){
//    list($width, $height) = @getimagesize(str_replace(' ', '%20', $element['attributes']['src']));
            
        // @todo move the image element building from the screens to here
        $attributes = $element['attributes'];
        $metadata = array();
        $prefix = 'data-wiziapp-';
        foreach($attributes as $attrKey=>$attrValue){
            if (strpos($attrKey, $prefix) === 0){
                $metadata[str_replace($prefix, '', $attrKey)] = $attrValue;
            }
        }
        
        if (!empty($metadata)){
            $element['metadata'] = $metadata;
        }
        /**if(empty($element['attributes']['width'])){
           $element['attributes']['width'] = $width; 
        }
        if(empty($element['attributes']['height'])){
           $element['attributes']['height'] = $height;
        }  */
        
        /** We dont resize images anymore!!! Only thumbnails, on demand.
        $image = new WiziappImageHandler($element['attributes']['src']);
        $size = wiziapp_getImageSize('full_image'); 
        $url_to_resized_image = $image->getResizedImageUrl($element['attributes']['src'], $size['width'], $size['height'], 'resize');
        $element['attributes']['src'] = $url_to_resized_image;
        
        $element['attributes']['width'] = $image->getNewWidth(); 
        $element['attributes']['height'] = $image->getNewHeight(); */
        
        return $this->_appendToArray($element, $full_element, 'images');
//    }
    }   
    
    function _handleAudio($src, $element, $full_element) {
        $duration = 0;
        $id3formats = array('mp3', 'ogg', 'avi', 'mov', 
                            '.qt', 'mp4', 'm4v', 'm4a', 
                            'wma', 'wmv', 'mpg', 'peg', 
                            'flv', 'swf');
        
        // Are we supporting this file
        if(in_array(strtolower(substr($src, -3, 3)), $id3formats)) {
            // Try to find the path for this file
            $full_path = '';
            if (strpos($src, '://') !== FALSE){
                // It a web path, but is it this site? 
                $blog_url = get_bloginfo('wpurl');
                if (strpos($src, $blog_url) === 0){
                    // Its here, find the local file
                    $sub_src = str_replace($blog_url . "/", "", $src);  
                    $full_path = WIZI_ABSPATH . '/' . $sub_src; 
                }
            } else {
                // Not a web url, so it must be local and relative
                $full_path = WIZI_ABSPATH . $src; 
            }
            
            if (!empty($full_path)){
                // Let's see if we were right...
                if ( file_exists($full_path) ){
                    require_once(dirname(__FILE__) . '/../libs/getid3/getid3.php');                                          
                    $getID3 = new getID3;
                    
                    $fileinfo = @$getID3->analyze($full_path);
                    getid3_lib::CopyTagsToComments($fileinfo);
                    if ( isset($fileinfo['playtime_string']) ){
                        $duration = $fileinfo['playtime_string'];
                    }
                }
            } 
        } 
        
        $title = substr($src, strrpos($src, '/') + 1);
        $title = substr($title, 0, strrpos($title, '.'));
        $title = str_replace(array('_', '%20'), ' ', $title);
        
        $image = '';
        
        // Search for an image tag inside the link element
        foreach($element['childs'] as $child){
            $GLOBALS['WiziappLog']->write('info', "checking child... : " . print_r($child, TRUE), 'media_extractor.WiziappMediaExtractor._handleAudio');
            // @todo add the thumb;
        }
        
        // Build the needed data
        $obj = array(
            'title' => $title,
            'duration' => empty($duration) ? __('unknown') : $duration,
            'actionURL' => wiziapp_buildAudioLink('audio', $src),
            'imageURL' => $image,
        );
        
        return $this->_appendToArray($obj, $full_element, 'audios');
    }
    
    function getVideos(){
        return $this->videos;
    }
    
    function getImages(){
        return $this->images;
    }
    
    function getAudios(){
        return $this->audios;
    }
}