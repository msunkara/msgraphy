<?php

/**
* @package WiziappWordpressPlugin
* @subpackage Core
* @author comobix.com plugins@comobix.com
*/
add_action('wp_ajax_wiziapp_batch_posts_processing', 'wiziapp_batch_posts_processing'); 
add_action('wp_ajax_wiziapp_batch_process_pages', 'wiziapp_batch_process_pages');
add_action('wp_ajax_wiziapp_batch_processing_finish', 'wiziapp_batch_processing_finish');
add_action('wp_ajax_wiziapp_report_issue', 'wiziapp_report_issue');

/**
* wiziappBatchShutdown
* 
* The last line of defense against the fatal errors that might be caused by external plugins
* This methods is registered in the batch processing function, and will handle situations when the 
* batch script ended due to a fatal error by alerting on the error to the client
* 
*/
function wiziappBatchShutdown() { 
    $error = error_get_last(); 
    if ($error['type'] == 1) { 
        if(isset($GLOBALS['wiziapp_post']) && $GLOBALS['wiziapp_post']){
            ob_end_clean();
            
            $header = array(
                'action' => 'batch_shutdown',
                'status' => FALSE,
                'code' => 500,
                'message' => 'Unable to process post ' . $GLOBALS['wiziapp_post'],
            );

            header("HTTP/1.0 200 OK");
            echo json_encode(array('header' => $header, 'post' => $GLOBALS['wiziapp_post']));
            exit();
        } elseif(isset($GLOBALS['wiziapp_page']) && $GLOBALS['wiziapp_page']){
            ob_end_clean();
            
            $header = array(
                'action' => 'batch_shutdown',
                'status' => FALSE,
                'code' => 500,
                'message' => 'Unable to process page ' . $GLOBALS['wiziapp_page'],
            );

            header("HTTP/1.0 200 OK");
            echo json_encode(array('header' => $header, 'page' => $GLOBALS['wiziapp_page']));
            exit();
        }
    } 
} 

function wiziapp_batch_posts_processing() {
    $GLOBALS['WiziappLog']->write('info', "Got a request to process posts as a batch: " . print_r($_POST, TRUE), 
                                        "post_install.wiziapp_batch_posts_processing");
                                        
    global $wpdb; 
    $status = TRUE;
    $message = '';
    
    if (!isset($_POST['posts'])){
        $status = FALSE;
        $message = 'incorrect usage';
    } else {
        ob_start();   
        ini_set('display_errors', 0);
        register_shutdown_function('wiziappBatchShutdown');  
        
        $postsIds = explode(',', $_POST['posts']);
        foreach($postsIds as $id){
            $GLOBALS['WiziappLog']->write('info', "Processing post: {$id} inside the batch", 
                                        "post_install.wiziapp_batch_posts_processing");
            $GLOBALS['wiziapp_post'] = $id;  
                
            wiziapp_save_post($id);

            $GLOBALS['WiziappLog']->write('info', "Finished processing post: {$id} inside the batch",
                                        "post_install.wiziapp_batch_posts_processing");
        }   
    }
    
    $header = array(
        'action' => 'batch_process_posts',
        'status' => $status,
        'code' => ($status) ? 200 : 500,
        'message' => $message,
    );
         
    $GLOBALS['WiziappLog']->write('debug', "Finished processing the requested post batch, going to return: " . print_r($_POST['posts'], TRUE).' '.print_r($header, TRUE),
                                        "post_install.wiziapp_batch_posts_processing");   
                                        
    echo json_encode(array('header' => $header));
    exit();
}

function wiziapp_batch_process_pages() {
    $GLOBALS['WiziappLog']->write('info', "Got a request to process pages as a batch: " . print_r($_POST, TRUE), 
                                        "post_install.wiziapp_batch_process_pages");
    global $wpdb; 
    $status = TRUE;
    $message = '';
    
    if (!isset($_POST['pages'])){
        $status = FALSE;
        $message = 'incorrect usage';
    } else {
        ob_start();   
        ini_set('display_errors', 0);
        register_shutdown_function('wiziappBatchShutdown');  
        
        $pagesIds = explode(',', $_POST['pages']);
        foreach($pagesIds as $id){
            $GLOBALS['WiziappLog']->write('info', "Processing page: {$id} inside the batch", 
                                        "post_install.wiziapp_batch_process_pages");
            $GLOBALS['wiziapp_page'] = $id;  
                
            wiziapp_save_page($id);

            $GLOBALS['WiziappLog']->write('info', "Finished processing page: {$id} inside the batch",
                                        "post_install.wiziapp_batch_process_pages");
        }   
    }
    
    $header = array(
        'action' => 'batch_process_posts',
        'status' => $status,
        'code' => ($status) ? 200 : 500,
        'message' => $message,
    );
            
    $GLOBALS['WiziappLog']->write('debug', "Finished processing the requested page batch:" . print_r($_POST['pages'], TRUE) .", going to return: " . print_r($header, TRUE),
                                        "post_install.wiziapp_batch_process_pages");
                                                            
    echo json_encode(array('header' => $header));
    exit();
}

