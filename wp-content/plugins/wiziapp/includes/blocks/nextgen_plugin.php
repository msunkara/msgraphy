<?php
/**
* This plugin handles the albums retrieval from NextGEN
* 
* @package WiziappWordpressPlugin
* @subpackage Plugins
* @author comobix.com plugins@comobix.com
* 
*/

add_filter('wiziapp_albums_request', 'wiziapp_get_nextgen_albums', 10);

function wiziapp_get_nextgen_albums($existing_albums){
    if (class_exists('nggLoader')) {
        global $nggdb;
//        $albums = $nggdb->find_all_album();
        $galleries = array();
        $formatedAlbums = array();
//        $ngg_galleries = $nggdb->find_all_galleries(); 

        $metadata = $GLOBALS['WiziappDB']->get_media_metadata('image', array('nextgen-album-id'));
        if ($metadata) {
            foreach($metadata as $media_id => $keys){
                //if(!$albums_ids || !in_array($keys['nextgen-album-id'], $albums_ids)){
                    $album = $nggdb->find_album($keys['nextgen-album-id']);
                    //(empty($album->pageid)){
                        $album->pageid = $GLOBALS['WiziappDB']->get_content_by_media_id($media_id);
                    //
                    $albums[]= $album;
                    //$albums_ids[] = $keys['nextgen-album-id'];
                //}
            }
        }    
        // If the album has multiple galleries use the galleries as albums
        if (!(count($albums) == 1 && count($albums[0]->galleries) > 1)){
            if ($albums) {
                foreach($albums as $album){
                    // Make sure the album is published in a page or post
                    if ($album->pageid != 0) {
                        $the_post = get_post($album->pageid);
                        $dateline = $the_post->post_date;
                        // id,name,images,plugin
                        $formatedAlbum = array(
                            'id' => $album->id,
                            'postID' => $the_post->ID,
                            'name' => str_replace('&amp;', '&', $the_post->post_title),
                            'plugin' => 'nextgen',
                            'images' => array(),
                            'publish_date' => $dateline
                        );
                        //$images = $nggdb->find_images_in_album($album);
                        // Get the galleries, and 
                        $images = array();

                        foreach($album->gallery_ids as $gallery_id){
                            $galleries[] = $gallery_id;
                            $images = array_merge($images, $nggdb->get_gallery($gallery_id));
                        }

                        $imagesCount = count($images);
                        $formatedAlbum['numOfImages'] = $imagesCount;
                        if ($formatedAlbum['numOfImages'] > 0){
                            //if ( $imagesCount >= 3 ){
    //                            $previewImages = array_rand($images, 3);
    //                        } else {
                                $previewImages = array_keys($images);
    //                        }

                            if ($previewImages){
                                for($i = 0, $total = count($previewImages); $i < $total; ++$i){
                                    $formatedAlbum['images'][] = $images[$previewImages[$i]]->thumbURL;
                                }
                            } 
                            $formatedAlbums[] = $formatedAlbum;   
                        }
                    }
                }
            }    
        }    
        // Go over all the galleries and make sure we didn't miss anything
        $metadata = $GLOBALS['WiziappDB']->get_media_metadata('image', array('nextgen-gallery-id'));
        if ($metadata) {
            foreach($metadata as $media_id => $keys){
                //if(!$galleries_ids || !in_array($keys['nextgen-gallery-id'], $galleries_ids)){
                    $gallery = $nggdb->find_gallery($keys['nextgen-gallery-id']);
                    //if(empty($gallery->pageid)){
                        $gallery->pageid = $GLOBALS['WiziappDB']->get_content_by_media_id($media_id);
                    //}
                    $ngg_galleries[] = $gallery;
                  //  $galleries_ids[] = $keys['nextgen-gallery-id'];
                //}
            }
        }    
        if ($ngg_galleries) {
            foreach ($ngg_galleries as $gallery){
                $GLOBALS['WiziappLog']->write('info', "Checking NextGEN galleries", 'nextgen_plugin.wiziapp_get_nextgen_albums');
                if (!in_array($gallery->gid, $galleries)){
                    $GLOBALS['WiziappLog']->write('info', "Checking if the gallery is published {$gallery->gid}", 
                                                  'nextgen_plugin.wiziapp_get_nextgen_albums');
                    // Make sure it is published 
                    if ($gallery->pageid != 0){
                        $GLOBALS['WiziappLog']->write('info', "About to create a component for the gallery", 
                                                      'nextgen_plugin.wiziapp_get_nextgen_albums');
                        $the_post = get_post($gallery->pageid);
                        $dateline = $the_post->post_date;
                        $formatedAlbum = array(
                            'id' => 'g' . $gallery->gid,
                            'postID' => $the_post->ID,
                            'name' => str_replace('&amp;', '&', $the_post->post_title),
                            'plugin' => 'nextgen',
                            'images' => array(),
                            'publish_date' => $dateline
                        );
                        //$images = $nggdb->find_images_in_album($album);
                        // Get the galleries, and
                        $gimages = $nggdb->get_gallery($gallery->gid);
                        $gimagesCount = count($gimages);
                        $GLOBALS['WiziappLog']->write('info', "The total number of items in this gallery is:" . $gimagesCount, 
                                                      'nextgen_plugin.wiziapp_get_nextgen_albums');
                        $formatedAlbum['numOfImages'] = $gimagesCount;
                        $minimumForAppearInAlbums = WiziappConfig::getInstance()->count_minimum_for_appear_in_albums;
                        
                        if ($formatedAlbum['numOfImages'] >= $minimumForAppearInAlbums){   
                            $previewImages = array_keys($gimages);
                            
//                            $GLOBALS['WiziappLog']->write('info', "The preview images are: " . print_r($previewImages, TRUE), "nextgen_plugin.wiziapp_get_nextgen_albums");
                            
                            $total = count($previewImages);
                            //$lastIndex = end($gimages)->pid;
                            //for($i = reset($gimages)->pid; $i <= $lastIndex; ++$i){
                            //$lastIndex = end($gimages)->pid;
                            //for($i = reset($gimages)->pid; $i <= $lastIndex; ++$i){
                                /** We dont resize images anymore!!! Only thumbnails, on demand.
                                $realImage = new WiziappImageHandler(htmlspecialchars_decode($gimages[$i]->imageURL));
                                $url_to_resized_image = $realImage->getResizedImageUrl($gimages[$i]->imageURL, wiziapp_getMultiImageWidthLimit(), 0, 'resize'); */
                              //  $formatedAlbum['images'][] = $gimages[$i]->thumbURL;
                            for($i=0; $i < $total; ++$i){
                                $formatedAlbum['images'][] = $gimages[$previewImages[$i]]->thumbURL;
                            }
                                
                            //}
                            $imagesCount = count($formatedAlbum['images']);
    //                        if ($imagesCount >= $minimumForAppearInAlbums) {
                            $formatedAlbum['numOfImages'] = $imagesCount;
                            $formatedAlbums[] = $formatedAlbum;
                            
    //                        $formatedAlbum = array_slice($formatedAlbum, 0, 3); //Limit to 3 for display
    //                        }    
                        }
                    }
                }
            }
        }    
    } else {
        $GLOBALS['WiziappLog']->write('error', "NextGEN plugin is not installed or not active", "nextgen_plugin.wiziapp_get_nextgen_albums");
    }

    $results = $existing_albums;
    if (is_array($formatedAlbums)){
        $results = array_merge($existing_albums, $formatedAlbums);
    }
    return $results;
}

