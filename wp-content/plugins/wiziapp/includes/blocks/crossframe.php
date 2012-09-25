<?php
/**
* @package WiziappWordpressPlugin
* @subpackage AdminDisplay
* @author comobix.com plugins@comobix.com
*/    

/**
* Used to get to enable communication between our SystemControl services (Plugin Dashboard / Generator and so on)
* and the cms admin ui. 
* By using cms -> iframe (systemControl) -> iframe(cms) we can avoid the security problems.
*/
function wiziapp_getCrossFrameHandler(){
    $handler = isset($_GET['report']) ? 'WIZIAPP_REPORT_HANDLER' : 'WIZIAPP_HANDLER';
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
    <title>WiziApp</title>
    <script type="text/javascript">
    if (window.location.search.length > 1) {
        if (window.parent.parent.<?php echo $handler; ?>) {
            window.parent.parent.<?php echo $handler; ?>.handleRequest(window.location.search.substring(1));
        }
    }
    </script>
    </head>
    <body>
    </body>
    </html>
<?php
    exit();
}