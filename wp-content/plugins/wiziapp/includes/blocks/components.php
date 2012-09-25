<?php
/**
* @todo orgenize the code here and remove the decrepted functions
* 
* @package WiziappWordpressPlugin
* @subpackage UIComponents
* @author comobix.com plugins@comobix.com
* 
*/

function wiziapp_makeShortString($str, $len){
    if (strlen($str) > $len) {
        $str = wordwrap($str, $len);
        $str = substr($str, 0, strpos($str, "\n"));
        if ($str[strlen($str) - 1] == ','){
            $str = substr($str, 0, strlen($str) - 1);
        }
        $str = $str . '...';
    }
    return $str;
}

function wiziapp_loadComponentsClasses(){
    $GLOBALS['WiziappLog']->write('info', "Loading components classes", 'components.wiziapp_loadComponentsClasses');
    if (is_dir(dirname(__FILE__) . "/components")){
        if ($func_dir = opendir(dirname(__FILE__) . "/components")){
            while (($file = readdir($func_dir)) !== false){
                if (preg_match("/\.php$/", $file) && !preg_match("/^index\.php$/i", $file)){
                    if (strpos($file, "_") !== 0 && strpos($file, '.') !== 0){
                        include_once dirname(__FILE__) . "/components/" . $file;   
                    }
                }
            } 
        }
    } 
    
    $GLOBALS['wiziapp_LoadedComponents'] = TRUE;       
}

$GLOBALS['wiziapp_LoadedComponents'] = FALSE;

/**
* This method will convert the page layout instruction 
* to a known component. and then it will append it to the page 
* which is passed by reference
* 
* @param array $page
* @param string $block
*/
function wiziapp_appendComponentByLayout(&$page, $block){
    // Replace the following code with autoloader in blocks.inc.php
    /**if (!$GLOBALS['wiziapp_LoadedComponents']){
        wiziapp_loadComponentsClasses();            
    }*/
    
    /**
    * Since this function is used for creating different type of pages
    * we can an unknown number of parameters depending on the 
    * calling method
    */
    $params = func_get_args();
    /**
    * Removes the first two parameters from the params array
    * since we already know them by name
    */
    $tmpPage = array_shift($params);
    $tmpBlock = array_shift($params);
    $num = func_num_args();
    //$GLOBALS['WiziappLog']->write('info', "Appending {$num} to page: ".print_r($params, TRUE), "content");

    $className = ucfirst($block['class']);
    $layout = $block['layout'];
    if (class_exists($className)){
        $obj = new $className($layout, $params);
        if ($obj->isValid()){
            $page[] = $obj->getComponent();        
        }
    } 
}   

function wiziapp_simplifyText($text){
    $text = preg_replace('/<br\\s*?\/??>/i', "\n", $text);
    $text = strip_tags($text);
    $text = stripslashes($text);
    return $text;        
}

function wiziappGetSubCommentsCount($post_id, $comment_id){
    global $wpdb;

    $approved = "comment_approved = '1'";
    $post_where = "comment_post_ID = '{$post_id}' AND comment_parent = '{$comment_id}' AND";
    $count = $wpdb->get_var( "SELECT count(*) FROM $wpdb->comments WHERE $post_where $approved" );

    return (int)$count;
}

function wiziapp_getPostThumbnail($post, $size, $limitSize){
    return get_bloginfo('url') . "/?wiziapp/getthumb/{$post->ID}&width={$size['width']}&height={$size['height']}&limitWidth={$limitSize['height']}&limitHeight={$limitSize['height']}";
}

/**    
* function wiziapp_getPostThumbnail($post, $size){
    if (!isset($_GLOBALS['WiziappPostThumbs'])){
        $GLOBALS['WiziappPostThumbs'] = array();
    }
    
    if (!isset($_GLOBALS['WiziappPostThumbs'][$post->ID])){
        $GLOBALS['WiziappPostThumbs'][$post->ID] = wiziapp_doPostThumbnail($post, $size);
    } 
    
    $imageUrl = $GLOBALS['WiziappPostThumbs'][$post->ID];
    
    return $imageUrl;
}  */


