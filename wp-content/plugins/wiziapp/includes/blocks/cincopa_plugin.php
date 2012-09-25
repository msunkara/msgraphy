<?php
/**
* @package WiziappWordpressPlugin
* @subpackage Plugins
* @author comobix.com plugins@comobix.com
* 
*/

add_filter('wiziapp_albums_request', 'wiziapp_get_cincopa_albums', 10);

function wiziapp_get_cincopa_albums($existing_albums){
    /** 
    * @todo: Add a cincopa plugin
    * The cincopa plugin adds everything as iframes and javascript code, so we need
    * to digg in and create a new "wordpress plugin" that will use the cincopa api directly
    */
    if(!is_array($existing_albums)){
        return $existing_albums;
    }
    
    $cincopaAlbums = array();
    $metadata = $GLOBALS['WiziappDB']->get_media_metadata('image', array('cincopa-id'));
    if ($metadata !== FALSE){
        foreach($metadata as  $media_id => $keys){
            $cincopaAlbums[$media_id] = $keys['cincopa-id'];
        }    
    }
    
//    $cincopaAlbums = array_unique($cincopaAlbums);
    if(function_exists('WpMediaCincopa_init') || function_exists('_cpmp_WpMediaCincopa_init')){
        
        $cincopaResponse = array();
        $processedAlbums = array();
        foreach($cincopaAlbums as $media_id=>$gallery){
            $post_ID = $GLOBALS['WiziappDB']->get_content_by_media_id($media_id);
            if ( !isset($processedAlbums[$post_ID]) ){
                $processedAlbums[$post_ID] = array();
            }
            
            if ( !isset($processedAlbums[$post_ID][$gallery]) ){                
                $the_post = get_post($post_ID);
                $dateline = $the_post->post_date;
                $images_url = array();
                
                $json = array();
                if ( !isset($cincopaResponse[$post_ID]) ){
                    $json = wiziapp_cincopaJson($gallery);
                    $cincopaResponse[$post_ID] = $json;    
                } else {
                    $json = $cincopaResponse[$post_ID];
                }
                
                $realImages = array_slice($json->items, 0); //All images
                
                $minimumForAppearInAlbums = WiziappConfig::getInstance()->count_minimum_for_appear_in_albums;
                if(count($realImages) >= $minimumForAppearInAlbums){
                    foreach ($realImages as $image){
                        /** We dont resize images anymore!!! Only thumbnails, on demand.
                        $realImage = new WiziappImageHandler(htmlspecialchars_decode($image->content_url));
                        $url_to_resized_image = $realImage->getResizedImageUrl($image->content_url, wiziapp_getMultiImageWidthLimit(), 0, 'resize'); */

                        $images_url[] = htmlspecialchars_decode($image->thumbnail_url);
                    }
                    $imagesCount = count($images_url);
    //                if ($imagesCount >= $minimumForAppearInAlbums) {
    //                $images_url = array_slice($images_url, 0, 3); //Limit to 3 for display
                    $album = array(
                        'id' => (string) $gallery,
                        'postID' => $post_ID,
                        'name' => (string) $the_post->post_title,
                        'plugin' => (string) 'cincopa', // e.g.: cincopa/pageflip,
                        'numOfImages' => (int) $imagesCount,
                        'images' => $images_url,
                        'publish_date'=> $dateline
                    );
                    $existing_albums[] = $album;
    //                }    
                } 
                $processedAlbums[$post_ID][$gallery] = TRUE;
            } // Set in the processed array, dont need to process twice...      
        }
    }
    return $existing_albums;
}

add_filter('wiziapp_get_cincopa_album', 'wiziapp_get_cincopa_album', 10, 2);
/**
 * get all images in album (plugin cincopa)
 * @param array $images - images
 * @param string $albumId - album id
 * @param int $postId - post id
 * @return array
 */
