<?php
/**
 * Functions for SmallBiz.
 *
 * @package WordPress
 * @subpackage Expand2Web SmallBiz
 * @since Expand2Web SmallBiz 3.3
 */ 
 
    require_once(TEMPLATEPATH . "/inc/fonts-options.php");
// SmallBiz Theme uses wp_nav_menu() in three locations.
    register_nav_menus( array(
        'primary' => __( 'Primary Smallbiz Theme Menu', 'Smallbiz' ),
        'secondary-menu' => __( 'Optional Facebook Menu (use only with -Facebook Page Template- based Pages)' ),
        'tertiary-menu' => __( 'Optional Mobile Device Menu (use only with -Mobile Page Template- based Pages)' )
    ) );

// This theme allows users to set a custom background
    add_custom_background();
    
    
// Create the function to output the Expand2Web Theme Message in Dashboard
function example_dashboard_widget_function() {
        
echo "<iframe SRC=\"http://members.expand2web.com/E2W-theme-images/WPThememessage.html\" width=\"530px\" height=\"350px\" framespacing=0 frameborder=no border=0 scrolling=no></iframe>";    
} 

// Create the function use in the action hook
function example_add_dashboard_widgets() {
    wp_add_dashboard_widget('example_dashboard_widget', 'Getting Started with the SmallBiz Theme', 'example_dashboard_widget_function');    
} 

// Hook into the 'wp_dashboard_setup' action to register our other functions
add_action('wp_dashboard_setup', 'example_add_dashboard_widgets' );

// Migrate the old version of smallbiz's headers to the new location if they exist...
function e2w_migrate_legacy_headers($user_banner_storage){
    $old_upload_path = dirname(__FILE__).'/images/banners';
    $directory = opendir($old_upload_path); //Our directory handler
    $upload_dir = wp_upload_dir();
    while (false !== ($file = readdir($directory))) {
      if ($file != '.' && $file != '..'){
        $banner_namepart = substr($file, 0, strpos($file, "."));
        // move the file (this is the right command, Thomas):
        rename($old_upload_path.'/'.$file, $upload_dir['path'].'/'.$file);
        $user_banner_storage[$file] = array('path'=>$upload_dir["path"], 'url'=>$upload_dir["url"]);  
      }
    }                    
    update_option('smallbiz_user_banner_storage', $user_banner_storage);
    return $user_banner_storage;
}


//Function to display RSS feed in Dashboard.
    function e2wdashboard_rss_dashboard_widget_function() {
    $rss = fetch_feed( "http://www.expand2web.com/blog/feed/rss/" );

    if ( is_wp_error($rss) ) {
        if ( is_admin() || current_user_can('manage_options') ) {
            echo '<p>';
            printf(__('<strong>RSS Error</strong>: %s'), $rss->get_error_message());
            echo '</p>';
        }
        return;
    }

    if ( !$rss->get_item_quantity() ) {
        echo '<p>Visit Expand2web.com - Our feed must be down.</p>';
        $rss->__destruct();
        unset($rss);
        return;
    }

    echo "<ul>\n";

    if ( !isset($items) )
        $items = 6;

    foreach ( $rss->get_items(0, $items) as $item ) {
        $publisher = '';
        $site_link = '';
        $link = '';
        $content = '';
        $date = '';
        $link = esc_url( strip_tags( $item->get_link() ) );
        $title = $item->get_title();
        $content = $item->get_content();
        $content = wp_html_excerpt($content, 150) . ' ...';

        echo "\t<li><a class='rsswidget' href='$link'>$title</a><br /> $content</li>\n";
    }

    echo "</ul>\n";
    $rss->__destruct();
    unset($rss);
};

//Function to add the rss feed to the dashboard.
function e2wdashboard_rss_add_dashboard_widget() {
wp_add_dashboard_widget('e2wdashboard_rss_dashboard_widget', 'Expand2Web News and Updates', 'e2wdashboard_rss_dashboard_widget_function');
}

//Action that calls the function that adds the widget to the dashboard.
add_action('wp_dashboard_setup', 'e2wdashboard_rss_add_dashboard_widget');

// Remove some default widgets from Dashboard

function example_remove_dashboard_widgets() {
// Globalize the metaboxes array, this holds all the widgets for wp-admin
global $wp_meta_boxes;

// Remove the right-now    
unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);

// Remove the comments    
unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);

// Remove quickpress    
unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);

// Remove the incoming links widget
unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);    
} 

// Hook into the 'wp_dashboard_setup' action to register our function
add_action('wp_dashboard_setup', 'example_remove_dashboard_widgets' );


// Remove browser warning
function disable_browser_upgrade_warning() {
    remove_meta_box( 'dashboard_browser_nag', 'dashboard', 'normal' );
}
add_action( 'wp_dashboard_setup', 'disable_browser_upgrade_warning' );
    
// Define the current version here:
$GLOBALS['smallbiz_cur_version'] = "3.8.4";

// Detection functions for mobile with mobileesp:
if (!class_exists('uagent_info')) {
include(TEMPLATEPATH."/mdetect.php");
}

// For our own purposes:
function smallbiz_setup_mobile(){
  $GLOBALS["smartphone"] = false;
  $type = false;
  $urlmd5 = md5(get_bloginfo('siteurl'));
  if(!isset($_COOKIE[$urlmd5."device_type"])){
      // DetectSmartphone() would restrict to, well, smartphones.
      // MobileLong will also show older devices the mobile layout, though they might not be able to see it well.
      $uagent_obj = new uagent_info();
      if($uagent_obj->DetectMobileLong()){
          $type = "Mobile";
      } else {
          $type = "Desktop";
      }
      setcookie($urlmd5."device_type", $type);
      $_COOKIE[$urlmd5."device_type"] = $type;
  } else {
      $type = $_COOKIE[$urlmd5."device_type"];
  }
  if(get_option('smallbiz_mobile-layout-enabled')){
    if(isset($_REQUEST["ui"])){
        if($_REQUEST["ui"] == "m"){
            $GLOBALS["smartphone"] = true;
        }
    } else {
        if(isset($_COOKIE[$urlmd5."ui"])){
            if($_COOKIE[$urlmd5."ui"] == "m"){
                $GLOBALS["smartphone"] = true;
            } else {
                $GLOBALS["smartphone"] = false;
            }
        } else {
            if($_COOKIE[$urlmd5."device_type"] == "Mobile"){
                $GLOBALS["smartphone"] = true;
            }
        }
    }
    if(!headers_sent()){
      if($GLOBALS["smartphone"]){
        setcookie($urlmd5."ui", "m");
      } else {
        setcookie($urlmd5."ui", "f");        
      }
    }
  }
  return $GLOBALS["smartphone"];
}
function smallbiz_get_current_layout(){
  $layout = get_option('smallbiz_layout');  
  if(get_option('smallbiz_mobile-layout-enabled')){
    if(!isset($GLOBALS["smartphone"])){
        $smartphone =  smallbiz_setup_mobile();
    }
    if($GLOBALS["smartphone"]){ 
        $layout = "mobile";   
    } 
  }
  return $layout;    
}

add_action('wp_loaded', 'smallbiz_setup_mobile');



// include our layout functions if they exist, and set up the layout before we do anything else besides check for mobile:
if(smallbiz_get_current_layout() || isset($_POST["layout"])){
  $currlayout = smallbiz_get_current_layout();
  if(isset($_POST["layout"])){
      $currlayout = $_POST["layout"];   
  }
  if(!$currlayout){
      $currlayout = "classic";
      update_option('smallbiz_layout',"classic");
  }
  if (file_exists(TEMPLATEPATH."/layouts/".$currlayout."/functions_layout.php")) {
        include(TEMPLATEPATH."/layouts/".$currlayout."/functions_layout.php");
  }
  if($currlayout != smallbiz_get_current_layout()){
    if(function_exists(smallbiz_defaults_for_layout)){
        $layout_opts = smallbiz_defaults_for_layout();
        foreach($layout_opts as $key => $value){
            if(!get_option("smallbiz_".$key)){
                add_option(("smallbiz_".$key), $value);
                update_option(("smallbiz_".$key), $value);
            }
            $_POST[$key] = get_option("smallbiz_".$key);
        }        
    } else {
        add_option("smallbiz_layout_title","");
    }
  }
}


if ( function_exists('register_sidebar') )

    register_sidebar(array('name'=>'Default Sidebar',

        'before_widget' => '<li id="%1$s" class="widget %2$s">',

        'after_widget' => '</li>',

        'before_title' => '<h3>',

        'after_title' => '</h3>',

    ));
    
        
register_sidebar(array('name'=>'Sidebar Contact Page','before_widget' => '','after_widget' => '','before_title' => '<h3>','after_title' => '</h3>',));
        
register_sidebar(array('name'=>'Sidebar Find Us Page ','before_widget' => '','after_widget' => '','before_title' => '<h3>','after_title' => '</h3>',));
             
register_sidebar(array('name'=>'Sidebar Blog Page ','before_widget' => '','after_widget' => '','before_title' => '<h3>','after_title' => '</h3>',));

register_sidebar(array('name'=>'Sidebar Optional 1 ','before_widget' => '','after_widget' => '','before_title' => '<h3>','after_title' => '</h3>',));
    
register_sidebar(array('name'=>'Sidebar Optional 2 ','before_widget' => '','after_widget' => '','before_title' => '<h3>','after_title' => '</h3>',));

    
register_sidebar(array('name'=>'Footer Left','before_widget' => '','after_widget' => '','before_title' => '<h3>','after_title' => '</h3>',));
register_sidebar(array('name'=>'Footer Middle','before_widget' => '','after_widget' => '','before_title' => '<h3>','after_title' => '</h3>',));
register_sidebar(array('name'=>'Footer Right','before_widget' => '','after_widget' => '','before_title' => '<h3>','after_title' => '</h3>',));


add_action("admin_menu", "smallbiz_on_admin_menu");

add_action('admin_head', 'smallbiz_on_admin_head');

add_action('wp_head', 'smallbiz_on_wp_head');

register_activation_hook(__FILE__, 'smallbiz_on_install');



/**
 * Register the Social Media Icons Widget
 */
add_action('widgets_init', 'smallbiz_social_media_widget_register');
function smallbiz_social_media_widget_register() {
    register_widget('SmallBiz_Social_Media_Widget');
}

/**
 * The Widget Class
 */
if ( !class_exists('Smallbiz_Social_Media_Widget') ) {
class Smallbiz_Social_Media_Widget extends WP_Widget {

    function Smallbiz_Social_Media_Widget() {
        $widget_ops = array( 'classname' => 'smallbiz-social-media', 'description' => __('Displays Social Media Icons in Sidebar') );
        $this->WP_Widget( 'smallbiz_social_media', __('SmallBiz Social Media Icons'), $widget_ops );
    }
    
    var $plugin_imgs_url;
    
    function spw_fields_array( $instance = array() ) {
        
        $this->plugins_imgs_url = get_bloginfo('template_url'). '/images';   
        
        return array(
        
            'facebook' => array(
                'title' => __('Facebook Profile URL', 'spw'),
                'img' => sprintf( '%s/FB_icon2.png', $this->plugins_imgs_url . esc_attr( $instance['icon_set'] ), esc_attr( $instance['size'] ) ),
                'img_widget' => sprintf( '%s/FB_icon2.png', $this->plugins_imgs_url . esc_attr( $instance['icon_set'] ), '48x48' ),
                'img_title' => __('Facebook', 'spw')
            ),
            
            'googleplus' => array(
                'title' => __('Google+ Profile URL', 'spw'),
                'img' => sprintf( '%s/G-Icon2.png', $this->plugins_imgs_url . esc_attr( $instance['icon_set'] ), esc_attr( $instance['size'] ) ),
                'img_widget' => sprintf( '%s/G-Icon2.png', $this->plugins_imgs_url . esc_attr( $instance['icon_set'] ), '48x48' ),
                'img_title' => __('Google+', 'spw')
            ),
            
            'twitter' => array(
                'title' => __('Twitter Profile URL', 'spw'),
                'img' => sprintf( '%s/TW_icon2.png', $this->plugins_imgs_url . esc_attr( $instance['icon_set'] ), esc_attr( $instance['size'] ) ),
                'img_widget' => sprintf( '%s/TW_icon2.png', $this->plugins_imgs_url . esc_attr( $instance['icon_set'] ), '48x48' ),
                'img_title' => __('Twitter', 'spw')
            ),
            
            'youtube' => array(
                'title' => __('YouTube Channel URL', 'spw'),
                'img' => sprintf( '%s/YT_icon2.png', $this->plugins_imgs_url . esc_attr( $instance['icon_set'] ), esc_attr( $instance['size'] ) ),
                'img_widget' => sprintf( '%s/YT_icon2.png', $this->plugins_imgs_url . esc_attr( $instance['icon_set'] ), '48x48' ),
                'img_title' => __('Youtube', 'spw')
            ),
        
            'linkedin' => array(
                'title' => __('LinkedIn Profile URL', 'spw'),
                'img' => sprintf( '%s/LK_icon2.png', $this->plugins_imgs_url . esc_attr( $instance['icon_set'] ), esc_attr( $instance['size'] ) ),
                'img_widget' => sprintf( '%s/LK_icon2.png', $this->plugins_imgs_url . esc_attr( $instance['icon_set'] ), '48x48' ),
                'img_title' => __('LinkedIn', 'spw')
            ),
            
            'googleplaces' => array(
                'title' => __('Google Places URL', 'spw'),
                'img' => sprintf( '%s/GP_icon.png', $this->plugins_imgs_url . esc_attr( $instance['icon_set'] ), esc_attr( $instance['size'] ) ),
                'img_widget' => sprintf( '%s/GP_icon.png', $this->plugins_imgs_url . esc_attr( $instance['icon_set'] ), '48x48' ),
                'img_title' => __('Google Places', 'spw')
            ),
        
        );
    }

    function widget($args, $instance) {
        
        extract($args);
        
        $instance = wp_parse_args($instance, array(
            'title' => '',
        
        ) );
        
        echo $before_widget;
        
            if ( !empty( $instance['title'] ) )
                echo $before_title . $instance['title'] . $after_title;
                
            
    print  "";   
    
                
            foreach ( $this->spw_fields_array( $instance ) as $key => $data ) {
                if ( !empty ( $instance[$key] ) ) {
                    printf( '<p class="center" style="padding-left:3px;"><a href="%s" target="_blank"><img src="%s" alt="%s"/></a></p>', esc_url( $instance[$key] ), esc_url( $data['img'] ), esc_attr( $data['img_title'] ) );
    
                }
            }
            
                print  "&nbsp;";  
        
        echo $after_widget;
        
    }

    function update($new_instance, $old_instance) {
        return $new_instance;
    }

    function form($instance) { 

        $instance = wp_parse_args($instance, array(
            'title' => '',
        ) );
?>
        
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Sidebar Title <br />Example: "Connect With Us" ', 'spw'); ?></label><br />
            <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" style="width:95%;" />
        </p>
        
        <p><?php _e('Enter the complete URL(s) of your Social Media Profile. <br /><br />Leave URL field empty to not display the according icon and link.', 'spw'); ?></p>
        
<?php

        foreach ( $this->spw_fields_array( $instance ) as $key => $data ) {
            echo '<p>';
            printf( '<img style="margin-right: 3px;" src="%s" title="%s" /><br />', $data['img_widget'], $data['img_title'] );
            printf( '<label for="%s"> %s:</label>', esc_attr( $this->get_field_id($key) ), esc_attr( $data['title'] ) );
            printf( '<input id="%s" name="%s" value="%s" style="%s" />', esc_attr( $this->get_field_id($key) ), esc_attr( $this->get_field_name($key) ), esc_url( $instance[$key] ), 'width:65%;' );
            echo '</p>' . "\n";
        }

    }
}}

/* don't strip iFrames */
function mytheme_tinymce_config( $init ) {
 $valid_iframe = 'iframe[id|class|title|style|align|frameborder|height|longdesc|marginheight|marginwidth|name|scrolling|src|width]';
 if ( isset( $init['extended_valid_elements'] ) ) {
  $init['extended_valid_elements'] .= ',' . $valid_iframe;
 } else {
  $init['extended_valid_elements'] = $valid_iframe;
 }
 $init['remove_script_host'] = true;
 $init['relative_urls'] = false;
 
 return $init;
}
add_filter('tiny_mce_before_init', 'mytheme_tinymce_config');




/* for preview fix start */
function biz_option($key){
    $value = get_option($key);
    if($value){
        return $value;
    } else if(strrpos($key,'smallbiz_')!== false && ('' == get_option('smallbiz_added_pages')   )){
        $smallbiz_defaults = smallbiz_defaults() ;       
        $value = $smallbiz_defaults[str_replace("smallbiz_","",$key)];
        return $value;
    }
}
/* for preview fix end */


