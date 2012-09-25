<?php
/**
* @package WiziappWordpressPlugin
* @subpackage Plugins
* @author comobix.com plugins@comobix.com
* 
*/

// Enable the page flip albums in the albums screen
add_filter('wiziapp_albums_request', 'wiziapp_get_pageflip_albums', 10);
/**
 * get albums from pageflip
 * @param array $existing_albums - albums
 * @return array - albums with albums from pageflip
 */
// No need to include files that are under the blocks directory
//include_once 'image_resizing.php';

function wiziapp_get_pageflip_albums($existing_albums){
    if(!is_array($existing_albums)){
        return $existing_albums;
    }
    global $pageFlip;
    if(is_object($pageFlip) && is_a($pageFlip, 'pageFlip_plugin_base')){
        $metadata = $GLOBALS['WiziappDB']->get_media_metadata('image', array('pageflipbook-id'));
        foreach($metadata as $media_id => $keys){
            $galleries[$media_id] = $keys['pageflipbook-id'];
        }
//        $galleries = array_unique($galleries);
        
        foreach($galleries as $media_id => $gallery){
            $post_ID = $GLOBALS['WiziappDB']->get_content_by_media_id($media_id);
            $the_post = get_post($post_ID);
            $dateline = $the_post->post_date;
            $book = new Book($gallery);
            $book->load();
            $images_url = array();
            $pages = array_slice($book->pages, 0);
            
            $minimumForAppearInAlbums = WiziappConfig::getInstance()->count_minimum_for_appear_in_albums;
            if(count($pages) >= $minimumForAppearInAlbums){
                foreach($pages as $page){
                    /** We dont resize images anymore!!! Only thumbnails, on demand.
                    $realImage = new WiziappImageHandler(htmlspecialchars_decode($page->image));
                    $url_to_resized_image = $realImage->getResizedImageUrl($page->image, wiziapp_getMultiImageWidthLimit(), 0, 'resize'); */
                    
                    $images_url[] = $page->image;
                }
                $imagesCount = count($images_url);
//                if ($imagesCount >= $minimumForAppearInAlbums) {
//                $images_url = array_slice($images_url, 0, 3); //Limit to 3 for display
                $album = array('id' => $book->id,   
                               'postID' => $post_ID,
                               'name' => str_replace('&amp;', '&', $the_post->post_title),
                               'plugin' => 'pageflip',
                               'numOfImages' => $imagesCount,
                               'images' => $images_url,
                               'publish_date' => $dateline);
                $existing_albums[] = $album;
//                }    
            }    
        }
    }
    return $existing_albums;
}


add_filter('wiziapp_get_pageflip_album', 'wiziapp_get_pageflip_album', 10, 2); 
/**
 * get all images in album (plugin pageflip)
 * @param array $images - images
 * @param string $albumId - album id
 * @param int $postId - post id
 * @return array
 */
function wiziapp_get_pageflip_album($images_external = array(), $albumId = 0, $postId = 0){
    global $pageFlip;
    $GLOBALS['WiziappLog']->write('info', "Got a request for a pageflip album: " . print_r($albumId, TRUE), "wiziapp_get_pageflip_album");
    if(is_object($pageFlip) && is_a($pageFlip, 'pageFlip_plugin_base')){
        $GLOBALS['WiziappLog']->write('info', "The album id is: {$albumId}", "wiziapp_get_pageflip_album");
        if(empty($albumId)) {
            return $images_external;
        }    
        
        // Get the post id
        $metadata = $GLOBALS['WiziappDB']->get_media_metadata_equal('image', 'pageflipbook-id', $albumId);
        if ($metadata != FALSE) {
            end($metadata);
            $postId = $metadata[key($metadata)]['content_id'];
        }
        
        $book = new Book($albumId);
        $book->load();
        $images = $book->album->images;
        
        foreach ($images as $key => $image){
//            if ($image->width >= wiziapp_getMinimumWidthForAppearInAlbums() && $image->height >= wiziapp_getMinimumHeightForAppearInAlbums()) {
            $imageUrl = $pageFlip->functions->getFullImage($image->thumb);
            $image_out =  array(
                'pid' => (int) $image->id,
                'thumbURL' => (string) $image->thumb,
                'imageURL' => (string) $imageUrl,
                'description' => (string) '',
                'relatedPost' => (int) $postId, // Where the image was published
                'alttext' => (string) $book->pages[$key]->name
            );
            $images_external[] = $image_out;
//            }
        }
    }
    $GLOBALS['WiziappLog']->write('info', "About to return the images: " . print_r($images, TRUE), 
                                        "wiziapp_get_pageflip_album");
    return $images_external;
}

add_filter('wiziapp_before_the_content', 'wpimage_pageflip_filter', 1);
/**
 * find all images on page from pageflip and replace it by <a><img></a>
 * @param string $content - content of post
 * @return string - changed content
 */
function wpimage_pageflip_filter($content){
    //FlippingBook
    global $pageFlip;
    // error_reporting(-1);
    if(is_object($pageFlip) && is_a($pageFlip, 'pageFlip_plugin_base')){
        $matches = array();
        preg_match_all("/\[\s*book\s*id='([0-9]*)'[\s\/]*\]/", $content, $matches);
        if(empty($matches[1])) {
            return $content;
        }
            
        foreach($matches[1] as $match_key => $code){
            $book = new Book($code);
            $book->load();
            $out = '';
            foreach($book->pages as $key => $page){
//                $out .= '<a href="'.$book->pages[$i]->image.'">
//                <img src="'.$book->album->images[$i]->thumb.'" '.($i == 0?'data-wiziapp-pageflipbook-id="'.$code.'"':'').'>
//                </a>';

                /** We dont resize images anymore!!! Only thumbnails, on demand.
                $image = new WiziappImageHandler($page->image);

                $size = wiziapp_getImageSize('full_image');
                $url_to_full_sized_image = $image->getResizedImageUrl($page->image, $size['width'], $size['height'], 'resize');

                $size = wiziapp_getImageSize('multi_image');
                $urlToMultiSizedImage = $image->getResizedImageUrl($page->image, $size['width'], $size['height'], 'resize');
                
                $width = $image->getNewWidth();
                $height = $image->getNewHeight();

                $sizeHtml = " width=\"{$width}\" height=\"{$height}\" "; */
                
                $out .= '<a href="' . $page->image . '" class="wiziapp_gallery wiziapp_pageflip_plugin"><img src="' . 
                        $page->image . '" data-wiziapp-pageflipbook-id="' . $code . '"></a>';
            }
            $content = str_replace($matches[0][$match_key], $out, $content);
        }
    }
    return $content;
}
