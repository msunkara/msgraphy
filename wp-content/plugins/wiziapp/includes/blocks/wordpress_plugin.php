<?php
/**
* @package WiziappWordpressPlugin
* @subpackage Plugins
* @author comobix.com plugins@comobix.com
*/

add_filter('wiziapp_albums_request', 'wiziapp_get_wordpress_albums', 10);
function wiziapp_get_wordpress_albums($existing_albums){
    if(!is_array($existing_albums)){
        return $existing_albums;
    }
    
    $albumsFromWordpress = array();
    $albums = array();
    
    $data = $GLOBALS['WiziappDB']->get_media_metadata('image', array('wordpress-gallery-id'));
    //$data = $GLOBALS['WiziappDB']->get_media_metadata_not_equal('image', array('cincopa-id'=>'', 'nextgen-gallery-id'=>'', 'nextgen-album-id'=>'', 'pageflipbook-id'=>''));

    if ($data !== FALSE){
        foreach($data as $content_id => $medias){
            $minimumForAppearInAlbums = WiziappConfig::getInstance()->count_minimum_for_appear_in_albums;
            if(count($medias) >= $minimumForAppearInAlbums){
                $images = array();
                foreach ($medias as $media){
                    /** We dont resize images anymore!!! Only thumbnails, on demand.
                    $realImage = new WiziappImageHandler($media[info]->attributes->src);
                    $url_to_resized_image = $realImage->getResizedImageUrl($media[info]->attributes->src, wiziapp_getMultiImageWidthLimit(), 0, 'resize');
                    $width = $realImage->getNewWidth();
                    $height = $realImage->getNewHeight(); */
                    
                    $images[] = $media['info']->attributes->src;
                }
                $imagesCount = count($images);
                
                $GLOBALS['WiziappLog']->write('info', "The Wordpress album ID is " . $content_id, 
                                          'wordpress_plugin.wiziapp_get_wordpress_albums');
                $albumsFromWordpress[] = array('content_id' => $content_id, 'count' => $imagesCount, 'images' => $images);    
            }
        }    
    }
    
    if(!empty($albumsFromWordpress)){
        foreach($albumsFromWordpress as $gallery){
            $the_post = get_post($gallery['content_id']);
            $album = array(
                'id' => (string) $gallery['content_id'],
                'postID' => $the_post->ID,
                'name' => (string) str_replace('&amp;', '&', $the_post->post_title),
                'plugin' => (string) 'wordpress', // e.g.: cincopa/pageflip,
                'numOfImages' => (int) $gallery['count'],
                'images' => $gallery['images'],
                'publish_date' => $the_post->post_date
            );
         $albums[] = $album;
        }
    }
    $result = array_merge($existing_albums, $albums);
    return $result;
}

add_filter('wiziapp_get_wordpress_album', 'wiziapp_get_wordpress_album', 10, 2);
/**
 * get all images in album from wordpress
 * @param array $images - images
 * @param string $albumId - album id
 * @param int $postId - post id
 * @return array
 */
function wiziapp_get_wordpress_album($images = array(), $postId = 0, $albumId = 0){
    $images = array();
    if(empty($postId)) {
        return $images;
    }
        
    $data = $GLOBALS['WiziappDB']->get_media_data('image', 'wordpress-gallery-id', $postId);
    if ($data !== FALSE){
        $GLOBALS['WiziappLog']->write('info', "The Wordpress album is " . print_r($data[$postId], TRUE),
                                          'wordpress_plugin.wiziapp_get_wordpress_album');
        foreach ($data as $image){
            $encoding = get_bloginfo('charset');
            $dom = new WiziappDOMLoader($image['original_code'], $encoding);
            $tmp = $dom->getBody();
            $attributes = (object) $tmp[0]['img']['attributes'];

            $info = json_decode($image['attachment_info']); 
            $image_info = $info->attributes;
            $title = '';
            if ( isset($image_info->title) ){
                $title = "{$image_info->title}";
            }
            $image_out = array(
                'pid' => (int) $image['id'],
                'thumbURL' => (string) $image_info->src,
                'imageURL' => (string) $attributes->src,
                'description' => $title,
                'relatedPost' => (int) $postId, // Where the image was published
                'alttext' => $title
            );
            $images[] = $image_out;
        }
    }
    return $images;
}