function wiziapp_get_cincopa_album($images = array(), $albumId = 0, $postId = 0){
    $images = array();
    if(empty($albumId)) {
        return $images;
    }
    
    if(function_exists('WpMediaCincopa_init') || function_exists('_cpmp_WpMediaCincopa_init')){
        $json = wiziapp_cincopaJson($albumId);
        
        // Get the post id
        $metadata = $GLOBALS['WiziappDB']->get_media_metadata_equal('image', 'cincopa-id', $albumId);
        if ($metadata != FALSE) {
//            $postId = $metadata[key($metadata)]['content_id'];
            $index = array_pop(array_keys($metadata));
            $postId = $metadata[$index]['content_id'];
        }
        
        $GLOBALS['WiziappLog']->write('info', "The Cincopa album is " . print_r($json->items, TRUE), 
                                          'cincopa_plugin.wiziapp_get_cincopa_album');
        
        foreach ($json->items as $image){
            /** We dont resize images anymore!!! Only thumbnails, on demand.
            $realImage = new WiziappImageHandler(htmlspecialchars_decode($image->content_url));
            $url_to_resized_image = $realImage->getResizedImageUrl($image->content_url, wiziapp_getMultiImageWidthLimit(), 0, 'resize'); */
            $image_out =  array(
                'pid' => (int) $image->id,
                'thumbURL' => (string) htmlspecialchars_decode($image->thumbnail_url),
                'imageURL' => (string) htmlspecialchars_decode($image->content_url),
                'description' => (string) $image->description,
                'relatedPost' => (int) $postId, // Where the image was published
                'alttext' => (string) $image->title 
            );
            $images[] = $image_out;
        }
    }
    return $images;
}

add_filter('wiziapp_before_the_content', 'wiziapp_cincopa_filter', 1);
/**
 * find all images on page from cincopa and replace it by <a><img></a>
 * @param string $content - content of post
 * @return string - changed content
 */
function wiziapp_cincopa_filter($content){
    //cincopa
    if(function_exists('WpMediaCincopa_init') || function_exists('_cpmp_WpMediaCincopa_init')){
        $matches = array();
        preg_match_all('/\[\s*cincopa\s*([a-zA-Z0-9_-]*)\s*\]/', $content, $matches);
        
        if(empty($matches[1])) {
            return $content;
        }
        
        foreach($matches[1] as $match_key=>$code) {
            $out = $video_out = '';

//            $xml_string = file_get_contents(wiziapp_cincopaUrl('rss') . urlencode($code));
            $xml_string = wiziapp_general_http_request('', wiziapp_cincopaUrl('rss') . urlencode($code), 'GET');
            $xml_string = str_replace('jwplayer:', 'jwplayer_', $xml_string);
            $xml = simplexml_load_string($xml_string['body']);
//            $images = wiziapp_cincopaJson($code);
            $images = $xml->channel;
            $counter = 1;
            if ($images) {
                foreach ($images->item as $image){
                    $link = $image->enclosure->attributes()->url;
    //                $image->content_url = htmlspecialchars_decode($image->content_url);
    //                $image->thumbnail_url = htmlspecialchars_decode($image->thumbnail_url);
                    if($image->jwplayer_type != 'image'){
                        $video_class = ' class="unsupported_video_format" data-wiziapp-type=video';
                        $link = $image->jwplayer_image;
                    }
    //                $out.='<a href="http://'.(string)$image->filename.'">';
    //                $out.='<img src="http://'.(string)$image->filename.'" '.($counter == 1?'data-wiziapp-cincopa-id="'.$code.'"':'').' />';
    //                $out.='</a>';
                    
                    else {
                        /** We dont resize images anymore!!! Only thumbnails, on demand.
                        $image = new WiziappImageHandler($link);
                        $size = wiziapp_getImageSize('full_image');
                        $url_to_full_sized_image = $image->getResizedImageUrl($link, $size['width'], $size['height'], 'resize');

                        $size = wiziapp_getImageSize('multi_image');
                        $urlToMultiSizedImage = $image->getResizedImageUrl($link, $size['width'], $size['height'], 'resize');
                        
                        $width = $image->getNewWidth();
                        $height = $image->getNewHeight(); */    

                        $out .= '<a href="' . $link . '" class="wiziapp_gallery wiziapp_cincopa_plugin">';
                        $out .= '<img src="' . $link . '" data-wiziapp-cincopa-id="' . $code . '"' . $video_class . ' />';
                        $out .= '</a>';
                    }    
                            
    //                }elseif($image->jwplayer_type == 'audio'){
    //                }else{
    //                    $video_out .='
    //                        <object width="512" height="430">
    //                        <embed width="512" height="430" flashvars="&amp;file='.urlencode($link).'&amp;controlbar=bottom&amp;icons=true&amp;playlist=none&amp;autostart=false&amp;linktarget=_blank&amp;repeat=none&amp;stretching=fill" allowscriptaccess="always" allowfullscreen="true" bgcolor="#C0C0C0" wmode="transparent" src="http://www.cincopa.com/media-platform/runtime/player44/player44c.swf">
    //                            </object>';
    //                    ;
    //                    $video_out .='
    //                    <object src="'.$link.'" width="300">
    //                            <param name="thumb" value="'.$thumb.'" />
    //                            <param name="title" value="'.(string)$image->title.'" />
    //                            <param name="description" value="'.(string)$image->description.'" />
    //                            <embed src="'.$link.'" type="application/x-shockwave-flash"></embed>
    //                    </object>';
    //                    ;
    //                }
                }
            }    
            $content = str_replace($matches[0][$match_key], '<p>' . $out . '</p>', $content);
//            $content = str_replace($matches[0][$match_key], '<p>' . $out . '</p><p>' . $video_out . '</p>', $content);
        }
    }
    return $content;
}
/**
 * return link for connection to cincopa
 * @param string $type - xml or json
 * @return string - link for connection to cincopa
 */