function wiziapp_doPostThumbnail($post, $size, $limitSize){
    $foundImage = FALSE;
    $GLOBALS['WiziappLog']->write('info', "Getting the post thumbnail: {$post}", "wiziapp_doPostThumbnail");  
    @include_once(ABSPATH . 'wp-includes/post-thumbnail-template.php');
    if(function_exists('get_the_post_thumbnail')){ //first we try to get the wordpress post thumbnail
        $GLOBALS['WiziappLog']->write('debug', "The blog supports post thumbnails", "wiziapp_doPostThumbnail");
        if (has_post_thumbnail($post)){
            $foundImage = wiziapp_tryWordpressThumbnail($post, $size, $limitSize);
        }
    } else {
        $GLOBALS['WiziappLog']->write('debug', "the function get_the_post_thumbnail does not exists", "wiziapp_doPostThumbnail");      
    }
    
    $singles = array();
    if (!$foundImage){ // if no wordpress thumbnail, we take the thumb from a gallery
        if ( !wiziapp_tryGalleryThumbnail($post, $size, $limitSize, $singles) ){
            // if no thumb from a gallery, we take the thumb from a video
            if ( !wiziapp_tryVideoThumbnail($post, $size, $limitSize) ){
                // if no thumb from a video, we take the thumb from a single image
                $foundImage = wiziapp_trySingleImageThumbnail($singles, $size, $limitSize);
            }
        }
    }    

    if ( !$foundImage ){
        // If we reached this point we couldn't find a thumbnail.... Throw 404
        header("HTTP/1.0 404 Not Found");
    }
    return;
}

function wiziapp_tryWordpressThumbnail($post, $size, $limitSize) {
    $showedImage = FALSE;
    $post_thumbnail_id = get_post_thumbnail_id($post);
    $wpSize = array(
        $size['width'],
        $size['height'],
    ); 
    $image = wp_get_attachment_image_src($post_thumbnail_id, $wpSize); 
    //$image = wp_get_attachment_image_src($post_thumbnail_id);
    $showedImage = wiziapp_processImageForThumb($image[0], $size, $limitSize);
    
    return $showedImage;
}

function wiziapp_tryGalleryThumbnail($post, $size, $limitSize, &$singles) {
    $post_media = $GLOBALS['WiziappDB']->find_post_media($post, 'image');
    $showedImage = FALSE;
    
    if(!empty($post_media)){
        $singlesCount = count($singles);
        $galleryCount = 0;
        foreach($post_media as $media) {
            $encoding = get_bloginfo('charset');
            $dom = new WiziappDOMLoader($media['original_code'], $encoding);
            $tmp = $dom->getBody();
            $attributes = (object) $tmp[0]['img']['attributes'];
            
            $info = json_decode($media['attachment_info']);
            if (!isset($info->metadata)){ // Single image
                if ($singlesCount < WiziappConfig::getInstance()->max_thumb_check){
                    $singles[] = $attributes->src;                                            
                    ++$singlesCount;   
                }
            } else {
                if ($galleryCount < WiziappConfig::getInstance()->max_thumb_check){
                    if ($showedImage = wiziapp_processImageForThumb($attributes->src, $size, $limitSize)){
                        return $showedImage;
                    }
                    ++$galleryCount;
                }
            }
        }
    }
    return $showedImage;
}

function wiziapp_tryVideoThumbnail($post, $size, $limitSize) {
    $showedImage = FALSE;
    $post_media = $GLOBALS['WiziappDB']->find_post_media($post, 'video');
    if(!empty($post_media)){
        $media = $post_media[key($post_media)];
        $info = json_decode($media['attachment_info']);
        if(intval($info->bigThumb->width) >= ($size['width'] * 0.8)){
            $image = new WiziappImageHandler($info->bigThumb->url);
            $showedImage = $image->wiziapp_getResizedImage($size['width'], $size['height'], 'adaptiveResize', true);
            
        }
    }
    return $showedImage;
}

