<?php
/**
* 
* @package WiziappWordpressPlugin
* @subpackage MediaUtils
* @author comobix.com plugins@comobix.com
*/

class WiziappResizer{
    private $newHeight = 0;
    
    private $newWidth = 0;
    
    /**
    * @todo can we add width and height calc if the blog doesn't have an images lib, find something
    */
    public function getNewWidth(){
         return $this->newWidth;
    }
    
    public function getNewHeight(){
        return $this->newHeight;            
    }
    
    public function load($image){
        
    }
    
    public function resize($image, $file, $width, $height, $type, $allow_up = false){
        $image = urlencode($image);
        $service = 'https://' . WiziappConfig::getInstance()->api_server . '/index.php/simulator/resize';
        $qs = "?src={$image}&w={$width}&h={$height}&t={$type}";
        
        if ( $allow_up ){
            $qs .= '&u=1';
        }
        
        $this->newHeight = $height;
        $this->newWidth = $width;
        
        return $service . $qs;
    }
}