<?php

/**
* @package WiziappWordpressPlugin
* @subpackage Core
* @author comobix.com plugins@comobix.com
*/
add_action('wp_ajax_wiziapp_upgrade_database', 'wiziapp_upgrade_database');
add_action('wp_ajax_wiziapp_upgrade_configuration', 'wiziapp_upgrade_configuration');
add_action('wp_ajax_wiziapp_upgrading_finish', 'wiziapp_upgrading_finish');

function wiziapp_upgrading_finish(){
    $GLOBALS['WiziappLog']->write('debug', "The upgrading is finished, letting the admin know",
                                        "post_upgrade.wiziapp_upgrading_finish");

    // Send the profile again, and allow it to fail since it's just an update
    $cms = new WiziappCms();
    $cms->activate();

    $status = TRUE;
    
    $header = array(
        'action' => 'upgrading_finish',
        'status' => $status,
        'code' => ($status) ? 200 : 500,
        'message' => '',
    );
                                                                        
    echo json_encode(array('header' => $header));
    exit;
}

function wiziapp_upgrade_database(){
    $installer = new WiziappInstaller();
    $status = $installer->upgradeDatabase();

    $header = array(
        'action' => 'upgrade_database',
        'status' => $status,
        'code' => ($status) ? 200 : 500,
        'message' => '',
    );

    echo json_encode(array('header' => $header));
    exit;
}

function wiziapp_upgrade_configuration(){
    $installer = new WiziappInstaller();
    $status = $installer->upgradeConfiguration();

    $header = array(
        'action' => 'upgrade_configuration',
        'status' => $status,
        'code' => ($status) ? 200 : 500,
        'message' => '',
    );

    echo json_encode(array('header' => $header));
    exit;
}