/**
* @todo Add paging support here
* 
*/
add_filter('wiziapp_get_nextgen_album', 'wiziapp_getAlbumFromNextGen', 10, 2);
function wiziapp_getAlbumFromNextGen($images, $item_id){
    $images = array();
    // Verify that the NextGEN plugin is exists and active
    if (class_exists('nggLoader')) {
        global $nggdb;
        
        if (strpos($item_id, 'g') === 0){
            $images = wiziapp_getImagesFromNextgenGallery(str_replace('g', '', $item_id));
        } else {
            $album = $nggdb->find_album($item_id);
            $GLOBALS['WiziappLog']->write('info', "The NextGet album is " . print_r($album, TRUE), 
                                          'nextgen_plugin.wiziapp_getAlbumFromNextGen');
            foreach($album->gallery_ids as $gallery_id){
                $images = array_merge($images, wiziapp_getImagesFromNextgenGallery($gallery_id, $album));
            }            
        }   
    } else {
        $GLOBALS['WiziappLog']->write('error', "NextGEN plugin is not installed or not active", 'screens.wiziapp_getAlbumFromNextGen');
    }
    return $images;    
}

function wiziapp_getImagesFromNextgenGallery($gallery_id, $album = FALSE){
    $GLOBALS['WiziappLog']->write('info', "The NextGet gallery is {$gallery_id}", 'screens.wiziapp_getAlbumFromNextGen');
    global $nggdb;   
    $images = array();
    $ngImages = $nggdb->get_gallery($gallery_id);
    
    foreach($ngImages as $image){
        /** We dont resize images anymore!!! Only thumbnails, on demand.
        $realImage = new WiziappImageHandler(htmlspecialchars_decode($image->imageURL));
        $url_to_resized_image = $realImage->getResizedImageUrl($image->imageURL, wiziapp_getMultiImageWidthLimit(), 0, 'resize'); */
        
        // Get the post id
        if (!$album){
            $metadata = $GLOBALS['WiziappDB']->get_media_metadata_equal('image', 'nextgen-gallery-id', $gallery_id);
        } else {
            $metadata = $GLOBALS['WiziappDB']->get_media_metadata_equal('image', 'nextgen-album-id', $album->id);
        }    
        if ( $metadata != FALSE ) {
//            $postId = $metadata[key($metadata)]['content_id'];
            $index = array_pop(array_keys($metadata));
            $postId = $metadata[$index]['content_id'];
        }
        
        if ($image->post_id == 0){
            // Get the page
            if ($image->pageid != 0){
                $image->relatedPost = $image->pageid;
            } else {
                if ($album != FALSE && $album->pageid != 0){
                    $image->relatedPost = $album->pageid;   
                }
            }
        } else {
            $image->relatedPost = $image->post_id;
        }
        
        if (!isset($image->relatedPost) && isset($postId)) {
            $image->relatedPost = $postId;
        }
        $images[] = $image;
    }
    return $images;
}


