<?php
/**
* @package WiziappWordpressPlugin
* @subpackage AdminDisplay
* @author comobix.com plugins@comobix.com
*/

function wiziapp_generator_display(){
    // Before opening this display get a one time usage token
    $response = wiziapp_http_request(array(), '/generator/getToken?app_id=' . WiziappConfig::getInstance()->app_id, 'GET');
    $tokenResponse = json_decode($response['body'], TRUE);

    $iframeId = 'wiziapp_generator' . time();
    if (!$tokenResponse['header']['status']) {
        // There was a problem with the token
        echo '<div class="error">' . $tokenResponse['header']['message'] . '</div>';
    } else {
        $token = $tokenResponse['token'];
        $httpProtocol = 'https';
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
            background: url(<?php echo WiziappConfig::getInstance()->getCdnServer(); ?>/images/simulator/phone.png) no-repeat scroll 8px 8px;
        }
        #wiziappBoxWrapper.sim_loaded{
            background-image: none;
        }
        #wiziappBoxWrapper #loading_placeholder{
            position: absolute;
            color:#E0E0E0;
            font-weight:bold;
            height:60px;
            top: 260px;
            left: 170px;
            width:75px;
            z-index: 0;
        }
        #wiziappBoxWrapper.sim_loaded #loading_placeholder{
            display: none;
        }
        #wiziappBoxWrapper iframe{
            visibility: hidden;
        }
        #wiziappBoxWrapper.sim_loaded iframe{
            visibility: visible;
        }
        #wiziapp_generator_container{
            background: #fff;
        }
        .processing_modal{
            background: url(<?php echo WiziappConfig::getInstance()->getCdnServer(); ?>/images/generator/Pament_Prossing_Lightbox.png) no-repeat top left;
            display:none;
            height: 70px;
            padding: 25px 35px;
            width: 426px;
        }
        #publish_modal .processing_message{
            font-size: 17px;
        }
        #publish_modal .loading_indicator{
            margin: 8px auto 2px;
        }
        #create_account_modal_close{
            display: none;
            clear: both;
            float: none;
        }
        .processing_modal .error{
            margin: 0px;
            width: 407px;
        }
        
        .processing_message{
            color: #000000;
            font-size: 18px;
            font-family: arial;
            margin: 2px 0;
            padding-left: 20px;
        }
        
        .processing_modal .loading_indicator{
            background: url(<?php echo WiziappConfig::getInstance()->getCdnServer(); ?>/images/generator/lightgrey_counter.gif) no-repeat;
            width: 35px;
            height: 35px;
            margin: 2px auto;
        }
        #general_error_modal{
            z-index: 999;
        }

        </style>
        <div id="wiziapp_generator_container">
            <?php 
                //$iframeSrc = 'http://'.wiziapp_getApiServer().'/generator?t='.$token; 
                //$iframeSrc = $httpProtocol.'://'.wiziapp_getServicesServer().'/generator?t='.$token;
                $iframeSrc = $httpProtocol.'://'.WiziappConfig::getInstance()->api_server.'/generator/index/'.$token.'?v='.WIZIAPP_P_VERSION;
            ?>
            <script type="text/javascript">
                var WIZIAPP_HANDLER = (function(){
                    jQuery(document).ready(function(){
                        jQuery('.report_issue').click(reportIssue);
                        jQuery('.retry_processing').click(retryProcessing);

                        jQuery('#general_error_modal').bind('closingReportForm', function(){
                            jQuery(this).addClass('s_container');
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

                    var actions = {
                        informErrorProcessing: function(params){
                            var $box = jQuery('#'+params.el);
                            $box
                                .find('.processing_message').hide().end()
                                .find('.loading_indicator').hide().end()
                                .find('.error').text(params.message).show().end()
                                .find('.close').show().end();
                            
                            $box = null;   
                        },
                        closeProcessing: function(params){
                            jQuery('#'+params.el).data("overlay").close();                             
                            if (typeof(params.reload) != 'undefined'){
                                if (params.reload == 1){
                                    if (typeof(params.qs) != 'undefined'){
                                        var href = top.location.href;
                                        var seperator = '?';
                                        if (href.indexOf('?')) {
                                            seperator = '&';
                                        }
                                        href += seperator + unescape(params.qs);    
                                        top.location.replace(href);    
                                    } else {
                                        top.location.reload(true);
                                    }
                                }
                            }
                            
                            if ( typeof(params.resizeTo) != 'undefined' ){
                                actions.resizeGeneratorIframe({height: params.resizeTo});
                            }
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
                                        color: '#444444',
                                        loadSpeed: 200,
                                        opacity: 0.9
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
                                .find('.loading_indicator').show().end()
                                .find('.close').hide().end()
                                .find('.processing_message').show().end();
                                
                            if (!$box.data("overlay")){
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
                            url = url + '&rnd=' + Math.floor(Math.random()*999999);
                            var $box = jQuery("#wiziappBoxWrapper");
                            if ($box.length == 0){
                                $box = jQuery("<div id='wiziappBoxWrapper'><div class='close overlay_close'></div><div id='loading_placeholder'>Loading...</div><iframe id='wiziappBox'></iframe>");
                                $box.find("iframe").attr('src', url+"&preview=1").unbind('load').bind('load', function(){
                                    jQuery("#wiziappBoxWrapper").addClass('sim_loaded');
                                });
                                
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
                            jQuery("#<?php echo $iframeId; ?>").css({
                                'height': (parseInt(params.height) + 50) + 'px'
                            });
                        }                                                      
                    };

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
                    
                    return {
                        handleRequest: function(q){
                            var paramsArray = q.split('&');
                            var params = {};
                            for (var i = 0; i < paramsArray.length; ++i) {
                                var parts = paramsArray[i].split('=');
                                params[parts[0]] = decodeURIComponent(parts[1]);
                            }
                            if (typeof(actions[params.action]) == "function"){
                                actions[params.action](params);
                            }
                            params = q = paramsArray = null;
                        }
                    };                    
                })();
                
                jQuery(document).ready(function($){
                    var $iframe = $("<iframe frameborder='0'>");
                    $("#wiziapp_generator_container").prepend($iframe);
                    $iframe.css({
                        'overflow': 'hidden',
                        'width': '100%',
                        'height': '1000px', 
                        'border': '0px none'
                    }).attr({
                        'src': "<?php echo $iframeSrc; ?>",
                        'frameborder': '0',
                        'id': '<?php echo $iframeId; ?>'
                    });
                });
            </script>
        </div>

        <div class="hidden wiziapp_errors_container s_container" id="general_error_modal">
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

        <div class="processing_modal" id="create_account_modal">
            <p class="processing_message">Please wait while we place your order...</p>
            <div class="loading_indicator"></div>
            <p class="error" class="errorMessage hidden"></p>
            <a class="close hidden" href="javascript:void(0);">Go back</a>
        </div>
        
        <div class="processing_modal" id="publish_modal">
            <p class="processing_message">Please wait while we are processing your request...</p>
            <div class="loading_indicator"></div>
            <p class="error" class="errorMessage hidden"></p>
            <a class="close hidden" href="javascript:void(0);">Go back</a>
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