function wiziapp_cincopaUrl($type = 'xml'){
    switch ($type){
        case'json': return 'http://www.cincopa.com/media-platform/runtime/json.aspx?fid=';
            break;
        case'rss': return 'http://www.cincopa.com/media-platform/runtime/player44/rssjw.aspx?fid=';
            break;
        default:
            return 'http://www.cincopa.com/media-platform/runtime/airtightinteractive/viewer.aspx?fid=';
            break;
    }
}
/**
 * get information about album from cincopa in json format
 * @param string $albumId - album id
 * @return json
 */
function wiziapp_cincopaJson($albumId){
    $url = wiziapp_cincopaUrl('json') . urlencode($albumId);
    $GLOBALS['WiziappLog']->write('info', "About to request a cincopa album with the url: {$url}", "wiziapp_get_cincopa_albums");
    $content = wiziapp_general_http_request('', $url, 'GET');
    $content = $content['body'];
    $content = str_replace(array("'"), array("\""), substr($content, 6, -1));
    $content = preg_replace('/([a-z_]*):"/', '"$1":"', $content);
    $content = preg_replace('/([a-z_]*):\[/', '"$1":[', $content);
    $content = preg_replace('/id:/', '"id":', $content);
    $json = json_decode($content);
    
    return $json;
}

//add_filter('wiziapp_unknown_flash_content', 'wiziapp_handleCincopaVideo', 10, 2);
//
//function wiziapp_handleCincopaVideo($element, $full_element){
//    $src = $element['attributes']['src'];
//    if ( strpos($src, 'cincopa.com') !== FALSE ){
//        $movie = &$element['childs'];
//        return $obj = array(
//                'title' => $movie[1]['param']['attributes']['value'],
//                'date' => date('Y-m-d'),
//                'thumb' => $movie[0]['param']['attributes']['value'],
//                'bigThumb' => array(
//                    'url' => $movie[0]['param']['attributes']['value'],
//                    'width'=> 200,
//                    'height'=> 'auto',
//                ),
//                'description' => $movie[2]['param']['attributes']['value'],
//                'duration' => '',
//                'actionURL' => 
//                wiziapp_buildVideoLink('cincopa',3,$src),
//            );
//    }else{
//        return $element;
//    }
//}