function wiziapp_batch_processing_finish(){
    $GLOBALS['WiziappLog']->write('debug', "The batch processing is finished, marking as finished", 
                                        "post_install.wiziapp_batch_processing_finish");

    // Send the profile again, and allow it to fail since it's just an update
    $cms = new WiziappCms();
    $cms->activate();



    // Mark the processing as finished

    WiziappConfig::getInstance()->finished_processing = TRUE;

    $status = TRUE;
    
    $header = array(
        'action' => 'batch_processing_finish',
        'status' => $status,
        'code' => ($status) ? 200 : 500,
        'message' => '',
    );
            
    $GLOBALS['WiziappLog']->write('debug', "The batch processing is finished, marked as finished", 
                                        "post_install.wiziapp_batch_processing_finish");
                                                            
    echo json_encode(array('header' => $header));
    exit;
}

function wiziapp_report_issue(){
    $report = new WiziappIssueReporter($_POST['data']);

    ob_start();
    $report->render();
    $content = ob_get_contents();

    ob_end_clean();
    echo $content;
    exit();
}

function wiziapp_activate_display(){
    global $wpdb;

    // Test for compatibilities issues with this installation
    $checker = new WiziappCompatibilitiesChecker();
    $errorsHtml = $checker->scanningTestAsHtml();

    $querystr = "
        SELECT DISTINCT(wposts.id)
        FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta
        WHERE wposts.ID not in (SELECT post_id 
                                FROM $wpdb->postmeta 
                                WHERE meta_key = 'wiziapp_processed' AND meta_value = '1')
        AND wposts.post_status = 'publish' 
        AND wposts.post_type = 'post' 
        ORDER BY wposts.post_date DESC
        LIMIT 0, 50
     ";

     $posts = $wpdb->get_results($querystr, OBJECT);
     $numposts = count($posts);
     $postsIds = array();
     foreach($posts as $post){
         $postsIds[] = $post->id;
     }
     $GLOBALS['WiziappLog']->write('info', "Going to process the following posts: " . print_r($postsIds, TRUE), 
                                        "post_install.wiziapp_activate_display");
     
     $pagesQuery = "
        SELECT DISTINCT(wposts.id)
        FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta
        WHERE wposts.ID not in (SELECT post_id 
                                FROM $wpdb->postmeta 
                                WHERE meta_key = 'wiziapp_processed' AND meta_value = '1')
        AND wposts.post_status = 'publish' 
        AND wposts.post_type = 'page' 
        ORDER BY wposts.post_date DESC
        LIMIT 0, 20
     ";

     $pages = $wpdb->get_results($pagesQuery, OBJECT);
     $numOfPages = count($pages);
     $pagesIds = array();
     
     foreach($pages as $page){
         // Get the parent
         $shouldAdd = TRUE;

         if (isset($page->post_parent)) {
             $parentId = (int)$page->post_parent;

             if ($parentId > 0){
                 $parent = get_page($parentId);
                 if ($parent->post_status != 'publish'){
                     $shouldAdd = FALSE;
                 }
             }
         }
         
         if ($shouldAdd){
            $pagesIds[] = $page->id;
         }
     }
     
     $GLOBALS['WiziappLog']->write('info', "Going to process the following posts: " . print_r($pagesIds, TRUE), 
                                        "post_install.wiziapp_activate_display");
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
    <?php echo "{$errorsHtml}" ?>
    <div id="wiziapp_activation_container">
        <div id="just_a_moment"></div>
        <p id="wizi_be_patient" class="text_label"><?php echo __('Please be patient while we generate your app. It may take several minutes.', 'wiziapp');?></p>
        <div id="wizi_icon_wrapper">
            <div id="wizi_icon_processing"></div>
            <div id="current_progress_label" class="text_label"><?php echo __('Initializing...', 'wiziapp'); ?></div>
        </div>
        <div id="main_progress_bar_container">
            <div id="main_progress_bar"></div>
            <div id="main_progress_bar_bg"></div>
        </div>
        <p id="current_progress_indicator" class="text_label"></p>
        
        <p id="wiziapp_finalize_title" class="text_label"><?php echo __('Ready, if the wizard doesn\'t load itself in a couple of seconds click ', 'wiziapp'); ?><span id="finializing_activation"><?php echo __('here', 'wiziapp'); ?></span></p>
        
        <div id="error_activating" class="wiziapp_errors_container s_container hidden">
            <div class="errors">
                <div class="wiziapp_error">
                    <?php echo __('There was an error loading the wizard, please contact support', 'wiziapp');?>
                </div>
            </div>
        </div>
        <div id="internal_error" class="wiziapp_errors_container s_container hidden">
            <div class="errors">
                <div class="wiziapp_error"><?php echo __('Connection error. Please try again.,', 'wiziapp');?></div>
                <div class="buttons">
                    <a href="javscript:void(0);" class="retry_processing"><?php echo __('retry', 'wiziapp'); ?></a>
                </div>
           </div>
        </div>
        <div id="internal_error_2" class="wiziapp_errors_container s_container hidden">
            <div class="errors">
                <div class="wiziapp_error">
                    <?php echo __('There were still errors contacting your server, please contact support', 'wiziapp');?>
                </div>
            </div>
        </div>
        <div id="error_network" class="wiziapp_errors_container s_container hidden">
            <div class="errors">
                <div class="wiziapp_error"><?php echo __('Connection error. Please try again.', 'wiziapp');?></div>
            </div>
            <div class="buttons">
                <a href="javscript:void(0);" class="retry_processing"><?php echo __('retry', 'wiziapp'); ?></a>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var can_run = <?php echo (empty($errorsHtml)) ? 'true' :'false'; ?>;
        var got_critical_errors = <?php echo ($checker->foundCriticalIssues()) ? 'true' :'false'; ?>;
        var post_ids = [<?php echo implode(',', $postsIds); ?>];
        var page_ids = [<?php echo implode(',', $pagesIds); ?>];
        //var batch_size = <?php echo WiziappConfig::getInstance()->post_processing_batch_size; ?>;
        var batch_size = 1;
        var profile_step = <?php echo (WiziappConfig::getInstance()->finished_processing) ? 0 : 1; ?>;
        
        var progressTimer = null;
        var progressWait = 30;
        
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
            if ( can_run ){
                startProcessing();
            } else {
                // Show the overlay
                 var $box = jQuery('#wiziapp_compatibilities_errors');

                var overlayParams = {
                    top: 100,
                    left: (screen.width / 2) - ($box.outerWidth() / 2),
                    /**mask: {
                        color: '#444444',
                        loadSpeed: 100,
                        opacity: 0.9
                    },*/
                    onClose: function(){
                        jQuery("#wiziapp_error_mask").hide();
                    },
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
                    },
                    // disable this for modal dialog-type of overlays
                    closeOnClick: false,
                    closeOnEsc: false,
                    //onClose: startProcessing,
                    // load it immediately after the construction
                    load: true
                };
                if ( !got_critical_errors ){
                    overlayParams.onClose = function(){
                        jQuery("#wiziapp_error_mask").hide();
                        startProcessing();
                    };
                }
                $box.overlay(overlayParams);
            }
        });
        
        function retryRequest(event){
            event.preventDefault();
            var $el = jQuery(this);
            
            $el.parents('.wiziapp_errors_container').hide();
            
            var request = $el.parents('.wiziapp_errors_container').data('reqObj');
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
            if (page_ids.length > 0){
                requestPageProcessing();
            } else if (post_ids.length > 0){
                requestPostProcessing();
            } else {
                requestFinalizingProcessing();
            }
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
        
        function requestPostProcessing(){
            var posts = post_ids.splice(0, batch_size);
            
            var params = {
                action: 'wiziapp_batch_posts_processing',  
                posts: posts.join(',')  
            };
            
            jQuery.post(ajaxurl, params, handlePostProcessing, 'json');
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
            
            var total_items = <?php echo $numposts + $numOfPages; // Added one for the profile activation ?>;
            total_items += profile_step;
            
            var done = ((post_ids.length + page_ids.length + profile_step) / total_items) * 100;
            var left = 100 - done;
            
            if (page_ids.length > 0){
                jQuery("#current_progress_label").text("<?php echo __('Initializing...', 'wiziapp'); ?>");
            } else if ( post_ids.length > 0 ){
                jQuery("#current_progress_label").text("<?php echo __('Generating...', 'wiziapp'); ?>");
            } else {
                jQuery("#current_progress_label").text("<?php echo __('Finalizing...', 'wiziapp'); ?>");
            }
            
            jQuery("#main_progress_bar").css('width', left + '%');
            jQuery("#current_progress_indicator").text(Math.floor(left) + '%');    
        };
        
        function handlePostProcessing(data){
            // Update the progress bar
            updateProgressBar();
            
            if ( typeof(data) == 'undefined'  || !data ){
                // The request failed from some reason... skip it
                startProcessing();
                return;
            }

            if (data.header.status){
                if (post_ids.length == 0){
                    requestFinalizingProcessing();
                } else {
                    requestPostProcessing();   
                }  
            } else {
                var params = this.data.split('&');
                var postsStr = params[1].split('=')[1].replace(data.post, '');
                var posts = unescape(postsStr).split(',');
                var noErrorPosts = cleanArray(posts);
                
                /**
                * Inform the server on the failure so we will not try to scan this post again 
                * when entering the page again
                */
                if (noErrorPosts.length > 0){
                    var params = {
                        action: 'wiziapp_batch_posts_processing',  
                        posts: noErrorPosts.join(','),
                        failed_post: data.post  
                    };
                    jQuery.post(ajaxurl, params, handlePostProcessing, 'json');     
                } else {
                    // Maybe there are more items in the queue
                    startProcessing();
                }    
            }
        }
        
        function requestPageProcessing(){
            var pages = page_ids.splice(0, batch_size);
            
            var params = {
                action: 'wiziapp_batch_process_pages',  
                pages: pages.join(',')  
            };
            
            jQuery.post(ajaxurl, params, handlePageProcessing, 'json');
            progressTimer = setTimeout(updateProgressBarByTimer, 1000 * progressWait);   
        };
        
        function handlePageProcessing(data){
            // Update the progress bar
            updateProgressBar();

            if ( typeof(data) == 'undefined'  || !data ){
                // The request failed from some reason... skip it
                startProcessing();
                return;
            }

            if (data.header.status){
                if (page_ids.length == 0){
                    requestPostProcessing();
                } else {
                    requestPageProcessing();   
                }  
            } else {
                var params = this.data.split('&');
                var pagesStr = params[1].split('=')[1].replace(data.page, '');
                var pages = unescape(pagesStr).split(',');
                var noErrorPages = cleanArray(pages);
                
                /**
                * Inform the server on the failure so we will not try to scan this page again 
                * when entering this page again
                */
                if (noErrorPages.length > 0){
                    var params = {
                        action: 'wiziapp_batch_pages_processing',  
                        pages: noErrorPages.join(','),
                        failed_page: data.page  
                    };
                    jQuery.post(ajaxurl, params, requestPageProcessing, 'json');     
                } else {
                    // Maybe there are more items in the queue
                    startProcessing();
                }    
            }
        }
        
        function requestFinalizingProcessing(){
            var params = {
                action: 'wiziapp_batch_processing_finish'    
            };
            
            jQuery.post(ajaxurl, params, handleFinalizingProcessing, 'json');
        };
        
        function handleFinalizingProcessing(data){
            if (data.header.status){
                --profile_step;
                // Update the progress bar
                updateProgressBar();
                jQuery("#wiziapp_finalize_title").show();
                document.location.reload();
            } else {
                // There was an error??
                jQuery("#error_activating").show();
            }
        }

        // Google analytics - Should *always* be in the end
        var _gaq = _gaq || [];
        if ( typeof(_gaq.splice) == 'function' ){
			_gaq.splice(0, _gaq.length);
		}

        _gaq.push(['_setAccount', 'UA-22328620-1']);       // TODO replace url with env param
        _gaq.push(['_setDomainName', '.apptelecom.com']); //$settings['api_server']   //.replace('api.', '.')
        _gaq.push(['_setAllowLinker', true]);
        _gaq.push(['_setAllowHash', false]);
        _gaq.push(['_trackPageview', '/StartScanningGoal.php']);

        (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();
    </script>
    <?php    
}