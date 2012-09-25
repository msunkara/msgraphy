<?php
/**
* @package WiziappWordpressPlugin
* @subpackage Display
* @author comobix.com plugins@comobix.com
* 
*/

/**
* wiziapp_prepareCategoriesPage
* 
* Creates a screen with a flat list of categories, since wordpress
* doesn't give us the ability to page the categories list in we are not implementing
* that for now. the chance of a website containing too many categories is not that high
* might add support for this in the future
* 
*/
function wiziapp_prepareCategoriesPage(){
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('categories');
    
    $page = array();
    $pageNumber = isset($_GET['wizipage']) ? $_GET['wizipage'] : 0;
    $catLimit = WiziappConfig::getInstance()->categories_list_limit;
    
    $limitForRequest = $catLimit * 2;
    $offset = $catLimit * $pageNumber;
    
    $categories = get_categories(array(
        'number' => $limitForRequest,
        'offset' => $offset,
        'hierarchical' => FALSE,
    ));
    
    $index = 0;
    foreach ($categories as $cat) {
        $cat->name = str_replace('&amp;', '&', $cat->name);
        wiziapp_appendComponentByLayout($page, $screen_conf['items'], $cat, ++$index);
    }
    
    $catCount = count($categories);
    $pager = new WiziappPagination($catCount, $catLimit);
    $pager->setOffset(0);
    $page = $pager->extractCurrentPage($page);
    $pager->addMoreCell(__("Load %s more items", 'wiziapp'), $page);
    
    echo json_encode(wiziapp_prepareScreen($page, __(WiziappConfig::getInstance()->getScreenTitle('categories'), 'wiziapp'), 'List', false, false, true));
}

function wiziapp_buildTagsPage(){
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('categories', 'tags_list');     
    
    $page = array();
    $pageNumber = isset($_GET['wizipage']) ? $_GET['wizipage'] : 0;
    $tagLimit = WiziappConfig::getInstance()->tags_list_limit;
    $limitForRequest = $tagLimit * 2;
    $offset = $tagLimit * $pageNumber;
    
    $tags = get_tags(array(
        'number' => $limitForRequest,
        'offset' => $offset,
        'hierarchical' => FALSE,
    ));
    
    $index = 0;                               
    foreach ($tags as $tag) {
        $tag->name = str_replace('&amp;', '&', $tag->name);
        wiziapp_appendComponentByLayout($page, $screen_conf['items'], $tag, ++$index);
    } 
    
    $tagCount = count($tags);
    $pager = new WiziappPagination($tagCount, $tagLimit);
    $pager->setOffset(0);
    $page = $pager->extractCurrentPage($page);
    $pager->addMoreCell(__("Load %s more items", 'wiziapp'), $page);
    
    echo json_encode(wiziapp_prepareScreen($page, __(WiziappConfig::getInstance()->getScreenTitle('tags'), 'wiziapp'), 'List', false, false, false));
}

function wiziapp_buildCategoriesByPost($post_id){
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('categories');     
    
    $page = array();

    $categories = get_the_category($post_id);

    $index = 0;
    foreach ($categories as $cat) {
        // Only show categories that has posts in them
        if ( $cat->category_count > 0 ){
            wiziapp_appendComponentByLayout($page, $screen_conf['items'], $cat, ++$index);
        }
    } 
    
    $pager = new WiziappPagination(count($page), WiziappConfig::getInstance()->categories_list_limit);
    $page = $pager->extractCurrentPage($page);
    
    $pager->addMoreCell(__("Load %s more items", 'wiziapp'), $page);
    
    echo json_encode(wiziapp_prepareScreen($page, __(WiziappConfig::getInstance()->getScreenTitle('categories'), 'wiziapp'), 'List'));
}

function wiziapp_buildTagsByPost($post_id){
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('categories', 'tags_list');     
    
    $page = array();
    $tags = get_the_tags($post_id);
    
    $index = 0;                               
    foreach ($tags as $tag) {
        wiziapp_appendComponentByLayout($page, $screen_conf['items'], $tag, ++$index);
    } 
    
    $pager = new WiziappPagination(count($page), WiziappConfig::getInstance()->categories_list_limit);
    $page = $pager->extractCurrentPage($page);
    
    $pager->addMoreCell(__("Load %s more items", 'wiziapp'), $page);
    
    echo json_encode(wiziapp_prepareScreen($page, __(WiziappConfig::getInstance()->getScreenTitle('tags'), 'wiziapp'), 'List', false, false, false));
}

function wiziapp_buildLinksCategoriesPage(){
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('categories', 'links_list');     
    
    $page = array();
    $pageNumber = isset($_GET['wizipage']) ? $_GET['wizipage'] : 0;
    $catLimit = WiziappConfig::getInstance()->links_list_limit;
    $limitForRequest = $catLimit * 2;
    $offset = $catLimit * $pageNumber;
    
    $categories = get_terms('link_category', array(
        'orderby' => 'name', 
        'order' => 'ASC', 
        'number' => $limitForRequest,
        'offset' => $offset,
        'hierarchical' => 0)
    );
    
    $index = 0;
    foreach ($categories as $cat) {
        if ($cat->count > 0){
            $cat->name = str_replace('&amp;', '&', $cat->name);
            wiziapp_appendComponentByLayout($page, $screen_conf['items'], $cat, ++$index);
        }
    } 
    
    $catCount = count($categories);
    $pager = new WiziappPagination($catCount, $catLimit);
    $pager->setOffset(0);
    $page = $pager->extractCurrentPage($page);
    $pager->addMoreCell(__("Load %s more items", 'wiziapp'), $page);
    
    echo json_encode(wiziapp_prepareScreen($page, __(WiziappConfig::getInstance()->getScreenTitle('categories'), 'wiziapp'), 'List', false, false, true));
}

