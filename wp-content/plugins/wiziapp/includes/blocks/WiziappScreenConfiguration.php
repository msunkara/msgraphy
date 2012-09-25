<?php if (!defined('WP_WIZIAPP_BASE')) exit();

/**
* @package WiziappWordpressPlugin
* @subpackage Configuration
* @author comobix.com plugins@comobix.com
*
*/
class WiziappScreenConfiguration{
    var $config = array();

    function WiziappScreenConfiguration(){
        $this->config = get_option('wiziapp_screens');
    }

    function getScreenLayout($screen, $type='list'){
        //return $this->config[$screen][$this->layouts[$screen.'_'.$type]];
        global $wiziappLoader;

        return $this->config[$wiziappLoader->getVersion()][$screen.'_'.$type];
    }
}

if ( ! isset($GLOBALS['WiziappScreens']) ) {
    /**
     * Initate the Wiziapp Database Object, for later cache reasons
     */
    unset($GLOBALS['WiziappScreens']);
    $GLOBALS['WiziappScreens'] = new WiziappScreenConfiguration() ;
}    
