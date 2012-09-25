<?php

class WiziappSupportDisplay{
    private $checker = null;

    public function __construct(){
        $this->checker = new WiziappCompatibilitiesChecker();
    }

    public function __destruct(){
        $this->checker = null;
    }
    
    public function display(){
        /**
         * The display is depended on the app status
         * it needs to fitted to the rest of the menu
         * so if the app has finished the wizard, and the
         * blog is scanned, it needs to show the proper tabs
         * else it needs to show only the support tab
         */
        $configured = WiziappConfig::getInstance()->settings_done;

        if (isset($_GET['wiziapp_configured']) && $_GET['wiziapp_configured'] == 1){
            $configured = TRUE;
        }

        $showAllTabs = WiziappConfig::getInstance()->finished_processing && $configured;

        ?>
        <script src="http://cdn.jquerytools.org/1.2.5/all/jquery.tools.min.js"></script>
        <style>
        #wiziapp_container{
            background: #fff;
            min-height: 500px;
            position: relative;
        }
        #wiziapp_logo{
            float: left;
            margin-right: 5px;
        }

        #wiziapp_logo a{
            text-decoration: none;
        }

        #wiziapp_logo a img{
            border: 0px none;
        }
        #wiziapp_header{
            clear: both;
            height: 62px;
            width: 875px;
            padding: 15px 10px 25px;
            margin: 0px auto;
        }
        #wiziapp_container_content{
            width: 875px;
            margin: 0px auto;
        }
        #wiziapp_header ul{
            list-style: none;
            height: 48px;
            width: 679px;
            background: url(<?php echo WiziappConfig::getInstance()->getCdnServer(); ?>/images/cms/menu_shadow_line.jpg) no-repeat bottom center;
            margin: 14px auto 0px;
            padding: 0px;
            float: right;
        }

        #wiziapp_header li{
            background: url(<?php echo WiziappConfig::getInstance()->getCdnServer(); ?>/images/cms/Menu_Close_Tabe.png) no-repeat bottom center;
            width: 104px;
            height: 48px;
            display: inline-block;
            text-align: center;
            font-size: 14px;
        }

        #wiziapp_header li.active{
            background: url(<?php echo WiziappConfig::getInstance()->getCdnServer(); ?>/images/cms/Menu_open_tab.png) no-repeat bottom center;
            font-weight: bolder;
            position: relative;
            top: -4px;
        }
        #wiziapp_header li.active.single{
            top: 1px;
        }
        #wiziapp_header li a{
            color: #000;
            text-decoration: none;
            display: block;
            margin-top: 23px;
        }
        #wiziapp_header li.active a{
            margin-top: 18px;
        }
        #wiziapp_container .col{
            float: left;
        }
        #wiziapp_support_links{
            width: 200px;
            background: url(<?php echo WiziappConfig::getInstance()->getCdnServer(); ?>/images/cms/MyAccount_Shadow_center.jpg) no-repeat scroll left center transparent;
            padding: 0 0 0 40px;
            min-height: 334px;
            margin-left: 25px;
        }
        #wiziapp_container .clear{
            clear: both;
        }
        #wiziapp_support_table{

        }
        #wiziapp_container table{
            width: 607px;
            margin-top: 15px;
            border-collapse: collapse;
        }
        #wiziapp_container table td{
            color: #353535;
            width: 80px;
        }
        #wiziapp_container table thead{
            background: url(<?php echo WiziappConfig::getInstance()->getCdnServer(); ?>/images/generator/main_sprite.png) no-repeat scroll 0px -349px;
        }
        #wiziapp_container table thead th{
            font-weight: normal;
            height: 59px;
            width: 80px;
            text-align: center;
            padding-right: 10px;
        }
        #wiziapp_container table .v_first-col{
            width: 150px;
            text-align: left;
            padding-left: 10px;
        }
        #wiziapp_container table td.v_first-col{
            padding-left: 10px;
            color:#0fb3fb;
        }
        #wiziapp_container table tbody td{
            border-right: 2px #ffffff solid;
             height: 59px;
            text-align: center;
        }
        #wiziapp_container table tbody tr.v_odd td{
            background-color: #f0f0f0;
        }
        #wiziapp_container table .status_col{
            width: 30px;
        }
        #wiziapp_container table .status_col div{
            height: 100%;
            width: 17px;
            margin: 0px auto;
        }
        #wiziapp_container table .status_col .success{
            background: url(<?php echo WiziappConfig::getInstance()->getCdnServer(); ?>/images/cms/V_Icon.png) no-repeat left center;
        }
        #wiziapp_container table .status_col .failed{
            background: url(<?php echo WiziappConfig::getInstance()->getCdnServer(); ?>/images/cms/validetion_error_Icon.png) no-repeat left center;
        }
        #wiziapp_container table span.sep, #wiziapp_container table a{
            color:#0fb3fb;
        }
        
        #wiziapp_container .wiziapp_button{
            background: url(<?php echo WiziappConfig::getInstance()->getCdnServer(); ?>/images/cms/ReportBTN.png) no-repeat left center;
            width: 164px;
            height: 33px;
            line-height: 33px;
            color: #000;
            text-align: center;
            font-weight: bold;
            text-decoration: none;
            display: block;
            margin: 30px auto;
        }
        #wiziapp_container table tr.details{
            display: none;
        }
         #wiziapp_container .wiziapp_error{
            padding: 0px 10px 20px;
            position: static;
            background: none transparent;
             width: auto;
             height: auto;
             text-align: left;
         }
         .wiziapp_errors_container{
             display: none;
              z-index: 999;
         }
         #wiziapp_env_indicator{
             color: #0fb3fb;
             font-size: 12px;
             position: absolute;
             top: 0px;
             right: 25px;
         }
        </style>
        <script type="text/javascript">
            jQuery(document).ready(function($){
                $("#wiziapp_main_tabs a").click(function(event){
                    event.preventDefault();
                    top.document.location.replace('<?php echo get_admin_url();?>admin.php?page='+$(this).attr('rel'));
                    return false;
                });

                $("#wiziapp_container .retry").click(function(event){
                    event.preventDefault();
                    top.document.location.reload(true);
                    return false;
                });

                $("#wiziapp_container .details").click(function(event){
                    event.preventDefault();
                    var $el = jQuery(this).parents("tr:first").next("tr");

                    if ( $el.is(':visible') ){
                        $el.hide();
                    } else {
                        $el.show();
                    }
                    $el = null;
                    return false;
                });

                $("#wiziapp_container .wiziapp_errors_container").bind('closingReportForm', function(){
                    jQuery('.wiziapp_errors_container').data('overlay').close();
                });
            });
        </script>
        <div id="wiziapp_container">
            <div id="wiziapp_header">
            <div id="wiziapp_logo"><a href="#"><img src="<?php echo WiziappConfig::getInstance()->getCdnServer(); ?>/images/generator/main_logo.png" alt="Extend your reach" /></a></div>
                <ul id="wiziapp_main_tabs">
                    <?php if ( $showAllTabs ) { ?>
                        <?php if (WiziappConfig::getInstance()->app_live !== FALSE){ ?>
                            <li class="wiziapp_header_link">
                                <a rel="wiziapp_statistics_display" href="/cms/controlPanel/statistics">Statistics</a>
                            </li>
                    <?php } ?>
                    <li class="wiziapp_header_link">
                        <a rel="wiziapp_app_info_display" href="/cms/controlPanel/appInfo">App Info</a>
                    </li>
                    <li class="wiziapp_header_link">
                        <a rel="wiziapp_my_account_display" href="/cms/controlPanel/myAccount">My Account</a>
                    </li>
                    <?php } ?>
                    <li class="wiziapp_header_link active <?php echo ($showAllTabs) ? '' : 'single'; ?>">
                        <a rel="wiziapp_support_display" href="/cms/controlPanel/support">Support</a>
                    </li>
                </ul>
            </div>
            <div id="wiziapp_container_content">
                <div id="wiziapp_support_table" class="col">
                    <table border="0">
                        <thead>
                            <tr>
                                <th class="v_first-col">Requirement</th>
                                <th class="status_col">Status</th>
                                <th>Solution</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                echo $this->getStatusRow('WritingPermissions', 'Writing Permissions');
                                echo $this->getStatusRow('PhpGraphicRequirements', 'GD / ImageMagick', 'v_odd');
                                echo $this->getStatusRow('AllowUrlFopen', 'allow_url_fopen');
                            ?>
                        </tbody>
                    </table>
                    <div class="report_container wiziapp_errors_container"></div>
                </div>
                <div id="wiziapp_support_links" class="col">
                    <a href="http://wiziapp.com/support" target="_blank" id="wiziapp_faq_link" class="wiziapp_button">FAQ</a>
                    <a href="http://wiziapp.com/contact" target="_blank" id="wiziapp_contact_link" class="wiziapp_button">Contact Us</a>
                </div>
                <div class="clear"></div>
           </div>
            <div id="wiziapp_env_indicator">
                <?php echo WIZIAPP_ENV; ?> :: <?php echo WIZIAPP_VERSION; ?>
            </div>
        </div>
        <?php
    }
    private function getStatusRow($check_name, $display_name, $extra_class = ''){
        $desc = '';
        $passed = TRUE;

        $testMethod = 'test'.$check_name;
        $test = $this->checker->{$testMethod}();
        if ( WiziappError::isError($test) ){
            $passed = FALSE;
            $desc .= $test->getHTML();
        }

        $statusClass = ($passed) ? 'success' : 'failed';
        $rowButtons = ($passed) ? '' : '<a href="" class="details">Details</a><span class="sep">&nbsp;|&nbsp;</span><a href="" class="retry">Retry</a>';

        $html = "<tr class=\"{$extra_class}\">
                            <td class=\"v_first-col\">{$display_name}</td>
                            <td class=\"status_col\">
                                <div class=\"{$statusClass}\"></div>
                            </td>
                            <td>{$rowButtons}</td>
                        </tr>";
        if ( !empty($desc) ){
            $html .= '<tr class="details ' . $extra_class . '"><td colspan="3">' . $desc . '</td></tr>';
        }
        return $html;
    }
}