<?php

class WiziappSingleton extends stdClass
{
    private static $instances = array();

    /**
    * Prevent cloning of the element
    *
    * @throws WiziappOperationNotAllowed
    */
    public function __clone(){
        throw new WiziappOperationNotAllowed('Clone is not allowed.');
    }

    // The singleton method
    /**
     * @static
     * @return self
     */
    public static function getInstance() {
        $class = get_called_class();
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class();
        }
        
        return self::$instances[$class];
    }
}