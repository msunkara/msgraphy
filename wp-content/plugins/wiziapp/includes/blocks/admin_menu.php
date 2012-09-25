<?php
/**
* @package WiziappWordpressPlugin
* @subpackage AdminDisplay
* @author comobix.com plugins@comobix.com
*/

/**
* Sets up the admin menu according to the application configuration state.
* for a fully installed app we show a full menu but untill then 
* way make things more complicated for the user
*/
function wiziapp_setup_menu(){
    $configured = WiziappConfig::getInstance()->settings_done;
    
    //$configured = FALSE;
    if (isset($_GET['wiziapp_configured']) && $_GET['wiziapp_configured'] == 1){
        $configured = TRUE;        
    }
    
    $iconPath = WiziappConfig::getInstance()->getCdnServer() . "/images/cms/WiziSmallIcon.png";

    $installer = new WiziappInstaller();

    //if(current_user_can('administrator') && !empty($options['app_token'])) {
    if ( current_user_can('administrator') ) {
        //add_action('admin_notices', 'wiziapp_dir_notice');
        add_action('admin_notices', 'wiziapp_config_notice');
        add_action('admin_notices', 'wiziapp_version_check');
        add_action('admin_notices', 'wiziapp_upgrade_check');

        if ( WiziappConfig::getInstance()->finished_processing === FALSE ){
            add_menu_page('WiziApp', 'WiziApp', 'administrator', 'wiziapp', 'wiziapp_activate_display', $iconPath);
        } else if ( $installer->needUpgrade() ){
            add_menu_page('WiziApp', 'WiziApp', 'administrator', 'wiziapp', 'wiziapp_upgrade_display', $iconPath);
        } else if ($configured === FALSE){
            add_menu_page('WiziApp', 'WiziApp', 'administrator', 'wiziapp', 'wiziapp_generator_display', $iconPath);
        } else { // We are installed and configured
            // add_submenu_page('wiziapp', __('dashboard'), __('dashboard'), 'administrator', 'wiziapp_dashboard_display', 'wiziapp_dashboard_display');
            add_menu_page('WiziApp', 'WiziApp', 'administrator', 'wiziapp', 'wiziapp_dashboard_display', $iconPath);
            
            add_submenu_page('wiziapp', '', '', 'administrator', 'wiziapp', ''); // This is to avoid having the top menu duplicated as a sub menu

            if (WiziappConfig::getInstance()->app_live !== FALSE){
                add_submenu_page('wiziapp', __('Statistics'), __('Statistics'), 'administrator', 
                                 'wiziapp_statistics_display', 'wiziapp_statistics_display');   
            }
            add_submenu_page('wiziapp', __('App Info'), __('App Info'), 'administrator', 
                             'wiziapp_app_info_display', 'wiziapp_app_info_display');
            add_submenu_page('wiziapp', __('My Account'), __('My Account'), 'administrator', 
                             'wiziapp_my_account_display', 'wiziapp_my_account_display');
        }

        add_submenu_page('wiziapp', __('Support'), __('Support'), 'administrator',
                             'wiziapp_support_display', 'wiziapp_support_display');
    }
}

function wiziapp_dashboard_display(){
    wiziapp_includeGeneralDisplay('dashboard');    
}

function wiziapp_statistics_display(){
    wiziapp_includeGeneralDisplay('statistics');    
}

function wiziapp_settings_display(){
    wiziapp_includeGeneralDisplay('settings');        
}

function wiziapp_my_account_display(){
    wiziapp_includeGeneralDisplay('myAccount');    
}

function wiziapp_app_info_display(){
    wiziapp_includeGeneralDisplay('appInfo', TRUE);    
}

function wiziapp_support_display(){
    $support = new WiziappSupportDisplay();

    $support->display();
}

