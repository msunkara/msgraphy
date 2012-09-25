<?php if (!defined('WP_WIZIAPP_BASE')) exit();
/**
* @package WiziappWordpressPlugin
* @subpackage AppWebServices
* @author comobix.com plugins@comobix.com
* 
*/

/**
* Searching can be trigger by the:
*   Auto Complete service
* 
*   Search service
* 
*  both should support the following categories:
*   - All
*   - Author
*   - Tag
*   - Post
* 
*/

/**
* Run the search itself
* 
* GET /wiziapp/search
* 
* @param string category defines where we are searching (all/author/tag/post)
* @param string keyword the search term
* 
* @returns a post list screen for the application 
*/
function wiziapp_do_search(){
    $category = $_GET['category'];
    $keyword = $_GET['keyword'];
    
    $screen_conf = $GLOBALS['WiziappScreens']->getScreenLayout('posts');
    $pageNumber = isset($_GET['wizipage']) ? $_GET['wizipage'] : 0;
    $resultLimit = WiziappConfig::getInstance()->posts_list_limit;
    $offset = $resultLimit * $pageNumber;
    $limitForQuery = $resultLimit * 2;
    $query = "offset={$offset}&orderby=modified&posts_per_page={$limitForQuery}";
    
    // Do not include the same posts, keep track on the posts we collected.
    // @todo there is no need to keep track of this for now
    $GLOBALS['wp_posts_listed'] = array();
    
    $GLOBALS['WiziappLog']->write('info', "Searching for posts for {$category}, and with the chars: {$keyword}", "search.wiziapp_do_search");
    // According to the search we need to get the searched posts here:
    if ($category == 'authors'){
        $query = "{$query}&author_name={$keyword}";
    } elseif ($category == 'posts'){
        $query = "{$query}&s={$keyword}&post_type=post";
    } else if ( $category == 'all' ){
        $query = "{$query}&s={$keyword}&post_type=any";
    }
    
    $page = wiziapp_buildPostListPage($query, '', $screen_conf['items'], true);

    $resultCount = count($page);
    $pager = new WiziappPagination($resultCount, $resultLimit);
    /**
     * We are querying the limit * 2 so we can show the next number of items.
     * Every query already takes the offset into account, therefore we need to
     * set the offset as 0 for the extract page part
     */
    $pager->setOffset(0);
    /**
     * When returning component lists we must *never* keep the array keys since the
     * protocol defined the component must be a non-associative array
     */
    $page = $pager->extractCurrentPage($page, FALSE);
    /**
     * Leave the check whether we should add the show more component to the pager
     */
    $pager->addMoreCell(__("Load %s more items", 'wiziapp'), $page);
    
    // The prepareScreen needs to know where are returning a list screen
    $screen = wiziapp_prepareScreen($page, __(WiziappConfig::getInstance()->getScreenTitle('search'), 'wiziapp'), 'list');
    echo json_encode($screen);
}

/**
* Used in the auto-complete feature
* 
* GET /wiziapp/keywords
* 
* @param string category defines where we are searching (all/author/tag/post)
* @param string keyword the search term
* 
* @returns a post list screen for the application 
*/
function wiziapp_get_search_keywords(){
    $category = $_GET['category'];
    $keyword = $_GET['keyword'];
    
    $returnStrings = array();
    
    if ($category == 'authors' || $category == 'all'){
        $authors = wiziapp_get_authors($keyword); 
        $returnStrings = array_merge($returnStrings, $authors);        
    }
    
    if ($category == 'tags' || $category == 'all'){
        $tags = wiziapp_get_tags($keyword); 
        $returnStrings = array_merge($returnStrings, $tags);        
    }
    
    if ($category == 'posts' || $category == 'all'){
        $subjects = wiziapp_get_posts_keywords($keyword);
        $returnStrings = array_merge($returnStrings, $subjects);
    }
    
    if ($category == 'all') {
        // if we are getting everything we might have doubles, save traffic by reducing doubles
        $returnStrings = array_unique($returnStrings);
    }
    
    $status = TRUE;
    $header = array(
        'action' => 'keywords',
        'status' => $status,
        'code' => ($status) ? 200 : 4004,
        'message' => '',
    );
    
    $contents =  json_encode(array_merge(array('header' => $header), 
                                        array('keywords' => implode(',', $returnStrings))));
                                                
    if (isset($_GET['callback'])){
        $GLOBALS['WiziappLog']->write('debug', "The callback GET param set:" . $_GET["callback"] .
                                    "(" . $contents . ")", "search.wiziapp_get_search_keywords");
        /**
        * Support cross-domain ajax calls for web clients
        * @todo Add a check to verify this is a web client
        */
         $contents = $_GET["callback"] . "({$contents})";  
    } else {
        $GLOBALS['WiziappLog']->write('debug', "The callback GET param is not set", 
                                                "remote.WiziappRequestHandler._routeContent");
    }    
            
    echo $contents;
    exit();
}

