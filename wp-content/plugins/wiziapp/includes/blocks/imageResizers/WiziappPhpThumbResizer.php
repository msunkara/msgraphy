<?php
/**
* 
* @todo sync this with the same code part in the global services
* 
* @package WiziappWordpressPlugin
* @subpackage MediaUtils
* @author comobix.com plugins@comobix.com
*/

class WiziappPhpThumbResizer{
    private $newHeight = 0;
    
    private $thumb = null;
    
    private $newWidth = 0;
    
    public function getNewWidth(){
         return $this->newWidth;
    }
    
    public function getNewHeight(){
        return $this->newHeight;            
    }
        
    public function load($image, $calc_size = TRUE){
        $basePath = dirname(__FILE__) . '/../../libs/';
        require_once $basePath . 'phpThumb/ThumbLib.inc.php';
        
        $GLOBALS['WiziappLog']->write('info', 'Before thumb create: ' . $image, 'WiziappPhpThumbResizer.load');

        $thumb = PhpThumbFactory::create($image);         
        $this->thumb = $thumb;
        
        $GLOBALS['WiziappLog']->write('info', 'After thumb create: ' . $image, 'WiziappPhpThumbResizer.load');
        
        if ($calc_size){
            $size = $thumb->getCurrentDimensions();
            $this->newHeight = $size['height'];
            $this->newWidth = $size['width'];   
        }
    }
    
    public function resize($image, $file, $width, $height, $type, $allow_up = false, $save_image = true){
        $basePath = dirname(__FILE__) . '/../../libs/';
        $url = '';
        require_once $basePath . 'phpThumb/ThumbLib.inc.php';
        $options = array();
        if ($allow_up){
            $options['resizeUp'] = true;
        }
        
        try {
            $GLOBALS['WiziappLog']->write('info', 'Before thumb resize: ' . $image, 'WiziappPhpThumbResizer.resize');             
            $thumb = PhpThumbFactory::create($image, $options);
            //$thumb->$type($width, $height);
            if ($height == 0){
                $type = 'resize';
                // Calc the new height based of the need to resize
                $dim = $thumb->getCurrentDimensions();
                $currWidth = $dim['width'];
                $currHeight = $dim['height'];
                
                if ($currWidth > $width){
                    $height = ($width / $currWidth) * $currHeight;
                } else {
                    $height = $currHeight;
                }
                
                $GLOBALS['WiziappLog']->write('info', "Resizing from width: {$currWidth} to: {$width} and therefore from height: {$currHeight} to: {$height}", 
                        'WiziappPhpThumbResizer.resize');
            } elseif ($width == 0) {
                $type = 'resize';
                // Calc the new height based of the need to resize
                $dim = $thumb->getCurrentDimensions();
                $currWidth = $dim['width'];
                $currHeight = $dim['height'];
                
                if ($currHeight > $height){
                    $width = ($height / $currHeight) * $currWidth;
                } else {
                    $width = $currWidth;
                }
                
                $GLOBALS['WiziappLog']->write('info', "Resizing from height: {$currHeight} to: {$height} and therefore from width: {$currWidth} to: {$width}", 
                        'WiziappPhpThumbResizer.resize');
            }
            
            $thumb->$type($width, $height);
            
            $size = $thumb->getCurrentDimensions();
            $this->newHeight = $size['height'];
            $this->newWidth = $size['width'];
            
            $this->thumb = $thumb;

            if ( $save_image ){
                $thumb->save($file);

                // Convert the cache filesystem path to a public url
                $url = str_replace(WIZI_ABSPATH, get_bloginfo('wpurl') . '/', $file);
                $url = str_replace('\\', '/', $url);
            } else {
                $url = FALSE;
            }

            $GLOBALS['WiziappLog']->write('info', 'After thumb resize: ' . $image, 'WiziappPhpThumbResizer.resize');
        }
        catch (Exception $e) {
             $GLOBALS['WiziappLog']->write('error', 'Error resizing: ' . $e->getMessage(), 
                'WiziappPhpThumbResizer.resize');
        }
        
        return $url;
    } 
    
    public function show(){
        if ($this->thumb != null){
            $this->thumb->show();
        }
    }   
}