function wiziapp_includeGeneralDisplay($display_action, $includeSimOverlay = TRUE){
    $response = wiziapp_http_request(array(), '/generator/getToken?app_id=' . WiziappConfig::getInstance()->app_id, 'GET');
    if ( is_wp_error($response) ) {
        /**
        * @todo get the design for the failure screen
        */
        echo '<div class="error">'.__('There was a problem contacting wiziapp services, please try again in a few minutes', 
             'wiziapp').'</div>';
        exit();
    }
    
    // We are here, so all is good and the main services are up and running
    $tokenResponse = json_decode($response['body'], TRUE);
    if (!$tokenResponse['header']['status']) {
        // There was a problem with the token
        echo '<div class="error">' . $tokenResponse['header']['message'] . '</div>';
    } else {
        $token = $tokenResponse['token'];
        $httpProtocol = 'https';
        if ( $includeSimOverlay ){
            ?>
            <script src="http://cdn.jquerytools.org/1.2.5/all/jquery.tools.min.js"></script>
            <style>
            .overlay_close {
                background-image:url(<?php echo WiziappConfig::getInstance()->getCdnServer(); ?>/images/generator/close.png);
                position:absolute; right:-17px; top:-17px;
                cursor:pointer;
                height:35px;
                width:35px;
            }
            #wiziappBoxWrapper{
                width: 390px;
                height: 760px;
                margin: 0px auto;
                padding: 0px;
            }
            </style>
            <script type="text/javascript">
                var WIZIAPP_HANDLER = (function(){
                    jQuery(document).ready(function(){
                        jQuery('.report_issue').click(reportIssue);
                        jQuery('.retry_processing').click(retryProcessing);

                        jQuery('#general_error_modal').bind('closingReportForm', function(){
                            jQuery(this).removeClass('s_container')
                        });
                    });
                    
                    function wiziappReceiveMessage(event){
                        // Just wrap our handleRequest 
                        if ( event.origin == '<?php echo "{$httpProtocol}://" . WiziappConfig::getInstance()->api_server ?>' ){
                            WIZIAPP_HANDLER.handleRequest(event.data);   
                        }
                    };
                    
                    if ( window.addEventListener ){ 
                        window.addEventListener("message", wiziappReceiveMessage, false); 
                    } 

                    function retryProcessing(event){
                        event.preventDefault();
                        document.location.reload(true);
                        return false;
                    };

                    function reportIssue(event){
                        // Change the current box style so it will enable containing the report form
                        event.preventDefault();
                        var $box = jQuery('#general_error_modal');
                        var $el = $box.find('.report_container');

                        var params = {
                            action: 'wiziapp_report_issue',
                            data: $box.find('.wiziapp_error').text()
                        };

                        $el.load(ajaxurl, params, function(){
                            var $mainEl = jQuery('#general_error_modal');
                            $mainEl
                                    .removeClass('s_container')
                                    .find(".errors_container").hide().end()
                                    .find(".report_container").show().end();
                            $mainEl = null;
                        });

                        var $el = null;
                        return false;
                    };

                    var actions = {
                        changeTab: function(params){
                            top.document.location.replace('<?php echo get_admin_url();?>admin.php?page='+params.page);    
                        },
                        informGeneralError: function(params){
                             var $box = jQuery('#'+params.el);
                            $box
                                .find('.wiziapp_error').text(params.message).end();

                            if ( parseInt(params.retry) == 0 ){
                                $box.find('.retry_processing').hide();
                            } else {
                                $box.find('.retry_processing').show();
                            }

                            if ( parseInt(params.report) == 0 ){
                                $box.find('.report_issue').hide();
                            } else {
                                $box.find('.report_issue').show();
                            }

                            if (!$box.data("overlay")){
                                $box.overlay({
                                    fixed: true,
                                    top: 200,
                                    left: (screen.width / 2) - ($box.outerWidth() / 2),
                                    /**mask: {
                                        color: '#fff',
                                        loadSpeed: 200,
                                        opacity: 0.1
                                    },*/
                                    // disable this for modal dialog-type of overlays
                                    closeOnClick: false,
                                    closeOnEsc: false,
                                    // load it immediately after the construction
                                    load: true,
                                    onBeforeLoad: function(){
                                        var $toCover = jQuery('#wpbody');
                                        var $mask = jQuery('#wiziapp_error_mask');
                                        if ( $mask.length == 0 ){
                                            $mask = jQuery('<div></div>').attr("id", "wiziapp_error_mask");
				                            jQuery("body").append($mask);
                                        }

                                        $mask.css({
                                            position:'absolute',
                                            top: $toCover.offset().top,
                                            left: $toCover.offset().left,
                                            width: $toCover.outerWidth(),
                                            height: $toCover.outerHeight(),
                                            display: 'block',
                                            opacity: 0.9,
                                            backgroundColor: '#444444'
                                        });

                                        $mask = $toCover = null;
                                    }
                                });
                            }
                            else {
                                $box.show();
                                $box.data("overlay").load();
                            }
                            $box = null;
                        },
                        showProcessing: function(params){
                            var $box = jQuery('#'+params.el);
                            $box
                                .find('.error').hide().end()
                                .find('.close').hide().end()
                                .find('.processing_message').show().end();
                                
                            if ( !$box.data("overlay") ){
                                $box.overlay({
                                    fixed: true,
                                    top: 200,
                                    left: (screen.width / 2) - ($box.outerWidth() / 2),
                                    mask: {
                                        color: '#444444',
                                        loadSpeed: 200,
                                        opacity: 0.9
                                    },

                                    // disable this for modal dialog-type of overlays
                                    closeOnClick: false,
                                    // load it immediately after the construction
                                    load: true
                                });
                            }
                            else {
                                $box.show();
                                $box.data("overlay").load();
                            }
                            
                            $box = null;    
                        },
                        showSim: function(params){
                            var url = decodeURIComponent(params.url);
                            var $box = jQuery("#wiziappBoxWrapper");
                            if ( $box.length == 0 ){
                                $box = jQuery("<div id='wiziappBoxWrapper'><div class='close overlay_close'></div><iframe id='wiziappBox'></iframe>");
                                $box.find("iframe").attr('src', url+"&preview=1");
                                
                                $box.appendTo(document.body);
                                
                                $box.find("iframe").css({
                                    'border': '0px none',
                                    'height': '760px',
                                    'width': '390px'
                                });
                                
                                $box.overlay({
                                    top: 20,
                                    fixed: false,
                                    mask: {
                                        color: '#444',
                                        loadSpeed: 200,
                                        opacity: 0.8
                                    },
                                    closeOnClick: true,
                                    onClose: function(){
                                        jQuery("#wiziappBoxWrapper").remove();
                                    },
                                    load: true
                                });
                            }
                            else {
                                $box.show();
                                $box.data("overlay").load();
                            }
                            
                            $box = null;
                        },
                        resizeGeneratorIframe: function(params){
                            jQuery("#wiziapp_frame").css({
                                'height': (parseInt(params.height) + 50) + 'px'
                            });
                        }                                                      
                    };
                    
                    return {
                        handleRequest: function(q){
                            var paramsArray = q.split('&');
                            var params = {};
                            for ( var i = 0; i < paramsArray.length; ++i) {
                                var parts = paramsArray[i].split('=');
                                params[parts[0]] = decodeURIComponent(parts[1]);
                            }
                            if ( typeof(actions[params.action]) == "function" ){
                                actions[params.action](params);
                            }
                            params = q = paramsArray = null;
                        }
                    };                    
                })();
            </script>
            <?php
        }
        ?>
        <style>
        #wiziapp_container{
            background: #fff;
        }
        .processing_modal{
            background: url(<?php echo WiziappConfig::getInstance()->getCdnServer(); ?>/images/generator/Pament_Prossing_Lightbox.png) no-repeat top left;
            display:none;
            width:486px;        
            height: 53px;
            padding:35px;
        }
        #general_error_modal{
            z-index: 999;
        }
        </style>
        <div id="wiziapp_container">
            <?php 
                $iframeSrc = $httpProtocol . '://' . WiziappConfig::getInstance()->api_server . '/cms/controlPanel/' . $display_action . '?app_id=' .
                            WiziappConfig::getInstance()->app_id . '&t=' . $token . '&v='.WIZIAPP_P_VERSION;
            ?>
            
            <iframe id="wiziapp_frame" src="" 
                style="overflow: hidden; width:100%; height: 880px; border:0px none;" frameborder="0"></iframe>
            <script type="text/javascript">
                var iframe_src = "<?php echo $iframeSrc; ?>";
                document.getElementById("wiziapp_frame").src = iframe_src;                   
            </script>
        </div>

        <div class="wiziapp_errors_container s_container hidden" id="general_error_modal">
            <div class="errors_container">
                <div class="errors">
                    <div class="wiziapp_error"></div>
                </div>
                <div class="buttons">
                    <a href="javascript:void(0);" class="report_issue">Report a Problem</a>
                    <a class="retry_processing close" href="javascript:void(0);">Retry</a>
                </div>
            </div>
            <div class="report_container hidden">

            </div>
        </div>
        
        <div class="processing_modal" id="reload_modal">
            <p class="processing_message">It seems your session has timed out.</p> 
            <p>please <a href="javascript:top.document.location.reload(true);">refresh</a> this page to try again</p>
            <p class="error" class="errorMessage hidden"></p>
            <a class="close hidden" href="javascript:void(0);">Go back</a>
        </div>
        <?php
    }
}