// returns the URL of the banner image:
function smallbiz_get_banner_image_url(){
    $banner_filename     = biz_option('smallbiz_banner');
    $user_banner_storage = biz_option('smallbiz_user_banner_storage');
    if(!is_array($user_banner_storage)){
        $user_banner_storage = array();
        e2w_migrate_legacy_headers(&$user_banner_storage);
    }
    if(isset($user_banner_storage[$banner_filename])){
        // new style stored image
        return $user_banner_storage[biz_option('smallbiz_banner')]['url'].'/'.$banner_filename;
    } else if (file_exists(get_theme_root() . '/images/banners/'.$file)){
        // old style stored image
        return bloginfo('template_url').'/images/banners/'.$banner_filename;
    } else {
        // if it is a default, load it from amazon:
        return smallbiz_fetch_banner_from_url('http://cdn4.expand2web.com/'.$banner_filename);
        //return 'http://cdn4.expand2web.com/'.$banner_filename;
    }        
    return "";
}
// fetches a default banner image from the cdn to the user's local hosting:
function smallbiz_fetch_banner_from_url($url){
    $user_banner_storage = get_option('smallbiz_user_banner_storage');
    $upload_dir = wp_upload_dir();
    $filename = basename($url);
    // try various methods of downloading, fall back on just sharing the cdn:
    if(function_exists("curl_version")){
        // curl is installed, use it:
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        $fp = fopen($upload_dir["path"].'/'.$filename, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        curl_close($ch);   
        fclose($fp);
        $user_banner_storage[$filename] = array('path'=>$upload_dir["path"], 'url'=>$upload_dir["url"]);
    } else if( ini_get('allow_url_fopen') ) {
        // file get contents:
        file_put_contents($upload_dir["path"].'/'.$filename, file_get_contents($url));
        $user_banner_storage[$filename] = array('path'=>$upload_dir["path"], 'url'=>$upload_dir["url"]);
    } else {
        // Neither is enabled...
        $user_banner_storage[$filename] = array('url'=>$url);
    }
    update_option('smallbiz_user_banner_storage', $user_banner_storage);
    return ($user_banner_storage[$filename]['url'].'/'.$filename);
}

function smallbiz_on_wp_head()
{
?><?php
}


// append a fake menu item with this title
function smallbiz_wp_nav_menu_append_helper($title){
    $wpnav_enditems = "";
    if(!smallbiz_get_special_page($title)){
          $titleurl = str_replace(" ","",strtolower($title)); 
          $wpnav_enditems  .= '<li class="page_item page-item-'.$titleurl;
          if(isset($_REQUEST[$titleurl])){
              $wpnav_enditems  .=' current_page_item';
          }
          $wpnav_enditems .='"><a href="'.get_bloginfo('url').'/?'.$titleurl.'=1" title="'.$title.'">'.$title.'</a></li>';          
    }
    return $wpnav_enditems;
}


function smallbiz_wp_nav_menu( $opts ){
  // echo by default... but don't let wp_nav_menu do that.
  $echoMe = true;
  if(isset($opts['echo']) && !$opts['echo']){
    $echoMe = false;   
  }
  $opts['echo']=false;
  $opts['fallback_cb'] = 'smallbiz_wp_page_menu'; 
  
  // wp_nav_menu's results.
  $wpnav = wp_nav_menu($opts);
  $wpnav_enditems = "";
  
  // For previews:
  if(isset($_REQUEST['preview'])){
      // fake uncreated pages -- to save time, these first three do nothing:
      $wpnav_enditems = "";
      if(!get_page_by_title('About')){
        $wpnav_enditems .= smallbiz_wp_nav_menu_append_helper("About");
      }
      if(!get_page_by_title('Contact')){
        $wpnav_enditems .= smallbiz_wp_nav_menu_append_helper("Contact");
      }
      if(!get_page_by_title('Find Us')){
        $wpnav_enditems .= smallbiz_wp_nav_menu_append_helper("Find Us");
      }
      if(!get_page_by_title('Blog')){
        $wpnav_enditems .= smallbiz_wp_nav_menu_append_helper("Blog");
      }
      $wpnav = str_replace('</ul>',''.$wpnav_enditems.'</ul>',$wpnav);
      if(!get_page_by_title('Home')){
          $home_li  = '<li class="page_item page-item-home';
          if(!strpos($wpnav,'current_page_item')){
              $home_li  .=' current_page_item';
          }
          $home_li .='"><a href="'.get_bloginfo('url').'" title="Home">Home</a></li>';
          $wpnav = str_replace('<ul>','<ul>'.$home_li, $wpnav);
      }
  }
  
  // If we are supposed to echo, do so:
  if($echoMe){
      echo $wpnav;
  } else {
      return $wpnav;
  }
}
// Fallback function for listing pages when a menu is not defined:
function smallbiz_wp_page_menu( $opts ){
    $excludeArray = array();
    if(!isset($opts['exclude'])){ 
        $excludeArray = explode(',', $opts['exclude']); 
    }
    // If the front page is not equal to the special "Home" page, aka, a custom home is set:
    if(get_option('smallbiz_page_on_front') != get_option('smallbiz_homepage_id')){
        $excludeArray[] = get_option('smallbiz_page_on_front');
    }
    $opts['exclude'] = implode(",", $excludeArray);
    return wp_page_menu($opts);
}



function smallbiz_on_admin_head()
{
?>

<script type="text/javascript">
/* <![CDATA[ */


/* for custom fields start */
function SEO_moreinfo_toggle(infoToToggle){
    var seoInfoDiv  = document.getElementById('SEO_moreinfo-'+infoToToggle);
    var seoInfoLink = document.getElementById('SEO_moreinfo_link-'+infoToToggle);
    if(seoInfoDiv && seoInfoLink){
        if(seoInfoDiv.style.display != "block"){
            seoInfoDiv.style.display = "block";
            seoInfoDiv.style.left = (seoInfoLink.offsetLeft+10) + "px";
        } else {
            seoInfoDiv.style.display = "none";
        }
    }        
}
/* for custom fields end  */

var mceOps ={
    cleanup_callback: "mce_custom_cleanup", 
    mode:"specific_textareas", 
    editor_selector:"theEditor", 
    width:"100%", 
    theme:"advanced", 
    skin:"default", 
 theme_advanced_buttons1:"code, |,bold,italic,underline,strikethrough,|,image,|,pasteword,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyfull,justifyright,|,link,unlink,|,forecolor,backcolor,formatselect,fontsizeselect,",
 
    theme_advanced_buttons2:"",
    theme_advanced_buttons3:"", 
    theme_advanced_buttons4:"", 
    language:"en", 
    spellchecker_languages:"+English=en,Danish=da,Dutch=nl,Finnish=fi,French=fr,German=de,Italian=it,Polish=pl,Portuguese=pt,Spanish=es,Swedish=sv", 
    theme_advanced_toolbar_location:"top", 
    theme_advanced_toolbar_align:"left", 
    theme_advanced_statusbar_location:"bottom", 
    theme_advanced_resizing:"1", 
    theme_advanced_resize_horizontal:"", 
    dialog_type:"modal", 
    relative_urls:"", 
    remove_script_host:"", 
    convert_urls:"", 
    apply_source_formatting:"", 
    remove_linebreaks:false, 
    gecko_spellcheck:"1", 
    entities:"38,amp,60,lt,62,gt", 
    accessibility_focus:"1", 
    tabfocus_elements:"major-publishing-actions", 
    media_strict:"",  
    wpeditimage_disable_captions:"",
    extended_valid_elements:"iframe[src|width|height|name|align]", 
    plugins:"safari,inlinepopups,spellchecker,paste,wordpress,wpeditimage,wpgallery,tabfocus"    
};
tinyMCEPreInit = {
    base : "<?php
        $url = get_bloginfo('url');
        $pos1 = (strpos($url, "http://"));
        $pos2 = (strpos($url, "https://"));
        if($pos1 !== false && $pos1 == 0){
             $url = substr($url, 7);   
        } else if ($pos2 !== false && $pos2 == 0){
             $url = substr($url, 8);           
        }
        if($_SERVER['HTTPS']){
            echo "https://";
        } else {
            echo "http://";
        }
        echo $url;
    ?>/wp-includes/js/tinymce",
    suffix : "",
    query : "ver=3241-1141",
    mceInit : mceOps,
    load_ext : function(url,lang){var sl=tinymce.ScriptLoader;sl.markDone(url+'/langs/'+lang+'.js');sl.markDone(url+'/langs/'+lang+'_dlg.js');}
    
};
console.log(mceOps);

function mce_custom_cleanup(type, value) {
    switch (type) {
        case "get_from_editor":
            value = value.replace(new RegExp("<p>","g"),"<p class='p'>");
            break;
        case "insert_to_editor":
            value = value.replace(new RegExp("<p>","g"),"<p class='p'>");
            break;
        case "submit_content":
            //alert("Value HTML Element: " + value);
            value = value;
            break;
        case "get_from_editor_dom":
            //alert("Value DOM Element " + value);
            value = value;
            break;
        case "insert_to_editor_dom":
            //alert("Value DOM Element: " + value);
            value = value;
            break;
        case "setup_content_dom":
            //alert("Value DOM Element: " + value);
            value = value;
            break;
        case "submit_content_dom":
            //alert("Value DOM Element: " + value);
            value = value;
            break;
    }        
    
    return value;
}

function remove_e2w_asset(asset){
console.log("REmoving", asset);        
        jQuery.ajax({
          url: String(window.location),
          type:'post',
          data:{
              asset:asset,
              action:'remove',
              ajax:true
          },
          success: function(result){
              // No need to do anything on success.
          }
        });
}
function change_active_e2w_selector(newValue, checkName, selectorType, optSuffix){
    //"layout","themeLayout",".css"   
    var radios = jQuery("input[name="+checkName+"]");
    if(typeof(newValue) == "undefined" || newValue == -1){
        // If we didn't pass a value, just change things so the one matching the checkbox is selected:
        newValue=radios[0].value;
        radios.each(function(idx){
                if(radios[idx].checked){
                    newValue=radios[idx].value;
                }
        });
    } else {
        // Otherwise, alter the checkboxes -- separate these to stop an onchange loop thing.
        radios.each(function(idx){
                if(radios[idx].value == newValue){
                    radios[idx].checked = true;
                }
        });
    }
    // update default colors:
    var update_default_colors = function(newValue){
        var header = newValue.substring(0, newValue.indexOf("."));
        url = '<?php bloginfo('template_url'); ?>/default-colors.php?header='+header;
        jQuery.ajax({
          url: url,
          success: function(result){
            jQuery("input.color").each(function(i, v){
                var input = jQuery(v);
                var color = result[input.attr("name")];
                if(color){
                    input.val(color);
                    v.color.fromString(color);
                } 
            });
          }
        });
    };
    if(selectorType == "themeHeader"){
        update_default_colors(newValue);
    }
    
    var divs = jQuery("div."+selectorType+"Selector_option");
    divs.each(function(idx){
      if(newValue == (String(divs[idx].id).replace(selectorType+'Selector_', '')+optSuffix)){
         jQuery(this).addClass(selectorType+"Selector_optionSelected");
      } else {
         jQuery(this).removeClass(selectorType+"Selector_optionSelected");
      }
    });
    if(selectorType == "themeHeader"){
        check_enable_upload_banner();
    } else {
        jQuery("#"+selectorType+"Selector_message").show();
        jQuery(".smallbizClickToSave").each(function(){
            jQuery(this).unbind('click');
            jQuery(this).click(function(){
                    jQuery("#smallbiz_options_form").submit();
            });
        });        
    }
}

function check_enable_upload_banner(){
    var new_banner_radio = document.getElementById('banner_');
    if(new_banner_radio){
        var new_banner       = document.getElementById('new_banner');
        var new_banner_label = document.getElementById('new_banner_label');
        if(new_banner_radio.checked){
            new_banner.disabled = false;
            new_banner_label.style.color ="";
        } else {
            new_banner_label.style.color ="#919191";
            new_banner.disabled = "disabled";
        }
        display_new_banner_message();
    }
}
function display_new_banner_message(){
    var new_banner_message = document.getElementById('new_banner_message');
    var new_banner_radio = document.getElementById('banner_');
    var new_banner_input = document.getElementById('new_banner');
    if(new_banner_message){
        if(new_banner_input.value != "" && new_banner_radio.checked){
            new_banner_message.innerHTML = "Please click the Save Changes button at the bottom of this page to activate your new header image.";
        } else {
            new_banner_message.innerHTML = "";
        }
    }
}
function mainText_HTML(exId){
    if(!exId){exId = ""; }
    tinyMCE.execCommand('mceRemoveControl', false, "main_text"+exId);
    jQuery('#'+'edButtonHTML'+exId).addClass('active');
    jQuery('#'+'edButtonPreview'+exId).removeClass('active');
}
function mainText_MCE(exId){
    if(!exId){exId = ""; }
    tinyMCE.execCommand('mceAddControl', false, "main_text"+exId)
    jQuery('#'+'edButtonPreview'+exId).addClass('active');
    jQuery('#'+'edButtonHTML'+exId).removeClass('active');
}
<?php 
function tinyMCE_HTMLarea($exId,$defaultValue){
?>
    <td class="extrastuff"><!-- So our editor gets styled toggling-->
    
    <div id="editor-toolbar-<?php echo($exId); ?>" class="editor-toolbar">        
        <a id="edButtonHTML-<?php echo($exId); ?>" class="edButtonHTML" onclick="mainText_HTML('-<?php echo($exId); ?>');">HTML</a>
        <a id="edButtonPreview-<?php echo($exId); ?>" class="edButtonPreview active" onclick="mainText_MCE('-<?php echo($exId); ?>');">Visual</a>
    </div>
    <div id="editorcontainer-<?php echo($exId); ?>" class="editorcontainer">
        <textarea name="<?php echo($exId); ?>" id="main_text-<?php echo($exId); ?>" class="theEditor" cols="90" rows="10"><?php echo $defaultValue; ?></textarea>
    </div>
    <?php
}
?>
// Hide options we disable and set the changed homepage value for us to import:
// (The hiding is also done via CSS, but this is for browsers that do not support the > selector)
jQuery(document).ready(function(){
 e2w.template_url = "<?php bloginfo('template_url'); ?>";
 jQuery("#front-static-pages>fieldset>p").hide();
 jQuery("#page_on_front").val("<?php echo get_option('smallbiz_page_on_front'); ?>");
 <?php 
 if(get_option("smallbiz_page_for_posts")){ 
     ?>
     jQuery("#page_for_posts").val("<?php echo get_option('smallbiz_page_for_posts'); ?>");
     <?php 
 }
 ?>
<?php
    $pageCountSubtract = 0;
    if(get_option('smallbiz_homepage_id')){
        $homePageId = get_option('smallbiz_homepage_id');
        echo 'jQuery("#page-'.$homePageId.'").remove();';
        echo 'jQuery("#post-'.$homePageId.' input").remove();';
        echo 'jQuery("#post-'.$homePageId.' div.row-actions").remove();';        
        $pageCountSubtract++;
        // Update error message:
        if(isset($_REQUEST["trashed"])){
            $ids = explode(",",($_REQUEST["ids"]));
            if(in_array($homePageId, $ids)){
                ?>
                jQuery("div.updated>p").each(function(){
                    var p = jQuery(this);
                    if(String(p.text()).indexOf("moved to the Trash") >= 0){
                        p.text("Special pages cannot be deleted while the SmallBiz theme is active.");
                    }
                    
                });                    
                <?php
            }
            
        }        
    }

?>
 <?php /*
    Get the dropdowns, set it up to show whatever we last had visible --
    it would be better to configure all of their behavior here, but stick with
    Thomas's way of doing it for now.
 */?>
    jQuery("#wpbody-content div.wrap>div").each(function(i, v){
       var div = jQuery(v);
      if(String(div.attr("id")).indexOf("dropdowns") == 0){
           var arr = jQuery(this).attr("id").split("-");
           div.click(function(){
                   var id = jQuery(this).attr("id");
                   var arr = id.split("-");
                   var content = jQuery("#"+arr[1]+"options");
                   if(content){
                       if(content.is(":visible")){
                           jQuery.cookie("smallbiz_dropdown_show_"+arr[1], true);
                       } else {
                           jQuery.cookie("smallbiz_dropdown_show_"+arr[1], null);
                       }
                   }
           });
           if(jQuery.cookie("smallbiz_dropdown_show_"+arr[1])){
               var content = jQuery("#"+arr[1]+"options");
               content.show();
           }
       }
    }); 
});
/* ]]> */
</script>
<link href="<?php bloginfo('template_url'); ?>/css/admin-ex.css" rel="stylesheet" type="text/css" media="screen,print" />
<link rel="stylesheet" type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/ui-lightness/jquery-ui.css" />
<link href="<?php bloginfo('template_url'); ?>/css/jquery.multiselect.css" rel="stylesheet" type="text/css" media="screen,print" />
<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/jquery.ck.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/jquery.multiselect.min.js"></script>
<?php wp_enqueue_script( 'jquery' ); ?>
<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/md5.js"></script>
<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/admin.min.js"></script>
<?php
if(get_option('smallbiz_authkey')){
    echo '<script type="text/javascript" src="http://www.smallbiztheme.com/checkout/unlock-js.php?email='.rawurlencode(get_option('smallbiz_authemail')).'&auth='.
        rawurlencode(get_option('smallbiz_authkey'))
    .'"></script>';
}
?>
<script type='text/javascript' src='<?php echo get_bloginfo('url'); ?>/wp-includes/js/tinymce/wp-tinymce.php?c=0&amp;ver=3241-1141'></script>
<script type='text/javascript' src='<?php echo get_bloginfo('url'); ?>/wp-includes/js/tinymce/langs/wp-langs-en.js?ver=3241-1141'></script>

<script type="text/javascript">
/* <![CDATA[ */
// setup font select fields:
jQuery(document).ready(function($){
   $("select.smallbiz_font_selector").multiselect({
        multiple:false, header:false,
        beforeopen:function(e){
          var widget = $(e.target).multiselect("widget");
          widget.find("ul li").each(function(){
               var li = $(this);
               var input = $(this).find("input");
               var span = $(this).find("span");
               span.css("fontFamily", input.val());
          });
        },
        selectedText:function(num,len,elements){
            return ('<span style="font-family:'+ $(elements[0]).val()+'">'+ $(elements[0]).attr("title")+'</span>');
        }
   });
});


if (typeof tinyMCEPreInit.go == "function" )
  {
tinyMCEPreInit.go();
  } else 
  {
(function(){var t=tinyMCEPreInit,sl=tinymce.ScriptLoader,ln=t.mceInit.language,th=t.mceInit.theme,pl=t.mceInit.plugins;sl.markDone(t.base+'/langs/'+ln+'.js');sl.markDone(t.base+'/themes/'+th+'/langs/'+ln+'.js');sl.markDone(t.base+'/themes/'+th+'/langs/'+ln+'_dlg.js');tinymce.each(pl.split(','),function(n){if(n&&n.charAt(0)!='-'){sl.markDone(t.base+'/plugins/'+n+'/langs/'+ln+'.js');sl.markDone(t.base+'/plugins/'+n+'/langs/'+ln+'_dlg.js');}});})();
  }
tinyMCE.init(tinyMCEPreInit.mceInit);
/* ]]> */
</script>

<?php

}
/* for preview fix start */
function smallbiz_defaults(){
  global $smallbiz_defaults, $smallbiz_cur_version;
  if($smallbiz_defaults){
      return $smallbiz_defaults;
  }

  $smallbiz_defaults = array(
    "version" =>  $smallbiz_cur_version,
    "layout" => 'classic',
    "banner" =>  'light_swoop_green.png',
    
    "font_family_header" => 'Arial,Sans-Serif',
    "font_family_headeraddress" => 'Arial,Sans-Serif',
    "font_family_menu" => 'Arial,Sans-Serif',
    "font_family_sideh3" => 'Arial,Sans-Serif',
    "font_family_pagetitle" => 'Arial,Sans-Serif',
    "font_family_main" => 'Arial,Sans-Serif',
    "font_family_side" => 'Arial,Sans-Serif',
    "font_family_feature" => 'Arial,Sans-Serif',
    "font_family_footer" => 'Arial,Sans-Serif',
   
    
    "font_size_head" => '34',
    "font_size_headeraddress" => '18',
    "font_size_menu" => '15',
    "font_size_pagetitle" => '22',
    "font_size_main" => '16',
    "font_size_side" => '16',
    "font_size_feature" => '15',
    "font_size_footer" => '10',
    
    "font_size_h1" => '28',
    "font_size_h2" => '22',
    "font_size_h3" => '20',
    "font_size_sideh3" => '14',

    "font_size_mobiname" => '35',
    "font_size_mobisubheader" => '25',
    
    "menu_color" => '262525',
    "active_color" => 'FFFFFF',
    "hover_color" => 'FFFFFF',
    "passive_color" => '88AB4F',
    "headertag_color" => '85AC54',
    "pages_color" => 'ffffff',
    "sideh3_color" => 'ffffff',
    
    "p_color" => '333333',
    "hyper_color"=>'333333',
    "hyperhover_color"=>'3818D9',
    "creds_color"=>'333333',
    "name" =>  'My Business',
    "name_color" =>  '333333',
    "sub_header" =>  'CityName My Business Name - My Tagline',
    "sub_header_color" =>  '333333',
    "street" =>  'Street Address',
    "street_color" =>  '333333',
    "city" => 'City',
    "city_color" => '333333',
    "state" =>  'State',
    "state_color" =>  '333333',
    "zip" =>  'Zip',
    "zip_color" =>  '333333',
    "countryprefix" => '+1',
    "telephone" =>  '192-555-1212',
    "telephone_color" =>  '333333',
    "email" =>  'test@myemail.com',
    "headeremail" =>  'test@myemail.com',
    "headeremail_color" =>  '333333',
    "header_box_disabled" =>  '',
    "directions" =>  ' We are located in the Shadow Trails Shopping Center on 4th Avenue in the North Hills area of Happyville',
    "cities" =>  'My Business is serving San Jose, Sunnyvale, Palo Alto, Los Gatos, Mountain View, Santa Clara, Cupertino',
    "credit" =>  'WordPress Website Template by <a href="http://www.smallbiztheme.com" rel="nofollow">Expand2Web</a>',
    "page_image" =>  'site1.jpg',
    "map_link" =>  '<!-- Google Maps Element Code -->
    <iframe frameborder="0" marginwidth="0" marginheight="0" border="0" style="margin:0;width:300px;height:250px;" src="http://www.google.com/uds/modules/elements/mapselement/iframe.html?maptype=roadmap" scrolling="no" allowtransparency="true"></iframe>' ,
    "business_hours" => '8:30am,5:00pm,8:30am,5:00pm,8:30am,5:00pm,8:30am,5:00pm,8:30am,5:00pm,closed,,closed,',
    "main_text" =>  '<h2>Welcome To My Business!</h2>
    <p class="p">If you are looking for first-class service, you have come to the right place! We aim to be friendly and approachable.</p>
    <p class="p">We are here to serve you and answer any questions you may have.</p>
    <h2>Call us today: 1.192.555.1212</h2>
    <p class="p">We put our customers first. We listen to you and help you find what you need. Come visit to see what we are all about:</p>
    <ul>
    <li>Industry Leading Products</li>
    <li>Quick Turnaround</li>
    <li>Friendly and Approachable</li>
    <li>And much, much more!</li>
    </ul>',
    "feature_page_1" =>  '2',
    "feature_page_2" =>  '6',
    "feature_page_3" =>  '7',
    "feature_box_disabled" =>  '',    
    "feature_page_summary_1" =>  'Learn more about personal chef and nutrition services.',
    "feature_page_summary_2" =>  'Featuring recipes, health tips, give aways and more!',
    "feature_page_summary_3" =>  'Visit our Retail Store, Call Us or send us a quick Email.',
    "page_summary_1_color" => '333333',
    "page_summary_2_color" => '333333',
    "page_summary_3_color" => '333333',
    "title" =>  '',
    "description" =>  '',
    "keywords" =>  '',
    "seo_disabled" =>  '',
    "analytics" =>  '',
    "webmaster" =>  '',
    
    "mobiname" =>  'My Business',
    "mobiname_color" =>  '333333',
    "mobisub_header" =>  'My Tagline',
    "mobisub_header_color" =>  '333333',
    
    "mobile-layout-enabled" =>  '',
    "mobidirections_disabled" =>  '',
        
    "mobile-button-color" =>  '000000',
    "mobile-text-button-color" =>  'ffffff',
    "mobile-body-color" =>  'ffffff',
    "mobile-home-text"  =>'<p class="p"><span style="color: #000000;">If you are looking for first-class service, you have come to the right place!</span></p>
    <p><strong><span style="color: #000000;">Call Us Now: 1-192-555-1212</span></strong></p>
    <p><span style="color: #000000;">We are here to serve you and answer any questions you may have.</span></p>',
    "mobile-bannerhome-image"  =>'1.png',
    "mobile-map"  =>'http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q=Ste+3100,+1111+South+Figueroa+Street,+Los+Angeles,+California+90015&sll=37.0625,-95.677068&sspn=57.249013,99.667969&ie=UTF8&hq=&hnear=1111+S+Figueroa+St,+Los+Angeles,+California+90015&t=h&z=17');
    if(function_exists(smallbiz_defaults_for_layout)){
      $smallbiz_defaults = array_merge($smallbiz_defaults, smallbiz_defaults_for_layout());      
    }
    return $smallbiz_defaults;
}
//array of default urls for banners:
function smallbiz_default_banners(){
    $arr = array(
            'http://cdn4.expand2web.com/concrete_brown.png',
            'http://cdn4.expand2web.com/concrete_sage_green.png',
            'http://cdn4.expand2web.com/couple-header.jpg',
            'http://cdn4.expand2web.com/dark_swoop_purple.png',
            'http://cdn4.expand2web.com/dark_swoop_teal.png',
            'http://cdn4.expand2web.com/grey-gradient.jpg',
            'http://cdn4.expand2web.com/iceberg-blue.png',
            'http://cdn4.expand2web.com/iceberg-teal.png',
            'http://cdn4.expand2web.com/light_swoop_green.png',
            'http://cdn4.expand2web.com/lights-lightgrey.png',
            'http://cdn4.expand2web.com/lights-orange.png',
            'http://cdn4.expand2web.com/ribbon_tan.png',                        
            );
    return $arr;
}

//        $page_id = $wpdb->insert_id;
//        $sql = "insert into ". $wpdb->postmeta . "(post_id, meta_key, meta_value) values($page_id, '_wp_page_template', 'posts.php')";


// helper function to get one of our special pages
function smallbiz_get_special_page($title){
    global $wpdb;
    $sql = "SELECT * FROM $wpdb->posts WHERE post_title = '".$title."' 
        AND (post_date='0000-00-00 00:00:00' OR post_date < '1980-00-00') 
        AND post_type='page'";
        
    $page = $wpdb->get_results($sql);

    return $page[0];    
}

function smallbiz_get_page_id_by_title($title,$order_number = 0,$content="",$template=false){
    global $wpdb;
    $page_ids = $wpdb->get_results( ( "SELECT ID FROM $wpdb->posts WHERE post_title = '".$title."' 
        AND (post_date='0000-00-00 00:00:00' OR post_content = '".mysql_escape_string($content)."') 
        AND post_type='page'" ));
    $page_id = false;
    $pagesCount = count($page_ids);
    if($pagesCount > 0){ 
        $page_id = $page_ids[0]->ID;
    }
    return $page_id;
}
// Descriptive.
function smallbiz_get_page_id_by_title_create_if_needed($title,$order_number = 0,$content="",$template=false){
    global $wpdb;
    $page_results = $wpdb->get_results( ( "SELECT ID,post_status FROM $wpdb->posts WHERE post_title = '".$title."' 
        AND (post_date='0000-00-00 00:00:00' OR post_content = '".mysql_escape_string($content)."') 
        AND post_type='page'" ));
    $page_id = false;
    $pagesCount = count($page_results);
    
    // To fix error that can result when another plugin changes our post time, this is a hacky workaround in php.
    // I am sorry...    
    if($pagesCount >= 1){
        if($pagesCount > 1){
            // a glich has happened; let's fix it:
            $wpdb->query( ( "DELETE FROM $wpdb->posts WHERE post_title = '".$title."' 
                AND (post_date='0000-00-00 00:00:00' OR post_content = '".mysql_escape_string($content)."') 
                AND post_type='page'" ));
            $pagesCount = 0;
        }
    }
    
    //get_post($page_ids, $output);
    //var_dump($page_ids);
    if($pagesCount == 1){ 
        $page_id = $page_results[0]->ID; 
        $page = $page_results[0];
    } else {    
        $page_id = 0;
        if($pagesCount == 0){
            $sql = "
            INSERT INTO $wpdb->posts (ID, post_author, post_date, post_date_gmt, post_content, post_title ,post_excerpt ,post_status ,comment_status ,ping_status ,post_password ,post_name ,to_ping ,pinged ,post_modified ,post_modified_gmt ,post_content_filtered ,post_parent ,guid ,menu_order ,post_type ,post_mime_type ,comment_count
            )
            VALUES (
                NULL , '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '".$content."', '".$title."', '', 'publish', 'open', 'open', '', '".strtolower($title)."', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0', '', '".intval($order_number)."', 'page', '', '0'
            )
            ";
            $wpdb->query($sql);
            $page_id = $wpdb->insert_id;
        } else {
            $page = smallbiz_get_special_page($title);
            $page_id = $page->ID;
        }
    }
    if($page_id && $page && $page->post_status != "publish"){
        $wpdb->query( ( "UPDATE $wpdb->posts SET  `post_status` =  'publish'  WHERE ID = '".$page_id."'" ));
    }
    $page_template = get_post_meta($page_id, '_wp_page_template');
    if(count($page_template) > 0){
        if($page_template[0] != $template){
           $wpdb->query( ( "UPDATE $wpdb->postmeta SET  `meta_value` =  '$template'  WHERE post_id = '".$page_id."'" ));           
        } 
    } else {
        if($template){
           $wpdb->query( ( "DELETE FROM $wpdb->postmeta WHERE post_id = '".$page_id."'" ));
           $sql = "INSERT INTO ". $wpdb->postmeta . "(post_id, meta_key, meta_value) values($page_id, '_wp_page_template', '$template')";
           $wpdb->query($sql);
        }
    }
    return $page_id;
}

function smallbiz_setup_menu_pages(){
    $home_id = smallbiz_get_page_id_by_title_create_if_needed('Home',-5,'Home ',"index.php");
    if(!get_option("smallbiz_page_for_posts")){
        if(get_option("smallbiz_page_for_posts_created")){
            $blog_id = smallbiz_get_page_id_by_title('Blog',40,'To add blog posts click on -Posts- and choose -Add New- in the WordPress Dashboard. Posts will show in full length - use the -More tag- to create excerpts of your posts.',"posts.php");
        } else {
            $blog_id = smallbiz_get_page_id_by_title_create_if_needed('Blog',40,'To add blog posts click on Posts - Add New in the WordPress Dashboard',"posts.php");        
            update_option("smallbiz_page_for_posts_created","true");
        }
    } else {
        $blog_id = get_option("smallbiz_page_for_posts");
    }
    
    
    // page stuff:    
    if(get_option("show_on_front") != "page"){
        update_option("show_on_front","page");
    }
    // If they've changed this, change it back... and store their pick in our system.
    if(get_option("page_on_front") != $home_id && get_option("page_on_front") != 0){
        update_option("smallbiz_page_on_front",get_option("page_on_front"));
        update_option("page_on_front", $home_id);
    } else if(get_option("page_on_front") == 0 || (isset($_REQUEST["page_on_front"]) && $_REQUEST["page_on_front"]  == $home_id)){
        update_option("smallbiz_page_on_front", $home_id);        
        update_option("page_on_front", $home_id);
    }
    
    if($blog_id != false)
    // Update our verion of the blog page:
    if(get_option("page_for_posts") && get_option("smallbiz_page_for_posts") != get_option("page_for_posts")){
        update_option("smallbiz_page_for_posts", get_option("page_for_posts"));
        $blog_id = get_option("smallbiz_page_for_posts");
    }
    //if(get_option("page_for_posts") != $blog_id){
    if(get_option("page_for_posts") != 0 && $blog_id){
        //update_option("smallbiz_page_for_posts",get_option("page_for_posts"));
        update_option("page_for_posts",$blog_id);
    }
    add_option('smallbiz_homepage_id', $home_id);
    add_option('smallbiz_added_menu_pages', "true");
}

function smallbiz_on_install()

{

    global $wpdb;
    
    $smallbiz_defaults = smallbiz_defaults();

    //$wpdb->show_errors(); 
    //$smallbiz_defaults['']

    add_option('smallbiz_version', $smallbiz_defaults['version']);
    add_option('smallbiz_name', $smallbiz_defaults['name']);
    add_option('smallbiz_layout', $smallbiz_defaults['layout']);
    
    add_option('smallbiz_font_family_header', $smallbiz_defaults['font_family_header']);
    add_option('smallbiz_font_family_headeraddress', $smallbiz_defaults['font_family_headeraddress']);
    add_option('smallbiz_font_family_menu', $smallbiz_defaults['font_family_menu']);
    add_option('smallbiz_font_family_sideh3', $smallbiz_defaults['font_family_sideh3']);
    add_option('smallbiz_font_family_side', $smallbiz_defaults['font_family_side']);
    add_option('smallbiz_font_family_pagetitle', $smallbiz_defaults['font_family_pagetitle']);
    add_option('smallbiz_font_family_main', $smallbiz_defaults['font_family_main']);
    add_option('smallbiz_font_family_feature', $smallbiz_defaults['font_family_feature']);
    add_option('smallbiz_font_family_footer', $smallbiz_defaults['font_family_footer']);
    
    add_option('smallbiz_font_size_mobiname', $smallbiz_defaults['font_size_mobiname']);
    add_option('smallbiz_font_size_mobisubheader', $smallbiz_defaults['font_size_mobisubheader']);

    add_option('smallbiz_font_size_head', $smallbiz_defaults['font_size_head']);
    add_option('smallbiz_font_size_headeraddress', $smallbiz_defaults['font_size_headeraddress']);
    add_option('smallbiz_font_size_menu', $smallbiz_defaults['font_size_menu']);
    add_option('smallbiz_font_size_pagetitle', $smallbiz_defaults['font_size_pagetitle']);
        
    add_option('smallbiz_font_size_main', $smallbiz_defaults['font_size_main']);
    add_option('smallbiz_font_size_feature', $smallbiz_defaults['font_size_feature']);
    add_option('smallbiz_font_size_footer', $smallbiz_defaults['font_size_footer']);
    
    add_option('smallbiz_font_size_h1', $smallbiz_defaults['font_size_h1']);
    add_option('smallbiz_font_size_h2', $smallbiz_defaults['font_size_h2']);
    add_option('smallbiz_font_size_h3', $smallbiz_defaults['font_size_h3']);
    add_option('smallbiz_font_size_sideh3', $smallbiz_defaults['font_size_sideh3']);
    add_option('smallbiz_font_size_side', $smallbiz_defaults['font_size_side']);
      
    add_option('smallbiz_sub_header', $smallbiz_defaults['sub_header']);
    add_option('smallbiz_street', $smallbiz_defaults['street']);
    add_option('smallbiz_city',$smallbiz_defaults['city']);
    add_option('smallbiz_state', $smallbiz_defaults['state']);
    add_option('smallbiz_zip', $smallbiz_defaults['zip']);
    add_option('smallbiz_countryprefix', $smallbiz_defaults['countryprefix']);
    add_option('smallbiz_telephone', $smallbiz_defaults['telephone']);
    add_option('smallbiz_email', $smallbiz_defaults['email']);
    add_option('smallbiz_headeremail', $smallbiz_defaults['headeremail']);
     add_option('smallbiz_header_box_disabled', $smallbiz_defaults['header_box_disabled']);
    add_option('smallbiz_directions', $smallbiz_defaults['directions']);
    add_option('smallbiz_cities', $smallbiz_defaults['cities']);
    add_option('smallbiz_credit', $smallbiz_defaults['credit']);
    add_option('smallbiz_banner', $smallbiz_defaults['banner']);
    add_option('smallbiz_page_image', $smallbiz_defaults['page_image']);
    add_option('smallbiz_map_link', $smallbiz_defaults['map_link']);
    add_option('smallbiz_business_hours', $smallbiz_defaults['business_hours']);
    add_option('smallbiz_main_text', $smallbiz_defaults['main_text']);
    add_option('smallbiz_feature_page_1', $smallbiz_defaults['feature_page_1']);
    add_option('smallbiz_feature_page_2', $smallbiz_defaults['feature_page_2']);
    add_option('smallbiz_feature_page_3', $smallbiz_defaults['feature_page_3']);
    add_option('smallbiz_page_summary_1_color', $smallbiz_defaults['page_summary_1_color']);
    add_option('smallbiz_page_summary_2_color', $smallbiz_defaults['page_summary_2_color']);
    add_option('smallbiz_page_summary_3_color', $smallbiz_defaults['page_summary_3_color']);
    add_option('smallbiz_feature_box_disabled', $smallbiz_defaults['feature_box_disabled']);
    add_option('smallbiz_feature_page_summary_1', $smallbiz_defaults['feature_page_summary_1']);
    add_option('smallbiz_feature_page_summary_2', $smallbiz_defaults['feature_page_summary_2']);
    add_option('smallbiz_feature_page_summary_3', $smallbiz_defaults['feature_page_summary_3']);
    add_option('smallbiz_title', $smallbiz_defaults['title']);
    add_option('smallbiz_description', $smallbiz_defaults['description']);
    add_option('smallbiz_keywords', $smallbiz_defaults['keywords']);
    add_option('smallbiz_seo_disabled', $smallbiz_defaults['seo_disabled']);
    add_option('smallbiz_analytics', $smallbiz_defaults['analytics']);
    add_option('smallbiz_webmaster', $smallbiz_defaults['analytics']);
    add_option('smallbiz_name_color', $smallbiz_defaults['color']);
    add_option('smallbiz_sub_header_color', $smallbiz_defaults['color']);
    add_option('smallbiz_street_color', $smallbiz_defaults['color']);
    add_option('smallbiz_city_color', $smallbiz_defaults['color']);
    add_option('smallbiz_state_color', $smallbiz_defaults['color']);    
    add_option('smallbiz_zip_color', $smallbiz_defaults['color']);
    add_option('smallbiz_telephone_color', $smallbiz_defaults['color']);
    add_option('smallbiz_email_color', $smallbiz_defaults['color']);
    add_option('smallbiz_pages_color', $smallbiz_defaults['pages_color']);
    add_option('smallbiz_headeremail_color', $smallbiz_defaults['color']);
    add_option('smallbiz_main_text_color', $smallbiz_defaults['color']);
    add_option('smallbiz_menu_color', $smallbiz_defaults['menu_color']);
    add_option('smallbiz_active_color', $smallbiz_defaults['active_color']);
    add_option('smallbiz_hover_color', $smallbiz_defaults['hover_color']);
    add_option('smallbiz_passive_color', $smallbiz_defaults['passive_color']);
    add_option('smallbiz_headertag_color', $smallbiz_defaults['headertag_color']);
    add_option('smallbiz_p_color', $smallbiz_defaults['p_color']);
    add_option('smallbiz_sideh3_color', $smallbiz_defaults['sideh3_color']);
    add_option('smallbiz_hyper_color', $smallbiz_defaults['hyper_color']);
    add_option('smallbiz_hyperhover_color', $smallbiz_defaults['hyperhover_color']);
    add_option('smallbiz_creds_color', $smallbiz_defaults['creds_color']);
    add_option('smallbiz_page_summary_1_color', $smallbiz_defaults['color']);
    add_option('smallbiz_page_summary_2_color', $smallbiz_defaults['color']);
    add_option('smallbiz_page_summary_3_color', $smallbiz_defaults['color']);
    add_option('smallbiz_mobile-layout-enabled', $smallbiz_defaults['mobile-layout-enabled']);
     add_option('smallbiz_mobiname', $smallbiz_defaults['mobiname']);
        add_option('smallbiz_mobiname_color', $smallbiz_defaults['mobiname_color']);
        add_option('smallbiz_mobisub_header', $smallbiz_defaults['mobisub_header']);
    add_option('smallbiz_mobisub_header_color', $smallbiz_defaults['mobisub_header_color']);
    
        add_option('smallbiz_mobidirections_disabled', $smallbiz_defaults['mobidirections_disabled']);
    add_option('smallbiz_mobile-home-text', $smallbiz_defaults['mobile-home-text']);
    add_option('smallbiz_mobile-bannerhome-image', $smallbiz_defaults['mobile-bannerhome-image']);
    add_option('smallbiz_mobile-map', $smallbiz_defaults['mobile-map']);   
    add_option('smallbiz_mobile-button-color', 
    $smallbiz_defaults['mobile-button-color']); 
    add_option('smallbiz_mobile-body-color', 
    $smallbiz_defaults['mobile-body-color']);  
    add_option('smallbiz_mobile-text-button-color', $smallbiz_defaults['mobile-text-button-color']);  
    
    

    if(function_exists(smallbiz_on_layout_activate)){
        smallbiz_on_layout_activate();
    }
/* for preview fix end  */

    if('' == get_option('smallbiz_added_pages'))

    {

    

                $sql = "

INSERT INTO $wpdb->posts (

`ID` ,

`post_author` ,

`post_date` ,

`post_date_gmt` ,

`post_content` ,

`post_title` ,

`post_excerpt` ,

`post_status` ,

`comment_status` ,

`ping_status` ,

`post_password` ,

`post_name` ,

`to_ping` ,

`pinged` ,

`post_modified` ,

`post_modified_gmt` ,

`post_content_filtered` ,

`post_parent` ,

`guid` ,

`menu_order` ,

`post_type` ,

`post_mime_type` ,

`comment_count`

)

VALUES (

NULL , '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'To change the phone number and email recipient of this page visit the SmallBiz Options Panel. Please visit this UserGuide Post if you would like to edit the appearance, contact text or form itself http://userguide.expand2web.com/editing-the-contact-us-page/.', 'Contact', '', 'publish', 'open', 'open', '', 'contact', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0', '', '0', 'page', '', '0'

)

            ";

                $wpdb->query($sql);

        $page_id = $wpdb->insert_id;

        $sql = "insert into ". $wpdb->postmeta . "(post_id, meta_key, meta_value) values($page_id, '_wp_page_template', 'contact_page.php')";

        $wpdb->query($sql);

        /*****/

        $sql = "

INSERT INTO $wpdb->posts (

`ID` ,

`post_author` ,

`post_date` ,

`post_date_gmt` ,

`post_content` ,

`post_title` ,

`post_excerpt` ,

`post_status` ,

`comment_status` ,

`ping_status` ,

`post_password` ,

`post_name` ,

`to_ping` ,

`pinged` ,

`post_modified` ,

`post_modified_gmt` ,

`post_content_filtered` ,

`post_parent` ,

`guid` ,

`menu_order` ,

`post_type` ,

`post_mime_type` ,

`comment_count`

)

VALUES (
NULL , '1', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'To change the map, directions, phone number and business hours of this page visit the SmallBiz Options Panel. This Page uses a Wordpress Page Template. You can edit the code of this Page Template by going to Appearance -> Editor -> Find Us Page Page Template (findeus_page.php).', 'Find Us', '', 'publish', 'open', 'open', '', 'find_us', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '0', '', '0', 'page', '', '0'

)

            ";

        $wpdb->query($sql);

        $page_id = $wpdb->insert_id;

        $sql = "insert into ". $wpdb->postmeta . "(post_id, meta_key, meta_value) values($page_id, '_wp_page_template', 'findeus_page.php')";

        $wpdb->query($sql);

        /*****/


        add_option('smallbiz_added_pages', "true");
    }
}
function get_page_($id)
{
    global $wpdb;
    $sql = "select * from ". $wpdb->prefix ."posts where post_type='page' and post_status='publish' and ID='". $id ."'";

    $page = $wpdb->get_results($sql);

    return $page[0];
}
function smallbiz_on_admin_menu()
{
    // URL redirect: Detect if we are editing the Home page:
    if(strpos($_SERVER["REQUEST_URI"], "wp-admin/post.php") !== false){
        $page = smallbiz_get_special_page('Home');
        $page_id = $page->ID;
        if(isset($_REQUEST['post']) && $_REQUEST['post'] == $page->ID){
            $url = $_SERVER["REQUEST_URI"];
            $url = str_replace("post.php?", ("themes.php?page=".(__FILE__)."&"), $url)."#mainpagetext";
            header("Location: ".$url,TRUE,307);
        }        
    } else // URL redirect: Detect if we have just activated our theme: 
    if (strpos($_SERVER["REQUEST_URI"], "wp-admin/themes.php") !== false){
        if(isset($_REQUEST['activated']) && $_REQUEST['activated'] == "true"){
            $url = $_SERVER["REQUEST_URI"];
            $url = str_replace("themes.php?activated=true", ("themes.php?page=".(__FILE__)."&"), $url)."";
            header("Location: ".$url,TRUE,307);
        }         
    }
    // If smallbiz_added_menu_pages is empty, add them:
    if('' == get_option('smallbiz_added_menu_pages') || isset($_REQUEST["reinit_smallbiz_menu_pages"])){
//        smallbiz_setup_menu_pages();
    }
    // For now, do this every time:
    smallbiz_setup_menu_pages();
    // add our theme page:
    add_theme_page(__('SmallBiz Options'), __('SmallBiz Options'), 'switch_themes', (__FILE__), 'smallbiz_theme_option_page'); }


function smallbiz_theme_option_page()
{
    global $wpdb, $smallbiz_cur_version ;
    if(isset($_POST['ajax'])){
        if(isset($_POST['asset']) && isset($_POST['action']) && $_POST['action'] == "remove"){
            $fileName = stripcslashes($_POST['asset']);
            $user_banner_storage = get_option('smallbiz_user_banner_storage');
            if(isset($user_banner_storage[$fileName]) && $user_banner_storage[$fileName]['path']){
                $user_banner_storage = get_option('smallbiz_user_banner_storage');
                unset($user_banner_storage[$fileName]);
                update_option('smallbiz_user_banner_storage', $user_banner_storage);
                //unlink($user_banner_storage[$fileName]['path'].'/'.$fileName);                
            }
        }
        die(); // not early enough.
    }

    $cur_version = $smallbiz_cur_version;

    $version = get_option('smallbiz_version');

    if($version != $cur_version)
    {
        smallbiz_on_install();
    }
    if(isset($_POST['layout']) && get_option("smallbiz_layout") != $_POST['layout']){
        smallbiz_on_install();        
    } 

	if(isset($_POST['authkey_add'])){
	    update_option("smallbiz_authkey",   $_POST['authkey_add']);
	    update_option("smallbiz_authname",  $_POST['authname_add']);
	    update_option("smallbiz_authemail", $_POST['authemail_add']);
	}

    if(isset($_POST['sales_update']) || isset($_POST['layout']))
    {
        $options = array(

			'name',
			
            'layout',
            
            'font_family_header',
            
            'font_family_headeraddress',
            
            'font_family_menu',
            
            'font_family_pagetitle',
            
            'font_family_sideh3',
            
            'font_family_side',
            
            'font_family_main',
            
            'font_family_feature',
            
            'font_family_footer',
            
            'font_size_mobiname',
            
            'font_size_mobisubheader',
            
            'font_size_head',
            
            'font_size_headeraddress',
            
            'font_size_menu',
            
            'font_size_pagetitle',
            
            'font_size_main',
            
            'font_size_feature',
            
            'font_size_footer',
            
            'font_size_h1',
            
            'font_size_h2',
            
            'font_size_h3',
            
            'font_size_sideh3',
            
            'font_size_side',
            
            'name',
            
            'name_color', 

            'sub_header',

            'sub_header_color',

            'street',

            'street_color',
            
            'city',
            
            'city_color',
            
            'state',
            
            'state_color',

            'zip',

            'zip_color',

            'telephone',
            
            'countryprefix',

            'telephone_color',

            'email',

            'email_color',

            'headeremail',

            'headeremail_color',
            
            'header_box_disabled',
            
            'directions',
            
            'cities',
            
            'credit',
            
            'main_text',

            'main_text_color',
            
            'menu_color',
            
            'active_color',
            
            'passive_color',
            
            'hover_color',
            
            'headertag_color',
            
            'pages_color',
            
            'p_color',
            
            'sideh3_color',
            
            'hyper_color',
            
            'hyperhover_color',
            
            'creds_color',
            
            'feature_page_1',

            'feature_page_2',

            'feature_page_3',

            'page_summary_1_color',

            'page_summary_2_color',

            'page_summary_3_color',

            'feature_box_disabled',

            'feature_page_summary_1',

            'feature_page_summary_2',

            'feature_page_summary_3',

            'title',
            
            'description',
            
            'keywords',
            
            'seo_disabled',
            
            'webmaster',

            'map_link',

            'analytics',
            
            'mobidirections',
            
            'mobiname',
            
            'mobiname_color', 

            'mobisub_header',

            'mobisub_header_color',
            
            'mobile-layout-enabled',
            
            'mobile-button-color',
            
            'mobile-text-button-color',
            
            'mobile-body-color',
            
            'mobile-home-text',
            
            'mobile-bannerhome-image',
            
            'mobile-map'

            );
        if(function_exists(smallbiz_layout_extra_options)){
             $options = array_merge($options, smallbiz_layout_extra_options()); 
        }

        foreach($options as $option)
        {
            if(isset($_POST[$option])){
                update_option('smallbiz_'. $option, stripcslashes($_POST[$option]));
            }
        }
        
        // check boxes -- toggle:
        if(isset($_POST['sales_update']) || isset($_POST['layout'])){
        
            if(isset($_POST["feature_box_disabled"])){
                update_option("smallbiz_feature_box_disabled", "true");
            } else {
                update_option("smallbiz_feature_box_disabled", "");
            }
            if(isset($_POST["seo_disabled"])){
                update_option("smallbiz_seo_disabled", "true");
            } else {
                update_option("smallbiz_seo_disabled", "");
            }
            if(isset($_POST["header_box_disabled"])){
                update_option("smallbiz_header_box_disabled", "true");
            } else {
                update_option("smallbiz_header_box_disabled", "");
            }
            
             if(isset($_POST["mobidirections_disabled"])){
                update_option("smallbiz_mobidirections_disabled", "true");
            } else {
                update_option("smallbiz_mobidirections_disabled", "");
            }

            if(isset($_POST["mobile-layout-enabled"])){
                update_option("smallbiz_mobile-layout-enabled", "true");
            } else {
                update_option("smallbiz_mobile-layout-enabled", "");
            }
            
        }
        
        // update frontpage -- don't include this with the others to avoid confusion with non-smallbiz
        // page_on_front.
        if(isset($_POST['smallbiz_page_on_front'])){
            update_option('smallbiz_page_on_front', stripcslashes($_POST['smallbiz_page_on_front']));
        }
        
        // Update photo
        
        if($_POST['banner'] == "" && $_FILES['new_banner']['tmp_name'] != ""){
            $newFileName = str_replace(" ", "_", $_FILES['new_banner']['name']);
            $upload_dir = wp_upload_dir();
            @move_uploaded_file($_FILES['new_banner']['tmp_name'], $upload_dir['path']. '/'. $newFileName);
            $user_banner_storage = get_option('smallbiz_user_banner_storage');
            $user_banner_storage[$newFileName] = array('path'=>$upload_dir["path"], 'url'=>$upload_dir["url"]);
            update_option('smallbiz_user_banner_storage', $user_banner_storage);
            update_option('smallbiz_banner', $newFileName);

        } else if($_POST['banner'] != get_option('smallbiz_banner')){
            $newFileName = stripcslashes($_POST['banner']);
            update_option('smallbiz_banner', $newFileName);
            $user_banner_storage = get_option('smallbiz_user_banner_storage');
            if(!isset($user_banner_storage[$newFileName])){
                $default_images = smallbiz_default_banners();
                foreach($default_images as $url){
                    if(basename($url) == $newFileName){
                        smallbiz_fetch_banner_from_url($url);
                    }
                }
            }
        }


        if($_FILES['page_image']['tmp_name'] != ""){

            unlink(dirname(__FILE__).'/images/'.get_option('smallbiz_page_image'));

            @move_uploaded_file($_FILES['page_image']['tmp_name'], dirname(__FILE__). '/images/'. $_FILES['page_image']['name']);

            update_option('smallbiz_page_image', $_FILES['page_image']['name']);

         }
         
if($_FILES['mobile-bannerhome-image']['tmp_name'] != ""){

unlink(dirname(__FILE__).'/images/'.get_option('smallbiz_mobile-bannerhome-image'));

@move_uploaded_file($_FILES['mobile-bannerhome-image']['tmp_name'], dirname(__FILE__). '/images/'. $_FILES['mobile-bannerhome-image']['name']);

update_option('smallbiz_mobile-bannerhome-image', $_FILES['mobile-bannerhome-image']['name']);

}


    if($_FILES['map']['tmp_name'] != ""){

            unlink(dirname(__FILE__).'/images/'.get_option('smallbiz_map_image'));

            @move_uploaded_file($_FILES['map']['tmp_name'], dirname(__FILE__). '/images/'. $_FILES['map']['name']);

            update_option('smallbiz_map_image', $_FILES['map']['name']);

        }

        if(is_array($_POST['omit_pages']) && count($_POST['omit_pages']) >0){

            $page = join(',', $_POST['omit_pages']);

        }

        $b_hours = $_POST['b_hours_1_s'] .','. $_POST['b_hours_1_e'].','.

            $_POST['b_hours_2_s'] .','. $_POST['b_hours_2_e'].','.

            $_POST['b_hours_3_s'] .','. $_POST['b_hours_3_e'].','.

            $_POST['b_hours_4_s'] .','. $_POST['b_hours_4_e'].','.

            $_POST['b_hours_5_s'] .','. $_POST['b_hours_5_e'].','.

            $_POST['b_hours_6_s'] .','. $_POST['b_hours_6_e'].','.

            $_POST['b_hours_7_s'] .','. $_POST['b_hours_7_e'];

        update_option('smallbiz_business_hours', $b_hours);

        update_option('smallbiz_omit_pages', $page);

    }

?>

<style>
#message{display:none;}
</style>

<div class="wrap">
<h2>SmallBiz Theme Options Panel</h2>

<!--<div id="optionspanelheader">-->

<div id="welcome-box">
<div id="outerbox"> 
<div id="welcome-box-inner">


<a href="http://members.expand2web.com/E2W-theme-images/optionspanelvideo.php" onclick="window.open('http://members.expand2web.com/E2W-theme-images/optionspanelvideo.php','popup','width=500,height=410,scrollbars=no,resizable=yes,toolbar=no,directories=no,location=no,menubar=no,status=no,left=50,top=0'); return false"><img src="<?php bloginfo('template_url'); ?>/sbthemescreen-optionspanel.jpg" style="float:left;border:solid 1px #ccc;margin-right:30px;margin-bottom:15px;border-radius: 6px 6px 6px 6px;"></a>



<h2>Welcome to the Expand2Web SmallBiz Theme!</h2>

<p>Build a complete, professionally designed, search engine optimized website that includes unlimited pages, Blog, <br />  Facebook Pages App and offers a touch-enabled mobile landing page!</p>

    <ul>
    <li>11 Pre-build layouts or create your own</li>
    <li>Touch enabled mobile landing pages (auto detected)</li>
    <li>Comprehensive User Guide and Printable PDF Starter Pack</li> 
    <li>Personal email support, no posting tickets, no dead forums</li>
    <li>Facebook App - manage your Facebook Page Tab content within WordPress</li>
    <li>SEO, custom nav menus, and much, much more...</li>
<li>Upgrade to unlock all Features Now!</li>
	</ul>
    
<p><em>---> Get started building your new website below!</em></p>

</div>
</div>
</div>

<div class="dropdown" id="dropdowns-help">
<a href="javascript:;" onmousedown="if(document.getElementById('helpbox').style.display == 'none'){ document.getElementById('helpbox').style.display = 'block'; }else{ document.getElementById('helpbox').style.display = 'none'; }"><div class="groupbox1">             
<div style="float:left;"><img src="http://members.expand2web.com/E2W-theme-images/arrow.png" alt="Expand2Web Arrow" /></div><div style="float:left;padding-top:5px;">Getting Started Video | User-Guide | Affiliate Program</a></div>
</div>
</div> <!--groupbox1-->

<div id="helpbox" style="display:none">
<div id="outerbox"> 
<div id="innerbox-help">

<a href="http://members.expand2web.com/E2W-theme-images/optionspanelvideo.php" onclick="window.open('http://members.expand2web.com/E2W-theme-images/optionspanelvideo.php','popup','width=500,height=410,scrollbars=no,resizable=yes,toolbar=no,directories=no,location=no,menubar=no,status=no,left=50,top=0'); return false"><img src="http://members.expand2web.com/E2W-theme-images/optionspanelvideo.png" alt="Expand2Web Options Panel Video" style="margin-right:15px;" /></a>

<a href="http://userguide.expand2web.com/" target="_blank"><img src="http://members.expand2web.com/E2W-theme-images/userguide.png" alt="Expand2Web User Guide" style="margin-right:15px;" /></a>

<a href="<?php bloginfo('template_url'); ?>/palette"
onclick="window.open('<?php bloginfo('template_url'); ?>/palette','popup','width=671,height=636,scrollbars=no,resizable=yes,toolbar=no,directories=no,location=no,menubar=no,status=no,left=50,top=0'); return false">
<img src="http://members.expand2web.com/E2W-theme-images/colorhelper.png" alt="Expand2Web Getting Started" style="margin-right:15px;"/></a>

<a href="http://www.expand2web.com/blog/affiliates/" target="_blank"><img src="http://members.expand2web.com/E2W-theme-images/affiliate.png" alt="Expand2Web Affiliate" style="margin-right:15px;" /></a>

</div>

<div id="protip">
<p>Need help? Email us: <a href="mailto:support@expand2web.com?subject=Userguide Support Request">support@expand2web.com</a>
 <span style="display:none;" id="alreadyPurchasedTop">| Already purchased the theme? <a href="#">Click here</a> to authorize this site.</span>
 <?php if(get_option('smallbiz_authkey')){ ?>
     <span id="purchaseInfo">| Theme registered to 
         <a href="mailto:<?php echo get_option('smallbiz_authemail'); ?>"><?php echo get_option('smallbiz_authname'); ?></a>.
         <input type="hidden" name="authkey" id="authkey" value="<?php echo get_option('smallbiz_authkey'); ?>" />
     </span>
 <?php } ?> 
</p>
</div>
</div>
</div>


<div  class="dropdown"  id="dropdowns-header"><a href="javascript:;" onmousedown="e2w.admin.toggleDropdown('headeroptions')"><div class="groupbox1"> 			
<div style="float:left;"><img src="http://members.expand2web.com/E2W-theme-images/arrow.png" alt="Expand2Web Arrow" /></div><div style="float:left;padding-top:5px;">Header Graphic | Business Information | Menu Colors | Background Colors</a></div>
</div>
</div> <!--groupbox1-->
    
<div id="headeroptions" style="display:none">    

<form enctype="multipart/form-data" action="" method="post" id="smallbiz_options_form">

<div class="outerbox" id="outerbox-headeroptions"> 
<h6>Select a Header Graphic or Upload your Own</h6>    
<div class="innerbox">

<p>The header graphic is the anchor of your site. Use it to determine the other colors.</p><p>The Smallbiz Theme will pre-populate color choices for you based on the header graphic you pick. You can overwrite any of the color suggestions.</p>

    <div id="header_banner_table">
            <?php                 
                $user_banner_storage = get_option('smallbiz_user_banner_storage');
                // Default images are just an array of paths:
                $default_images = smallbiz_default_banners();
                function draw_banner_entry($file, $url, $allow_remove){
                    $banner_namepart = substr($file, 0, strpos($file, "."));
                    ?>
                      <div class="themeHeaderSelector_container" id='themeHeaderSelector_container_<?php echo $banner_namepart; ?>'>
                          <div class="themeHeaderSelector_radio">
                            <input type="radio" name="banner" id="banner_<?php echo $file ?>" class="banner_radio" value="<?php echo  $file; ?>" <?php
                            if( get_option('smallbiz_banner') == "$file"){
                                echo " checked=\"checked\" ";
                            }
                            echo ' onchange="javascript:change_active_e2w_selector(-1,\'banner\',\'themeHeader\',\'\');"';
                            ?> />
                          </div>
                          <div class="themeHeaderSelector_option
                          <?php
                          if(get_option('smallbiz_banner') == $file){
                              echo ' themeHeaderSelector_optionSelected';
                          }
                          echo '" id="themeHeaderSelector_'.$file;
                          ?>"><?php
                          echo '<img src="'.$url.'" alt="'.$banner_namepart.'" title="'.$banner_namepart.'" />';
                          ?>
                            <?php if($allow_remove) { ?>
                                <div class='remover' title='remove'> &#10006; </div>
                            <?php } ?>
                            </div>
                        </div>    
                        <script type='text/javascript'>
                            jQuery('#themeHeaderSelector_container_<?php echo $banner_namepart; ?> div.themeHeaderSelector_option').click(function(e){
                                if(jQuery(e.target).hasClass("remover")){
                                    if(confirm("Really remove the <?php echo $banner_namepart; ?> banner?")){
                                       jQuery('#themeHeaderSelector_container_<?php echo $banner_namepart; ?>').remove();
                                       remove_e2w_asset('<?php echo $file; ?>');
                                    }
                                } else {
                                    change_active_e2w_selector('<?php echo $file; ?>','banner','themeHeader','');
                                }
                            });
                        </script>
                    <?php                
                };                
                if(!is_array($user_banner_storage)){
                    $user_banner_storage = array();
                    e2w_migrate_legacy_headers(&$user_banner_storage);
                }                
                foreach($user_banner_storage as $file=>$dirs){                      
                      draw_banner_entry($file, ($user_banner_storage[$file]['url'].'/'.$file), true);
                }
                // defaults:
                foreach($default_images as $url){
                    $file = basename($url);
                    if(!isset($user_banner_storage[$file])){
                      draw_banner_entry($file, $url, $false);
                    }
                }
                   
                   
                   
                $directory = opendir(dirname(__FILE__).'/images/banners/'); //Our directory handler     
                // cycle through all fies
                while (false !== ($file = readdir($directory))) {
                    if ($file != '.' && $file != '..'){
                      $banner_namepart = substr($file, 0, strpos($file, "."));    
                      ?>
                      <div class="themeHeaderSelector_container">
                          <div class="themeHeaderSelector_radio">
                            <input type="radio" name="banner" id="banner_<?php echo $file ?>" class="banner_radio" value="<?php echo  $file; ?>" <?php
                            if( get_option('smallbiz_banner') == "$file"){
                                echo " checked=\"checked\" ";
                            }
                            echo ' onchange="javascript:e2w.admin.change_active_selector(-1,\'banner\',\'themeHeader\',\'\');"';
                            ?> />
                          </div>
                          <div onclick="javascript:return e2w.admin.change_active_selector('<?php echo $file; ?>','banner','themeHeader','')" class="themeHeaderSelector_option
                          <?php
                          if(get_option('smallbiz_banner') == $file){
                              echo ' themeHeaderSelector_optionSelected';
                          }
                          echo '" id="themeHeaderSelector_'.$file;
                          ?>"><?php
                          echo '<img src="';
                          echo get_bloginfo('template_url').'/images/banners/'.$file;
                          echo '" alt="'.$banner_namepart.'" title="'.$banner_namepart.'" />';
                          ?>
                            </div>
                        </div>
                     
                      <?php
                    }
                }
                closedir($directory);
            ?>
            <div class="clear"></div>
            <div class="themeHeaderSelector_container" style="width:400px;">
               <div class="themeHeaderSelector_radio" style="top:20px;">
                  <input type="radio" name="banner" id="banner_" class="banner_radio" value="" 
                   onchange="javascript:e2w.admin.change_active_selector(-1,'banner','themeHeader','');"
                  />
               </div>
               <div onclick="javascript:return e2w.admin.change_active_selector('','banner','themeHeader','')" class="themeHeaderSelector_option" style="width:380px; height:40px"
                 id="themeHeaderSelector_">
                    <table>
                        <tr>
                            <th style="width:136px;" id="new_banner_label">
                                New Header
                                <br/><small>960 x 150px</small>
                            </th>
                            <td class="header_info_cell" >
                                <input type="file" class="fileinput" onchange="javascript:e2w.admin.display_new_banner_message();" name="new_banner" id="new_banner" />
                            </td>
                        </tr>
                    </table>
               </div>
            </div>
            <div class="clear"></div>            
            <p id="new_banner_message" ></p>
            

            <p>When creating your own header graphic make sure there are no spaces or punctuation in the file name.</p>
            Example of bad file name: my.super (8)cool headergraph.jpg<br />
            Example of a good file name: mycoolheader.jpg
            <br />
            <p class="userguide">More header graphic tips and links to free image tools can be found in this <a href="http://userguide.expand2web.com/creating-and-adding-your-own-header-graphic/" target="_blank">User Guide Post</a>.</p>
            <br />
     <p><input type="submit" value="Save Changes" name="sales_update" /></p>
    </div>
    <div class="clear"></div>    
    
</div> <!--headerbackground-->
<div id="protip">
<p>ProTip: Download more <a href="http://userguide.expand2web.com/banner-images.php" target="_blank">graphics here</a> and learn how to customize your header in our <a href="http://userguide.expand2web.com/creating-and-adding-your-own-header-graphic/" target="_blank"> User Guide</a> .</p>
</div>
</div> <!--outerbox-->


<div class="outerbox" id="outerbox-header-notext">         
<h6>Display no text over the header graphic</h6>
<div class="innerbox">         
        
          <p><strong>Check box to not display Business name, Tag line and Address on top of your header graphic. </strong> 
        
        <input type="checkbox" name="header_box_disabled"
        <?php if(get_option('smallbiz_header_box_disabled') != ""){?>
                  checked="checked"
              <?php } ?>
              ></input></p>
              
         <p>Use this to hide the header text when you have your own banner or logo and you don't want the header text (Name, Tagline & Address) to write over it. <br />Your Business info will still be <em>available for search engine</em> crawlers to be indexed - but won't over-write your beautiful custom header graphic!</p>
         
            <br />
     <p><input type="submit" value="Save Changes" name="sales_update" /></p>
</div> <!--hideheader-->
</div> <!--outerbox-->

<div class="outerbox" id="outerbox-businessinfo">     
<h6>Business Information</h6>
<div class="innerbox"> 

<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/jscolor/jscolor.js"></script>


        <p>Business Name:<br /> <input style="width:400px" type="text" name="name" value="<?php echo get_option("smallbiz_name")?>" />
            
        Font Color<input class="color" type="text" style="width:100px" name="name_color" value="<?php echo get_option('smallbiz_name_color')?>"/>  </p>

        <p>Sub Header / Tag Line:<br /> <input style="width:400px" type="text" name="sub_header" value="<?php echo get_option("smallbiz_sub_header")?>" />

        Font Color<input class="color" type="text" style="width:100px" name="sub_header_color" value="<?php echo get_option('smallbiz_sub_header_color')?>"/> </p>

        <p>Street Address:<br /> <input style="width:400px" type="text" name="street" value="<?php echo get_option("smallbiz_street")?>" />

        Font Color<input class="color"type="text" style="width:100px" name="street_color" value="<?php echo get_option('smallbiz_street_color')?>"/> </p>

        <p>City:<br /> <input style="width:400px" type="text" name="city" value="<?php echo get_option("smallbiz_city")?>" />

        Font Color<input class="color" type="text" style="width:100px" name="city_color" value="<?php echo get_option('smallbiz_city_color')?>"/> </p>

        <p>State:<br /> <input style="width:400px" type="text" name="state" value="<?php echo get_option("smallbiz_state")?>" />

        Font Color<input class="color"type="text" style="width:100px" name="state_color" value="<?php echo get_option('smallbiz_state_color')?>"/> </p>

        <p>Zip:<br /> <input style="width:400px" type="text" name="zip" value="<?php echo get_option("smallbiz_zip")?>" />

        Font Color<input class="color" type="text" style="width:100px" name="zip_color" value="<?php echo get_option('smallbiz_zip_color')?>"/> </p>
        
        <p>Phone Country Code (Example: "+1" for USA, "+44" for UK etc.)<br /> <input style="width:400px" type="text" name="countryprefix" value="<?php echo get_option("smallbiz_countryprefix")?>" />

        <p>Telephone:<br /> <input style="width:400px" type="text" name="telephone" value="<?php echo get_option("smallbiz_telephone")?>" />

        Font Color<input class="color" type="text" style="width:100px" name="telephone_color" value="<?php echo get_option('smallbiz_telephone_color')?>"/> </p>

        <p>Email Address (optional - displayed in page header) :<br /> <input style="width:400px" type="text" name="headeremail" value="<?php echo get_option("smallbiz_headeremail")?>" />

        Font Color<input class="color" type="text" style="width:100px" name="headeremail_color" value="<?php echo get_option('smallbiz_headeremail_color')?>"/> </p>
        
        <p>Email Address (used for "Contact Us Page" email form) :<br /> <input style="width:400px" type="text" name="email" value="<?php echo get_option("smallbiz_email")?>" />

     </p>
     
     <br />
     <p><input type="submit" value="Save Changes" name="sales_update" /></p>

</div> <!--businessinfo-->
<div id="protip">
<p>ProTip: Don't leave the address field blank. Use the Option above to not display the address in the header - Search Engines can still find it.</p>
</div>
        
</div> <!--outerbox-->

            

<div class="outerbox" id="outerbox-menucolor">         
<h6>Menu Color Selector</h6>
<div class="innerbox"> 

<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/jscolor/jscolor.js"></script>
<p><strong>Pick your Menu colors - the Sidebar will inherent the colors too.</strong></p>
              
              <br />
<div style="width:835px; height:190px;">    
              
<div style="width:480px; height:190px; float:left">
<table width="410" border="0">
  <tr>
    <td width="290">1) Menu Background Color</td>
    <td width="110"><input class="color" type="text" style="width:100px" name="menu_color" value="<?php echo get_option('smallbiz_menu_color')?>"/></td>
  </tr>
  
</table>
                
<br />
        
<table width="410" border="0">
  <tr>
    <td width="290">2) Current (Active) - Font Color</td>
    <td width="110"><input class="color" type="text" style="width:100px" name="active_color" value="<?php echo get_option('smallbiz_active_color')?>"/></td>
  </tr>
  
  </table>
  
  <br />
  
<table width="410" border="0">
  <tr>
    <td width="290">3) Hover over - Font Color</td>
    <td width="110"><input class="color" type="text" style="width:100px" name="hover_color" value="<?php echo get_option('smallbiz_hover_color')?>"/></td>
  </tr>
  
  </table>
  
  <br />
  
<table width="410" border="0">
  <tr>
    <td width="290">4) Passive - Font Color</td>
    <td width="110"><input class="color" type="text" style="width:100px" name="passive_color" value="<?php echo get_option('smallbiz_passive_color')?>"/></td>
  </tr>
  
</table>

</div>
<div style="width:355px; float:left;margin-top:16px;">
<p><em>Click image for Menu Color Picker Legend:</em><a href="http://members.expand2web.com/E2W-theme-images/legendmenu.png" alt="Smallbiz Mobile Layout" target="_blank"><img src="http://members.expand2web.com/E2W-theme-images/legendmenu-prev.png" /></a></p>
</div>
</div>

<br />
        
<p><input type="submit" value="Save Changes" name="sales_update" /></p>
</div>
<div id="protip">
<p>ProTip: Need to Omit Pages from the Menu or Create Sub Menus? <a href="http://userguide.expand2web.com/introduction-to-the-wordpress-menu-builder/" target="_blank">Learn about the Menu Builder</a>.</p>
</div>
</div> <!--outerbox-->

<div class="outerbox" id="outerbox-background">    
<h6>Creating a Menu and Sub-menus, Re-order Pages, Omit Pages, </h6>
<div class="innerbox" >         
        
<p><strong>Use the "WordPress Menu Builder" for Sub-menu's and to Re-order your pages.</strong></p>
<p>1) Click on "Appearance" -> "Menus"</p>
<p>2) Create a new Menu.</p>
<p>3) Don't forget to set your main menu as "Primary Menu" - this gets often forgotton.</p>
<br />


<p><img src="http://members.expand2web.com/E2W-theme-images/menu-example.png" alt="WordPress Menu Builder" title="WordPress Menu Builder" style="border:solid 1px #ccc;" /></p>

<br />

     <p class="userguide"><strong>For more detailed WordPress Menu Builder instructions with video</strong> visit this <a href="http://userguide.expand2web.com/introduction-to-the-wordpress-menu-builder/" target="_blank">SmallBiz User Guide Post</a>.</p>

<br />


</div> 
</div> <!--outerbox-->


<div class="outerbox" id="outerbox-background">    
<h6>Page and Body Background Color/Image</h6>
<div class="innerbox" >         
        
<p><strong>1) Body Background Color </strong></p>
<p>To access the WordPress Background Options click <a href="<?php bloginfo('url')?>/wp-admin/themes.php?page=custom-background"> HERE</a>.</p>
<p>Or go to "Appearance -> Background" in the left Wordpress Dashboard Sidebar.</p>
<p>Options include a visual color picker and image uploader. You can choose to tile (repeat) and align your image as well.</p>
<br />

