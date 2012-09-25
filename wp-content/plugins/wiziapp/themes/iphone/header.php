<!DOCTYPE HTML>
<html>
<head profile="http://gmpg.org/xfn/11">
    <?php
        // Disable the admin bar
        if ( function_exists("show_admin_bar") ){
            show_admin_bar(false);
        }
    ?>
    <base href="<?php bloginfo('url'); ?>/" />
    <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
    <title><?php echo wiziapp_apply_request_title(wp_title('&laquo;', false, 'right').get_bloginfo('name')); ?></title>
    <meta name="viewport" content="width=device-width,user-scalable=no" />
    <link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
    <?php
        wp_head();
        /**
         * When we are coming from a single post page we need to include the script as is, but in a
         * post list screen the application will search and replace the content of the @@@WORD@@@ with the
         * resource from the application configuration request
         */
        if (!isset($GLOBALS['WiziappOverrideScripts']) || !$GLOBALS['WiziappOverrideScripts']) {
            echo '<script type="text/javascript" src="' . WiziappConfig::getInstance()->getCdnServer() . '/assets/scripts"></script>';
            echo '<script type="text/javascript" src="' . WiziappConfig::getInstance()->getCdnServer() . '/scripts/jquery.mousewheel.min.js"></script>';
            echo '<script type="text/javascript" src="' . WiziappConfig::getInstance()->getCdnServer() . '/scripts/jScrollPane-1.2.3.min.js"></script>';
        } else {
            echo '@@@BASE@@@';
        }
    ?>

    <style type="text/css">
        <?php
            $cssFileName = dirname(__FILE__) . '/' . WiziappConfig::getInstance()->wiziapp_theme_name . '.css';
            $baseCssFileName = dirname(__FILE__) . '/style.css';
            $baseFile = file_get_contents($baseCssFileName);

            $file = file_get_contents($cssFileName);
            $css = $baseFile . $file;
            /* remove comments */
            $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
            /* remove tabs, spaces, newlines, etc. */
            $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);

            $cdnServer = WiziappConfig::getInstance()->getCdnServer();

            $css = str_replace("@@@WIZIAPP_CDN@@@", $cdnServer, $css);

            if ( isset($_GET['sim']) && isset($_GET['sim']) == 1 ){
                $css .= ' body{ overflow-y: hidden; }';
            }
            echo $css;
        ?>
    </style>
    <link id="themeCss" rel="stylesheet" href="https://<?php echo WiziappConfig::getInstance()->api_server . '/application/postViewCss/'.WiziappConfig::getInstance()->app_id.'?v=' . WIZIAPP_VERSION . '&c=' . (WiziappConfig::getInstance()->configured ? 1 : 0);  ?>" type="text/css" />
</head>
<body>