<?php
/**
* Lazy loading, load all of our classes, except for components that are only needed in screens
* @todo Add some more auto loading functionality, no need to load everything everytime. keep in mind that php4 is still out there (pending refactoring the code to be classes only)
* 
* @package WiziappWordpressPlugin
* @author comobix.com plugins@comobix.com
*/

class WiziappLoader
{
    private $versions = array();
    private $defaultVersion = '1.2.0';
    private $version = WIZIAPP_P_VERSION;
    private $prefix = 'wiziapp';

    function __construct(){
        $this->_checkSetIncludePath();
        // Register this class as autoloader for classes
        spl_autoload_register(array($this, 'loadClass'));

        $this->setVersion();
        $this->loadVersions();
        $this->load();
    }

    private function _checkSetIncludePath(){
        /**
         * Some plugins like wordpress-backup-to-dropbox version 0.8 might do something silly like:
         * ini_set( 'include_path', dirname( __FILE__ ) . '/PEAR_Includes' . PATH_SEPARATOR . DEFAULT_INCLUDE_PATH );
         * which will remove the include path from our path....
         * so we need to run this check and set the path everytime just in case...
         */
        $currentPath = get_include_path();
        $currentFilePath = dirname(__FILE__);
        if ( strpos($currentPath, $currentFilePath) === FALSE ){
            // Make sure the include path is correct
            $path =  $currentFilePath . DIRECTORY_SEPARATOR . 'blocks' . PATH_SEPARATOR;
            $path .= $currentFilePath . DIRECTORY_SEPARATOR . 'blocks' . DIRECTORY_SEPARATOR . 'components';
            $path .= PATH_SEPARATOR . $currentFilePath . DIRECTORY_SEPARATOR . 'classes';

            set_include_path($currentPath . PATH_SEPARATOR . $path);
        }
    }

    protected function setVersion(){
        if ( isset($_SERVER['HTTP_WIZIAPP_VERSION']) ){
            $this->version = $_SERVER['HTTP_WIZIAPP_VERSION'];
        } else {
            $this->version = $this->defaultVersion;
        }
    }
    
    public function getVersion(){
        return $this->version; 
    }

    protected function loadVersions(){
        if ( empty($this->versions) ){
            $this->_checkSetIncludePath();
            $this->versions = require_once('version_routes.inc.php');
        }
    }
    protected function load(){
        /**
        * @todo ticket #798 should be here
        */
        if (is_dir(dirname(__FILE__) . "/blocks")){
            if ($func_dir = opendir(dirname(__FILE__) . "/blocks")){
                while (($sub_dir = readdir($func_dir)) !== false){
                    if (preg_match("/\.php$/", $sub_dir) && !preg_match("/^index\.php$/i", $sub_dir)){
                        if ( strpos($sub_dir, "_") !== 0){
                            $block = $this->getFilePath("/blocks/".$sub_dir);
                            if ( $block !== FALSE ){
                                include_once dirname(__FILE__) . $block;       
                            }
                            
                        }
                    }
                } 
            }
        }      
    }

    public function loadClass($className){

        // Make sure the class is ours
        if ( stripos($className, $this->prefix) === 0 ){
            if ( !class_exists($className, FALSE)  && !interface_exists($className, FALSE) ){
                $this->_checkSetIncludePath();
                $vClassName = $this->getClassFileName($className);
                /** @noinspection PhpIncludeInspection */
                include($vClassName);
            }
        }
    }
    
    private function _getFromVersionConfig($type, $name){
        $version = $this->getVersion();
        
        if ( isset($this->versions[$version]) ) {
            if ( isset($this->versions[$version][$type]) ) {
                if ( isset($this->versions[$version][$type][$name]) ){
                    $name = $this->versions[$version][$type][$name];
                    /**
                    * @todo ticket #979 should be here for type = 'core'
                    */
                }
            }
        }
        return $name;      
    }
    
    protected function getFilePath($name){
        return $this->_getFromVersionConfig('core', $name);
    }
    
    protected function getClassFileName($name){
        $result = $this->_getFromVersionConfig('classes', $name);
        if ( $result == $name ){
            /**$result = substr($name, strlen('wiziapp')+1);
            $result = strtolower(substr($name, strlen('wiziapp'), 1)).$result.'.php';*/
            $result .= '.php';
        }
        return $result;
    }
    
    protected function getFuncName($func){
        return $this->_getFromVersionConfig('functions', $func);      
    }
}


global $wiziappLoader;
$wiziappLoader = new WiziappLoader();