<p><strong>2) Page Content and Text Background Color</strong></p>
<p>Page Background Color <input class="color" type="text" style="width:100px" name="pages_color" value="<?php echo get_option('smallbiz_pages_color')?>"/></p>
<p>Ooops -Can't see your text anymore? ...make sure your text is not the same color as your background! <br /> Use the "Kitchensink" icon <img src="http://members.expand2web.com/E2W-theme-images/kitchensink.png"/> in the WordPress Pages and Post editor to change your text/font colors.</p>


<br />
        
<p><input type="submit" value="Save Changes" name="sales_update" /></p>

</div> 
</div> <!--outerbox-->

</div> <!--Layout options closing-->    


<div  class="dropdown"  id="dropdowns-layouts"><a href="javascript:;" onmousedown="e2w.admin.toggleDropdown('layoutsoptions')"><div class="groupbox1"> 						
<div style="float:left;"><img src="http://members.expand2web.com/E2W-theme-images/arrow.png" alt="Expand2Web Arrow" /></div><div style="float:left;padding-top:5px;">Homepage Layouts | Font Colors | Font Family | Link Colors</a></div>
</div>
</div> <!--groupbox1-->

<div id="layoutsoptions" style="display:none">    

<div class="outerbox" id="outerbox-layoutsoptions">     
<h6>Theme Layout Selector</h6>
<div class="innerbox" > 
<p><strong>Pick a layout for your homepage.</strong></p> <p>The expandable "Homepage Text & Image" sections below will change accordingly.</p>
  <p>Your text will not transfer from one layout to another do to size and alignment differences in the layouts. But your text will be saved if you want to come back to a layout.</p>
  <?php
    $current_layout = get_option('smallbiz_layout');
    if(!$current_layout){ $current_layout = "classic"; }
    $theme_layouts = array("classic", "blank", "blank-side", "classic-video", "rotator", "rotatorsidebar", "rotator-video", "cynthia", "allinone", "four", "slider", "fader", "five");
    foreach($theme_layouts as $theme_layout){
       ?>
       <div class="themeLayoutSelector_container">
           <div class="themeLayoutSelector_radio">
               <input type="radio" name="layout" id="layout_<?php 
                   echo $theme_layout;
                   echo '" value="'.$theme_layout.'"';
                   if($current_layout == $theme_layout){
                      echo ' checked="checked" ';
                   }
                   echo ' onchange="javascript:e2w.admin.change_active_selector(-1,\'layout\',\'themeLayout\',\'\');"';
               ?>" />
           </div>
           <div onclick="javascript:return e2w.admin.change_active_selector('<?php echo $theme_layout; ?>','layout','themeLayout','')" class="themeLayoutSelector_option
           <?php
              if($current_layout == $theme_layout){
                 echo ' themeLayoutSelector_optionSelected';
              }
              echo '" id="themeLayoutSelector_'.$theme_layout;
           ?>"><?php
              echo '<img src="'.get_bloginfo('template_url').'/layouts/'.$theme_layout.'-wire.jpg" ';
              echo '" alt="'.$display_title.'" title="'.$display_title.'" />';
              ?>
           </div>
       </div>
    <?php
    }
    ?>
    <div class="clear"></div>
    <!--<p id="themeLayoutSelector_message" style="display:none;">After making your selection, click "<a class="smallbizClickToSave" href="javascript:void(0);">Save Changes</a>" to apply.</p>-->
    
     <br />
     <p><input type="submit" value="Save Changes" name="sales_update" /></p>