add_filter('wiziapp_before_the_content', 'wiziapp_nextgenImagebrowserFilter', 1);

function wiziapp_nextgenImagebrowserFilter($content){
    global $nggdb;
    $matches = array();
    /* Find all nextgen singlepic */
    preg_match_all('/\[\s*singlepic\s*id=([a-zA-Z0-9]*)(.*)\]/', $content, $matches);
    /* Extract image*/
    if(!empty($matches[1])){
        foreach($matches[1] as $match_key=>$image_id){
            $out = '';
            $image = $nggdb->find_image($image_id);
            $params = trim($matches[2][$match_key]);
            if(!empty($params)){
                $width = array();
                $height = array();
                $float = array();
                preg_match('/w=(\d*)/', $params, $width);
                preg_match('/h=(\d*)/', $params, $height);
                preg_match('/float=(\D*)/', $params, $float);
                $width = (empty($width) ? 'auto' : $width[1]);
                $height = (empty($height) ? 'auto' : $height[1]);
                $float = (empty($float) ? 'none' : $float[1]);
            }
            if ($float == 'center') {
                $out = "<a href=\"{$image->imageURL}\"><img src=\"{$image->imageURL}\" width=\"$width\" height=\"$height\" class=\"aligncenter\"></a>";
            } else {
                $out = "<a href=\"{$image->imageURL}\"><img src=\"{$image->imageURL}\" width=\"$width\" height=\"$height\" style=\"float: {$float};\"></a>";
            }
            $content = str_replace($matches[0][$match_key], $out, $content);
        }
    }           
    /* Find all nextgen imagebrowsers */
    $patterns = array(
        '/\[\s*imagebrowser\s*id=([a-zA-Z0-9]*)\]/'
        ,'/\[\s*slideshow\s*id=([a-zA-Z0-9]*)\]/'
        ,'/\[\s*nggallery\s*id=([a-zA-Z0-9]*)\]/'
    );
    foreach ($patterns as $pattern) {
        $matches = array();
        preg_match_all($pattern, $content, $matches);
        /* Extract images*/
        if(!empty($matches[1])){
            foreach($matches[1] as $match_key=>$gallery_id){
                $out = '';
                $images = wiziapp_getImagesFromNextgenGallery($gallery_id);
                foreach ($images as $image){
                    /** We dont resize images anymore!!! Only thumbnails, on demand.
                    $image = new WiziappImageHandler($image->imageURL);
                    $url_to_resized_image = $image->getResizedImageUrl($image->imageURL, wiziapp_getMultiImageWidthLimit(), 0, 'resize');
                    $width = $image->getNewWidth();
                    $height = $image->getNewHeight();
                    $size = wiziapp_getImageSize('multi_image');
                    $urlToDeviceSizedImage = $image->getResizedImageUrl($image->imageURL, $size['width'], $size['height'], 'resize'); */
                    $out .= "<a href=\"{$image->imageURL}\" class=\"wiziapp_gallery wiziapp_nextgen_plugin\"><img src=\"{$image->imageURL}\" data-wiziapp-nextgen-gallery-id=\"{$gallery_id}\"></a>";
                }
                $content = str_replace($matches[0][$match_key], $out, $content);
            }
        }
    }       
    $matches = array();
    preg_match_all('/\[\s*album\s*id=([a-zA-Z0-9]*)\s*.*\]/', $content, $matches);
    /* Extract images*/
    if(!empty($matches[1])){
        foreach($matches[1] as $match_key=>$album_id){
            $out = '';
            $galleries_id = $nggdb->find_album($album_id)->gallery_ids;
            if (!empty($galleries_id)){
                foreach($galleries_id as $gallery_id){
                    $images = wiziapp_getImagesFromNextgenGallery($gallery_id);
                    if (!empty($images)){
                        foreach ($images as $image){
                            /** We dont resize images anymore!!! Only thumbnails, on demand.
                            $image = new WiziappImageHandler($image->imageURL);

                            $size = wiziapp_getImageSize('full_image');
                            $url_to_full_sized_image = $image->getResizedImageUrl($image->imageURL, $size['width'], $size['height'], 'resize');

                            $size = wiziapp_getImageSize('multi_image');
                            $urlToMultiSizedImage = $image->getResizedImageUrl($image->imageURL, $size['width'], $size['height'], 'resize');
                            
                            $width = $image->getNewWidth();
                            $height = $image->getNewHeight(); */
                            
                            $out .= "<a href=\"{$image->imageURL}\" class=\"wiziapp_gallery wiziapp_nextgen_plugin\">" . 
                                    "<img src=\"{$image->imageURL}\" data-wiziapp-nextgen-album-id=\"$album_id\"></a>";
                        }   
                    }
                }
                $content = str_replace($matches[0][$match_key], $out, $content);
            }
        }
    }   
    return $content;
}