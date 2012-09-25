<?php

class WiziappGalleries{

    private function _prepareAlbumBasicData($content_id, $images){
        $thePost = get_post($content_id);
        $title = $thePost->post_title;
        if ( !empty($title) ){
            $title = str_replace('&amp;', '&', $title);
        }

        $album = array(
            'images' => array(),
            'numOfImages' => count($images),
            'content_id' => $content_id,
            'id' => $content_id,
            'postID' => $content_id,
            'name' => $title,
            'plugin' => 'bypost', // We can get the albums by plugin like 'wordpress' / 'nextgen' or by post
            'publish_date' => $thePost->post_date,
        );

        foreach ($images as $image) {
            $album['images'][] = $image['info']->attributes->src;
        }

        return $album;
    }

    public function getAll(){
        $albums = array();

        // Get all the images grouped by the content
        $data = WiziappDB::getInstance()->get_images_for_albums();

        if ($data !== FALSE) {
            // We have images, now we need to sort them into albums if they fit the rules
            foreach ($data as $content_id => $images) {
                $minimumForAppearInAlbums = WiziappConfig::getInstance()->count_minimum_for_appear_in_albums;
                if (count($images) >= $minimumForAppearInAlbums) {
                    $albums[] = $this->_prepareAlbumBasicData($content_id, $images);
                }
            }
        }

        return $albums;
    }
}