</div>
<div id="protip">
<p>ProTip: Create Squeeze and Opt-in Pages witout a Menu <a href="http://userguide.expand2web.com/squeeze-page-or-sales-page-no-menu-with-the-smallbiz-theme/" target="_blank">Click here for more info!</a> .</p>
</div>

</div> <!--outerbox-->


<div class="outerbox" id="outerbox-customhome"> 
<h6>Custom Home Page</h6>
<div class="innerbox">
   <p>
              <p><strong>For Experienced WordPress Users:</strong></p>
             <p> If you don't want to use a pre-build layout above - you can create a custom page.</p>
             <p>Under no circumstances delete the original "Home" page!</p>
                  <p class="userguide">Please refer to this tutorial before proceeding: <a href="http://userguide.expand2web.com/setting-your-own-homepage/" target="_blank">Setting your own Homepage</a></p>
       
       <p>Front page: 
       <select name="smallbiz_page_on_front">
            <?php
            $pages = $wpdb->get_results('select * from '. $wpdb->prefix .'posts where post_type="page" and post_status="publish"');            
                $x = get_option('smallbiz_page_on_front');
                $smallbiz_homepage_id = get_option('smallbiz_homepage_id');
                echo '<option ';
                if($x ==$smallbiz_homepage_id){ echo "selected='selected' "; } 
                echo 'value="'.$smallbiz_homepage_id.'">Smallbiz '.get_option('layout_title').'</selected>';
                foreach($pages as $page)
                {
                  if($page->ID != $smallbiz_homepage_id){
                    if($x == $page->ID){
                        echo '<option selected="selected" value="'. $page->ID .'">'. $page->post_title .'</option>';
                    }else{
                        echo '<option value="'. $page->ID .'">'. $page->post_title .'</option>';
                    }
                  }
                }
            ?>
        </select>       
   </p>
 
   
   <br />
     <p><input type="submit" value="Save Changes" name="sales_update" /></p>
