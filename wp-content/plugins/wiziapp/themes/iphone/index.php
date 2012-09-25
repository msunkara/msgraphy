<?php  
$GLOBALS['WiziappLog']->write('info', "Loaded index template", "themes.default.index");
/**
* Get access to the globals
*/
global $wiziapp_block, $cPage, $nextPost, $prevPost;   
/**
* Start wordpress loop, the condition for the loop was prepared in the screens functions
*/
if (have_posts()) : 
    $GLOBALS['WiziappOverrideScripts'] = TRUE;
    if (!isset($GLOBALS['WiziappEtagOverride'])){
        $GLOBALS['WiziappEtagOverride'] = '';
    }
    $injectLoadedScript = '<script type="text/javascript">WIZIAPP.doLoad();</script>';
    // Start capturing output from loop events
    ob_start();
    while (have_posts()) : the_post(); 
        $GLOBALS['WiziappEtagOverride'] .= serialize($post);
        $GLOBALS['WiziappLog']->write('info', "The id: {$post->ID}", "themes.default.index");
        
        if ( isset($GLOBALS['wp_posts_listed']) ){
            if ( in_array($post->ID, $GLOBALS['wp_posts_listed']) ){
                continue;
            } else {
                $GLOBALS['wp_posts_listed'][] = $post->ID;    
            }
        }
        
        /**
        * In this template we are only doing posts list
        * for posts list we will to pre-load the post template so get the template
        * inside a string to pass it to the component building functions
        */
        $contents = null;
        
        if ( WiziappConfig::getInstance()->usePostsPreloading() ){
            
            $GLOBALS['WiziappLog']->write('info', "Preloading the posts", "themes.default.index");  
            ob_start();

            include('_content.php');     

            $contents = ob_get_contents();
            // Inject the doLoad method to avoid timing issues when getting the post in this bundle 
            $contents = str_replace('</body>', $injectLoadedScript.'</body>', $contents);
            ob_end_clean();    
        }
        wiziapp_appendComponentByLayout($cPage, $wiziapp_block, $post->ID, $contents);
    endwhile;
    ob_end_clean(); // End capturing output from loop events
    // In case something in the template changed, add the modified date to the etag
    
    $GLOBALS['WiziappEtagOverride'] .= date("F d Y H:i:s.", filemtime(dirname(__FILE__).'/_content.php'));
    $GLOBALS['WiziappEtagOverride'] .= date("F d Y H:i:s.", filemtime(dirname(__FILE__).'/index.php'));
else : 
    $GLOBALS['WiziappLog']->write('error', "No posts???", "themes.default.index");
endif; 