function wiziapp_buildLinksByCategoryPage($cat_id){
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('links');
    
    $page = array();
    $pageNumber = isset($_GET['wizipage']) ? $_GET['wizipage'] : 0;
    $linkLimit = WiziappConfig::getInstance()->links_list_limit;
    $limitForRequest = $linkLimit * 2;
    $offset = $linkLimit * $pageNumber;

    $links = get_bookmarks(array(
        'limit' => $limitForRequest,
        'category' => $cat_id,
        'offset' => $offset,
    ));
    
    foreach ($links as $link) {
        wiziapp_appendComponentByLayout($page, $screen_conf['items'], $link);
    }
    
    $linkCount = count($links);
    $pager = new WiziappPagination($linkCount, $linkLimit);
    $pager->setOffset(0);
    $page = $pager->extractCurrentPage($page);
    $pager->addMoreCell(__("Load %s more items", 'wiziapp'), $page);
    
    echo json_encode(wiziapp_prepareScreen($page, __(WiziappConfig::getInstance()->getScreenTitle('links'), 'wiziapp'), 'List', false, false, true));
}

function wiziapp_buildPagesListPage(){
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('pages');
    
    $page = array();
    $pageNumber = isset($_GET['wizipage']) ? $_GET['wizipage'] : 0;
    $linkLimit = WiziappConfig::getInstance()->links_list_limit;
    $limitForRequest = $linkLimit * 2;
    $offset = $linkLimit * $pageNumber;
    
    $pages = get_pages(array(
        'number' => $limitForRequest,
        'offset' => $offset,
        'sort_column' => 'post_date',
    )); 
    
    $section = array(
        'section' => array(         
            'title' => '',
            'id'    => "allPages",
            'items' => array(),
        )
    );
    
    //$pagesConfig = get_option('wiziapp_pages');
    //$allowedPages = implode(',', $pagesConfig['pages']);
    /**
    * @todo replace this algorithm all together...
    * The admin should send the rules and not the allowed
    */
    //var_dump($allowedPages);
    
    foreach ($pages as $p) {
        $title = str_replace('&amp;', '&', $p->post_title);
        //if ( stripos($allowedPages, $title) !== FALSE ){
        if (true){
            $link = array(
                'link_name' => $title,
                'link_url' => wiziapp_buildPageLink($p->ID),
                'link_id' => $p->ID,
            );
            wiziapp_appendComponentByLayout($section['section']['items'], $screen_conf['items'], (object) $link);                   
        }
    }
    
    $linkCount = count($section['section']['items']);
    $pager = new WiziappPagination($linkCount, $linkLimit);
    $pager->setOffset(0);
    $page = $pager->extractCurrentPage($section['section']['items']);
    $pager->addMoreCell(__("Load %s more items", 'wiziapp'), $page);
    
    echo json_encode(wiziapp_prepareScreen($page, __(WiziappConfig::getInstance()->getScreenTitle('pages'), 'wiziapp'), 'List', false, false, false));
}

function wiziapp_buildLinksPage(){
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('links');
    
//    $limit = wiziapp_getLinksLimit();
    
    $categories = get_terms('link_category', array(
        'orderby' => 'name', 
        'order' => 'ASC', 
        'number' => 200, //We limit this to 200 for now
        'hierarchical' => 0)
    );
    
    $sections = array();
    foreach ($categories as $cat) {
        // Build a section for each category
        if ( $cat->count > 0 ){
            // Get all the links in this category
            $section = array(
                'section' => array(         
                    'title' => $cat->name,
                    'id'    => "cat_{$cat->term_id}",
                    'items' => array(),
                )
            );
            $links = get_bookmarks(array(
                'limit' => $cat->count,
                'category' => $cat->term_id,
            ));
            
            foreach ($links as $link) {
                wiziapp_appendComponentByLayout($section['section']['items'], $screen_conf['items'], $link);
            } 
            $sections[] = $section;
        }
    }
    
    echo json_encode(wiziapp_prepareScreen($sections, __(WiziappConfig::getInstance()->getScreenTitle('links'), 'wiziapp'), 'List', true));
}

function wiziapp_prepareSectionScreen($page = array(), $title = '', $type = 'List', $hide = false, $show_ads = false, $css_class = ''){
    return wiziapp_prepareScreen($page, $title, $type, TRUE, false, $hide, $css_class, $show_ads);
}

function wiziapp_prepareScreen($page = array(), $title = '', $type = 'Post', $sections = FALSE, $force_grouped = FALSE, $hide_seperator = FALSE, $css_class = '', $show_ads = FALSE){
    $key = $sections ? 'sections' : 'items';
    
    $grouped = ($sections || $force_grouped) ? TRUE : FALSE;
    $css_class_name = empty($css_class) ? (($grouped) ? 'screen' : 'flat_screen') : $css_class;
    
    if ($grouped){
        // Verify that the app supports group, the theme might force everything to be not grouped
        if (!WiziappConfig::getInstance()->allow_grouped_lists || $title == 'Links'){
            $grouped = FALSE;
        }
    }
    
    $screen = array(
        'screen' => array(
            'type'    => strtolower($type),
            'title'   => $title,
            'class'   => $css_class_name,
            $key      => $page,
            'update'  => (isset($_GET['wizipage']) && $_GET['wizipage']) ? TRUE : FALSE,
            'grouped' => $grouped,
            'showAds' => $show_ads,
            //'hideCellSeparator' => $hide_seperator,
        )
    );     
    
    if (!$hide_seperator) {
        $screen['screen']['separatorColor'] = WiziappConfig::getInstance()->sep_color;
    }
    
    return $screen;
}