</div> <!--navigationMenuBox-->
            <div id="protip">
<p>ProTip: Use the "Menu Builder" (Dashboard -> Appearance -> Menus) if you want to omit pages or if you need even more control over your Navigation Menu.</p>
</div>
</div> <!--outerbox-->

<div class="outerbox" id="outerbox-fontandtext">     
<h6>Font and Text Options</h6>
<div class="innerbox" > 

<p><strong>Choose your Font Family & Size</strong></p>
<p>Simply enter the font size in Pixel - you don't need to add "px" behind your digits.</p>
<p>You can overwrite the font size on a sentence to sentence bases in your Pages ond Posts using the "Kitchensink icon" and "Font Size" dropdown.</p>
<br />


<table width="810" border="0">
  <tr>
  
  <td width="165">
<label for="font_family_header" title="font-family_header">Header Business Name :</label>

  </td>
 <td width="290">
<select name="font_family_header" class="smallbiz_font_selector" id="font_family_header" title="font-family_header" tabindex="1">
<?php echo_fonts_options(get_option('smallbiz_font_family_header')); ?>
</select>
</td>

  </tr><tr>
  
<td width="165">Header Business Name Font Size:</td>
<td width="290"><input style="width:25px" type="text" name="font_size_head" value="<?php echo get_option("smallbiz_font_size_head")?>" /> Suggestion: 34</td>

