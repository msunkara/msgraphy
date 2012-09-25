<?php

class WiziappIssueReporter{
    private $data = '';

    public function __construct($data){
        if ( !empty($data) ){
            $this->data = $data;
        }
    }
    
    public function render(){
        // Load the form from the api services inside an iframe

        // show the iframe as an overlay
        $httpProtocol = 'https';
        $iframeSrc = $httpProtocol . '://' . WiziappConfig::getInstance()->api_server . '/cms/reportIssue/?v='.WIZIAPP_P_VERSION;
        $iframeSrc .= '&d='.urlencode($this->data) . '&c=' . urlencode(get_bloginfo('url') . "/?wiziapp/system/frame&report=1");
        ?>
            <?php // <script src="http://cdn.jquerytools.org/1.2.5/all/jquery.tools.min.js"></script> ?>
            <script type="text/javascript" src="<?php echo get_bloginfo('url') . '/wp-content/plugins/wiziapp/themes/admin/report_issue.js'; ?>"></script>
            <style>
                #wiziapp_report_issue_container{
                    height: 420px;
                    margin: 0 auto;
                    width: 530px;
                }
                #wiziapp_report_issue_container iframe{
                    width: 100%;
                    height: 100%;
                    border: 0px none;
                    overflow:hidden;
                }
            </style>
            <div id="wiziapp_report_issue_container">
                <iframe id="wiziapp_report_frame" src="<?php echo $iframeSrc; ?>" frameborder="0" data-origin="<?php echo "{$httpProtocol}://" . WiziappConfig::getInstance()->api_server ?>"></iframe>
            </div>
        <?php
    }
}