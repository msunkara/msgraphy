<?php
/**
* Convert the audio macros for the supported plugins to a readable format for the Media Extractor
* 
* @package WiziappWordpressPlugin
* @subpackage Plugins
* @author comobix.com plugins@comobix.com
* 
*/

add_filter('wiziapp_before_the_content', 'wiziapp_audioplayer_filter', 1);
add_filter('wiziapp_before_the_content', 'wiziapp_wpaudiomp3_filter', 1);

function wiziapp_audioplayer_filter($content) {
    $matches = array();
    preg_match_all('/\[audio:\s*(.*)\]/', $content, $matches);
    foreach($matches[1] as $key => $link) {
        list($link, $title) = explode('|', $link);
        if($title == '') {
            $info = pathinfo($link);
            $name = $info['filename'];
        } else {
            list($tmp,$name) = explode('=', $title);
        }
        $content = str_replace($matches[0][$key], '<a href="' . $link . '">' . $name . '</a>', $content);
    }
    return $content;
}

function wiziapp_wpaudiomp3_filter($content){
    $matches = array();
    // without download
    preg_match_all('/\[wpaudio \s*url="(.*)".+\]/', $content, $matches);
    foreach($matches[1] as $key => $link){
        $info = pathinfo($link);
        $name = $info['filename'];
        $content = str_replace($matches[0][$key], '<a href="' . $link . '">' . $name . '</a>', $content);
    }
    // with download
    preg_match_all('/\[wpaudio \s*url="(.*)"\]/', $content, $matches);
    foreach($matches[1] as $key => $link){
        $info = pathinfo($link);
        $name = $info['filename'];
        $content = str_replace($matches[0][$key], '<a href="' . $link . '">' . $name . '</a>', $content);
    }
    return $content;
}