</tr>
</table>


<br />
<table width="810" border="0">
  <tr>
  <td width="165">
<label for="font_family_headeraddress" title="font-family_headeraddress">Header Tagline & Adress:</label>

  </td>
 <td width="290">
<select name="font_family_headeraddress"  class="smallbiz_font_selector"  id="font_family_headeraddress" title="font-family_headeraddress" tabindex="1">
<?php echo_fonts_options(get_option('smallbiz_font_family_headeraddress')); ?>
</select>
</td></tr>

<tr>
  
<td width="165">Header Tagline Font Size:</td>
<td width="290"><input style="width:25px" type="text" name="font_size_headeraddress" value="<?php echo get_option("smallbiz_font_size_headeraddress")?>" /> Suggestion: 18</td>

</tr>

</table>

<br />
<table width="810" border="0">
  <tr>
  <td width="165">
<label for="font_family_menu" title="font-family_menu">Menu and Sub-menu:</label>

  </td>
 <td width="290">
<select name="font_family_menu"  class="smallbiz_font_selector"  id="font_family_menu" title="font-family_menu" tabindex="1">
<?php echo_fonts_options(get_option('smallbiz_font_family_menu')); ?>
</select>
</td></tr>

<tr>
  
<td width="165">Menu Font Size:</td>
<td width="290"><input style="width:25px" type="text" name="font_size_menu" value="<?php echo get_option("smallbiz_font_size_menu")?>" /> Suggestion: 15</td>

</tr>

</table>

<br />

<table width="810" border="0">
  <tr>
  <td width="165">
<label for="font_family_pagetitle" title="font-family_pagetitle">Blog Post Title:</label>

  </td>
 <td width="290">
<select name="font_family_pagetitle"  class="smallbiz_font_selector"  id="font_family_pagetitle" title="font-family_pagetitle" tabindex="1">
<?php echo_fonts_options(get_option('smallbiz_font_family_pagetitle')); ?>
</select>
</td></tr>

<tr>
  
<td width="165">Title Font Size:</td>
<td width="290"><input style="width:25px" type="text" name="font_size_pagetitle" value="<?php echo get_option("smallbiz_font_size_pagetitle")?>" /> Suggestion: 22</td>

</tr>

</table>

<br />


<table width="810" border="0">
  <tr>
  <td width="165">
<label for="font_family_pagetitle" title="font-family_sideh3">Sidebar Title </label>

  </td>
 <td width="290">
<select name="font_family_sideh3"  class="smallbiz_font_selector"  id="font_family_sideh3" title="font-family_sideh3" tabindex="1">
<?php echo_fonts_options(get_option('smallbiz_font_family_sideh3')); ?>
</select>
</td></tr>

<tr>
<td width="165">Sidebar Title Size:</td>
<td width="290"><input style="width:25px" type="text" name="font_size_sideh3" value="<?php echo get_option("smallbiz_font_size_sideh3")?>" /> Suggestion: 14</td>
</tr>

</table>

<br />

<table width="810" border="0">
  <tr>
  <td width="165">
<label for="font_family_side" title="font-family_side">Sidebar Text and Paragraph</label>

  </td>
 <td width="290">
<select name="font_family_side"  class="smallbiz_font_selector"  id="font_family_side" title="font-family_side" tabindex="1">
<?php echo_fonts_options(get_option('smallbiz_font_family_side')); ?>
</select>
</td></tr>

<tr>
<td width="165">Sidebar Text Font Size:</td>
<td width="290"><input style="width:25px" type="text" name="font_size_side" value="<?php echo get_option("smallbiz_font_size_side")?>" /> Suggestion: 16</td>

</tr>
</table>

<br />


<table width="810" border="0">
  <tr>
  <td width="165">
<label for="font_family_main" title="font-family_main">Text and Paragraph Pages & Posts</label>

  </td>
 <td width="290">
<select name="font_family_main"  class="smallbiz_font_selector"  id="font_family_main" title="font-family_main" tabindex="1">
<?php echo_fonts_options(get_option('smallbiz_font_family_main')); ?>
</select>
</td></tr>

<tr>
<td width="165">Text Font Size:</td>
<td width="290"><input style="width:25px" type="text" name="font_size_main" value="<?php echo get_option("smallbiz_font_size_main")?>" /> Suggestion: 16</td>

</tr>
</table>

<br />

<table width="810" border="0">
  <tr>
  <td width="165">
<label for="font_family_feature" title="font-family_feature">Footer 3 Feature Boxes</label>

  </td>
 <td width="290">
<select name="font_family_feature" class="smallbiz_font_selector"  id="font_family_feature" title="font-family_feature" tabindex="1">
<?php echo_fonts_options(get_option('smallbiz_font_family_feature')); ?>
</select>
</td></tr>

<tr>
<td width="165">Feature Font Size:</td>
<td width="290"><input style="width:25px" type="text" name="font_size_feature" value="<?php echo get_option("smallbiz_font_size_feature")?>" /> Suggestion: 15</td>

</tr>
</table>

<br />

<table width="810" border="0">
  <tr>
  <td width="165">
<label for="font_family_footer" title="font-family_footer">Footer Credits and Cities Served</label>
    </td>
 <td width="290">
<select name="font_family_footer"  class="smallbiz_font_selector" id="font_family_footer" title="font-family_footer" tabindex="1">
<?php echo_fonts_options(get_option('smallbiz_font_family_footer')); ?>
</select>
</td></tr>

<tr>
<td width="165">Footer Font Size:</td>
<td width="290"><input style="width:25px" type="text" name="font_size_footer" value="<?php echo get_option("smallbiz_font_size_footer")?>" /> Suggestion: 10</td>

</tr>
</table>

<br />

<table width="810" border="0">
<tr>
<td width="165">H1 Tag Size:</td>
<td width="290"><input style="width:25px" type="text" name="font_size_h1" value="<?php echo get_option("smallbiz_font_size_h1")?>" /> Suggestion: 28</td>
</tr>
<tr>
<td width="165">H2 Tag Size:</td>
<td width="290"><input style="width:25px" type="text" name="font_size_h2" value="<?php echo get_option("smallbiz_font_size_h2")?>" /> Suggestion: 22</td>
</tr>
<tr>
<td width="165">H3 Tag Size:</td>
<td width="290"><input style="width:25px" type="text" name="font_size_h3" value="<?php echo get_option("smallbiz_font_size_h3")?>" /> Suggestion: 20</td>
</tr>

</table>
<br />

     <p class="userguide"><strong>Keep in mind that differnet Font Families have different line-heights.<br />If your text sits "on top of each other" or needs more space top and bottom look in the <a href="http://userguide.expand2web.com/how-to-change-font-size-and-font-type/" target="_blank">SmallBiz User Guide</a>.</strong></p>

<p>...are your font size changes not showing? Only enter font size digits - without the "px" for Pixel.</p>

<br />

<p><input type="submit" value="Save Changes" name="sales_update" /></p>

<br />
<p><strong>Choose your Font Colors</strong></p> 
<P>You can overwrite the colors of your text on a page to page basis by using the Color options and "Kitchensink" in the Wordpress Editor.</p><br />
        
<div style="width:835px; height:240px;">    
              
<div style="width:480px; height:240px; float:left">
<table width="410" border="0">
  <tr>
    <td width="290">1) Page Title ( 'h2' tag ) Color</td>
    <td width="110"><input class="color" type="text" style="width:100px" name="headertag_color" value="<?php echo get_option('smallbiz_headertag_color')?>"/></td>
  </tr>
  
</table>
                
<br />
        
<table width="410" border="0">
  <tr>
    <td width="290">2) Main text ( 'p' paragraph ) Color</td>
    <td width="110"><input class="color" type="text" style="width:100px" name="p_color" value="<?php echo get_option('smallbiz_p_color')?>"/></td>
  </tr>
  
  </table>
  
  <br />
  
<table width="410" border="0">
  <tr>
    <td width="290">3) Hyperlink ( 'a' link ) Color</td>
    <td width="110"><input class="color" type="text" style="width:100px" name="hyper_color" value="<?php echo get_option('smallbiz_hyper_color')?>"/></td>
  </tr>
  
  </table>
  
    <br />
    
    <table width="410" border="0">
  <tr>
    <td width="290">4) Mouse-over Hyperlink ( 'a:hover' link ) Color</td>
    <td width="110"><input class="color" type="text" style="width:100px" name="hyperhover_color" value="<?php echo get_option('smallbiz_hyperhover_color')?>"/></td>
  </tr>
  
  </table>
  
    <br />
  
  <table width="410" border="0">
  <tr>
    <td width="290">5) Footer Credits Color</td>
    <td width="110"><input class="color" type="text" style="width:100px" name="creds_color" value="<?php echo get_option('smallbiz_creds_color')?>"/></td>
  </tr>
  
  </table>
  
  <br />
  
    <table width="410" border="0">
  <tr>
    <td width="290">6) Sidebar Title Font Color</td>
    <td width="110"><input class="color" type="text" style="width:100px" name="sideh3_color" value="<?php echo get_option('smallbiz_sideh3_color')?>"/></td>
  </tr>
  
  </table>
  

    
  
  <br />
  

</div>
<div style="width:355px; float:left;margin-top:-11px;">
<p><em>Click image for Font Color Legend:</em><a href="http://members.expand2web.com/E2W-theme-images/legendfont.png" alt="Smallbiz Mobile Layout" target="_blank"><img src="http://members.expand2web.com/E2W-theme-images/legendfont-prev.png" /></a></p>
</div>
</div>

<br />
<br />
<p><input type="submit" value="Save Changes" name="sales_update" /></p>
</div> 

<div id="protip">
<p>ProTip: Not all Font Families use the same height - Arial 15px is a different height compared to Courier 15px for example.</a></p>
</div>

</div> <!--outerbox-->

</div> <!--Layout options closing-->    


<div  class="dropdown"  id="dropdowns-business"><a href="javascript:;" onmousedown="if(document.getElementById('businessoptions').style.display == 'none'){ document.getElementById('businessoptions').style.display = 'block'; }else{ document.getElementById('businessoptions').style.display = 'none'; }"><div class="groupbox1">             
<div style="float:left;"><img src="http://members.expand2web.com/E2W-theme-images/arrow.png" alt="Expand2Web Arrow" /></div><div style="float:left;padding-top:5px;">Homepage Text & Images | Business Hours | Driving Directions | Feature Boxes | Footer Credits</a></div>
</div>
</div> <!--groupbox1-->

<div id="businessoptions" style="display:none">    

<?php 
if(function_exists(smallbiz_theme_option_page_layout_options)){
  smallbiz_theme_option_page_layout_options();
} 
?>
                
<div class="outerbox"  id="outerbox-localinfo"> 
<h6>Local Info - Good for your Customer, Good for SEO</h6>
<div class="innerbox" > 

<p><strong>The following four text and code boxes are html enabled by default for faster editing.</strong></p>
<br />

        <p>List all cities and areas you serve. This information will be displayed in the footer. Leave blank if not desired or applicable :<br />

        <textarea name="cities" cols="70" rows="3"><?php echo get_option('smallbiz_cities')?></textarea> </p>
        
        <p>Footer Credits :<br />
        
        <textarea name="credit" cols="70" rows="2"><?php echo get_option('smallbiz_credit')?></textarea> </p>
            
            <br />
                
         <p>Local Directions (displayed under Google Maps image on Find Us page) :<br />

        <textarea name="directions" cols="70" rows="3"><?php echo get_option('smallbiz_directions')?></textarea> </p>

             

         <p>Visit "Google Maps" to get your embed code for your address. 
         <br />Use a custom 300x250px Map. For a <a href="http://userguide.expand2web.com/google-maps-elements-alternative/" target="_blank">complete Tutorial click here</a>.
         <br />

        <textarea name="map_link" cols="70" rows="10"><?php echo get_option('smallbiz_map_link')?></textarea> </p>
        
        <br />
<p><input type="submit" value="Save Changes" name="sales_update" /></p>

</div> <!--local-->
<div id="protip">
<p>ProTip: You can use <a href="http://userguide.expand2web.com/linking-cities-served-to-pages/" target=_blank">HTML to link the cities you serve</a> to a special landing page that could be optimized with keywords for that particular city.<br /><em>Note: Incorrect or Incomplete HTML can break your website. Use code with caution!</em></p>
</div>
</div> <!--outerbox-->
            
<div class="outerbox" id="outerbox-businesshours">                    
<h6>Business Hours</h6>
<div class="innerbox" >

<p><strong> Use only digits. Avoid text. If your Businsess hours box becomes too wide due to text etc it will shift below the Map on your Find Us Page.</strong></p>
<br />

<table>

<?php

$b_hours = explode(',', get_option('smallbiz_business_hours'));

?>

                <tr><td></td><td>Morning</td><td>Afternoon</td></tr>

                <tr><td>Mon</td><td><input type="text" name="b_hours_1_s" value="<?php echo $b_hours[0]?>"/></td><td><input type="text" name="b_hours_1_e" value="<?php echo $b_hours[1]?>" /></td></tr>

                <tr><td>Tues</td><td><input type="text" name="b_hours_2_s" value="<?php echo $b_hours[2]?>" /></td><td><input type="text" name="b_hours_2_e" value="<?php echo $b_hours[3]?>" /></td></tr>

                <tr><td>Wed</td><td><input type="text" name="b_hours_3_s" value="<?php echo $b_hours[4]?>" /></td><td><input type="text" name="b_hours_3_e" value="<?php echo $b_hours[5]?>" /></td></tr>

                <tr><td>Thu</td><td><input type="text" name="b_hours_4_s" value="<?php echo $b_hours[6]?>" /></td><td><input type="text" name="b_hours_4_e" value="<?php echo $b_hours[7]?>" /></td></tr>

                <tr><td>Fri</td><td><input type="text" name="b_hours_5_s" value="<?php echo $b_hours[8]?>" /></td><td><input type="text" name="b_hours_5_e" value="<?php echo $b_hours[9]?>" /></td></tr>

                <tr><td>Sat</td><td><input type="text" name="b_hours_6_s" value="<?php echo $b_hours[10]?>" /></td><td><input type="text" name="b_hours_6_e" value="<?php echo $b_hours[11]?>" /></td></tr>

                <tr><td>Sun</td><td><input type="text" name="b_hours_7_s" value="<?php echo $b_hours[12]?>" /></td><td><input type="text" name="b_hours_7_e" value="<?php echo $b_hours[13]?>" /></td></tr>

            </table>
            
            <br />
     <p><input type="submit" value="Save Changes" name="sales_update" /></p>

</div>
</div> <!--outerbox-->

<div class="outerbox" id="outerbox-featuredfooterleft"> 
<h6>Featured Pages Box</h6>
<div class="innerbox" > 

<p>1) Featured Page Box Footer Left<br/>Select the page to feature and provide a brief summary : <select name="feature_page_1">

                <?php

                $x = get_option('smallbiz_feature_page_1');

                foreach($pages as $page)

                {

                    if($x == $page->ID){

                        echo '<option selected value="'. $page->ID .'">'. $page->post_title .'</option>';

                    }else{

                        echo '<option value="'. $page->ID .'">'. $page->post_title .'</option>';

                    }

                }

                ?>

            </select> Font Color<input class="color" type="text" style="width:100px" name="page_summary_1_color" value="<?php echo get_option('smallbiz_page_summary_1_color')?>"/>
            

            <br/><br/>

            Summary Text 1<br/><textarea name="feature_page_summary_1" cols="40" rows="2"><?php echo get_option('smallbiz_feature_page_summary_1')?></textarea>

            
</p>


<p>2) Featured Page Box Footer Middle<br/>Select the page to feature and provide a brief summary : <select name="feature_page_2">

                <?php

                $x = get_option('smallbiz_feature_page_2');

                foreach($pages as $page)

                {

                    if($x == $page->ID){

                        echo '<option selected value="'. $page->ID .'">'. $page->post_title .'</option>';

                    }else{

                        echo '<option value="'. $page->ID .'">'. $page->post_title .'</option>';

                    }

                }

                ?>

            </select> Font Color<input class="color" type="text" style="width:100px" name="page_summary_2_color" value="<?php echo get_option('smallbiz_page_summary_2_color')?>"/>

            <br/><br/>

            Summary Text 2<br/><textarea name="feature_page_summary_2" cols="40" rows="2"><?php echo get_option('smallbiz_feature_page_summary_2')?></textarea>

</p>

        