function wiziapp_buildRecentPostsPage(){
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('posts');  
    
    $title = WiziappConfig::getInstance()->app_name;
    
    if ( isset($screen_conf['items_inner']) ){
        wiziapp_buildScrollingCategoriesScreen($screen_conf, $title);
        return;
    }
    
    $numberOfPosts = WiziappConfig::getInstance()->posts_list_limit;
    
    /**
    * Handle paging
    */
    $page = isset($_GET['wizipage'])?$_GET['wizipage']:0;
    
    $query = "orderby=post_date&posts_per_page=".$numberOfPosts;
    
    if ( !empty($page) ){
        $query .= "&offset=".(($numberOfPosts * $page)+1);
    } else {
        // We are in the first page, we still need 1 offset so we won't repeat the featured post
        $query .= '&offset=1';
    }
    
    // With the offset which post have we reached?
    $totalShownPosts = $numberOfPosts + (($numberOfPosts * $page)+1);
    
    // Find the total number of posts in the blog
    $countPosts = wp_count_posts();
    $publishedPosts = $countPosts->publish;
    if ( $totalShownPosts < $publishedPosts ) {
        $leftToShow = $publishedPosts - $totalShownPosts;
        $showMore = $leftToShow < $numberOfPosts ? $leftToShow : $numberOfPosts;
    } else {
        $showMore = FALSE;
    }
        
    /**
    *  Only show the first section on the first request (main page) 
    * the rest of the requests needs to update the recent section
    */
    if ( empty($page) || $page == 0 ){
        $firstQuery = "orderby=post_date&posts_per_page=1";
        $featuredPostSection = array(
            'section' => array(         
                'title' => '',
                'id'    => 'featured_post',
                'items' => array(),
            )
        );
        $featuredPostSection['section']['items'] = 
                wiziapp_buildPostListPage($firstQuery, '', $screen_conf['header'], true);    
    } else {
        $featuredPostSection = array();
    }
    
    $recentSection = array(
        'section' => array(
            'title' => '',
            'id' => 'recent_posts',
            'items' => array(),
        )
    );

    $recentSection['section']['items'] = wiziapp_buildPostListPage($query, '', $screen_conf['items'], true, $showMore);
    
    if ( !empty($featuredPostSection) ){
        $mergedSections = array($featuredPostSection, $recentSection);    
    } else {
        $mergedSections = array($recentSection);
    }
    
    $GLOBALS['WiziappEtagOverride'] .= $title;
    $screen = wiziapp_prepareSectionScreen($mergedSections, $title, "List", false, true);
    echo json_encode($screen);     
}

function wiziapp_buildPostListPage($query = '', $title = '', $block, $just_return = false, $show_more = false, $display_more_item = true){
    /**
    * Use the power of wordpress loop by passing the post component building to a template
    */
    global $wiziapp_block, $cPage;
    $cPage = array();
    $wiziapp_block = $block;
    
    $GLOBALS['WiziappLog']->write('info', "About to query posts by: " . print_r($query, TRUE), 
                                            "screens.wiziapp_buildPostListPage");
    
    query_posts($query);
    
    wiziapp_load_template(dirname(__FILE__) . '/../../themes/iphone/index.php');
    
    /**
    * Format the components in the appropiated screen format
    */
    
    if ($show_more > 0){
        $offset = 0;
        if (!is_array($query)){
            parse_str($query);
        } else {
            $offset = isset($query['offset']) ? $query['offset'] : 0;
        }
        if ($offset > 0){
            $page = floor($offset / WiziappConfig::getInstance()->posts_list_limit);
        } else {
            $page = 0;
        }
        // Now increase the current page so it will point to the next
        ++$page;
        
        if ($display_more_item) {
            $obj = new WiziappMoreCellItem('L1', array(sprintf(__("Load %d more items", 'wiziapp'), $show_more), $page));
            $moreComponent = $obj->getComponent();
            $cPage[] = $moreComponent;
        }    
        
         // Posts lists screens alter their etag, so we need to force include the more tag into the calculation...
         $GLOBALS['WiziappEtagOverride'] .= serialize($moreComponent);
    }
    
    //$pager = new WiziappPagination(count($cPage), appcom_getAppPostListLimit());
//    $cPage = $pager->extractCurrentPage($cPage);
//    
//    $pager->addMoreCell(__("Load %s more items", 'wiziapp'), $cPage);
    
    if ($just_return){    
        $GLOBALS['WiziappLog']->write('info', 'About to return the posts section', 'screens');
        return $cPage;
    } 

    echo json_encode(wiziapp_prepareScreen($cPage, $title, 'list', false, true));
}

function wiziapp_buildVideoPage($video_id){
//    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('video', 'page');
    global $video_row, $blog_title;
//    $page = array();
    //$video_row = $GLOBALS['WiziappDB']->get_videos_by_provider_id($video_id);
    $video_row = WiziappDB::getInstance()->get_videos_by_id($video_id);
    $blog_title = wiziapp_apply_request_title(wp_title('&laquo;', false, 'right').get_bloginfo('name'));

    $GLOBALS['WiziappLog']->write('info', "Preloading the video: " . $video_id, "screens.wiziapp_buildVideoPage");

    wiziapp_load_template(dirname(__FILE__) . '/../../themes/iphone/video.php');
}

//function wiziapp_buildVideoEmbedPage($video_id){
//    header('Content-Type: text/html');
//    $video_row = $GLOBALS['WiziappDB']->get_videos_by_id($video_id);
//    $video_info = json_decode($video_row['attachment_info'], TRUE);
//    $size = 'thumb';
//    if ( isset($_GET['size']) ){
//        $size = $_GET['size'];
//    }
//    $embed = wiziapp_getVideoEmbedCode($video_info['actionURL'], $size, true);
//    echo $embed;
//}

