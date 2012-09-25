<?php
/**
* @package WiziappWordpressPlugin
* @subpackage UIComponents
* @author comobix.com plugins@comobix.com
*/    
function wiziapp_buildAuthorLink($author_id){
    return 'nav://list/' . urlencode(get_bloginfo('url') . "/?wiziapp/content/list/author/{$author_id}/posts");
}

function wiziapp_buildPostLink($post_id){
    //return get_bloginfo('url')."/wiziapp/content/post/{$post_id}";
    $link = urlencode(get_permalink($post_id));
    $url = '';
    if ( !empty($link) ){
        $url = "nav://post/{$link}";
    }
    return $url;
}

function wiziapp_buildPageLink($page_id){
    return 'nav://page/' . urlencode(get_page_link($page_id));
}

function wiziapp_buildBlogPageLink($page_name){
    $page = get_page_by_title($page_name);
    return wiziapp_buildPageLink($page->ID);
}

function wiziapp_buildCategoryLink($category_id){
    return 'nav://list/' . urlencode(get_bloginfo('url') . "/?wiziapp/content/list/category/{$category_id}");
}

function wiziapp_buildTagLink($tag_id){         
    return 'nav://list/' . urlencode(get_bloginfo('url') . "/?wiziapp/content/list/tag/{$tag_id}");
}

function wiziapp_buildPostTagsLink($post_id){
    return 'nav://list/' . urlencode(get_bloginfo('url') . "/?wiziapp/content/list/post/{$post_id}/tags");    
}

function wiziapp_buildLinksByCategoryLink($cat_id){
    return 'nav://list/' . urlencode(get_bloginfo('url') . "/?wiziapp/content/list/links/category/{$cat_id}");
}

function wiziapp_buildPostCategoriesLink($post_id){
    return 'nav://list/' . urlencode(get_bloginfo('url') . "/?wiziapp/content/list/post/{$post_id}/categories");
}

function wiziapp_buildPostImagesGalleryLink($post_id){
    return 'nav://gallery/' . urlencode(get_bloginfo('url') . "/?wiziapp/content/list/post/{$post_id}/images");
}

function wiziapp_buildPostCommentsLink($post_id){
    //return 'nav://list/'.urlencode(get_bloginfo('url')."/wiziapp/content/list/post/{$post_id}/comments");
    return 'nav://comments/' . urlencode(get_bloginfo('url') . "/?wiziapp/content/list/post/{$post_id}/comments");
}

function wiziapp_buildPostCommentSubCommentsLink($post_id, $comment_id){
    return 'nav://comments/' . urlencode(get_bloginfo('url') . "/?wiziapp/content/list/post/{$post_id}/comments/{$comment_id}");
}

function wiziapp_buildPostNewCommentLink($post_id){
    return "nav://comment/{$post_id}";
}

/**
* If the providers won't support mp4 version of the file try the video id will be -1
* 
* 
* @param mixed $provider
* @param mixed $video_id
* @param mixed $url
*/
function wiziapp_buildVideoLink($provider, $video_id, $url=''){
    $url = urlencode($url);
    return "cmd://open/video/{$provider}/{$video_id}/{$url}";
}        

function wiziapp_buildAudioLink($provider, $url=''){
    $url = urlencode($url);
    return "cmd://open/{$provider}/{$url}";
}        


function wiziapp_extractProviderFromVideoLink($link){
    $tmp = str_replace('://', '', $link);
    $tmp = split('/', $tmp);
    return $tmp[2];
}

function wiziapp_getVideoPageLink($item_id){
    return "nav://page/" . urlencode(get_bloginfo('url') . "/?wiziapp/content/video/{$item_id}");
}

function wiziapp_buildVideoDetailsLink($item_id){
    return "nav://video/" . urlencode(get_bloginfo('url') . "/?wiziapp/content/list/media/video/{$item_id}");
}

function wiziapp_buildArchiveYearLink($year){
    return 'nav://list/' . urlencode(get_bloginfo('url') . "/?wiziapp/content/list/archive/{$year}");
}

function wiziapp_buildArchiveMonthLink($year, $month){
    return 'nav://list/' . urlencode(get_bloginfo('url') . "/?wiziapp/content/list/archive/{$year}/{$month}");
}

function wiziapp_buildArchiveDateLink($year, $month, $day){
    return 'nav://list/' . urlencode(get_bloginfo('url') . "/?wiziapp/content/list/archive/{$year}/{$month}/{$day}");
}

function wiziapp_buildAttachmentRelatedPostsLink($attachment_id){
    return 'nav://list/' . urlencode(get_bloginfo('url') . "/?wiziapp/content/list/attachment/{$attachment_id}/posts");
}

function wiziapp_buildPluginAlbumLink($plugin='', $album_id){
    return 'nav://gallery/' . urlencode(get_bloginfo('url') . "/?wiziapp/content/list/gallery/{$plugin}/{$album_id}");
}

function wiziapp_getRatingLink(){
    $url = urlencode(get_bloginfo('url') . "/?wiziapp/rate/post/");
    return "cmd://openRanking/{$url}";
}


function wiziapp_buildMoreLink($page){
    // Get the current request url
    $requestUri = $_SERVER['REQUEST_URI'];
    // Isolate wiziapp part of the request
    $wsUrl = substr($requestUri, strpos($requestUri, 'wiziapp/')); 
    
    $sep = '&';
    if (strpos($wsUrl, '?') !== FALSE){
        $wsUrl = str_replace('?', '&', $wsUrl);   
    }
    
    $url = 'nav://list/' . urlencode(get_bloginfo('url') . "/?{$wsUrl}{$sep}wizipage={$page}");

    return $url;
}

function wiziapp_buildExternalLink($url){
    return $url;
}

/**
* @deprecated
*/
function wiziapp_buildImageResizingLink($src, $width, $height){
    // @todo Find a solution for the index.php
    return "http://admin.apptelecom.com/index.php/simulator/resize?src={$src}&w={$width}&h={$height}";
}

function wiziapp_buildLinkToImage($url){
    /**
    * Make sure the image doesn't exceed the device size,
    * but only do this if we haven't converted it yet
    */
    
    /** We dont resize images anymore!!! Only thumbnails, on demand.
    if (strpos($url, 'wiziapp/cache/') === FALSE){
        $image = new WiziappImageHandler($url);
        $size = wiziapp_getImageSize('full_image');
        $url = $image->getResizedImageUrl($url, $size['width'], 0);
    } */
                
    return "cmd://open/image/" . urlencode($url);
}

function wiziapp_convertVideoActionToWebVideo($actionURL){ 
    return str_replace("open/video", "open/videopage", $actionURL);
} 