function wiziapp_trySingleImageThumbnail($singles, $size, $limitSize) {
    $showedImage = FALSE;
    foreach($singles as $single) {
        $image = new WiziappImageHandler($single);  // The original image
        $image->load();
        $width = $image->getNewWidth();
        $height = $image->getNewHeight();
        if(intval($width) >= $limitSize['width'] && intval($height) >= $limitSize['height']){
            if(intval($width) >= ($size['width'] * 0.8) && intval($height) >= ($size['height'] * 0.8)){
                $showedImage = wiziapp_processImageForThumb($single, $size, $limitSize);
            }
        }            
    }
    
    return $showedImage;
}

function wiziapp_processImageForThumb($src, $size, $limitSize){
    $showedImage = FALSE;
    if (!empty($src)){          
        $image = new WiziappImageHandler($src);  // The original image
        $image->load();
        $width = $image->getNewWidth();
        $height = $image->getNewHeight();
        
        if(intval($width) >= $limitSize['width'] && intval($height) >= $limitSize['height']){
            if(intval($width) >= ($size['width'] * 0.8) && intval($height) >= ($size['height'] * 0.8)){
                //$imageUrl = $image->getResizedImageUrl($src, $size['width'], $size['height'], 'adaptiveResize', true);                        
                $image->wiziapp_getResizedImage($size['width'], $size['height'], 'adaptiveResize', true);
                $showedImage = TRUE;
            }    
        }
    }
    return $showedImage;
}

function wiziapp_getPostShortList($post_id){
    $post = get_post($post_id);
    
    $count = $post->comment_count;
    
    $authorId = $post->post_author;
    $authorLink = wiziapp_buildAuthorLink($authorId);
    $authorInfo = get_userdata($authorId);
    $authorName = $authorInfo->display_name;
    $title = strip_tags($post->post_title);
    $postLink = wiziapp_buildPostLink($post_id);
    
    $obj = array(
        "Paragraph" => array(
            "class" => "comments_count_css",
            "params" => array(
                "CDATA" => "<h3><a href=\"{$postLink}\" title=\"{$title}\">{$title}</a></h3>Posted by <a href=\"{$authorLink}\" title=\"{$authorName}\">{$authorName}</a><p><span>{$count} " . __("comments") . "</span></p>",
            )
        )
    );
    
    return $obj;    
}

/**function wiziapp_getCommentsCount($post_id){
    $post = get_post($post_id);        
    $count = $post->comment_count;
    $obj = array(
        "Text" => array(
            "class" => "comments_count_css",
            "params" => array(
                "CDATA" => "{$count} ".__("comments"),
            )
        )
    );
    return $obj;    
}  */

function wiziapp_getPostTitleAuthorDateLink($post_id){
    $post = get_post($post_id);
    $title = strip_tags($post->post_title);
    $postLink = wiziapp_buildPostLink($post_id);
    $authorId = $post->post_author;
    $authorLink = wiziapp_buildAuthorLink($authorId);
    $authorInfo = get_userdata($authorId);
    $authorName = $authorInfo->display_name;
    $postDate = $post->post_date;
    $obj = array(
        "Paragraph" => array(
            "class" => "header_css",
            "params" => array(
                "CDATA" => "<h2><a href=\"{$postLink}\" title=\"{$title}\">{$title}</a></h2><a href=\"{$authorLink}\" title=\"{$authorName}\">{$authorName}</a><span> on {$postDate}</span>",
            )
        )
    );
    return $obj;
}

function wiziapp_getPostAuthorLink($post_id){
    $post = get_post($post_id);        
    $authorId = $post->post_author;
    $authorLink = wiziapp_buildAuthorLink($authorId);
    $authorInfo = get_userdata($authorId);
    $authorName = $authorInfo->display_name;
    $obj = array(
        "Paragraph" => array(
            "class" => "author_date_css",
            "params" => array(
                "CDATA" => "<a href=\"{$authorLink}\" title=\"{$authorName}\">{$authorName}</a>",
            )
        )
    );
    return $obj;
}

