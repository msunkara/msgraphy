<?php
add_filter('wiziapp_before_the_content', 'wiziapp_viper_filter', 1);

/**
 * find all viper videos (youtube, blip and vimeo) on page and replace it by a standard embed
 * @param string $content - content of post
 * @return string - changed content
 */
function wiziapp_viper_filter($content){
    if(function_exists('VipersVideoQuicktags')){
        $matches = array();
        $youtubeMatches = array();
        $vimeoMatches = array();
        $blipMatches = array();
        
        preg_match_all('/\[\s*youtube\s*\](\S)+\[\s*\/\s*youtube\s*\]/', $content, $youtubeMatches);
        preg_match_all('/\[\s*vimeo\s*\][\S]+\[\s*\/\s*vimeo\s*\]/', $content, $vimeoMatches);
        preg_match_all('/\[\s*blip\.tv\s*\?(\S)+\s*\]/', $content, $blipMatches);
         
        $matches = array_merge($youtubeMatches, $vimeoMatches, $blipMatches);
        
        if(empty($matches[1]))
            return $content;
            
//        [youtube]http://www.youtube.com/watch?v=NjfFpFW9OdB[/youtube]
//        [vimeo]http://www.vimeo.com/240975[/vimeo]
//        [blip.tv ?posts_id=1213119&amp;dest=-2]

        foreach($matches as $match_key => $code){
            if (substr($code[0], 0, 1) == '[') {
                if (strpos($code[0], 'blip.tv')){
                    $start = strpos($code[0], '=');
                    $end = strpos($code[0], '&amp');
                    $id = substr($code[0], $start + 1, $end - $start - 1);
//                    $url = wiziapp_buildVideoLink('blip.tv', $id);
                    $url = 'http://blip.tv/file/' . $id;
                } else {
                    $start = strpos($code[0], ']');
                    $end = strpos($code[0], '[/');
                    $url = substr($code[0], $start + 1, $end - $start - 1);
                }
    
                $embeddedVideo = wp_oembed_get($url, array());
                $content = str_replace($matches[$match_key][0], $embeddedVideo, $content);
            }
        }
    }
    return $content;
}
/**
function wiziapp_getVideoEmbedCodeFromURL($url) {
    $contentWidth = $GLOBALS['content_width'];
    
    if (strpos($url, 'youtube')) {
        $html = '<div class="video_wrapper">' . wp_oembed_get($url, array()) . '</div>';
        // we removed support for blip coz of bugs and no time to fix, might come back in the future...
//    } elseif (strpos($url, 'blip.tv')){
//        $html = '<div class="video_wrapper">' . wp_oembed_get($url, array()) . '</div>';
    } elseif (strpos($url, 'vimeo')){
        $GLOBALS['content_width'] = 300;
        $html = '<div class="vimeo_wrapper">' . wp_oembed_get($url, array()) . '</div>';
        $GLOBALS['content_width'] = $contentWidth;
    }
    
    // Wrap the html with the full page
    $fullPage = $html;
    if (!empty($html)){
        $fullPage = "<!DOCTYPE HTML><html><head><meta name=\"apple-mobile-web-app-capable\" content=\"yes\">
        <meta name=\"viewport\" content=\"width={$contentWidth}, initial-scale=1.0; user-scalable=0;\" />
        <title>Video Page</title><style>body{padding:0px;margin:0px;overflow:hidden;}</style></head>
        <body>{$html}
        <script type=\"text/javascript\">
            document.ontouchmove=function(event){
                event.preventDefault();
            };
            function playVid(){ 
                var vid = document.getElementById('vid'); 
                if (vid.tagName == 'VIDEO') { 
                    vid.play(); 
                } 
            }
        </script></body></html>";
    }
    return $fullPage;
}*/