add_filter('wiziapp_before_the_content', 'wiziapp_wordpress_filter', 1);
/**
 * find all images on page from wordpress and replace it by <a><img></a>
 * @param string $content - content of post
 * @return string - changed content
 */
function wiziapp_wordpress_filter($content){
    global $post;
    $matches = array();
    
    preg_match_all("/\[\s*gallery.+/", $content, $matches);
    if(!empty($matches[0])) {
        $output = apply_filters('post_gallery', '', $matches[0]);
        if ($output != '')
            return $output;

        // We're trusting author input, so let's at least make sure it looks like a valid orderby statement
        if (isset($matches[0]['orderby'])) {
            $matches[0]['orderby'] = sanitize_sql_orderby($matches[0]['orderby']);
            if (!$matches[0]['orderby'])
                unset($matches[0]['orderby']);
        }
        
        foreach($matches[0] as $matchId => $match) {
            $images = '';
            
            if(stripos($match, 'id=')) {
                preg_match_all("/id=\"\d+\"/", $match, $idMatch);
                if(!empty($match[0])) {
                    $postId = explode('=', $idMatch[0][0]);
                }    
                $imagesFromPost =& get_children('post_type=attachment&post_mime_type=image&post_parent=' . trim($postId[1], '"\\'));
            } else {
                $imagesFromPost =& get_children('post_type=attachment&post_mime_type=image&post_parent=' . $post->ID);
            }
            
            if(stripos($match, 'include=')) {
                preg_match_all("/include=\"[0-9, ]+\"/", $match, $includeMatch);
                if(!empty($includeMatch[0][0])) {
                    $explodedIncludes = explode('=', $includeMatch[0][0]);
                    $includeIds = trim($explodedIncludes[1], '"\\');
                }
                
                $itemList = explode(',', $includeIds);
                foreach($imagesFromPost as $imageFromPost) {
                    if(!in_array($imageFromPost->ID, $itemList)) {
                        unset($imagesFromPost[$imageFromPost->ID]);
                    }
                }
            } elseif(stripos($match, 'exclude=')) {
                preg_match_all("/exclude=\"[0-9, ]+\"/", $match, $excludeMatch);
                if(!empty($excludeMatch[0][0])) {
                    $explodedExcludes = explode('=', $excludeMatch[0][0]);
                    $excludeIds = trim($explodedExcludes[1], '"\\');
                }
                
                $itemList = explode(',', $excludeIds);
                foreach($itemList as $excludeId) {
                    unset($imagesFromPost[trim($excludeId)]);
                }
            }
            
            if(stripos($match, 'order="ASC"')) {
                ksort($imagesFromPost);
            } elseif(stripos($match, 'order="DESC"')) {
                krsort($imagesFromPost);
            } elseif (stripos($match, 'orderby="RAND"')) {
                shuffle($imagesFromPost);
            }    
                
            if($imagesFromPost) {
                foreach($imagesFromPost as $image) {
                    // $link = wp_get_attachment_thumb_url($image->ID);
                    $link = wp_get_attachment_url($image->ID);
                    $html = '<a href="' . $link . '" class="wiziapp_gallery wiziapp_wordpress_plugin">';
                    //$html .= '<img src="' . $link . '" data-wiziapp-id="' . $image->ID . '" wordpress-gallery-id="' . $post->ID . '" />';
                    $html .= '<img src="' . $link . '" wordpress-gallery-id="' . $post->ID . '" />';
                    $html .= '</a>';
                    $images .= $html;                
                }
            }
            
            $content = str_replace($matches[0][$matchId], $images, $content);
        }
    }
    return $content;
}
?>