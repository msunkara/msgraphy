<?php
/**
* @package WiziappWordpressPlugin
* @subpackage ContentDisplay
* @author comobix.com plugins@comobix.com
*/

// Listen to the requests
add_filter('wiziapp_video_albums_request', 'wiziapp_get_videos_albums', 10);

/**
* Adds the basic video album list to the video albums request by using the
* wiziapp_video_albums_request filter
* 
* @see wiziapp_video_albums_request 
* @param array $existing_albums
* @return array the sent albums with the addition of the albums found in the media table
*/
function wiziapp_get_videos_albums($existing_albums){
    $formatedAlbums = array();
    
    $videos = $GLOBALS['WiziappDB']->get_all_videos();
    
    if ( count($videos) > 0 ){
        $formatedAlbums = array();
        $formatedAlbum = array(
            'id' => 'all_videos',
            'name' => 'Videos',
            'plugin' => 'videos',
            //'html' => array(),
            'images' => array(),
            'numOfVideos' => count($videos),
        );        
        
        //$html = array();
        $images = array();
        $rand_videos = array_rand($videos, 3);    
        
	    $size = 'gallery'.count($rand_videos);

        for($v=0, $vTotal=count($rand_videos);$v<$vTotal;++$v){
            $video_info = json_decode($videos[$rand_videos[$v]]['attachment_info'], TRUE);
            //$html[] = wiziapp_getVideoEmbedCode($video_info['actionURL'], $size);
            $images[] = $video_info['thumb'];
        } 
        
        //$formatedAlbum['html'] = $html;
        $formatedAlbum['images'] = $images;
        $formatedAlbums[] = $formatedAlbum; 
    }
    return array_merge($existing_albums, $formatedAlbums);
}