function wiziapp_getPostAuthorDateLink($post_id){
    $post = get_post($post_id);        
    $authorId = $post->post_author;
    $authorLink = wiziapp_buildAuthorLink($authorId);
    $authorInfo = get_userdata($authorId);
    $authorName = $authorInfo->display_name;
    $postDate = $post->post_date;
    $obj = array(
        "Paragraph" => array(
            "class" => "author_date_css",
            "params" => array(
                "CDATA" => "<a href=\"{$authorLink}\" title=\"{$authorName}\">{$authorName}</a><span> on {$postDate}</span>",
            )
        )
    );
    return $obj;
}

function wiziapp_getPostTitleLink($post_id){
    $post = get_post($post_id);
    $title = strip_tags($post->post_title);
    $postLink = wiziapp_buildPostLink($post_id);
    $obj = array(
        "Paragraph" => array(
            "class" => "major_title_css",
            "params" => array(
                "CDATA" => "<a href=\"{$postLink}\" title=\"{$title}\">{$title}</a>",
            )
        )
    );
    return $obj;
}

function wiziapp_getCategoriesLinks($post_id){
    $navLinks = array();
    
    foreach((get_the_category($post_id)) as $category) { 
        $navLinks[] = array("link"=>array(
            "text"  => wiziapp_formatComponentText($category->cat_name),
            "image" => "",
            "link"  => wiziapp_buildCategoryLink($category->cat_ID),
        ));
    }
    
    $nav = array("navigation"=>array("links" => $navLinks), 'class'=>'categories_nav_css');
    return wiziapp_specialComponent("navigation", $nav);
}

function wiziapp_getTagsLinks($post_id){
    $navLinks = array();
    $tags = get_the_tags($post_id);
    
    if ($tags){
        foreach($tags as $tag) { 
            $navLinks[] = array("link"=>array(
                "text"  => wiziapp_formatComponentText($tag->name),
                "image" => "",
                "link"  => wiziapp_buildTagLink($tag->term_id),
            ));
        }    
    } else {
        return FALSE;
    }
    
    
    $nav = array("navigation"=>array("links" => $navLinks), 'class'=>'tags_nav_css');
    return wiziapp_specialComponent("navigation", $nav);
}


function wiziapp_getPostNavigation($post_id){
    /**
    * Navigation template tags works inside templates,
    * but since we could really use the great work 
    * wordpress team made there we should fake the
    * loop so we can reuse the code. It will require manually
    * settings the is_single attribute of the global wp_query 
    * to true, so wordpress will think there is a point in showing
    * the navigation links...
    */
    global $post, $wp_query;
    $wp_query->is_single = TRUE;
    $post = get_post($post_id);    
    setup_postdata($post);
    $nav = array();
    $navLinks = array();
    
    // Get the prev/next posts links
    $prevPost = get_adjacent_post(FALSE, '', TRUE);
    if($prevPost) {
        $navLinks[] = array("link"=>array(
            "text"  => wiziapp_formatComponentText(str_replace('&amp;', '&', $prevPost->post_title), __("Previous Post")),
            "image" => wiziapp_getPrevPostImage(),
            "link"  => wiziapp_buildPostLink($prevPost->ID),
        ));
    }

    $nextPost = get_adjacent_post(FALSE, '', FALSE);
    if($nextPost) {
        $navLinks[] = array("link"=>array(
            "text"  => wiziapp_formatComponentText(str_replace('&amp;', '&', $nextPost->post_title), __("Next Post")),
            "image" => wiziapp_getNextPostImage(),
            "link"  => wiziapp_buildPostLink($nextPost->ID),
        ));        
    }
    $nav = array("navigation"=>array("links" => $navLinks));
    return wiziapp_specialComponent("navigation", $nav);
}

function wiziapp_formatComponentText($str, $default=''){
    $str = strip_tags($str);
    if (empty($str)){
        $str = $default;
    }
    return $str;
}