function wiziapp_buildVideosPage(){
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('video', 'list');         
    $title = __(WiziappConfig::getInstance()->getScreenTitle('videos'), 'wiziapp');
    
    $cPage = array();
    $pageNumber = isset($_GET['wizipage']) ? $_GET['wizipage'] : 0;
    $videoLimit = WiziappConfig::getInstance()->videos_list_limit;
    $limitForRequest = $videoLimit * 2;
    $offset = $videoLimit * $pageNumber;
    
    $videos = $GLOBALS['WiziappDB']->get_all_videos($offset, $limitForRequest);
    if ($videos !== FALSE){
        $videos = apply_filters('wiziapp_video_request', $videos);
		$videoCount = count($videos);
        $pager = new WiziappPagination($videoCount, $videoLimit);
        $pager->setOffset(0);
        $videos = $pager->extractCurrentPage($videos);

        $sortedVideos = array();
        $allVideos = array();

        for($v = 0, $vTotal = count($videos); $v < $vTotal; ++$v){
            // Get the video date
            $post = get_post($videos[$v]['content_id']);
            $authorId = $post->post_author;
            $authorInfo = get_userdata($authorId);

            $video = array_merge(
                array(
                    'id' => $videos[$v]['id'],
                    'content_id' => $videos[$v]['content_id'],
                    'author' => $authorInfo->display_name,
                ),
                json_decode($videos[$v]['attachment_info'], TRUE)
            );
            if (!isset($video['gotMobile']) || $video['gotMobile'] == TRUE){
                $sortedVideos[$video['id']] = strtotime($post->post_date);
                $allVideos[$video['id']] = $video;
            }
        }
        arsort($sortedVideos);

        /**
        * Handle paging
        */
        foreach($sortedVideos as $videoId => $videoDate){
            $video = $allVideos[$videoId];
            wiziapp_appendComponentByLayout($cPage, $screen_conf['items'], $video);
        }
        
        $pager->addMoreCell(__("Load %s more items", 'wiziapp'), $cPage);
    }
    
    $screen = wiziapp_prepareScreen($cPage, $title, 'list');
    $screen['screen']['default'] = 'list';
    $screen['screen']['sub_type'] = 'video';
    echo json_encode($screen);
}

function wiziapp_buildAudioPage(){
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('audio', 'list');         
    $title = __(WiziappConfig::getInstance()->getScreenTitle('audio'), 'wiziapp');
    
    $page = array();
    $pageNumber = isset($_GET['wizipage']) ? $_GET['wizipage'] : 0;
    $audioLimit = WiziappConfig::getInstance()->audios_list_limit;
    $limitForRequest = $audioLimit * 2;
    $offset = $audioLimit * $pageNumber;
    
    $audios = $GLOBALS['WiziappDB']->get_all_audios($offset, $limitForRequest);
    $audios = apply_filters('wiziapp_audio_request', $audios);
    
    if ($audios !== FALSE){
        $GLOBALS['WiziappLog']->write('info', "The audios are: " . print_r($audios, TRUE), "screens.wiziapp_buildAudioPage");

        $sortedAudio = array();
        $allAudio = array();
        
        for($a = 0, $aTotal = count($audios); $a < $aTotal; ++$a){
            $audio = array_merge(
                array(
                    'id' => $audios[$a]['id'],
                ), 
                json_decode($audios[$a]['attachment_info'], TRUE)
            );
            $post = get_post($audios[$a]['content_id']);
            $sortedAudio[$audio['id']] = strtotime($post->post_date);
            $allAudio[$audio['id']] = $audio;
        }
        
        arsort($sortedAudio);
        /**
        * Handle paging
        */
        foreach($sortedAudio as $audioId => $audioDate){
            $audio = $allAudio[$audioId];
            wiziapp_appendComponentByLayout($page, $screen_conf['items'], $audio);
        } 

        $audioCount = count($sortedAudio);
        $pager = new WiziappPagination($audioCount, $audioLimit);
        $pager->setOffset(0);
        $page = $pager->extractCurrentPage($page, TRUE);
        $pager->addMoreCell(__("Load %s more items", 'wiziapp'), $page);
    }    
    
    $screen = wiziapp_prepareScreen($page, $title, 'list');
    $screen['screen']['default'] = 'list';
    $screen['screen']['sub_type'] = 'audio';
    echo json_encode($screen);
}

function wiziapp_load_template($template_file){
    global $posts, $post, $wp_did_header, $wp_did_template_redirect, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;
 
    if ( is_array($wp_query->query_vars) )
        extract($wp_query->query_vars, EXTR_SKIP);
 
    require($template_file);
}

function wiziapp_buildPostsByAuthorPage($author_id){
    $numberOfPosts = WiziappConfig::getInstance()->posts_list_limit;
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('posts', 'author_list');
    
    $cQuery = "orderby=modified&posts_per_page=-1&author={$author_id}";
    
    $countQuery = new WP_Query($cQuery);
    $total = count($countQuery->posts);
    
    $pager = new WiziappPagination($total);
    
    $query = "orderby=modified&posts_per_page={$numberOfPosts}&author={$author_id}&offset={$pager->getOffset()}";
    $authorInfo = get_userdata($author_id);
    $authorName = $authorInfo->display_name;
    $title = wiziapp_formatComponentText(__("Posts By:", 'wiziapp')." {$authorName}");
    
    wiziapp_buildPostListPage($query, $title, $screen_conf['items'], false, $pager->leftToShow);
}
        
function wiziapp_buildPostsByIdsPage($ids, $title, $screen_type){
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('posts'); 
    
    $query = array("post__in" => $ids, "orderby"=>"none"); // The oderby none is available only from wordpress 2.8
    $page = wiziapp_buildPostListPage($query, '', $screen_conf['items'], TRUE);
                   
    echo json_encode(wiziapp_prepareScreen($page, $title, $screen_type));     
}

function wiziapp_buildPostsByAttachmentPage($attachment_id){
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('posts'); 
    
    $image = get_post($attachment_id);
    $query = array("post__in" => array($image->post_parent), "orderby"=>"none"); // The oderby none is available only from wordpress 2.8
    $page = wiziapp_buildPostListPage($query, '', $screen_conf['items'], TRUE);
    
    echo json_encode(wiziapp_prepareScreen($page, __('Related Posts', 'wiziapp'), 'list', false, false, false));     
}

