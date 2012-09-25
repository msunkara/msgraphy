<?php
/**
* @package WiziappWordpressPlugin
* @subpackage Display
* @author comobix.com plugins@comobix.com
* 
*/

function wiziapp_getAllCategories(){
    $header = array(
        'action' => 'wiziapp_getAllCategories',
        'status' => TRUE,
        'code' => 200,
        'message' => '',
    );

    $categoriesLimit = WiziappConfig::getInstance()->categories_list_limit;

    $pageNumber = isset($_GET['wizipage']) ? $_GET['wizipage'] : 0;
    
    $categories = get_categories(array(
        'number' => $categoriesLimit,
        'offset' => $pageNumber * $categoriesLimit,
        'hide_empty' => FALSE,
    ));
    $categoriesSummary = array();
    foreach($categories as $category) {
        $categoriesSummary[$category->cat_ID] = $category->cat_name;
    }
    // Get the total number of categories
    $total = wp_count_terms('category');

    echo json_encode(array('header' => $header, 'categories' => $categoriesSummary, 'total' => $total));
}

function wiziapp_getAllTags(){
    $header = array(
        'action' => 'wiziapp_getAllTags',
        'status' => TRUE,
        'code' => 200,
        'message' => '',
    );
    
    $tagsLimit = WiziappConfig::getInstance()->tags_list_limit;
    $pageNumber = isset($_GET['wizipage']) ? $_GET['wizipage'] : 0;
    
    $tags = get_tags(array(
        'number' => $tagsLimit,
        'offset' => $pageNumber * $tagsLimit,
        'hide_empty' => FALSE, 
    ));

    $tagsSummary = array();
    foreach($tags as $tag) {
        $tagsSummary[$tag->term_id] = $tag->name;
    }

    // Get the total number of tags
    $total = wp_count_terms('post_tag');

    echo json_encode(array('header' => $header, 'tags' => $tagsSummary, 'total' => $total));
}

function wiziapp_getAllPages(){
    $header = array(
        'action' => 'wiziapp_getAllPages',
        'status' => TRUE,
        'code' => 200,
        'message' => '',
    );
    
    $pagesLimit = WiziappConfig::getInstance()->pages_list_limit;
    $pageNumber = isset($_GET['wizipage']) ? $_GET['wizipage'] : 0;
    
    $pages = get_pages(array(
        'number' => $pagesLimit,
        'offset' => $pageNumber * $pagesLimit,
    ));
    $pagesSummary = array();
    foreach($pages as $singlePage) {
        $pagesSummary[get_permalink($singlePage->ID)] = $singlePage->post_name;
    }

    // Get the total number of pages
    $total  = wp_count_posts( 'page' );
    
    echo json_encode(array('header' => $header, 'pages' => $pagesSummary, 'total'=>$total));
}

function wiziapp_getAllLinks(){
    $header = array(
        'action' => 'wiziapp_getAllLinks',
        'status' => TRUE,
        'code' => 200,
        'message' => '',
    );

    $linksLimit = WiziappConfig::getInstance()->links_list_limit;
    $pageNumber = isset($_GET['wizipage']) ? $_GET['wizipage'] : 0;

    $links = get_bookmarks(array(
        'limit' => $linksLimit,
        'offset' => $pageNumber * $linksLimit,
    ));

    $linksSummary = array();
    foreach($links as $link) {
        $linksSummary[$link->link_url] = $link->link_name;
    }

    // Get the total number of pages
    $total  = $GLOBALS['WiziappDB']->get_links_count();

    echo json_encode(array('header' => $header, 'links' => $linksSummary, 'total'=>$total));
}