function wiziapp_upgrade_display(){
    ?>
    <script src="http://cdn.jquerytools.org/1.2.5/all/jquery.tools.min.js"></script>
    <style>
        #wpbody{
            background-color: #fff;
        }
        #wiziapp_posts_progress_bar_container{
            border: 1px solid #0000FF;
            background-color: #C0C0FF;
            position: relative;
            width: 300px;
            height: 30px;
        }
        #wiziapp_posts_progress_bar_container .progress_bar{
            background-color: #0000FF;
            width: 0%;    
        }
        .progress_bar{
            position: absolute;
            top: 0px;
            left: 0px;
            height: 100%;
        }
        .progress_indicator{
            color: #fff;
            font-weight: bolder;
            text-align:center;
            width: 100%;
            position: absolute;
            top: 0px;
            left: 0px;
        }
        
        #wiziapp_finalize_title, #wiziapp_pages_title, #all_done{
            display: none;
        }
        
        #just_a_moment{
            background: url(<?php echo WiziappConfig::getInstance()->getCdnServer(); ?>/images/cms/processingJustAmoment.png) no-repeat top center;
            width: 262px;
            margin: 50px auto 17px;
            height: 32px;
        }
        
        #wizi_icon_processing{
            background: url(<?php echo WiziappConfig::getInstance()->getCdnServer(); ?>/images/cms/wiziapp_processing_icon.png) no-repeat top center;
            width: 135px;
            height: 93px;
        }
        
        #wizi_icon_wrapper{
            margin: 52px auto 29px;
            position: relative;
            height:  93px;
            width: 235px;
        }
        
        #current_progress_label{
            position: absolute;
            top: 40px;
            right: 4px;
        }
        
        .text_label{
            color: #0ca0f5;
            font-weight: bold;
            text-align: center;
            font-size: 14px;
            margin: 15px 0 9px;
        }
        #main_progress_bar_container{
            width: 260px;
            height: 12px;
            position: relative;
            margin: 0px auto;
        }
        #main_progress_bar_bg{
            position: absolute;
            top: 0px;
            left: 0px;
            width: 100%;
            height: 100%;
            background: url(<?php echo WiziappConfig::getInstance()->getCdnServer(); ?>/images/cms/progress_bar_bg.png) no-repeat;
            z-index: 2;
        }
        #main_progress_bar{
            z-index: 1;
            position: absolute;
            top: 0px;
            left: 0px;
            height: 100%;
            background-color: #0ca0f5;
        }
        
        #current_progress_indicator{
            font-size: 17px;
            margin: 10px;
        }
        #wizi_be_patient{
            margin: 15px 0px 9px 32px;
        }

    </style>
    <div id="wiziapp_activation_container">
        <div id="just_a_moment"></div>
        <p id="wizi_be_patient" class="text_label"><?php echo __('Please be patient while we are upgrading your app. It may take several minutes.', 'wiziapp');?></p>
        <div id="wizi_icon_wrapper">
            <div id="wizi_icon_processing"></div>
            <div id="current_progress_label" class="text_label"><?php echo __('Initializing...', 'wiziapp'); ?></div>
        </div>
        <div id="main_progress_bar_container">
            <div id="main_progress_bar"></div>
            <div id="main_progress_bar_bg"></div>
        </div>
        <p id="current_progress_indicator" class="text_label"></p>
        
        <p id="wiziapp_finalize_title" class="text_label"><?php echo __('Ready, if the page doesn\'t change in a couple of seconds click ', 'wiziapp'); ?><span id="finializing_activation"><?php echo __('here', 'wiziapp'); ?></span></p>
        
        <div id="error_activating" class="wiziapp_error hidden"><div class="icon"></div><div class="text"><?php echo __('There was an error loading the wizard, please contact support', 'wiziapp');?></div></div>
        <div id="internal_error" class="wiziapp_error hidden"><div class="icon"></div><div class="text"><?php echo __('Connection error. Please try again.,', 'wiziapp');?> <a href="javscript:void(0);" class="retry_processing"><?php echo __('retry', 'wiziapp'); ?></a></div></div>
        <div id="internal_error_2" class="wiziapp_error hidden"><div class="icon"></div><div class="text"><?php echo __('There were still errors contacting your server, please contact support', 'wiziapp');?></div></div>
        <div id="error_network" class="wiziapp_error hidden"><div class="icon"></div><div class="text"><?php echo __('Connection error. Please try again.', 'wiziapp');?> <a href="javscript:void(0);" class="retry_processing"><?php echo __('retry', 'wiziapp'); ?></a></div></div>

        <div id="error_upgrading_db" class="wiziapp_error hidden"><div class="icon"></div><div class="text"><?php echo __('There was a problem upgrading, please contact support', 'wiziapp');?></div></div>
        <div id="error_upgrading_config" class="wiziapp_error hidden"><div class="icon"></div><div class="text"><?php echo __('There was a problem updating, please contact support', 'wiziapp');?></div></div>
    </div>

    <script type="text/javascript">
        var progressTimer = null;
        var progressWait = 30;
        var upgrade_step = 0;
        var upgrade_steps = [requestDatabaseUpgrade, requestConfigurationUpgrade, requestFinalizingProcessing];

        
        jQuery(document).ready(function(){
            wiziappRegisterAjaxErrorHandler();

            // Register the report an error button
            jQuery('#wiziapp_report_problem').click(function(event){
                event.preventDefault();
                var $el = jQuery(this).parents(".wiziapp_errors_container").find(".report_container");

                var data = {};
                jQuery.each(jQuery('.wiziapp_error'), function(index, error){
                    var text = jQuery(error).text();
                    if ( text.indexOf('.') !== -1 ){
                        text = text.substr(0, text.indexOf('.'));
                    }
                    data[index] = text;
                });
                var params = {
                    action: 'wiziapp_report_issue',
                    data: jQuery.param(data, true)
                };

                $el.load(ajaxurl, params, function(){
                    var $mainEl = jQuery(".wiziapp_errors_container");
                    $mainEl
                            .find(".errors_container").hide().end()
                            .find(".report_container").show().end();
                    $mainEl = null;
                });

                var $el = null;
                return false;
            });

            jQuery(".retry_processing").bind("click", retryRequest);
            // Start sending requests to generate content till we are getting a flag showing we are done
            startProcessing();
        });
        
        function retryRequest(event){
            event.preventDefault();
            var $el = jQuery(this);
            
            $el.parents('.wiziapp_error').hide();
            
            var request = $el.parents('.wiziapp_error').data('reqObj');
            /**request.error = function(req, error){
                retryingFailed();
            };*/
            delete request.context;
            delete request.accepts;

            jQuery.ajax(request);

            $el = null;
            return false;
        }

        function retryingFailed(req, error){
            jQuery("#internal_error_2").show();
        }

        function startProcessing(){
            upgrade_steps[upgrade_step].call();
        }

        function wiziappRegisterAjaxErrorHandler(){
            jQuery.ajaxSetup({
                timeout: 60*1000,
                error:function(req, error){
                    clearTimeout(progressTimer);
                    if (error == 'timeout'){
                        //jQuery("#internal_error").data('reqObj', this).show();
                        startProcessing();
                    } else if(req.status == 0){
                        jQuery("#error_network").data('reqObj', this).show();
                    } else if(req.status == 404){
                        jQuery("#error_activating").show();
                    } else if(req.status == 500){
                        // Check if this is our request..
                        var data = jQuery.parseJSON(req.responseText);
                        if (data){
                            var requestParams = this.data.split('&');
                            var itemsStr = requestParams[requestParams.length - 1].split('=')[1];
                            
                            var neededAction = '';
                            var type = '';
                            var failed = '';
                            
                            if (typeof(data.post) == 'undefined'){
                                itemsStr = itemsStr.replace(data.page, '');
                                neededAction = 'wiziapp_batch_pages_processing';
                                type = 'pages';
                                failed = data.page;
                            } else {
                                itemsStr = itemsStr.replace(data.post, '');
                                neededAction = 'wiziapp_batch_posts_processing';
                                type = 'posts';
                                failed = data.post;
                            }
                            
                            var items = unescape(itemsStr).split(',');
                            var noErrorItems = cleanArray(items);
                            
                            if (noErrorItems.length > 0){
                                var params = {
                                    action: neededAction,  
                                    failed: failed  
                                };
                                params[type] = noErrorItems.join(',');
                                
                                if (type == 'posts'){
                                    jQuery.post(ajaxurl, params, handlePostProcessing, 'json');     
                                } else if (type == 'pages'){
                                    jQuery.post(ajaxurl, params, handlePageProcessing, 'json');         
                                }
                            } else {
                                // Maybe there are more items in the queue
                                startProcessing();
                            }    
                        } else {
                            //jQuery("#internal_error").data('reqObj', this).show();
                            // Don't show the errors, just try to continue
                            startProcessing();
                        }   
                    } else if(error == 'parsererror'){
                        jQuery("#error_activating").show();  
                    /**} else if(error == 'timeout'){
                        //jQuery("#error_network").show();
                        jQuery("#internal_error").data('reqObj', this).show();     
                        */
                    } else {
                        jQuery("#error_activating").show();
                    }
                }
            });  
        };
        
        function cleanArray(arr){
            var newArr = new Array();
            
            for (k in arr) {
                if (arr.hasOwnProperty(k)){
                    if(arr[k]) 
                        newArr.push(arr[k]);           
                }
            }
            
            return newArr;
        }
        
        function requestDatabaseUpgrade(){
            var params = {
                action: 'wiziapp_upgrade_database'
            };
            
            jQuery.post(ajaxurl, params, handleDatabaseUpgrade, 'json');
            progressTimer = setTimeout(updateProgressBarByTimer, 1000 * progressWait);
        };
        
        function updateProgressBarByTimer(){
            var current = jQuery("#current_progress_indicator").text(); 
            
            if (current.length == 0){
                current = 0;
            } else if (current.indexOf('%') != -1){
                current.replace('%', '');
            }
            
            current = parseInt(current) + 1;
            
            if (current != 100){
                jQuery("#main_progress_bar").css('width', current + '%');
                jQuery("#current_progress_indicator").text(current + '%');        

                // Repeat only once
                //progressTimer = setTimeout(updateProgressBarByTimer, 1000*progressWait);   
            }
        };
        
        function updateProgressBar(){
            clearTimeout(progressTimer);
            progressTimer = null;
            
            var total_items = upgrade_steps.length
            
            var done = ((upgrade_step) / total_items) * 100;
            
            if (upgrade_step < upgrade_steps.length){
                jQuery("#current_progress_label").text("<?php echo __('Upgrading...', 'wiziapp'); ?>");
            } else {
                jQuery("#current_progress_label").text("<?php echo __('Finalizing...', 'wiziapp'); ?>");
            }
            
            jQuery("#main_progress_bar").css('width', done + '%');
            jQuery("#current_progress_indicator").text(Math.floor(done) + '%');
        };
        
        function handleDatabaseUpgrade(data){
            ++upgrade_step;
            // Update the progress bar
            updateProgressBar();
            if ( typeof(data) == 'undefined'  || !data ){
                // The request failed from some reason... skip it
                jQuery("#error_upgrading_db").show();
                return;
            }

            if (data.header.status){
                startProcessing();
            } else {
                jQuery("#error_upgrading_db").show();
            }
        }
        
        function requestConfigurationUpgrade(){
            var params = {
                action: 'wiziapp_upgrade_configuration'
            };
            
            jQuery.post(ajaxurl, params, handleConfigurationUpgrade, 'json');
            progressTimer = setTimeout(updateProgressBarByTimer, 1000 * progressWait);   
        };
        
        function handleConfigurationUpgrade(data){
            ++upgrade_step;
            // Update the progress bar
            updateProgressBar();
            if ( typeof(data) == 'undefined'  || !data ){
                // The request failed from some reason... skip it
                jQuery("#error_upgrading_config").show();
                return;
            }

            if (data.header.status){
                startProcessing();
            } else {
                jQuery("#error_upgrading_config").show();
            }
        }
        
        function requestFinalizingProcessing(){
            var params = {
                action: 'wiziapp_upgrading_finish'
            };
            
            jQuery.post(ajaxurl, params, handleFinalizingProcessing, 'json');
        };
        
        function handleFinalizingProcessing(data){
            ++upgrade_step;
            // Update the progress bar
            updateProgressBar();
            jQuery("#wiziapp_finalize_title").show();
            document.location.reload();
        }
    </script>
    <?php    
}