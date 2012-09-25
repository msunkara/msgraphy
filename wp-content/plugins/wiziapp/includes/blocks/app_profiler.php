<?php if (!defined('WP_WIZIAPP_BASE')) exit();
// $_SERVER['SERVER_NAME']
/**
* Basic profiler log class for the wordpress plugin
*
* @package WiziappWordpressPlugin
* @subpackage Utils
* @author comobix.com plugins@comobix.com
*
* @todo Add log files rotation management
*/

class WiziappProfiler {
    /**
    * Is the log enabled?
    *
    * @var boolean
    */
    var $enabled = WP_WIZIAPP_PROFILER;

    /**
    * writes a log message to the log file
    *
    * The messages sent to this method will be filtered according to their level
    * if the level meets the threshold and the logging is enabled the message
    * will be written to a log file. The method also receives the component
    * related to this log message to ease the reading of the log file itself
    * If you want to keep your sanity make sure to send this "optional" parameter
    *
    * @param string $msg The log message
    * @param string $component The component related to this message
    * @return boolean
    */
    function write($msg, $component = '') {
        if ($this->enabled === FALSE) {
            return FALSE;
        }
        $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;

        $message  = '';
        $filepath = $path . 'profiler.log.php';
        // Prevent direct access to the log, to avoid security issues
        if (!file_exists($filepath)){
            $message .= "<"."?php if (!defined('WP_WIZIAPP_BASE')) exit(); ?".">\n\n";
        }

        // If we can't open the file for appending there isn't much we can do
        if (!$fp = @fopen($filepath, 'ab')){
            return FALSE;
        }

        $date = date('Y-m-d H:i:s:u');
        $message .= "[{$date}][$component]$msg\n";

        flock($fp, LOCK_EX);
        fwrite($fp, $message);
        flock($fp, LOCK_UN);
        fclose($fp);

        @chmod($filepath, 0666);
        return TRUE;
    }
}

/**
* One logger to rule them all...
* @global WiziappLog $GLOBALS['WiziappLog']
*/
if (!isset($GLOBALS['WiziappProfiler'])) {
    // Initiate the Wiziapp Login Object
    $GLOBALS['WiziappProfiler'] = new WiziappProfiler() ;
}