/**
* TODO: Add paging support here
* 
*/
function wiziapp_buildPostCommentsPage($post_id){
//    $numberOfPosts = appcom_getAppCommentsListLimit();
    
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('comments');  
    
    $page = array();
    $comments = get_approved_comments($post_id);
    
    $allSection = array(
        'section' => array(
            'title' => '',
            'id' => 'all_comments',
            'items' => array(),
        )
    );
    
    foreach($comments as $comment){
//        $comment_id = $comment->comment_ID;
        // Only add top level comments unless told otherwise
        if ( $comment->comment_parent == 0 ){
            wiziapp_appendComponentByLayout($allSection['section']['items'], $screen_conf['items'], $comment);
        }
     }
     
//     $post = get_post($post_id);
     //$title = str_replace('&amp;', '&', $post->post_title);
     $title = __('Comments', 'wiziapp');
     
     $screen = wiziapp_prepareSectionScreen(array($allSection), $title, "List", false, false, 'comments_screen');
     echo json_encode($screen);
     //echo json_encode(wiziapp_prepareScreen($page, $title, "List", false, true));     
}

/**
* TODO: Add paging support here
* 
*/
function wiziapp_buildPostCommentSubCommentsPage($post_id, $p_comment_id){
//    $numberOfPosts = appcom_getAppCommentsListLimit();
    
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('comments', 'sub_list');     
    
    $page = array();
    $comments = get_approved_comments($post_id);
    // First add the parent comment to the list     
    $parentCommentSection = array(
        'section' => array(         
            'title' => '',
            'id'    => 'parent_comment',
            'items' => array(),
        )
     );
     
    $subCommentsSection = array(
        'section' => array(         
            'title' => '',
            'id'    => 'subComments',
            'items' => array(),
        )
     ); 
        
    $comment = get_comment($p_comment_id);
    
    wiziapp_appendComponentByLayout($parentCommentSection['section']['items'], $screen_conf['header'], $comment);
    foreach($comments as $comment){
        // Only add top level comments unless told otherwise
        if ( $comment->comment_parent == $p_comment_id ){
            wiziapp_appendComponentByLayout($subCommentsSection['section']['items'], $screen_conf['items'], $comment);
        }
     }
     
     //$post = get_post($post_id);
     //$title = str_replace('&amp;', '&', $post->post_title);
     $title = __("Comments", 'title');
     
     $screen = wiziapp_prepareSectionScreen(array($parentCommentSection, $subCommentsSection), $title, "List");
     echo json_encode($screen);
}

function wiziapp_buildTagPage($tag_id){
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('posts');
    
    $pageNumber = isset($_GET['wizipage']) ? $_GET['wizipage'] : 0;
    $numberOfPosts = WiziappConfig::getInstance()->posts_list_limit;
    $offset = $numberOfPosts * $pageNumber;
    
    $tag = get_tag($tag_id);
    $title = wiziapp_apply_request_title("{$tag->name}");
    $query = "tag__in={$tag_id}&orderby=post_date&posts_per_page=" . $numberOfPosts . "&offset=" . $offset;
    
    // Find the total number of posts in the blog
    $totalPostsInTag = $tag->count;
    
    if ($totalPostsInTag < $offset) {
        echo json_encode(wiziapp_prepareSectionScreen(array(), $title, "List"));
        exit();
    }
    
    if ($numberOfPosts < $totalPostsInTag) {
        $showMore = $totalPostsInTag - $numberOfPosts;
    } else {
        $showMore = FALSE;
    }
    
    $posts = wiziapp_buildPostListPage($query, '', $screen_conf['items'], TRUE, $showMore, FALSE);
    $postsCount = count($posts);
    $totalShownPosts = $totalPostsInTag - ($offset + $postsCount);
    if ($totalShownPosts < $numberOfPosts) {
        $showMore = $totalShownPosts;
    } else {
        $showMore = $numberOfPosts;
    }
    
    if ($showMore) {
        $obj = new WiziappMoreCellItem('L1', array(sprintf(__("Load %d more items", 'wiziapp'), $showMore), $pageNumber + 1));
        $moreComponent = $obj->getComponent();
        $posts[] = $moreComponent;
    }

    $section = array();
    $section[] = array(
        'section' => array(
            'title' => '',
            'id' => 'recent',
            'items' => $posts,
        )
    );
    
    echo json_encode(wiziapp_prepareSectionScreen($section, $title, "List"));
    
//    echo json_encode(wiziapp_prepareSectionScreen(array($posts), $title, "List"));
//    echo json_encode(wiziapp_prepareScreen($cPage, $title, 'list', false, true));
}

/**
* Used as an alternative recent page only for supported layouts
* 
* @param array $screen_conf
* @param string $title
*/
function wiziapp_buildScrollingCategoriesScreen($screen_conf, $title){    
    $page = array();
    $numberOfPosts = WiziappConfig::getInstance()->posts_list_limit * 2;
    $numOfScrollingItems = 6; 
    $minScrollingItems = 3;
    
    // Get the recent posts and gather them in categories
//    $posts = get_posts("category={$cat->cat_ID}&numberposts=" . $numberOfPosts);
    $posts = get_posts("numberposts=" . $numberOfPosts);
    $categories = array();
    
    foreach($posts as $post){
        foreach(get_the_category($post->ID) as $cat){
            if ( isset($categories[$cat->cat_ID]) ){
                ++$categories[$cat->cat_ID];
            } else {
                $categories[$cat->cat_ID] = 1;  
            } 
        }
    }

    $catsCounter = 0;
    
    foreach($categories as $catId => $count){
        if ( $count >= $minScrollingItems ){
            $query = "cat={$catId}&orderby=post_date&posts_per_page={$numOfScrollingItems}";
            $items = wiziapp_buildPostListPage($query, '', $screen_conf['items_inner'], TRUE);
            wiziapp_appendComponentByLayout($page, $screen_conf['items'], get_category($catId), $items);
            ++$catsCounter;
        }
    }
    
    echo json_encode(wiziapp_prepareScreen($page, $title, 'List', false, false, true));             
}

