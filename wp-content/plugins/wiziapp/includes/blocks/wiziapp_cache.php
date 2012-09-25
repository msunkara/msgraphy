<?php if (!defined('WP_WIZIAPP_BASE')) exit();
/**
* @package WiziappWordpressPlugin
* @subpackage Cache
* @author comobix.com plugins@comobix.com
* 
* @todo Split this to 3 classes: WiziappCache, WiziappFileCache, WiziappApcCache
* @todo add support for memcache configuration, will require configuration, maybe from wordpress cache plugins?
*/

/**
 * Description of wiziapp_cache class
 */
class WiziappCache{
    /**
     * type of caching file or apc
     * @var string
     */
    private $prefix;
    /**
     * path to dirctory with cache files
     * @var string
     */
    private $fileDir = '/tmp/';
    /**
     * defaults options
     * @var array
     */
//    private $default_options = array('duration'=>3600);
    private $default_options = array('duration'=>60);
    /**
     * Stack of caches for nesting cashe
     * @var array
     */
    private $cache_stack = array();

    private $enabled = TRUE;

    public function __construct() {
        // choose type of cache
        if ( function_exists('apc_store') ) {
            $GLOBALS['WiziappLog']->write('info', "APC caching system is active", "WiziappCache.__construct");
            $this->prefix = 'apc';
        }else{
            $GLOBALS['WiziappLog']->write('info', "Did not find APC installed, using file caching", "WiziappCache.__construct");
            $this->prefix = 'file';
            $uploads_dir = wp_upload_dir();
            $uploads_dir = $uploads_dir['basedir'];
            $this->fileDir = $uploads_dir . '/wiziapp_cache/';

            if(!file_exists($this->fileDir)){
                $created = @mkdir($this->fileDir,0777,true);
                if ( $created ){
                    $GLOBALS['WiziappLog']->write('info', "Created the wiziapp file caching directory:".$this->fileDir, "WiziappCache.__construct");
                } else {
                    $GLOBALS['WiziappLog']->write('error', "Couldn't create the wiziapp file caching directory: ".$this->fileDir, "WiziappCache.__construct");
                }
            } else {
                if ( !is_readable($this->fileDir) ){
                    $GLOBALS['WiziappLog']->write('error', "The upload directory exists but its not readable: ".$this->fileDir, "WiziappCache.__construct");
                } else {
                    if ( !is_writable($this->fileDir) ){
                        $GLOBALS['WiziappLog']->write('error', "The upload directory exists but its not writable: ".$this->fileDir, "WiziappCache.__construct");
                        $this->enabled = FALSE;
                    } else {
                        $GLOBALS['WiziappLog']->write('info', "The file cache directory exists and readable and writable: ".$this->path, "WiziappCache.__construct");
                    }
                }
            }
        }
    }

    public function checkPath(){
        return (!empty($this->fileDir)) ? is_writable($this->fileDir) : TRUE;
    }
    /**
     * start cache if need of print content form cache
     * @param string $key
     * @param array $options
     * @return bool
     */
    public function beginCache($key, $options = array()){
        $shouldContinue = TRUE;
        /**
         * First make sure we are able to cache if not return true so let the caller know
         * it needs to continue with the rest of the code.
         */
        if ( $this->enabled ){
            // If we are enabled, continue
            if(!is_array($options) || empty($options)){
                $options = $this->default_options;
            }else{
                //change minutes to seconds
                $options['duration'] = $options['duration'] * 60;
            }

            if($this->{$this->prefix.'BeginCache'}($key,$options)){
                ob_start();
                //add to stack information about cache block, need for inherited caching
                array_push($this->cache_stack, array('key'=>$key,'options'=>$options));
            } else {
                $shouldContinue = FALSE;
            }
        }
        return $shouldContinue;
    }

    /**
     * save content in cache
     */
    public function endCache($output=TRUE){
        $content = ob_get_clean();
        $GLOBALS['WiziappLog']->write('info', "The content is: {$content}", 
                                        "WiziappCache.endCache");
                                        
        if ( $output ){
            $this->outputContent($content, FALSE);   
        }
        if ( $this->enabled ) {
            $cache_info = array_pop($this->cache_stack);
            $GLOBALS['WiziappLog']->write('info', "Saving cache key: {$cache_info['key']}",
                                            "WiziappCache.endCache");

            $headers_content = serialize(headers_list());
            if ( strlen($content) > 0 ){
                $this->{$this->prefix.'EndCache'}($cache_info['key'],serialize($content),$cache_info['options'],$headers_content);
            } else {
                $GLOBALS['WiziappLog']->write('info', "No content to save in: {$this->prefix}",
                                            "WiziappCache.endCache");
            }
        }
    }
    
