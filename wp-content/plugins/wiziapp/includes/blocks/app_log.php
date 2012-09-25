<?php 

if (!defined('WP_WIZIAPP_BASE')) 
    exit();
    
/**
* Basic log class for the wordpress plugin
*
* This log class can help us trace runtime information,
* from debugging to errors this class supports erros, debug, info, and all
* usually all logging will be disabled, but in case the problem comes knocking
* on our doors we can enable the Log and see whats up in every step of the way.
*
* @package WiziappWordpressPlugin
* @subpackage Utils
* @author comobix.com plugins@comobix.com
*
* @todo Add log files rotation managment
*/

class WiziappLog {
    /**
    * The desired logging level
    *
    * @var integer
    */
    var $threshold = 4;

    /**
    * Is the log enabled?
    *
    * @var boolean
    */
    var $enabled = WP_WIZIAPP_DEBUG;

    /**
    * The log levels
    *
    * @var array
    */
    var $levels = array('ERROR' => '1', 'WARNING' => 2, 'DEBUG' => '2', 'INFO' => '3', 'ALL' => '4');

    /**
     * The file maximum size in bytes
     *
     * @var integer
     *
     */
    var $max_size = 1048576; // 1MB

    /**
      * @var integer
      *
      */
    var $max_days = 10;

    var $path = '';

    public function __construct(){
        $this->path = dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
        if ( !$this->checkPath() ){
            $this->enabled = FALSE;
        }
    }

    public function checkPath(){
        return is_writable($this->path);
    }

    /**
      * Checks the file size, if file to big starts new one .
      */
    private function toLarge($filepath){
        if (!file_exists($filepath)){
            return false;
        }
        if(filesize($filepath) > $this->max_size){
            return true;
        }else{
         return false;
        }
    }

    /**
    * @todo Change this method to put the old file as .X and not the new, save the scanning of all the log files
    * when adding a new message
    * 
    */
    private function getFilePath(){
        $filepath = $this->path . 'wiziapplog-' . date('Y-m-d') . '.log.php';

        if(filesize($filepath) > $this->max_size){
            $file_indx = 1;
            $new_filepath = $this->path.'wiziapplog-' . date('Y-m-d') . '.log' . $file_indx . '.php';
            while ($this->toLarge($new_filepath)){
                $file_indx++;
                $new_filepath = $this->path . 'wiziapplog-' . date('Y-m-d') . '.log' . $file_indx . '.php';
            }
            return $new_filepath;
        }else{
            return $filepath;
        }
    }

    private function deleteOldFiles() {
        $oldest_date = mktime(0, 0, 0, date('m'), date('d') - $this->max_days, date('Y'));

        $dirHandle = opendir($this->path);
        while($file = readdir($dirHandle)){
            $fileinfo = pathinfo($file);
            $basename = $fileinfo['basename'];
            if(preg_match("/^wiziapplog-/", $basename)){
                $date = strtotime(substr($basename, 11, 10));
                if($date <= $oldest_date){
                    @unlink($this->path . $fileinfo['basename'] );
                }
            }
        }
    }

    /**
    * writes a log message to the log file
    *
    * The messages sent to this method will be filtered according to their level
    * if the level meets the trashold and the logging is enabled the message
    * will be written to a log file. The method also receives the component
    * related to this log message to ease the reading of the log file itself
    * If you want to keep your sanity make sure to send this "optional" parameter
    *
    * @param string $level The log message level
    * @param string $msg The log message
    * @param string $component The component related to this message
    */
    function write($level = 'error', $msg, $component='') {
        ob_start();
        
        clearstatcache(); // We need to clear the cache of the filesize function so that the size checks will work
        
        if ($this->enabled === FALSE){
            ob_end_clean();  
            return FALSE;
        }
        $this->deleteOldFiles();
        // Don't trust the user to use the right case, switch to upper
        $level = strtoupper($level);

        // If the wanted level is above the trashold nothing to do
        if (!isset($this->levels[$level]) || ($this->levels[$level] > $this->threshold)){
            ob_end_clean();  
            return FALSE;
        }

    //        $filepath = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'wiziapplog-'.date('Y-m-d').'.log.php';
        $filepath = $this->getFilePath();
        $message  = '';

        // Prevent direct access to the log, to avoid security issues
        if (!file_exists($filepath)){
            $message .= "<?php if (!defined('WP_WIZIAPP_BASE')) exit(); ?" . ">\n\n";
            $message .= print_r($this->writeServerConfiguration(), TRUE);
            $message .= '==================================================================\n\n\n\n\n';
        }

        // If we can't open the file for appending there isn't much we can do
        if (!$fp = @fopen($filepath, 'ab')){
            ob_end_clean();  
            return FALSE;
        }

        $date = date('Y-m-d H:i:s');
        $message .= "[$level][{$date}][$component]$msg\n";

        flock($fp, LOCK_EX);
        fwrite($fp, $message);
        flock($fp, LOCK_UN);
        fclose($fp);

        @chmod($filepath, 0666);
        
        ob_end_clean();
        return TRUE;
    }
    
    function writeServerConfiguration(){
        global $wpdb;
    
        // mysql version
        $sqlversion = $wpdb->get_var("SELECT VERSION() AS version");
    
        // sql mode
        $mysqlinfo = $wpdb->get_results("SHOW VARIABLES LIKE 'sql_mode'");
        if (is_array($mysqlinfo)){
            $sql_mode = $mysqlinfo[0]->Value;   
        }
        
        if (empty($sql_mode)) {
            $sql_mode = 'Not Set';
        }   
        
        $config = array(
            'php_os' => PHP_OS,
            'sql version' => $sqlversion,
            'sql mode' => $sql_mode,
            'safe_mode' => ini_get('safe_mode'),
            'output buffer size' => ini_get('pcre.backtrack_limit') ? ini_get('pcre.backtrack_limit') : 'NA',
            'post_max_size'  => ini_get('post_max_size') ? ini_get('post_max_size') : 'NA',
            'max_execution_time' => ini_get('max_execution_time') ? ini_get('max_execution_time') : 'NA',
            'memory_limit' => ini_get('memory_limit') ? ini_get('memory_limit') : 'NA',
            'memory_get_usage' => function_exists('memory_get_usage') ? round(memory_get_usage() / 1024 / 1024, 2).'MByte' : 'NA',
            'server config' => $_SERVER,
            'display_errors' => ini_get('display_error'), 
            'error_reporting' => ini_get('error_reporting'), 
        );
        
        return $config;
    }
}

/**
* One logger to rule them all...
* @global WiziappLogm $GLOBALS['WiziappLog']
*/
if (!isset($GLOBALS['WiziappLog'])) {
    // Initate the Wiziapp Loggin Object
    $GLOBALS['WiziappLog'] = new WiziappLog() ;
}                 