function wiziapp_buildCategoryPage($category_id){                                         
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('posts');
    
    $pageNumber = isset($_GET['wizipage']) ? $_GET['wizipage'] : 0;
    $numberOfPosts = WiziappConfig::getInstance()->posts_list_limit;
    $offset = $numberOfPosts * $pageNumber;
    
    $cat = get_category($category_id);
    $title = wiziapp_apply_request_title($cat->cat_name);

    $query = "cat={$category_id}&orderby=post_date&posts_per_page=" . $numberOfPosts . "&offset=" . $offset;
    
    // Find the total number of posts in the blog
    $totalPostsInCat = $cat->count;
    
    if ($totalPostsInCat < $offset) {
        echo json_encode(wiziapp_prepareSectionScreen(array(), $title, "List"));
        exit();
    }
    
    if ($numberOfPosts < $totalPostsInCat) {
        $showMore = $totalPostsInCat - $numberOfPosts;
    } else {
        $showMore = FALSE;
    }
    
    $posts = wiziapp_buildPostListPage($query, '', $screen_conf['items'], TRUE, $showMore, FALSE);
    $postsCount = count($posts);
    $totalShownPosts = $totalPostsInCat - ($offset + $postsCount);
    if ($totalShownPosts < $numberOfPosts) {
        $showMore = $totalShownPosts;
    } else {
        $showMore = $numberOfPosts;
    }
    
    if ($showMore) {
        $obj = new WiziappMoreCellItem('L1', array(sprintf(__("Load %d more items", 'wiziapp'), $showMore), $pageNumber + 1));
        $moreComponent = $obj->getComponent();
        $posts[] = $moreComponent;
    }

    $section = array();
    $section[] = array(
        'section' => array(
            'title' => '',
            'id' => 'recent',
            'items' => $posts,
        )
    );

    echo json_encode(wiziapp_prepareSectionScreen($section, $title, "List"));
//    echo json_encode(wiziapp_prepareSectionScreen(array($posts), $title, "List"));
}

function wiziapp_buildCommentPage($comment_id){
     echo json_encode(wiziapp_prepareScreen(array(), 'TBD', "Sample"));    
}