function wiziapp_version_check(){
    $needCheck = TRUE;
    $needShow = TRUE;
    
    // Check only if we didn't check in the last 12 hours
    if ( isset(WiziappConfig::getInstance()->last_version_checked_at) ){
        // We checked for the version already, but was it in the last 12 hours?
        if ((time() - WiziappConfig::getInstance()->last_version_checked_at) <= 60*60*12){
            // We need to check again
            $needCheck = FALSE;
        } 
    } 
    if ( $needCheck ){
        // Get the current version
        if ( empty(WiziappConfig::getInstance()->wiziapp_avail_version) ){
            WiziappConfig::getInstance()->wiziapp_avail_version = WIZIAPP_P_VERSION;
        }
        $response = wiziapp_http_request(array(), '/cms/version', 'GET');
        if ( !is_wp_error($response) ) {    
            $vResponse = json_decode($response['body'], TRUE);
            if ( !empty($vResponse) ){
                WiziappConfig::getInstance()->wiziapp_avail_version = $vResponse['version'];
                WiziappConfig::getInstance()->last_version_checked_at = time();
                //update_option('wiziapp_settings', $options);
            }
        }
    }
    
    if ( WiziappConfig::getInstance()->wiziapp_avail_version != WIZIAPP_P_VERSION ){
        if ( isset(WiziappConfig::getInstance()->show_need_upgrade_msg) && WiziappConfig::getInstance()->show_need_upgrade_msg === FALSE ) {
            // The user choose to hide the version alert, but was the version alert for the version he saw?
            if ( WiziappConfig::getInstance()->last_version_shown === WiziappConfig::getInstance()->wiziapp_avail_version ){
                $needShow = FALSE;    
            }
        }
        
        if ( $needShow ){
            ?>
            <div id="wiziapp_upgrade_needed_message" class="updated fade">
                <p style="line-height: 150%">
                    An important update is available for the WiziApp WordPress plugin.
                    <br />
                    Make sure to update as soon as possible, to enjoy the security, bug fixes and new features contained in this update.
                </p>
                <p>
                    <input id="wiziappHideUpgrade" type="button" class="button" value="Hide this message" />
                </p>
                <script type="text/javascript">
                jQuery(document).ready(function(){
                    jQuery("#wiziappHideUpgrade").click(function(){
                        var params = {
                            action: 'wiziapp_hide_upgrade_msg'
                        };
                        jQuery.post(ajaxurl, params, function(data){
                            jQuery("#wiziapp_upgrade_needed_message").remove();
                        });     
                    });    
                });
                </script>
            </div>    
        <?php   
        }
    } 
}