function wiziapp_get_posts_keywords($keyword){
    $posts = get_posts('s=' . $keyword . '&numberposts=' . WiziappConfig::getInstance()->search_limit);

    $returnSubjects = array();
    foreach($posts as $post){
        $returnSubjects[] = str_replace('&amp;', '&', $post->post_title);
    }
    return $returnSubjects;
}

function wiziapp_get_terms($chars, $return_id = FALSE){
    global $wpdb;  
    $returnTerms = array();
    $limit = WiziappConfig::getInstance()->search_limit;
    //$sql = "SELECT name FROM {$wpdb->terms} t, {$wpdb->term_taxonomy} tt WHERE tt.term_id = t.term_id AND tt.count > 0 AND t.name LIKE '%{$chars}%' ORDER BY t.name LIMIT {$limit}";
    $sql = $wpdb->prepare("SELECT name FROM {$wpdb->terms} t, {$wpdb->term_taxonomy} tt WHERE tt.term_id = t.term_id AND tt.count > 0 AND t.name LIKE %s ORDER BY t.name LIMIT {$limit}", "%{$chars}%");
    $terms = $wpdb->get_results($sql);
    $GLOBALS['WiziappLog']->write('info', "The sql was: {$sql}", "search.wiziapp_get_terms");
    $GLOBALS['WiziappLog']->write('info', "The terms are: " . print_r($terms, TRUE), "search.wiziapp_get_terms");
    
    foreach ($terms as $term){  
        if (!$return_id){
            $returnTerms[] = $term->name;    
        } else {
            $returnTerms[] = $term->term_id;
        }
    }
    return $returnTerms;
}

function wiziapp_get_tags($chars, $return_id = FALSE){
    $tags = get_tags(array(
        'search' => $chars,
        'limit' => WiziappConfig::getInstance()->search_limit,
    ));
    $resultTags = array();
    
    foreach($tags as $tag){
        if (!$return_id){
            $resultTags[] = $tag->name;    
        } else {
            $resultTags[] = $tag->term_id;    
        }
        
    }
    return $resultTags;
}

function wiziapp_get_authors($chars, $return_id = FALSE) {
    global $wpdb;

//    $hide_empty = TRUE;
//    $html = FALSE;
//
//
//    $defaults = array(
//        'optioncount' => false, 'exclude_admin' => true,
//        'show_fullname' => false, 'hide_empty' => true,
//        'feed' => '', 'feed_image' => '', 'feed_type' => '', 'echo' => true,
//        'style' => 'list', 'html' => true
//    );

    // @todo Refactor the authors and author_count queries to one, or replace with get_author when such a fucntion will exist
    $sql = $wpdb->prepare("SELECT ID, display_name from $wpdb->users WHERE display_name LIKE %s ORDER BY display_name", "%{$chars}%");
    $authors = $wpdb->get_results($sql);

    $author_count = array();
    foreach ((array) $wpdb->get_results("SELECT DISTINCT post_author, COUNT(ID) AS count FROM $wpdb->posts WHERE post_type = 'post' AND " . get_private_posts_cap_sql( 'post' ) . " GROUP BY post_author") as $row) {
        $author_count[$row->post_author] = $row->count;
    }
    
    $returnAuthors = array();
    foreach ( (array) $authors as $author ) {
        $author = get_userdata( $author->ID );
        $posts = (isset($author_count[$author->ID])) ? $author_count[$author->ID] : 0;
        $name = $author->display_name;

        if ( $posts != 0 ){
            if ( $return_id ){
                $returnAuthors[$name] = $author->ID;
            } else {
                $returnAuthors[] = $name;
            }
        }
    }
    
    return $returnAuthors;
}