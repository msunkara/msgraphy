<?php if (!defined('WP_WIZIAPP_BASE')) exit();
/**
* @package WiziappWordpressPlugin
* @subpackage Configuration
* @author comobix.com plugins@comobix.com
*
*/
class WiziappComponentsConfiguration{
    private $config = array();
    private static $instance;

    private function __construct() {
        $this->config = get_option('wiziapp_components');
    }

    public static function getInstance(){
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    function getAttrToAdd($name){
        $attrs = array();
        if ( isset($this->config[$name]) &&
            isset($this->config[$name]['extra'])){

            $attrs = $this->config[$name]['extra'];
        }

        return $attrs;
    }

    function getAttrToRemove($name){
        $attrs = array();
        if ( isset($this->config[$name]) &&
            isset($this->config[$name]['remove'])){

            $attrs = $this->config[$name]['remove'];
        }

        return $attrs;
    }
}