function wiziapp_upgrade_check(){
    $installer = new WiziappInstaller();

    if ( $installer->needUpgrade() && $_GET['page'] != 'wiziapp' ){
        ?>
        <div id="wiziapp_internal_upgrade_needed_message" class="updated fade">
            <p style="line-height: 150%">
                WiziApp needs one more step to finish the upgrading process, click <a href="admin.php?page=wiziapp">here</a> to upgrade your database.
                <br />
                Make sure to update as soon as you can to enjoy the security, bug fixes and new features this update contain.
            </p>
        </div>
    <?php
    }
}
/**
* Displays a notice for the user
* 
*/
function wiziapp_config_notice(){
    /**if (empty($options['app_token'])){
        // Error
        ?>
        <div id="message" class="error fade">
            <p style="line-height: 150%">
                <strong><?php echo __('ERROR', 'wiziapp'); ?></strong><?php echo __(' - There was a problem activating the wiziapp plugin', 'wiziapp'); ?>
            </p>
            <p>
                <?php echo __('Please try to reactivate the plugin via the plugins page and if the error persisits please contact support', 'wiziapp')?>
            </p>
        </div>
        <?php
    } else {*/
        if (!isset(WiziappConfig::getInstance()->wiziapp_showed_config_once) ||
                WiziappConfig::getInstance()->wiziapp_showed_config_once !== TRUE){
            ?>
                <!--Google analytics-->
                <script type="text/javascript">
                    var _gaq = _gaq || [];
                    _gaq.splice(0, _gaq.length);
                    var url = location.host;
                    _gaq.push(['_setAccount', 'UA-22328620-1']);
                    _gaq.push(['_setDomainName', url.replace('www.', '.')]);
                    _gaq.push(['_setAllowLinker', true]);
                    _gaq.push(['_setAllowHash', false]);
                    _gaq.push(['_trackPageview', '/WiziappDownloadFunnel.php']);
                    _gaq.push(['_trackPageview', '/ActivePluginGoal.php']);
                
                    (function() {
                        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
                    })();
                </script>
                
                <div id="message" class="updated fade" style="font-size: 1.1em">
                    <p>
                        <span style="font-weight: bolder;">WiziApp</span> automatically turns your WordPress blog into a native iPhone app. 
                    </p>
                    <p>To get started, we need you to complete 3 simple steps to configure your app using our friendly Wizard. </p>
                    <p>You can do that right now by clicking <a href="admin.php?page=wiziapp">here</a>, or any time you want via your WordPress admin control panel in the main menu.
                    </p>
                </div>         
            <?php
            WiziappConfig::getInstance()->wiziapp_showed_config_once = TRUE;
        }
    ?>
    <?php
    //}
    

    if (!WiziappConfig::getInstance()->email_verified &&
            WiziappConfig::getInstance()->settings_done &&
            WiziappConfig::getInstance()->show_email_verified_msg &&
            WiziappConfig::getInstance()->finished_processing){
        ?>
            <div id="wiziapp_email_verified_message" class="error fade">
                <p style="line-height: 150%">
                    Your Email address is not verified yet. We have sent you a verification Email, please go to your Email account and click the verify link.
                    <br />
                    In case you haven’t got this email please go to <a href="admin.php?page=wiziapp_my_account_display">my account</a> and click "verify email". 
                </p>
                <p>
                    <input id="wiziappHideVerify" type="button" class="button" value="Hide this message" />
                </p>
                <script type="text/javascript">
                jQuery(document).ready(function(){
                    jQuery("#wiziappHideVerify").click(function(){
                        var params = {
                            action: 'wiziapp_hide_verify_msg'  
                        };
                        jQuery.post(ajaxurl, params, function(data){
                            jQuery("#wiziapp_email_verified_message").remove();
                        });     
                    });    
                });
                </script>
            </div>    
        <?php
    }
}


