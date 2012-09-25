<?php

/**
* Plugin Name: Wiziapp
* Description: WiziApp automatically turns your WordPress blog into a native iPhone app. Customize the app to make it your own by using our friendly wizard.
* Author: Wiziapp Solutions Ltd.
* Version: 1.2.2b
* Author URI: http://www.wiziapp.com/
*/
/**
* This is the plugin entry script, it checks for compatibility and if compatible
* it will loaded the needed files for the CMS plugin
* @package WiziappWordpressPlugin
* @author comobix.com plugins@comobix.com
* 
* @todo Handle in-compatibilities issues more nicely (after the graphic design)
*/

// Run only once
if(!defined('WP_WIZIAPP_BASE')){
    define('WP_WIZIAPP_BASE', plugin_basename(__FILE__)); 
    define('WP_WIZIAPP_DEBUG', TRUE);
    define('WP_WIZIAPP_PROFILER', FALSE);
    define('WIZI_ABSPATH', realpath(ABSPATH));
    define('WIZIAPP_ENV', 'prod'); // can be dev/test/prod
    define('WIZIAPP_VERSION', '1.2.2b');   // MAKE SURE TO UPDATE BOTH THIS AND THE UPPER VALUE
    define('WIZIAPP_P_VERSION', '1.2.2');   // The platform version
    
    function wiziapp_shutdownWrongPHPVersion(){
        ?>
            <script type="text/javascript">alert("<?php echo __('You need PHP version 5.2 or higher to use the WiziApp plugin.', 'wiziapp');?>")</script>
        <?php
    }

    function wiziapp_shutdownWrongWPVersion(){
        ?>
            <script type="text/javascript">alert("<?php echo __('You need WordPress® 2.8.4 or higher to use the WiziApp plugin.', 'wiziapp');?>")</script>
        <?php
    }
    
    if (version_compare (PHP_VERSION, "5.2", ">=") && version_compare (get_bloginfo ("version"), "2.8.4", ">=")){
        include_once dirname (__FILE__) . "/includes/classes/WiziappExceptions.php";
        include_once dirname (__FILE__) . "/includes/blocks.inc.php";
        include_once dirname (__FILE__) . "/includes/hooks.inc.php";
    } else if ( is_admin() ){
        if (!version_compare (PHP_VERSION, "5.2", ">=")){
            register_shutdown_function ('wiziapp_shutdownWrongPHPVersion');
        }
        else if (!version_compare (get_bloginfo ("version"), "2.8.4", ">=")){
            register_shutdown_function ('wiziapp_shutdownWrongWPVersion');
        }
    }
} else {
    function wiziapp_getDuplicatedInstallMsg(){
        return '<div class="error">'
                . __( 'An older version of the plugin is installed and must be deactivated. To do this, locate the old WiziApp plugin in the WordPress plugins interface and click Deactivate, then activate the new plugin.', 'wiziapp')
            .'</div>';
    }

    die(wiziapp_getDuplicatedInstallMsg());
}