<p>3) Featured Page Box Footer Right<br/>Select the page to feature and provide a brief summary : <select name="feature_page_3">

                <?php

                $x = get_option('smallbiz_feature_page_3');

                foreach($pages as $page)

                {

                    if($x == $page->ID){

                        echo '<option selected value="'. $page->ID .'">'. $page->post_title .'</option>';

                    }else{

                        echo '<option value="'. $page->ID .'">'. $page->post_title .'</option>';

                    }

                }

                ?>

            </select> Font Color<input class="color" type="text" style="width:100px" name="page_summary_3_color" value="<?php echo get_option('smallbiz_page_summary_3_color')?>"/>

            <br/><br/>

            Summary Text 3<br/><textarea name="feature_page_summary_3" cols="40" rows="2"><?php echo get_option('smallbiz_feature_page_summary_3')?></textarea>

            
</p>
<br />

<hr />

<p>Check to completely disable the Featured Pages Box from showing on your Website: <input type="checkbox" name="feature_box_disabled"
              <?php if(get_option('smallbiz_feature_box_disabled') != ""){?>
                  checked="checked"
              <?php } ?> ></input>        
</p>

<br />
     <p><input type="submit" value="Save Changes" name="sales_update" /></p>
            
</div> 

<div id="protip">
<p>ProTip: You can also use Widgets in your Feature Boxes. Visit <a href="http://userguide.expand2web.com/the-feature-boxes/" target="_blank">this User Guide Post for more</a>.</p>
</div>        
</div> <!--outerbox-->        
            
</div> <!--Business options closing-->            
    
    
<div  class="dropdown"  id="dropdowns-seo"><a href="javascript:;" onmousedown="e2w.admin.toggleDropdown('seooptions');"><div id="groupbox"> 	

<div style="float:left;"><img src="http://members.expand2web.com/E2W-theme-images/arrow.png" alt="Expand2Web Arrow" /></div><div style="float:left;padding-top:5px;">SEO (Search Engine Optimization) | Google Analytics | Google Webmaster Tools</a></div>
</div>
</div> <!--groupbox-->

<div id="seooptions" style="display:none">            
<div id="seobox">

<p><strong>You do <u>not</u> need a Third-Party SEO Plugin with the SmallBiz Theme.</strong></p>

<p>The Smallbiz Theme has Custom Meta Options for all Pages and Posts. If you decide to use a third-party plugin make sure you<br /> are not submitting duplicate meta data. You can disable the SmallBiz SEO Options below.</p>

     <p class="userguide">New to SEO? Visit our <a href="http://www.expand2web.com/blog/wordpress-seo-beginners/" target="_blank">SEO Beginners Guide</a>.</p>
<br />

<script type="text/javascript">
<!--
maxKeys = 70;
var IE = (document.all) ? 1 : 0;
var DOM = 0;
if (parseInt(navigator.appVersion) >=5) {
DOM=1
};
function txtshow(txt2show) {
if (DOM) {
var viewer = document.getElementById("txtmsg");
viewer.innerHTML=txt2show;
}
else if(IE) {
document.all["txtmsg"].innerHTML=txt2show;
}
}
function keyup(what) {
var str = new String(what.value);
var len = str.length;
var showstr = len + " characters of " + maxKeys + " recommended";
if (len > maxKeys) showstr += '<br><strong>You are using more than 70 Characters. Characters above the suggested count will be capped by Google, we suggest to revise your entry. </strong>';
txtshow(showstr);
}
//-->
</script>


<p>1) Homepage Title</p>
<p>For SEO purposes: Describe your home page title. Anything over 69 characters will be truncated in Google search results.<br/>

            <input type="text" style="width:400px" name="title" onkeyup="keyup(this)" value="<?php echo get_option('smallbiz_title')?>"/>

    </p>
    
<div id="txtmsg">You are using 0 characters of 70 recommended by Google for your Title Tag</div>
    
    <br />
    
    <script type="text/javascript">
<!--
maxKeys1 = 150;
var IE = (document.all) ? 1 : 0;
var DOM = 0;
if (parseInt(navigator.appVersion) >=5) {
DOM=1
};
function txtshow1(txt2show1) {
if (DOM) {
var viewer = document.getElementById("txtmsg1");
viewer.innerHTML=txt2show1;
}
else if(IE) {
document.all["txtmsg1"].innerHTML=txt2show1;
}
}
function keyupdesc(what) {
var str = new String(what.value);
var len = str.length;
var showstr = len + " characters of " + maxKeys1 + " recommended";
if (len > maxKeys1) showstr += '<br><strong>You are using more than 150 Characters. Characters above the suggested count will be capped by Google, we suggest to revise your entry. </strong>';
txtshow1(showstr);
}
//-->
</script>
        
<p>2) Homepage Description</p>
<p>For SEO purposes: Describe your home page in more detail. Anything over 150 characters will be truncated in Google search results.<br/>

            <input type="text" style="width:400px" onkeyup="keyupdesc(this)" name="description" value="<?php echo get_option('smallbiz_description')?>"/>
    </p>

<div id="txtmsg1">You are using 0 characters of 150 recommended by Google for your Meta Description</div>

<br/>

    

<p>3) Keywords</p>
<p>For SEO purposes, use a comma to split each keyword.<br/>

            <input type="text" style="width:400px" name="keywords" value="<?php echo get_option('smallbiz_keywords')?>"/>

        </p>
        <div id="txtmsg2">There is no max character recommendedation by Google for your Meta Keywords</div>
        
        
        <br />

<hr />

    
<p>Check to completely disable the Smallbiz SEO Features - Use this when you want to use a Third-Party SEO Plugin: <input type="checkbox" name="seo_disabled"
<?php if(get_option('smallbiz_seo_disabled') != ""){?>
checked="checked"<?php } ?> ></input>
        
</p>
        
        <br />
<p><input type="submit" value="Save Changes" name="sales_update" /></p>
        
<div style="background:#f2f2f4;margin-left:-10px;">
<p style="padding-left:10px;">ProTip: Look for the SEO option fields when creating a new Post or Page.</p>
</div>    
        
</div> <!--seobox-->

<div class="outerbox" id="outerbox-analytics">                 
<h6>Optional yet essential tools to keep track of your Websites performance!</h6>
<div class="innerbox" >

     <p class="userguide"><strong>Not sure what Analytics and Webmasters is? </strong><br /> Visit our User Guide <a href="http://userguide.expand2web.com/google-analytics-and-google-webmaster-tools/" target="_blank">post to learn more</a>.</p>
<br />

<p>Google Analytics</p>
<p>(Sign up: <a href="http://www.google.com/analytics/" target="_blank">HERE</a> & paste the entire Google generated code into the field below. <br />

            <textarea name="analytics" cols="40" rows="10"><?php echo get_option('smallbiz_analytics')?></textarea>
        </p>
        <br />
        
<p>Google Webmaster Tools</p>
<p>(Sign up: <a href="http://www.google.com/webmaster/" target="_blank">HERE</a> & paste the entire "Verifying with Meta Tag" code snippet into the field below. <br />

            <textarea name="webmaster" cols="40" rows="2"><?php echo get_option('smallbiz_webmaster')?></textarea>
            <br />
            <em>(Note: Make sure WordPress "Privacy Settings" are off - otherwise your site will not vaildate)</em>
            
        </p>
        
            <br />
     <p><input type="submit" value="Save Changes" name="sales_update" /></p>
        
</div> <!--webtools-->
<div style="background:#f2f2f4;">
<p style="padding-left:10px;">ProTip: The SmallBiz Theme will automatically insert your Google Analytics code into every existing and future page and post.</p>
</div>

</div> <!--seo options closing-->
</div>


<div  class="dropdown"  id="dropdowns-mobile"><a href="javascript:;" onmousedown="e2w.admin.toggleDropdown('mobileoptions')"><div id="groupbox"> 			
<div style="float:left;"><img src="http://members.expand2web.com/E2W-theme-images/arrow.png" alt="Expand2Web Arrow" /></div><div style="float:left;padding-top:5px;">Mobile Device Page Settings</a></div>
</div>
</div> <!--groupbox-->

<div id="mobileoptions" style="display:none">            
            
<div id="mobilebox">
<div style="height:180px;">
<div style="float:left; width:215px; padding-left:20px">
<a href="http://members.expand2web.com/E2W-theme-images/mobile-layout-384.png" alt="Smallbiz Mobile Layout" target="_blank"><img src="http://members.expand2web.com/E2W-theme-images/mobilepagescreen-384.png" alt="Smallbiz Mobile Layout Small"></a>
</div>
<br />
<p>The Expand2Web Smallbiz Wordpress Theme is designed to automatically detect Mobile and Handheld Devices such as the iPhone, Android, Blackberry etc. </p> <p>The "Call" Button in the mobile layout will only work correctly if you have your phone country code prefix specified in the "Business Information" section above.</p><p>All of our stock layouts are optimized to be viewed on mobile devices, however you can serve a special touch enabled layout by checking the box below.
</p>

 <p class="userguide"><a href="http://members.expand2web.com/E2W-theme-images/mobile-layout-384.png" alt="Smallbiz Mobile Layout" target="_blank"># Click for a Screenshot of the Touch Enabled Layout activated.</a>
</p>
</div>
<br />
<hr />
<p><strong>Activate Mobile Touch-Interface Enabled Layout</strong></p>
<p>Check box to display the mobile layout when a mobile device is detected.
        
<input type="checkbox" name="mobile-layout-enabled"
        <?php if(get_option('smallbiz_mobile-layout-enabled')){?>
                  checked="checked"
              <?php } ?>
              ></input></p>
              
<p><input type="submit" value="Save Changes" name="sales_update" /></p>
              
<p>All of the stock layouts are optimized to be viewed on mobile devices, however you can serve a special touch enabled layout by checking the box above.</p>
<br />
</div>    

<div class="outerbox" id="outerbox-analytics">                 
<h6>Mobile Title, Tagline and Header Graphic</h6>
<div class="innerbox" >

<p><img src="http://members.expand2web.com/E2W-theme-images/mobile-header-384-small.png" alt="Smallbiz Mobile Header"></p>

<p><strong>Optional Upload a Mobile Header Graphic or Logo </strong></p>
<p>Use a high quality image without intricate artwork. Make sure you don't have spaces in your file name. </p>

<p>Upload: <input type="file" class="fileinput" name="mobile-bannerhome-image"/></p>
<br /> 

<p><strong>Optional Mobile Title or Business Name:</strong><br /> <input style="width:400px" type="text" name="mobiname" value="<?php echo get_option("smallbiz_mobiname")?>" />
            
Font Color<input class="color" type="text" style="width:100px" name="mobiname_color" value="<?php echo get_option('smallbiz_mobiname_color')?>"/>  
</p>

<p>Mobile Title Font Size: <input style="width:25px" type="text" name="font_size_mobiname" value="<?php echo get_option("smallbiz_font_size_mobiname")?>" /> Suggestion: 35</p>

<br />


<p><strong>Optional Mobile Tag Line:</strong><br /> <input style="width:400px" type="text" name="mobisub_header" value="<?php echo get_option("smallbiz_mobisub_header")?>" />

Font Color<input class="color" type="text" style="width:100px" name="mobisub_header_color" value="<?php echo get_option('smallbiz_mobisub_header_color')?>"/> </p>

<p>Mobile Tag Line Font Size: <input style="width:25px" type="text" name="font_size_mobisubheader" value="<?php echo get_option("smallbiz_font_size_mobisubheader")?>" /> Suggestion: 25</p>

<br />

   
<p><input type="submit" value="Save Changes" name="sales_update" /></p>

<br />
</div>
</div>  

<div class="outerbox" id="outerbox-analytics">                 
<h6>Mobile Colors</h6>
<div class="innerbox" >

<p><img src="http://members.expand2web.com/E2W-theme-images/mobileoptions-color384-small.png" alt="Smallbiz Mobile Colors"></p>

<p><strong>Mobile Page and Touch Enabled Button(s) Color Picker</strong></p>  

<p>Mobile Page Background Color <input class="color" type="text" style="width:100px" name="mobile-body-color" value="<?php echo get_option('smallbiz_mobile-body-color')?>"/></p>

<p>Button and Menu Background Color <input class="color" type="text" style="width:100px" name="mobile-button-color" value="<?php echo get_option('smallbiz_mobile-button-color')?>"/></p>
          
<p>Button and Menu Font Color <input class="color" type="text" style="width:100px" name="mobile-text-button-color" value="<?php echo get_option('smallbiz_mobile-text-button-color')?>"/></p>
       
<br />
         
<p><input type="submit" value="Save Changes" name="sales_update" /></p>

<br />
</div>
</div>        


<div class="outerbox" id="outerbox-analytics">                 
<h6>Mobile Homepage Text and Image</h6>
<div class="innerbox" >

<p><strong>Homepage Text for Mobile Layout</strong></p>
<p>Keep it short and to the point with a clear call to action. Keep in mind the small size of mobile screens. </p>
   <p class="userguide">You can also add an image into your text. <a href="http://userguide.expand2web.com/adding-images-to-the-4-bottom-boxes/">Learn how to here.</a></p>

<?php echo tinyMCE_HTMLarea('mobile-home-text',get_option("smallbiz_mobile-home-text")); ?>

<br />
<p><input type="submit" value="Save Changes" name="sales_update" /></p>
<br />
</div>
</div>

  <div class="outerbox" id="outerbox-analytics">                 
<h6>Mobile Map and GPS Driving Directions</h6>
<div class="innerbox" >
<p><img src="http://members.expand2web.com/E2W-theme-images/mobile-maps.png" alt="Smallbiz Mobile Maps"></p>
<p><strong>Google Maps Link for Directions Button</strong></p>
<p>Go to <a href="http://maps.google.com/" target="_blank">Google Maps</a>, enter your address and click the "Link" icon in the upper right to obtain an "http" link. Do not paste an iframe in here.<br/> The button will trigger the mobile devices native maps app.</p>
<p>
<input type="text" style="width:400px" name="mobile-map" value="<?php echo get_option('smallbiz_mobile-map')?>"/>
</p>
<br />

<p><strong>I do not have a physical location:</strong></p>
 <p>Check box to hide and disable the Directions icon/graphic <input type="checkbox" name="mobidirections_disabled"
        <?php if(get_option('smallbiz_mobidirections_disabled') != ""){?>
                  checked="checked"
              <?php } ?>
              ></input></p>
         <br />
<p><input type="submit" value="Save Changes" name="sales_update" /></p>

<br />
</div>
</div>  

<div class="outerbox" id="outerbox-analytics">                 
<h6>Additional Mobile Pages and Mobile Menu</h6>
<div class="innerbox" >

<p><img src="http://members.expand2web.com/E2W-theme-images/mobile-menumore-small.png" alt="Smallbiz Mobile More Pages"></p>

<p class="userguide"><strong>Additional Mobile Pages and Mobile Menu</strong></p>
             <p class="userguide">Learn how to <a href="http://userguide.expand2web.com/creating-mobile-pages-and-a-mobile-menu/" target="_blank">add additional Mobile Pages and a Mobile Menu</a> if desired.</p>
<br />
</div>
</div>
      

                    
</div> <!--mobileoptions closing-->            

<div  class="dropdown"  id="dropdowns-facebook"><a href="javascript:;" onmousedown="e2w.admin.toggleDropdown('fboptions')"><div id="groupbox"> 			
<div style="float:left;"><img src="http://members.expand2web.com/E2W-theme-images/arrow.png" alt="Expand2Web Arrow" /></div><div style="float:left;padding-top:5px;">Facebook Page Setup</a></div>
</div>
</div> <!--groupbox-->
			
<div id="fboptions" style="display:none">        
<div id="facebookbox">

<div style="float:left; width:180px;padding-left:10px">
<img src="http://members.expand2web.com/E2W-theme-images/fbpagescreen.png" alt="Smallbiz Facebook App" style="border:solid 1px #ccc solid;"></a>
</div>

<p><strong>The SmallBiz Theme lets you create and manage content on your Facebook Page Tabs.</strong></p> 
     <p class="userguide"><strong>1)</strong> Create a Facebook Page. New to Facebook? - <a href="http://www.facebook.com/help/?page=904" target="_blank">Here is How To</a></p>
     <p class="userguide"><strong>2)</strong> Create Page(s) using the SmallBiz Facebook Page Templates.  - <a href="http://userguide.expand2web.com/the-smallbiz-facebook-pages-and-app/" target="_blank">Here is How To</a></p>
<p><strong>3)</strong> If you are done with Step 1 and 2 - Use the SmallBiz Facebook App to link it all together. <a href="http://apps.facebook.com/smallbiz-wp-tabs" target="_blank">Click here to launch the App</a></p>

<p>Facebook mandated Security Requirements: <br /><em>The content you serve using <u>any</u> app must be delivered from a secure server. <br />This means you need to get an SSL certificate on your hosting server where your content is being delivered from.</em></p>
<br />
</div> <!--fbbox-->
</div> <!--fboptions closing-->   

<div  class="dropdown"  id="dropdowns-commerce"><a href="javascript:;" onmousedown="if(document.getElementById('commerceoptions').style.display == 'none'){ document.getElementById('commerceoptions').style.display = 'block'; }else{ document.getElementById('commerceoptions').style.display = 'none'; }"><div id="groupbox">             
<div style="float:left;"><img src="http://members.expand2web.com/E2W-theme-images/arrow.png" alt="Expand2Web Arrow" /></div><div style="float:left;padding-top:5px;">Ecommerce</a></div>
</div>
</div> <!--groupbox-->
            
<div id="commerceoptions" style="display:none">        
<div id="commercebox">

<p><a href="http://members.expand2web.com/downloads/cashie-commerce-expand2web.zip"><img src="http://cdn4.expand2web.com/cashie-smallbiz.png" alt="Ecommerce Plugin Download" style="padding:7px; margin-top: -12px;" /></a></p>

<div style="padding-left:20px;">
<p><strong><a href="http://members.expand2web.com/downloads/cashie-commerce-expand2web.zip">Download the Free Plugin</a></strong></p>
<p>Once you're ready to start processing real transactions, upgrade to a production account and link Cashie Commerce to your PayPal account. <br/>It's totally risk free since you don't pay anything until you start making money.</p>

<p>Step 1) <a href="http://members.expand2web.com/downloads/cashie-commerce-expand2web.zip">Download the plugin here.</a></p>
<p>Step 2) In WordPress go to Plugins -> Add New -> Choose "Upload"</p>
<p>Step 3) Activate the plugin and Build Your Store.</p>


</div>
<br />
</div> <!--fbbox-->
</div> <!--fboptions closing--> 

</form>

<div id="pillar-box">
<div id="outerbox"> 
<div id="pillar-box-inner">
<h2>Why Most Small Business Websites Don't Work... And What To Do About It!</h2>
<br/>

<div style="float:left;padding-right:22px;">
<img style="border:1px solid #ccc;" src="http://members.expand2web.com/E2W-theme-images/block-pillar-vidtest.png" />
</div>

