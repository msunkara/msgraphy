<!DOCTYPE HTML>
<html>
    <head>
        <?php
        /**
         * When we are coming from a single post page we need to include the script as is, but in a
         * post list screen the application will search and replace the content of the @@@WORD@@@ with the                * resource from the application configuration request
         */
        if (!isset($GLOBALS['WiziappOverrideScripts']) || !$GLOBALS['WiziappOverrideScripts']) {
            echo '<script type="text/javascript" src="http://'.WiziappConfig::getInstance()->getCdnServer().'/assets/scripts"></script>';
        } else {
            echo '@@@BASE@@@';
        }
        ?>
        <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
        <title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
        <meta name="viewport" content="width=device-width,user-scalable=no" />
        <?php
        /**
         * The application is preloading the posts as strings, we need to include as much
         * data as we can and save on network connections, so load the css directly in the file
         */
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

        echo $css;
?>
        </style>
    </head>
    <body class="registration">
        <div class="page_content">
            <div class="post">
                <p class="message register"><?php echo $_SESSION['wiziapp_message'];unset($_SESSION['wiziapp_message']); ?></p>
                <div id="registration">
                <form name="registerform" id="registerform" action="<?php bloginfo('url');?>/wiziapp/user/register" method="post">
                    <div class="border">
                        <label for="user_login">User Name:</label>
                        <input type="text" name="user_login" id="user_login" class="input" value="" size="20" tabindex="10" />
                    </div>
                    <div class="border">
                        <label for="user_email">E-mail:</label>
                        <input type="email" name="user_email" id="user_email" class="input" value="" size="25" tabindex="20" />
                    </div>
                    <div class="border bottom">
                        <input type="submit" name="wp-submit" id="submit" value="Singup" tabindex="100" />
                    </div>
                </form>
                </div>
                <p id="reg_passmail">A password will be e-mailed to you.</p>
         </div>
            <br />
        </div>
   </body>
</html>