/**
* TODO: Add paging support here
* 
*/
function wiziapp_buildCommentedPostsPage($author_id){
    $numberOfPosts = WiziappConfig::getInstance()->comments_list_limit;
    
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('posts', 'commented_list');   
    
    $page = array();
    
    global $wpdb;

    $key = md5( serialize( "posts=true&number={$numberOfPosts}&user_id={$author_id}")  );
    $last_changed = wp_cache_get('last_changed', 'comment');
    if ( !$last_changed ) {
        $last_changed = time();
        wp_cache_set('last_changed', $last_changed, 'comment');
    }
    $cache_key = "get_comments:$key:$last_changed";

    if ( $cache = wp_cache_get( $cache_key, 'comment' ) ) {
        $comments = $cache;
    } else {
        $approved = "comment_approved = '1'";     
        $order = 'DESC';
        $orderby = 'comment_date_gmt'; 
        $number = 'LIMIT ' . $numberOfPosts; 
        $post_where = "user_id = '{$author_id}' AND ";
        
        $comments = $wpdb->get_results( "SELECT * FROM $wpdb->comments 
                                        WHERE $post_where $approved ORDER BY $orderby $order $number" );
        wp_cache_add( $cache_key, $comments, 'comment' );
    }
    
    $posts = array();
    // Get the posts id, and num of personal comments in each
    foreach($comments as $comment){
        $post_id = $comment->comment_post_ID;
        if ( !isset($posts[$post_id]) ){
            $posts[$post_id] = 0;
        }
        ++$posts[$post_id];
     }
     
     foreach($posts as $post_id => $user_comments_count){
        //$comment_id = $comment->comment_ID;
        wiziapp_appendComponentByLayout($page, $screen_conf['items'], $post_id, $user_comments_count);
     }
     
     $title = __('My Commented Posts', 'wiziapp');
     
     echo json_encode(wiziapp_prepareScreen($page, $title, "List"));         
}

/**
* TODO: Add paging support here
* 
*/
function wiziapp_buildMyCommentsPage($author_id){
    $numberOfPosts = WiziappConfig::getInstance()->comments_list_limit;
    
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('comments', 'user_list');  
    
    $page = array();
    
    global $wpdb;

    $key = md5( serialize( "number={$numberOfPosts}&user_id={$author_id}")  );
    $last_changed = wp_cache_get('last_changed', 'comment');
    if ( !$last_changed ) {
        $last_changed = time();
        wp_cache_set('last_changed', $last_changed, 'comment');
    }
    $cache_key = "get_comments:$key:$last_changed";

    if ( $cache = wp_cache_get( $cache_key, 'comment' ) ) {
        $comments = $cache;
    } else {
        $approved = "comment_approved = '1'";     
        $order = 'DESC';
        $orderby = 'comment_date_gmt'; 
        $number = 'LIMIT ' . $numberOfPosts; 
        $post_where = "user_id = '{$author_id}' AND ";
        
        $comments = $wpdb->get_results( "SELECT * FROM $wpdb->comments WHERE $post_where $approved ORDER BY $orderby $order $number" );
        wp_cache_add( $cache_key, $comments, 'comment' );
    }
    
    foreach($comments as $comment){
        wiziapp_appendComponentByLayout($page, $screen_conf['items'], $comment);
     }
     
     $title = __('My Comments', 'wiziapp');
     
     echo json_encode(wiziapp_prepareScreen($page, $title, "List"));     
}

/**
* TODO: Add paging support here
* 
*/
function wiziapp_buildArchiveYearsPage(){
    global $wpdb;
    
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('archives', 'list');  
    
    $where = apply_filters('getarchives_where', "WHERE post_type = 'post' AND post_status = 'publish'");
    $join = apply_filters('getarchives_join', "");
    
    $query = "SELECT YEAR(post_date) AS `year`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date) ORDER BY post_date DESC";
    $GLOBALS['WiziappLog']->write('info', "Prepared the archive query: {$query}", "screens.wiziapp_buildArchiveYearsPage");
    $key = md5($query);
    $cache = wp_cache_get( 'wiziapp_buildArchiveYearsPage' , 'general');
    if ( !isset( $cache[ $key ] ) ) {
        $results = $wpdb->get_results($query);
        $cache[ $key ] = $results;
        wp_cache_add( 'wiziapp_buildArchiveYearsPage', $cache, 'general' );
    } else {
        $results = $cache[ $key ];
    }
    
    
    $allYears = array(
        'section' => array(
            'title' => '',
            'id' => 'allYears',
            'items' => array(),
        )
    );
     
    if ($results) {
        foreach ( (array) $results as $result) {
            $year = sprintf('%d', $result->year);
            $posts = $result->posts;
            wiziapp_appendComponentByLayout($allYears['section']['items'], $screen_conf['items'], $year, $posts, 'years');
        }
    }
    //$title = __('Archive', 'wiziapp');
    $title = __(WiziappConfig::getInstance()->getScreenTitle('archive'), 'wiziapp');
         
    echo json_encode(wiziapp_prepareSectionScreen(array($allYears), $title, "List", false, true));     
}

/**
* TODO: Add paging support here
* 
*/
function wiziapp_buildArchiveMonthsPage($year){
    global $wpdb, $wp_locale;
    
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('archives', 'months_list');    
    
    $allMonths = array(
        'section' => array(
            'title' => '',
            'id' => 'allYears',
            'items' => array(),
        )
    );
    
    $where = apply_filters('getarchives_where', 
                            "WHERE post_type = 'post' AND post_status = 'publish' 
                            AND YEAR(post_date) = {$year}");
                            
    $join = apply_filters('getarchives_join', "");
    
    $query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts 
                FROM $wpdb->posts $join $where 
                GROUP BY YEAR(post_date), MONTH(post_date) 
                ORDER BY post_date DESC";
    $GLOBALS['WiziappLog']->write('info', "Prepared the archive query: {$query}", 
                                        "screens.wiziapp_buildArchiveYearsPage");
    $key = md5($query);
    $cache = wp_cache_get( 'wiziapp_buildArchiveMonthsPage' , 'general');
    if ( !isset( $cache[ $key ] ) ) {
        $results = $wpdb->get_results($query);
        $cache[ $key ] = $results;
        wp_cache_add( 'wiziapp_buildArchiveMonthsPage', $cache, 'general' );
    } else {
        $results = $cache[ $key ];
    }
    
    if ($results) {
        foreach ( (array) $results as $result) {
            $title = sprintf(__('%1$s'), $wp_locale->get_month($result->month)); 
            $posts = $result->posts;
            wiziapp_appendComponentByLayout($allMonths['section']['items'], 
                                            $screen_conf['items'], $title, $posts, 
                                            'months', $year, $result->month);
        }
    }
    $title = $year;
    
    echo json_encode(wiziapp_prepareSectionScreen(array($allMonths), $title, "List", false, true));       
}

/**
* TODO: Add paging support here
* 
*/
function wiziapp_buildArchiveByDayOfMonthPage($year, $month, $day){
    global $wp_locale;      
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('posts', 'archived_list'); 
    
    $numberOfPosts = WiziappConfig::getInstance()->posts_list_limit;
    
    $query = "orderby=modified&posts_per_page={$numberOfPosts}&monthnum={$month}&year={$year}&day={$day}";
    
    $title = sprintf(__('%3$d %1$s %2$d'), $wp_locale->get_month($month), $year, $day);
    wiziapp_buildPostListPage($query, $title, $screen_conf['items']); 
}

function wiziapp_buildArchiveByMonthPage($year, $month){
    global $wp_locale;
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('posts', 'archived_list'); 
    
    $numberOfPosts = WiziappConfig::getInstance()->posts_list_limit;
    
    $cQuery = "orderby=modified&posts_per_page=-1&monthnum={$month}&year={$year}";
    
    $countQuery = new WP_Query($cQuery);
    $total = count($countQuery->posts);
    
    $pager = new WiziappPagination($total);
    
    $query = "orderby=modified&posts_per_page={$numberOfPosts}&monthnum={$month}&year={$year}&offset={$pager->getOffset()}";
    
    $title = sprintf(__('%1$s %2$d'), $wp_locale->get_month($month), $year);
     
    wiziapp_buildPostListPage($query, $title, $screen_conf['items'], false, $pager->leftToShow); 
}

function wiziapp_getImageUrl () {
    $width = $_GET['width'];
    //if ($width == 0 || $width == '') {
//        $width = $_GET['wiziapp/content/list/getimage?width'];
//    }
     
    $image = new WiziappImageHandler($_GET['url']);
    $image->wiziapp_getResizedImage($width, $_GET['height'], $_GET['type'], $_GET['allow_up']);
}

/**
* @todo Add paging support here
* 
*/
function wiziapp_buildImagesGalleryByPost($post_id, $ids = false){
    if($ids){
        $images_ids = explode('_', $ids);
    }else{
        $images_ids = false;
    }
    $post = get_post($post_id);

    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('images');  
    $page = array();

    if ($post->post_type == 'page') {
        // Get all of the page images as stored on our table
        $images = $GLOBALS['WiziappDB']->find_page_media($post_id, 'image');
    } else {
        // Get all of the post images as stored on our table
        $images = $GLOBALS['WiziappDB']->find_post_media($post_id, 'image');
    }
    
    /**if ( ! function_exists('wp_load_image') ) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }  */
    
    foreach($images as $image_info){
        $attributes = json_decode($image_info['attachment_info'], TRUE);
        
        /**if(is_array($images_ids) && (!in_array($image_info['id'], $images_ids) && !in_array($attributes['metadata']['id'], $images_ids))){
            continue;
        }*/

        if ( is_array($images_ids) ){
            if ( !in_array($image_info['id'], $images_ids) ){
                continue;
            }
        }

        $image = $attributes['attributes'];
        
        $pid = $image_info['id'];
        $image['pid'] = $pid;
        $image['description'] = '';
        $image['alttext'] = $image['title'];
        $image['imageURL'] = $image['src'];
        $image['relatedPost'] = $post_id;
        
        // The images component will take care of the resizing
        $image['thumbURL'] = $image['src'];
        
        wiziapp_appendComponentByLayout($page, $screen_conf['items'], $image, true);
     }
     
     $title = str_replace('&amp;', '&', $post->post_title);
     $screen = wiziapp_prepareScreen($page, $title, 'gallery', false, true);
     
     $screen['screen']['default'] = 'grid';
     $screen['screen']['sub_type'] = 'image';
     echo json_encode($screen);
}

/**
* TODO: Add paging support here
* 
*/
function wiziapp_buildImagesPage($list_view = TRUE){
    $numberOfPosts = WiziappConfig::getInstance()->comments_list_limit;
    
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('images');  
    
    $page = array();
    
    $args = array(
        'post_type' => 'attachment',
        'numberposts' => -1,
        'post_status' => null,
        'post_parent' => null, // any parent
    ); 
    $attachments = get_posts($args);
    
    $counter = 0;
    foreach($attachments as $attachment){
        $isImage = wp_attachment_is_image($attachment->ID);
        if ( $isImage && $counter < $numberOfPosts ){      
            wiziapp_appendComponentByLayout($page, $screen_conf['items'], $attachment);
            ++$counter;
        }
        if ( $counter == $numberOfPosts ){
            break;
        }
     }
     
     $title = __('Gallery', 'wiziapp');
     $screen = wiziapp_prepareScreen($page, $title, 'gallery', false, true);
     
     $screen['screen']['default'] = 'grid';
     $screen['screen']['sub_type'] = 'image';
     echo json_encode($screen);     
}

function wiziapp_buildGalleriesPage(){
    $GLOBALS['WiziappLog']->write('info', "Building galleries page", 
                                            'screens.wiziapp_buildPluginGalleriesPage');
    
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('albums');  
    
    $page = array();
    $albumLimit = WiziappConfig::getInstance()->posts_list_limit;
    
    $sortedAlbums = array();
    $allAlbums = array();
    
//    $albums = apply_filters('wiziapp_albums_request', $albums);
    // @todo If such method already exists, it should implement the pager.
    $galleries = new WiziappGalleries();
    $albums = $galleries->getAll();
    
    for($a = 0, $total_albums = count($albums); $a < $total_albums; ++$a){
        $album = $albums[$a];
        
        $sortedAlbums[$album['postID'] . '_' . $album['id']] = strtotime($album['publish_date']); 
        $allAlbums[$album['postID'] . '_' . $album['id']] = $album; 
    }
    
    arsort($sortedAlbums); 
    
    foreach($sortedAlbums as $albumId => $albumDate){ 
        $album = $allAlbums[$albumId]; 
        $config_key = 'items';
        if ($sortedAlbums[$albumId]['plugin'] == 'videos'){
            $config_key = 'videos_items';
        }
        wiziapp_appendComponentByLayout($page, $screen_conf[$config_key], $album);            
    }
    
    $albumCount = count($sortedAlbums);
    $pager = new WiziappPagination($albumCount, $albumLimit);
    $page = $pager->extractCurrentPage($page, FALSE);
    $pager->addMoreCell(__("Load %s more items", 'wiziapp'), $page);
    
    /*$GLOBALS['WiziappLog']->write('info', "Got the page: ".print_r($page, TRUE), 
                                            "screens.wiziapp_buildPluginGalleriesPage");*/
    $title = __(WiziappConfig::getInstance()->getScreenTitle('albums'), 'wiziapp');
    $screen = wiziapp_prepareScreen($page, $title, 'list', false, true);
     
    echo json_encode($screen);
}

/**
* @todo Add paging support here
* 
*/
function wiziapp_buildGalleryPluginPage($plugin, $item_id){
    $GLOBALS['WiziappLog']->write('info', "Got a request for a gallery from {$plugin} item is: {$item_id}", "screens.wiziapp_buildGalleryPluginPage");
    $images = array();
    // Check if we support this plugin
    $plugin = strtolower($plugin);
    
    $images = apply_filters("wiziapp_get_{$plugin}_album", $images, $item_id);
    
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('images');  
    $page = array();
    
    foreach($images as $image){
        wiziapp_appendComponentByLayout($page, $screen_conf['items'], $image, true);
    }
     
    $title = __('Gallery', 'wiziapp');
    $screen = wiziapp_prepareScreen($page, $title, 'gallery');
     
    $screen['screen']['default'] = 'grid';
    $screen['screen']['sub_type'] = 'image';
    echo json_encode($screen);     
}

function wiziapp_buildAboutScreen(){
    $actions = array();
    
    $pages = get_option('wiziapp_pages');
    foreach($pages['about'] as $aboutPage){
        $actions[] = array(
            'imageURL' => 'cuts_reg_gold',
            'title' => "{$aboutPage}",
            'actionURL' => wiziapp_buildBlogPageLink($aboutPage),
        );
    }

    $app_name = WiziappConfig::getInstance()->app_name;
    if (strlen($app_name) > 20) {
        $app_name = wiziapp_makeShortString($app_name, 20);
    }

    $page = array(
        'title' => $app_name,
        'version' =>  __('version') . ' ' . WiziappConfig::getInstance()->version,
        'imageURL' => WiziappConfig::getInstance()->getAppIcon(),
        'aboutTitle' => __('About', 'wiziapp') . ' ' . $app_name,
        'aboutContent' => WiziappConfig::getInstance()->getAppDescription(),
        //'actions' => $actions
        'actions' => array()
    );
    $screen = wiziapp_prepareScreen($page, __(WiziappConfig::getInstance()->getScreenTitle('about'), 'wiziapp'), 'about');
    $screen['screen']['class'] = 'about_screen';
    echo json_encode($screen);
}

function wiziapp_buildRegisterForm($message=''){
    $_SESSION['wiziapp_message'] = $message;
    wiziapp_load_template(dirname(__FILE__).'/../../themes/iphone/register.php');
    exit();
}
function wiziapp_buildForgotPassForm($message=''){
    $_SESSION['wiziapp_message'] = $message;
    wiziapp_load_template(dirname(__FILE__).'/../../themes/iphone/forgot_password.php');
    exit();
}