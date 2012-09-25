<?php
/**
* Gets the video html embed code for the specified provider
* 
* @todo Adds external plugins integration support here
* 
* @package WiziappWordpressPlugin
* @subpackage UIComponents
* @author comobix.com plugins@comobix.com
*/

class WiziappVideoEmbed{
    private $content_width = 300;

    /**
     * This method will return the embed html code for a specific video by
     * it's actionURL as defined in the protocol API.
     *
     * If the request indicates that it is inside the simulator the code will
     * be returned with images instead of the video object since the simulator
     * doesn't handle flash videos well.
     *
     * @param  $url the actionURL for the video
     * @param  int $id the id to identify the video by
     * @param  array $thumbData an object containing the needed data on the thumbnail to display for the movie
     * @return string the html code for the embed
     */
    public function getCode($url, $id=0, $thumbData=array()){
        if (isset($_GET['sim']) && $_GET['sim'] == 1 && !empty($id) && !empty($thumbData)){
            return $this->_getSimulatorCode($id, $thumbData);
        } else {
            return $this->_getNormalEmbedCode($url);
        }
    }

    private function _getNormalEmbedCode($url){
        $urlParts = explode('/', $url);
        $movie_id =  $urlParts[5];
        $provider = $urlParts[4];
        $html = "";

        $contentWidth = $GLOBALS['content_width'];
        $GLOBALS['content_width'] = $this->content_width;

        if ($provider == 'youtube'){
            $html = '<div class="video_wrapper data-wiziapp-iphone-support">' . wp_oembed_get('http://www.youtube.com/watch?v=' . $movie_id, array()) . '</div>';

            // we removed support for blip coz of bugs and no time to fix, might come back in the future...
    //    } elseif ($provider == 'blip.tv'){
    //        $partUrl = urldecode($urlParts[6]);
    //        $html = '<div class="video_wrapper data-wiziapp-iphone-support">' . wp_oembed_get( $partUrl, array()) . '</div>';
        } elseif ( $provider == 'vimeo' ){
            $partUrl = urldecode($urlParts[6]);
            $movie_id = substr($partUrl, strrpos($partUrl, '/') + 1);

            $html = '<div class="vimeo_wrapper data-wiziapp-iphone-support">' . wp_oembed_get('http://vimeo.com/' . $movie_id, array()) . '</div>';
        }

        $GLOBALS['content_width'] = $contentWidth;

        return $html;
    }

    private function _getSimulatorCode($id, $thumbData){
        $html = '<div class="video_wrapper_container">';
        $html .= '<div class="video_wrapper" data-video="video_' . $id . '">
                            <img src="' . $thumbData['url'] . '" height="' . $thumbData['height'] . '" width="' . $thumbData['width'] . '"/>
                            <div class="video_effect"></div>
                         </div>';
        $html .= '</div>';

        return $html;
    }
}