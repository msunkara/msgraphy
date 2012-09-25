<?php
define('UPPSITE_MAX_TITLE_LENGTH', 45);
define('UPPSITE_DEFAULT_ANALYTICS_KEY', "BDF2JD6ZXWX69Y9BZQBC");
if (isset($_REQUEST['uppsite_request'])) {
    	define('UPPSITE_AJAX', $_REQUEST['uppsite_request']);
        update_option('show_on_front', 'posts');
    remove_filter('template_redirect', 'redirect_canonical');
}
function uppsite_get_appid() {
    $data = get_option(MYSITEAPP_OPTIONS_DATA);
    return isset($data['app_id']) ? $data['app_id'] : 0;
}
function uppsite_get_member() {
    $avatar = null;
    if (function_exists('get_the_author_meta')) {
        $avatar = get_avatar(get_the_author_meta('user_email'));
    } elseif (function_exists('get_the_author_id')) {
        $avatar = get_avatar(get_the_author_id());
    }
    return array(
        'name' => get_the_author(),
        'link' => get_the_author_link(),
        'avatar' => uppsite_extract_src_url($avatar),
    );
}
function uppsite_format_html_to_array($output) {
    preg_match_all('/href=("|\')(.*?)("|\')(.*?)>(.*?)<\/a>/', $output, $result);
    $array = array();
    for($i = 0; $i < count($result[0]); $i++) {
        $array[] = array(
            'title' => $result[5][$i],
            'permalink' => $result[2][$i],
        );
    }
    return $array;
}
function uppsite_get_comment_member(){
    $avatar = get_avatar(get_comment_author_email());
    return array(
        'name' =>  get_comment_author(),
        'avatar' => uppsite_extract_src_url($avatar),
    );
}
function uppsite_get_comment() {
	global $comment;
	return array(
		'comment_ID' => get_comment_ID(),
		'post_id' => get_the_ID(),
		'isApproved' => $comment->comment_approved == '0' ? "false" : "true",
		'permalink' => get_permalink(),
		'comment_date' => get_comment_date( '', 0 ),
		'unix_time' => get_comment_date( 'U', 0 ),
		'comment_content' => get_comment_text( 0 ),
		'comment_author' => uppsite_get_comment_member(get_comment_ID()),
	);
}
function uppsite_extract_image_from_post_content(&$content) {
    if (!preg_match("/<img[^>]*src=\"([^\"]+)\"[^>]*>/", $content, $matches)) {
        return null;
    }
    $content = str_replace($matches[0], "", $content);
    return $matches[1];
}
function uppsite_strlen($str) {
    if (function_exists('mb_strlen')) {
        return mb_strlen($str);
    }
    return strlen($str);
}
function uppsite_match($pattern, $subject) {
    $ret = array();
    if (function_exists('mb_eregi')) {
        mb_eregi($pattern, $subject, $ret);
    } else {
        preg_match("/" . $pattern . "/", $subject, $ret);
    }
    return $ret;
}
function uppsite_process_post($with_content = false) {
    $thumb_url = mysiteapp_extract_thumbnail();
	$ret = array(
		'id' => get_the_ID(),
		'permalink' => get_permalink(),
		'title' => html_entity_decode(get_the_title(), ENT_QUOTES, 'UTF-8'),
		'member' => uppsite_get_member(),
		'excerpt' => get_the_excerpt(),
		'time' => apply_filters('the_time', get_the_time( 'm/d/y G:i' ), 'm/d/y G:i'),
		'unix_time' => apply_filters('the_time', get_the_time( 'U' ), 'U'),
		'comments_link' => get_comments_link(),
        'comments_num' => get_comments_number(),
        'comments_open' => comments_open(),
		'tags' => uppsite_posts_list('get_the_tag_list', false),
		'categories' => uppsite_posts_list('wp_list_categories', false),
	);
    if ($with_content || is_null($thumb_url)) {
        ob_start();
        the_content();
        $post_content = ob_get_contents();
        ob_get_clean();
        if (is_null($thumb_url)) {
                        $thumb_url = uppsite_extract_image_from_post_content($post_content);
        }
    }
    $ret['thumb_url'] = $thumb_url;
    if ($with_content) {
        $ret['content'] = $post_content;
    } else {
            	$maxChar = is_null($ret['thumb_url']) ? UPPSITE_MAX_TITLE_LENGTH + 15 : UPPSITE_MAX_TITLE_LENGTH;
    	$maxChar += (isset($_GET['view']) && $_GET['view'] == "excerpt") ? 0 : -10;
    	$orgLen = uppsite_strlen($ret['title']);
	    if ($orgLen > $maxChar) {
            $matches = uppsite_match("(.{0," . $maxChar . "})\s", $ret['title']);
	    	$ret['title'] = rtrim($matches[1]);
	    	$ret['title'] .= (uppsite_strlen($ret['title']) == $orgLen) ? "" : " ..."; 	    }
    }
    return $ret;
}
function uppsite_posts_list($funcname, $echo = true) {
    $list = call_user_func($funcname, array('echo' => false));
    $arr = uppsite_format_html_to_array($list);
    if (count($arr) == 0) {
        return;
    }
    if (!$echo) {
        return $arr;
    }
    print json_encode($arr);
}
function uppsite_get_webapp_page($template) {
	if (!defined('UPPSITE_AJAX')) {
		return $template;
	}
	if (function_exists('uppsite_func_' . UPPSITE_AJAX)) {
		call_user_func('uppsite_func_' . UPPSITE_AJAX);
		return null;
	}
	$page = TEMPLATEPATH . "/" . UPPSITE_AJAX . "-ajax.php";
    if (!file_exists($page)) {
        $page = TEMPLATEPATH . "/index-ajax.php";
    }
    return $page;
}
function fetch_current_request_data($template){
    if (!defined('UPPSITE_AJAX')) {
        return $template;
    }
    $all_posts = array();
    while ( have_posts() ) {
        the_post();
        $all_posts[] = uppsite_process_post();
    }
    $total_count = count($all_posts);
    print json_encode(array('root' => $all_posts, 'total_count' => $total_count));
    exit;
}
function redirect_login($url, $queryRedirectTo, $user) {
    if (!defined('UPPSITE_AJAX')) {
        return $url;
    }
    if (UPPSITE_AJAX == "user_details") {
                if (is_user_logged_in()) {
            global $current_user;
            get_currentuserinfo();
            $res = array(
                'success' => true,
                'username' => $current_user->user_login,
                'userid' => $current_user->ID,
                'publish' => $current_user->has_cap('publish_posts'),
                'logged' => true
            );
        } else {
            $res = array('logged'=>false);
        }
        print json_encode($res);
    } elseif (UPPSITE_AJAX == "logout") {
                wp_logout();
    } else {
                if (isset($user->ID)) {
            print json_encode(
                array(
                    'success' => true,
                    'username' => $user->user_login,
                    'userid' => $user->ID,
                    'publish' => $user->has_cap('publish_posts')
                )
            );
        } else {
            print json_encode(array('success' => false));
        }
    }
    exit;
}
function redirect_comment() {
    print json_encode(array('success' => false));
    exit;
}
function uppsite_get_analytics_key() {
    return UPPSITE_DEFAULT_ANALYTICS_KEY;
}
function uppsite_get_pref_direction() {
	$options = get_option(MYSITEAPP_OPTIONS_PREFS);
	return isset($options['direction']) ? $options['direction'] : 'ltr';
}
function uppsite_func_create_quick_post() {
    if (current_user_can('publish_posts')) {
        if (!isset($_POST['post_title']) || !isset($_POST['content'])) {
            exit;
        }
        $post_title =  $_POST['post_title'];
        $post_content = $_POST['content'];
        $post_date = current_time('mysql');
        $post_date_gmt = current_time('mysql', 1);
        $post_status = 'publish';
                $_POST['post_status'] = $post_status;
        $current_user = wp_get_current_user();
        $post_author = $current_user->ID;
        $post_data = compact('post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_tags', 'post_status');
        $post_ID = wp_insert_post($post_data);
        print json_encode(array(
            'success' => !is_wp_error($post_ID) && is_numeric($post_ID),
            'post_id' => $post_ID
        ));
    }
    exit;
}
function create_post($title,$tag,$content){
    global $current_user;
    get_currentuserinfo();
     if ($current_user->has_cap('publish_posts')) {
        $my_post = array(
            'post_title' => wp_strip_all_tags( $title ),
            'post_content' => $content,
            'post_status' => 'publish',
            'post_author' => $current_user->ID,
        );
         wp_insert_post( $my_post );
     }
}
add_filter('index_template', 'uppsite_get_webapp_page');
add_filter('front_page_template', 'uppsite_get_webapp_page');
add_filter('home_template', 'uppsite_get_webapp_page');
add_filter('sidebar_template', 'uppsite_get_webapp_page');
add_filter('category_template', 'uppsite_get_webapp_page');
add_filter('search_template', 'uppsite_get_webapp_page');
add_filter('tag_template', 'uppsite_get_webapp_page');
add_filter('archive_template', 'uppsite_get_webapp_page');
add_filter('login_redirect', 'redirect_login', 10, 3);
add_filter('comment_post_redirect', 'redirect_comment', 10, 3);