add_action('wp_ajax_wiziapp_hide_verify_msg', 'wiziapp_hide_verify_msg'); 
function wiziapp_hide_verify_msg(){
    $status = (WiziappConfig::getInstance()->show_email_verified_msg = FALSE);
    
    $header = array(
        'action' => 'hideVerifyMsg',
        'status' => $status,
        'code' => ($status) ? 200 : 4004,
        'message' => '',
    );
            
    echo json_encode(array('header' => $header));
    exit;
}

add_action('wp_ajax_wiziapp_hide_upgrade_msg', 'wiziapp_hide_upgrade_msg'); 
function wiziapp_hide_upgrade_msg(){
    $status = TRUE;

    WiziappConfig::getInstance()->show_need_upgrade_msg = FALSE;
    WiziappConfig::getInstance()->last_version_shown = WiziappConfig::getInstance()->wiziapp_avail_version;
    
    $header = array(
        'action' => 'hideUpgradeMsg',
        'status' => $status,
        'code' => ($status) ? 200 : 4004,
        'message' => '',
    );
            
    echo json_encode(array('header' => $header));
    exit;
}

/*
 * Displays directories permission
 */
function wiziapp_dir_notice(){
    
    $perms = wiziapp_dirs_perms();
    if(!$perms['cache'] || !$perms['logs']){
        ?>
        <div id="message" class="updated fade">
        <p>
            <strong><?php echo __('The following directories needs to be writable:', 'wiziapp'); ?></strong><br>
            <?php echo !$perms['logs']?'wiziapp/logs<br>' : '';?>
            <?php echo !$perms['cache']?'wiziapp/cache<br>' : '';?>
        </p>
        </div>
        <?php
    }
}

function wiziapp_dirs_perms(){
    $logs_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'logs';
    $cache_dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cache';
    return array('cache'=>is_readable($cache_dir) && is_writable($cache_dir), 
                 'logs'=>is_readable($logs_dir) && is_writable($logs_dir));
}

/*
* Add the settings link to the plugin manage table
*/ 
function wiziapp_setup_add_settings_link($links, $file){
   if($file == WP_WIZIAPP_BASE){
        $settings_link = '<a href="admin.php?page=wiziapp_settings_page">Settings</a>';
        array_unshift($links, $settings_link); 
    } 
    return $links;    
}

function wiziapp_content_status_display(){
    echo '<h1>Content status display</h1>';
    echo '<h2>Posts</h2>';
    $posts_content = $GLOBALS['WiziappDB']->get_all_posts();
    var_dump($posts_content);
}