    private function outputContent($content, $should_descrypt = TRUE){
        if ( $should_descrypt ){
            $content = unserialize($content);   
        }
        /**
        * Gzip the output, support weird headers
        */
        $encoding = false; 
        if ( isset($_SERVER["HTTP_ACCEPT_ENCODING"]) ){
            $HTTP_ACCEPT_ENCODING = $_SERVER["HTTP_ACCEPT_ENCODING"]; 
            if ( isset($_SERVER["HTTP_X_CEPT_ENCODING"]) ){
                $HTTP_ACCEPT_ENCODING = $_SERVER["HTTP_X_CEPT_ENCODING"];
            }
            if( headers_sent() ) {
                $encoding = false; 
            } else if( strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false ) {
                $encoding = 'x-gzip'; 
            } else if( strpos($HTTP_ACCEPT_ENCODING,'gzip') !== false ){
                $encoding = 'gzip'; 
            }
                
        }
        if( $encoding ) { 
            /**
            * Although gzip encoding is best handled by the zlib.output_compression 
            * Our clients sometimes send a different accpet encoding header like X-cpet-Encoding
            * in that case the only way to catch it is to manually handle the compression 
            * and headers check
            */
            $len = strlen($content); 
            header('Content-Encoding: '.$encoding); 
            echo "\x1f\x8b\x08\x00\x00\x00\x00\x00"; 
            $content = gzcompress($content, 9); 
            $content = substr($content, 0, $len); 
        } 
        echo $content;
    }
    
    private function outputHeaders($headers){
        if ( !empty($headers) ){
            $headers = unserialize($headers);
            for($h=0,$total=count($headers);$h<$total;++$h){
                header($headers[$h]);
            }   
        }
    }

    private function headersKey($key){
        return $key.'_headers';
    }

    //Files part
    /**
     * start file type cache
     * @param string $key
     * @param array $options
     * @return bool
     */
    private function fileBeginCache($key,$options){
        if($this->fileCheckCache($key,$options)){
            // Output the headers for the cache
            $this->outputHeaders(@file_get_contents($this->fileDir.$this->headersKey($key)));
            
            $contents = @file_get_contents($this->fileDir.$key);
            
            $this->outputContent($contents);
            
            /**if(!@readfile($this->fileDir.$key)){
                throw new Exception('Can\'t read file :'.$this->fileDir.$key);
            }  */
            return false;
        }
        return true;
    }

    /**
     * check if cache exist for this $key
     * @param string $key
     * @param array $options
     * @return boll
     */
    private function fileCheckCache($key,$options){
        if (!file_exists($this->fileDir.$key) || (time() - filemtime($this->fileDir.$key) >= $options['duration'])){
            return false;
        }else{
            return true;
        }
    }

    /**
     * save content to file with $key name
     * @param string $key
     * @param string $content
     * @param array $options
     */
    private function fileEndCache($key,$content,$options,$headers){
        @unlink($this->fileDir.$key);
        @unlink($this->fileDir.$this->headersKey($key));
        if(!file_put_contents($this->fileDir.$this->headersKey($key), $headers)){
            throw new Exception('Can\'t write file.');
        }
        if(!file_put_contents($this->fileDir.$key, $content)){
            throw new Exception('Can\'t write file.');
        }
        chmod($this->fileDir.$key, 0777);
        chmod($this->fileDir.$this->headersKey($key), 0777);

    }

    //APC part
    /**
     * start apc type cache
     * @param string $key
     * @param array $options
     * @return bool
     */
    private function apcBeginCache($key,$options){
        if($this->apcCheckCache($key)){
            // Output the headers for the cache
            $this->outputHeaders(apc_fetch($this->headersKey($key)));
            $content = apc_fetch($key,$bool);
            
            if(!$bool){
                throw new Exception(__('Can\'t fetch cache :', 'wiziapp').$key);   
            } else {
                $this->outputContent($content);
            }
            return false;
        }
        return true;
    }

    /**
     * check if $key exist
     * @param string $key
     * @return bool
     */
    private function apcCheckCache($key){
        if(function_exists('apc_exists')){
            if(!apc_exists($key)){
                return false;
            }else{
                return true;
            }
        }else{
            if(!apc_fetch($key)){
                return false;
            }else{
                return true;
            }
        }
    }

    /**
     * save content 
     * @param string $key
     * @param string $content
     * @param array $options
     */
    private function apcEndCache($key,$content,$options,$headers){
        apc_delete($this->headersKey($key));
        apc_delete($key);

        $GLOBALS['WiziappLog']->write('info', "Saving cache key: {$key}", 
                                        "WiziappCache.apcEndCache");
        if(!apc_store($this->headersKey($key), $headers,$options['duration'])){
            $GLOBALS['WiziappLog']->write('error', "Cant save cache: {$key}", 
                                        "WiziappCache.endCache");
            throw new Exception(__('Can\'t store content.', 'wiziapp'));
        }
        
        if(!apc_store($key, $content,$options['duration'])){
            $GLOBALS['WiziappLog']->write('error', "Cant save cache: {$key}", 
                                        "WiziappCache.endCache");
            throw new Exception(__('Can\'t store content.', 'wiziapp'));
        }
    }


}