<p><strong>Learn how to build a complete Web presence that brings you tons of traffic</strong> using the <br />
"Circle of Trust" method. Enter your email below and we'll send you this video series for FREE!</p>
<br />

<div style="float:left;margin-right:20px;">
<!-- AWeber Web Form Generator 3.0 -->
<style type="text/css">
#af-form-1865653734 .af-body .af-textWrap{width:98%;display:block;float:none;}
#af-form-1865653734 .af-body .privacyPolicy{color:#000000;font-size:11px;font-family:Verdana, sans-serif;}
#af-form-1865653734 .af-body a{color:#094C80;text-decoration:underline;font-style:normal;font-weight:normal;font-size:10px;}
#af-form-1865653734 .af-body input.text, #af-form-1865653734 .af-body textarea{background-color:#FFFFFF;border-color:#919191;border-width:1px;border-style:solid;color:#000000;text-decoration:none;font-style:normal;font-weight:normal;font-size:12px;font-family:Verdana, sans-serif;}
#af-form-1865653734 .af-body input.text:focus, #af-form-1865653734 .af-body textarea:focus{background-color:#FFFAD6;border-color:#030303;border-width:1px;border-style:solid;}
#af-form-1865653734 .af-body label.previewLabel{display:block;float:none;text-align:left;width:auto;color:#000000;text-decoration:none;font-style:normal;font-weight:normal;font-size:12px;font-family:Verdana, sans-serif;}
#af-form-1865653734 .af-body{padding-bottom:15px;background-repeat:no-repeat;background-position:inherit;background-image:none;color:#000000;font-size:11px;font-family:Verdana, sans-serif;}
#af-form-1865653734 .af-header{padding-bottom:9px;padding-top:9px;padding-right:10px;padding-left:10px;background-image:url('http://forms.aweber.com/images/auto/body/ff5/555/993/333');background-position:top left;background-repeat:repeat-x;background-color:#993333;border-width:1px;border-bottom-style:none;border-left-style:none;border-right-style:none;border-top-style:none;color:#FFFFFF;font-size:16px;font-family:Verdana, sans-serif;}
#af-form-1865653734 .af-quirksMode .bodyText{padding-top:2px;padding-bottom:2px;}
#af-form-1865653734 .af-quirksMode{padding-right:15px;padding-left:15px;}
#af-form-1865653734 .af-standards .af-element{padding-right:15px;padding-left:15px;}
#af-form-1865653734 .bodyText p{margin:1em 0;}
#af-form-1865653734 .buttonContainer input.submit{background-image:url("http://forms.aweber.com/images/auto/gradient/button/c44.png");background-position:top left;background-repeat:repeat-x;background-color:#ac2424;border:1px solid #ac2424;color:#FFFFFF;text-decoration:none;font-style:normal;font-weight:normal;font-size:14px;font-family:Verdana, sans-serif;}
#af-form-1865653734 .buttonContainer input.submit{width:auto;}
#af-form-1865653734 .buttonContainer{text-align:center;}
#af-form-1865653734 body,#af-form-1865653734 dl,#af-form-1865653734 dt,#af-form-1865653734 dd,#af-form-1865653734 h1,#af-form-1865653734 h2,#af-form-1865653734 h3,#af-form-1865653734 h4,#af-form-1865653734 h5,#af-form-1865653734 h6,#af-form-1865653734 pre,#af-form-1865653734 code,#af-form-1865653734 fieldset,#af-form-1865653734 legend,#af-form-1865653734 blockquote,#af-form-1865653734 th,#af-form-1865653734 td{float:none;color:inherit;position:static;margin:0;padding:0;}
#af-form-1865653734 button,#af-form-1865653734 input,#af-form-1865653734 submit,#af-form-1865653734 textarea,#af-form-1865653734 select,#af-form-1865653734 label,#af-form-1865653734 optgroup,#af-form-1865653734 option{float:none;position:static;margin:0;}
#af-form-1865653734 div{margin:0;}
#af-form-1865653734 fieldset{border:0;}
#af-form-1865653734 form,#af-form-1865653734 textarea,.af-form-wrapper,.af-form-close-button,#af-form-1865653734 img{float:none;color:inherit;position:static;background-color:none;border:none;margin:0;padding:0;}
#af-form-1865653734 input,#af-form-1865653734 button,#af-form-1865653734 textarea,#af-form-1865653734 select{font-size:100%;}
#af-form-1865653734 p{color:inherit;}
#af-form-1865653734 select,#af-form-1865653734 label,#af-form-1865653734 optgroup,#af-form-1865653734 option{padding:0;}
#af-form-1865653734 table{border-collapse:collapse;border-spacing:0;}
#af-form-1865653734 ul,#af-form-1865653734 ol{list-style-image:none;list-style-position:outside;list-style-type:disc;padding-left:40px;}
#af-form-1865653734,#af-form-1865653734 .quirksMode{width:225px;}
#af-form-1865653734.af-quirksMode{overflow-x:hidden;}
#af-form-1865653734{background-color:#F0F0F0;border-color:#CF4449;border-width:3px;border-style:dashed;}
#af-form-1865653734{overflow:hidden;}
.af-body .af-textWrap{text-align:left;}
.af-body input.image{border:none!important;}
.af-body input.submit,.af-body input.image,.af-form .af-element input.button{float:none!important;}
.af-body input.text{width:100%;float:none;padding:2px!important;}
.af-body.af-standards input.submit{padding:4px 12px;}
.af-clear{clear:both;}
.af-element label{text-align:left;display:block;float:left;}
.af-element{padding:5px 0;}
.af-form-wrapper{text-indent:0;}
.af-form{text-align:left;}
.af-header{margin-bottom:0;margin-top:0;padding:10px;}
.af-quirksMode .af-element{padding-left:0!important;padding-right:0!important;}
.lbl-right .af-element label{text-align:right;}
body {
}
</style>
<form method="post" name="videoform" class="af-form-wrapper" action="http://www.aweber.com/scripts/addlead.pl" target="_new"  >
<div style="display: none;">
<input type="hidden" name="meta_web_form_id" value="1865653734" />
<input type="hidden" name="meta_split_id" value="" />
<input type="hidden" name="listname" value="4pillars" />
<input type="hidden" name="redirect" value="http://www.aweber.com/thankyou-coi.htm?m=text" id="redirect_5bc7ae546248e7f4d8948b97fda32a08" />

<input type="hidden" name="meta_adtracking" value="My_Web_Form" />
<input type="hidden" name="meta_message" value="1" />
<input type="hidden" name="meta_required" value="email" />

<input type="hidden" name="meta_tooltip" value="" />
</div>
<div id="af-form-1865653734" class="af-form"><!--<div id="af-header-1865653734" class="af-header"><div class="bodyText"><p style="text-align: center;"><strong><span style="font-size: 18px;">&nbsp;FREE 4-Week Teleseminar Worth $497</span></strong></p></div></div>-->
<div id="af-body-1865653734" class="af-body af-standards">
<div class="af-element">
<div class="bodyText"><!--<p><span style="font-size: 14px;">Yes, Please reserve my spot at your <strong>FREE 4-Week Teleseminar</strong> and send me my videos right away.</span></p>--></div><div class="af-clear"></div>
</div>
<!--<div class="af-element">
<label class="previewLabel" for="awf_field-25653209">Name: </label>
<div class="af-textWrap">
<input id="awf_field-25653209" type="text" name="name" class="text" value=""  tabindex="500" />
</div>
<div class="af-clear"></div></div>-->
<div class="af-element">
<label class="previewLabel" for="awf_field-25653210">Email: </label>
<div class="af-textWrap"><input class="text" id="awf_field-25653210" type="text" name="email" value="" tabindex="501"  />
</div><div class="af-clear"></div>
</div>
<div class="af-element buttonContainer">
<input name="submit" class="submit" type="submit" value="Send My Videos" tabindex="502" />
<div class="af-clear"></div>
</div>
<div class="af-element privacyPolicy" style="text-align: center"><p><a title="Privacy Policy" href="http://www.aweber.com/permission.htm" target="_blank">We respect your email privacy</a></p>
<div class="af-clear"></div>
</div>
</div>
</div>
<div style="display: none;"><img src="http://forms.aweber.com/form/displays.htm?id=jBxsrGyszOzMLA==" alt="" /></div>
</form>
<script type="text/javascript">
    <!--
    (function() {
        var IE = /*@cc_on!@*/false;
        if (!IE) { return; }
        if (document.compatMode && document.compatMode == 'BackCompat') {
            if (document.getElementById("af-form-1865653734")) {
                document.getElementById("af-form-1865653734").className = 'af-form af-quirksMode';
            }
            if (document.getElementById("af-body-1865653734")) {
                document.getElementById("af-body-1865653734").className = "af-body inline af-quirksMode";
            }
            if (document.getElementById("af-header-1865653734")) {
                document.getElementById("af-header-1865653734").className = "af-header af-quirksMode";
            }
            if (document.getElementById("af-footer-1865653734")) {
                document.getElementById("af-footer-1865653734").className = "af-footer af-quirksMode";
            }
        }
    })();
    -->
</script>
</div>

<ul>
<li>Video 1:  How to build a Website that works for you</li>
<li>Video 2:  Why having a Blog brings you more traffic</li>
<li>Video 3:  Use Social Media to boost your rankings</li>
<li>Video 4:  How to Mobile enable your site and get more business</li>
</ul>

</div>
</div>
</div>

<div id="lower-help">
<p><a href="http://www.expand2web.com/blog/smallbiz-getting-started/" target="_blank">Getting Started</a> | <a href="http://userguide.expand2web.com/" target="_blank">Smallbiz Theme User Guide & Tutorials</a> | <a href="http://www.expand2web.com/blog/affiliates" target="_blank"> Become an Affiliate</a> 
<?php if(!get_option('smallbiz_authkey')){ ?>
| <a href="javascript:e2w.admin.showDemoAuthDialog();">Existing Customer Unlock Theme</a>
<?php } ?>

<br /> Need help? Email us: <a href="mailto:support@expand2web.com?subject=Userguide Support Request">support@expand2web.com</a> </p>

<div style="float:right;margin-top:-57px;margin-right:35px;"><p><img src="http://members.expand2web.com/E2W-theme-images/Expand2WebLogo45.png" alt="E2W logo" /></p></div>


</div>


		
</div>

<script type="text/javascript">
/* <![CDATA[ */
<?php if(get_option('smallbiz_authkey')){ ?>
if(typeof e2w == "undefined"){   e2w = {};         }
e2w.auth = { key:"<?php echo get_option('smallbiz_authkey'); ?>", name:"<?php echo get_option('smallbiz_authname'); ?>", email:"<?php echo get_option('smallbiz_authemail'); ?>" };
<?php } ?> 
e2w.admin.check_enable_upload_banner();
/* ]]> */
</script>

<?php

}


/* for custom fields start  */

if ( !class_exists('myCustomFields') ) {



    class myCustomFields {

        /**

        * @var  string  $prefix  The prefix for storing custom fields in the postmeta table

        */

        var $prefix = '_smallbiz_';

        /**

        * @var  array  $customFields  Defines the custom fields available

        */

        var $customFields = array(

            array(

                "name"          => "title",

                "title"         => "Custom Title Tag",

                "moreinfo"      => "Override the automatic page title.",

                "description"   => "custom <span>&#60;title&#62;</span> tag",

                "type"          =>  "text",

                "scope"         =>  array( "post", "page" ),

                "capability"    => "edit_posts"

            ),

            array(

                "name"          => "description",

                "title"         => "Meta Description",

                "moreinfo"      => "Add a page description.",

                "description"   => "<span>&#60;meta&#62;</span> description",

                "type"          =>  "text",

                "scope"         =>  array( "post", "page" ),

                "capability"    => "edit_posts"

            ),

            array(

                "name"          => "keywords",

                "title"         => "Meta Keywords",

                "moreinfo"      => "Replace the site-wide page keywords with a comma-separated list.",

                "description"   => "<span>&#60;meta&#62;</span> keywords",

                "type"          =>  "text",

                "scope"         =>  array( "post", "page" ),

                "capability"    => "edit_posts"

            )

        );

        /**

        * PHP 4 Compatible Constructor

        */

        function myCustomFields() { $this->__construct(); }

        /**

        * PHP 5 Constructor

        */

        function __construct() {

            add_action( 'admin_menu', array( &$this, 'createCustomFields' ) );

            add_action( 'save_post', array( &$this, 'saveCustomFields' ), 1, 2 );

            // Comment this line out if you want to keep default custom fields meta box

            add_action( 'do_meta_boxes', array( &$this, 'removeDefaultCustomFields' ), 10, 3 );

        }

        /**

        * Remove the default Custom Fields meta box

        */

        function removeDefaultCustomFields( $type, $context, $post ) {

        /*

            foreach ( array( 'normal', 'advanced', 'side' ) as $context ) {

                remove_meta_box( 'postcustom', 'post', $context );

                remove_meta_box( 'pagecustomdiv', 'page', $context );

            }

            */

        }

        /**

        * Create the new Custom Fields meta box

        */

        function createCustomFields() {

            if ( function_exists( 'add_meta_box' ) ) {

                add_meta_box( 'my-custom-fields', 'SmallBiz Theme SEO Details', array( &$this, 'displayCustomFields' ), 'page', 'normal', 'high' );

                add_meta_box( 'my-custom-fields', 'SmallBiz Theme SEO Details', array( &$this, 'displayCustomFields' ), 'post', 'normal', 'high' );

            }

        }

        /**

        * Display the new Custom Fields meta box

        */

        function displayCustomFields() {

            global $post;

            ?>

            <div class="form-wrap">

                <?php

                wp_nonce_field( 'my-custom-fields', 'my-custom-fields_wpnonce', false, true );

                foreach ( $this->customFields as $customField ) {

                    // Check scope

                    $scope = $customField[ 'scope' ];

                    $output = false;

                    foreach ( $scope as $scopeItem ) {

                        switch ( $scopeItem ) {

                            case "post": {

                                // Output on any post screen

                                if ( basename( $_SERVER['SCRIPT_FILENAME'] )=="post-new.php" || $post->post_type=="post" )

                                    $output = true;

                                break;

                            }

                            case "page": {

                                // Output on any page screen

                                if ( basename( $_SERVER['SCRIPT_FILENAME'] )=="page-new.php" || $post->post_type=="page" )

                                    $output = true;

                                break;

                            }

                        }

                        if ( $output ) break;

                    }

                    // Check capability

                    if ( !current_user_can( $customField['capability'], $post->ID ) )

                        $output = false;

                    // Output if allowed

                    if ( $output ) { ?>

                        <div class="form-field form-required SEO_form_area">

                            <?php

                            switch ( $customField[ 'type' ] ) {

                                case "checkbox": {

                                    // Checkbox

                                    echo '<label for="' . $this->prefix . $customField[ 'name' ] .'" style="display:inline;"><b>' . $customField[ 'title' ] . '</b></label>&nbsp;&nbsp;';

                                    echo '<input type="checkbox" name="' . $this->prefix . $customField['name'] . '" id="' . $this->prefix . $customField['name'] . '" value="yes"';

                                    if ( get_post_meta( $post->ID, $this->prefix . $customField['name'], true ) == "yes" )

                                        echo ' checked="checked"';

                                    echo '" style="width: auto;" />';

                                    break;

                                }

                                case "textarea": {

                                    // Text area

                                    echo '<label for="' . $this->prefix . $customField[ 'name' ] .'"><b>' . $customField[ 'title' ] . '</b></label>';

                                    echo '<textarea name="' . $this->prefix . $customField[ 'name' ] . '" id="' . $this->prefix . $customField[ 'name' ] . '" columns="30" rows="3">' . htmlspecialchars( get_post_meta( $post->ID, $this->prefix . $customField[ 'name' ], true ) ) . '</textarea>';

                                    break;

                                }

                                default: {

                                    // Plain text field

                                    echo '<label for="' . $this->prefix . $customField[ 'name' ] .'"><b>' . $customField[ 'title' ] . '</b>';

                                    echo '<span class="SEO_moreinfo_link" id="SEO_moreinfo_link-'. $customField[ 'name' ].'" onclick="javascript:SEO_moreinfo_toggle(\''. $customField[ 'name' ].'\');">&nbsp;&nbsp;&nbsp;&nbsp;[+] more info</span>';

                                    echo '</label>';

                                    echo '<input type="text" name="' . $this->prefix . $customField[ 'name' ] . '" id="' . $this->prefix . $customField[ 'name' ] . '" value="' . htmlspecialchars( get_post_meta( $post->ID, $this->prefix . $customField[ 'name' ], true ) ) . '" />';

                                    break;

                                }

                            }

                            ?>

                            <?php if ( $customField[ 'description' ] ) echo '<p class="SEO_desc">' . $customField[ 'description' ] . '</p>'; ?>

                            <?php if ( $customField[ 'moreinfo' ] ) echo '<div class="SEO_moreinfo" id="SEO_moreinfo-'. $customField[ 'name' ].'" onclick="javascript:SEO_moreinfo_toggle(\''. $customField[ 'name' ].'\');">[-] ' . $customField[ 'moreinfo' ] . '</div>'; ?>

                        </div>

                    <?php

                    }

                } ?>

            </div>
            <hr style="color:#ccc;background-color:#ccc;border:#ccc;"  />
            <br />
             <p><strong>Wordpress Editor Tip:</strong> </p>
             <p>1) Use the Kitchen Sink <img src="http://members.expand2web.com/E2W-theme-images/kitchensink.png"/> icon above to reveal more options for your text editor</p>
             <p>2)  When creating a new Blog Post - Use the "more" tag <img src="http://members.expand2web.com/E2W-theme-images/moretag.png"/>    icon to show a "teaser" of the post on your blog page. And read here: <a href="http://userguide.expand2web.com/how-to-guide/contact-find-us-and-blog-customization/how-to-write-the-perfect-blogpost/" target="_blank">How to create the perfect blog post.</a></p> 
            <?php

        }
        
                

        /**

        * Save the new Custom Fields values

        */

        function saveCustomFields( $post_id, $post ) {

            if ( !wp_verify_nonce( $_POST[ 'my-custom-fields_wpnonce' ], 'my-custom-fields' ) )

                return;

            if ( !current_user_can( 'edit_post', $post_id ) )

                return;

            if ( $post->post_type != 'page' && $post->post_type != 'post' )

                return;

            foreach ( $this->customFields as $customField ) {

                if ( current_user_can( $customField['capability'], $post_id ) ) {

                    if ( isset( $_POST[ $this->prefix . $customField['name'] ] ) && trim( $_POST[ $this->prefix . $customField['name'] ] ) ) {

                        update_post_meta( $post_id, $this->prefix . $customField[ 'name' ], $_POST[ $this->prefix . $customField['name'] ] );

                    } else {

                        delete_post_meta( $post_id, $this->prefix . $customField[ 'name' ] );

                    }

                }

            }

        }



    } // End Class



} // End if class exists statement



// Instantiate the class

if ( class_exists('myCustomFields') ) {

    $myCustomFields_var = new myCustomFields();

}

/* for custom fields end  */
?>