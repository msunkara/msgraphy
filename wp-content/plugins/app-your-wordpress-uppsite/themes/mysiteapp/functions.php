<?php
remove_all_filters('get_sidebar');
remove_all_filters('get_header');
remove_all_filters('get_footer');
remove_all_actions('loop_start');
remove_all_actions('loop_end');
remove_all_actions('the_excerpt');
remove_all_actions('wp_footer');
remove_all_actions('wp_print_footer_scripts');
remove_all_actions('comments_array');
function mysiteapp_fix_content_fb_social($content){
    global $msap;
    $fixed_content =  $content;
    if ($msap->is_app){
        $fixed_content = preg_replace('/<p class=\"FacebookLikeButton\">.*?<\/p>/','',$content);
        $fixed_content = preg_replace('/<iframe id=\"basic_facebook_social_plugins_likebutton\" .*?<\/iframe>/','',$fixed_content);
    }
    return $fixed_content;
}
add_filter('the_content','mysiteapp_fix_content_fb_social',20,1);
