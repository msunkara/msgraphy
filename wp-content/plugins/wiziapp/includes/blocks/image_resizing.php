<?php
/**
* Make sure every image size we need is available from wordpress
* 
* @todo sync this with the same code part in the global services
* 
* @todo Add file rotating like in the log files, only the conditions here
* should be: older then 30 days, wasn't accessed for 7 days, if the folder is bigger than 10 mb 
* delete the older file according to the access date.
* 
* @package WiziappWordpressPlugin
* @subpackage MediaUtils
* @author comobix.com plugins@comobix.com
*/

class WiziappImageHandler {
    /**
    * The implementation of the actual resizing
    * can be PhpThumb or our services in this order
    * 
    * if an empty string it is our services.
    * 
    * @var string
    */
    private $imp = '';
    
    /**
    * The image resizing object, will be initialized according to the $imp
    * 
    * @var mixed
    */
    private $handler = null;
    
    /**
    * The directory to save the cache files in
    * 
    * @var mixed
    */
    private $cache = 'cache';
    
    /**
    * holds the image src as was given
    * 
    * @var string
    */
    private $imageFile = '';

    private $path = '';
    /**
    * constructor
    * 
    * @param string $imageFile
    * @return WiziappImageHandler
    */
    public function __construct ($imageFile='') {
        /**
        * Init the cache path under our plugin installation, if the user deletes the plugin
        * there is no need to leave traces in his wordpress installation
        */
        $basePath = dirname(__FILE__) . '/../..';
        $this->cache = $basePath . '/' . $this->cache;

        if (!empty($imageFile)) {
            $this->imageFile = $imageFile;
            
            /**
            * Check and initialized the image resizing object according to the availability
            */
            $this->_checkImp();
            $imageClass = "Wiziapp{$this->imp}Resizer";
            require_once('imageResizers/' . $imageClass . '.php');
            $this->handler = new $imageClass();    
        }
    }

    public function checkPath(){
        return is_writable($this->cache);
    }

    public function getResizedImageUrl($url, $width, $height, $type = 'adaptiveResize', $allow_up = FALSE){
        $url = urlencode($url);
        return get_bloginfo('url') . "/?wiziapp/getimage/&width={$width}&height={$height}&type={$type}&allow_up={$allow_up}&url={$url}";
    }

    public function wiziapp_getResizedImage($width, $height, $type = 'adaptiveResize', $allow_up = FALSE){
        if ($this->handler == null){
            return false;
        }
        // Get the ext
        $tmp = explode('?', $this->imageFile);
        $ext = substr($tmp[0], strrpos($tmp[0], '.'));
        
        if (strpos($ext, '/') !== FALSE){
            // There was a slash so this image doesn't have an extension, force file type change to png
            $ext = '.png';
        }
        
        $cacheFile = realpath($this->cache) . '/' . md5($this->imageFile . $width . $height . $type) . $ext;
        
        if ($this->_cacheExists($cacheFile)){
            $url = str_replace(WIZI_ABSPATH, get_bloginfo('wpurl') . '/', $cacheFile);
            $GLOBALS['WiziappLog']->write('debug', "Before loading image from cache: " . $cacheFile, "image_resizing.getResizedImage");
            $this->handler->load($cacheFile, FALSE);
            $GLOBALS['WiziappLog']->write('debug', "After loading image from cache: " . $cacheFile, "image_resizing.getResizedImage");
        } else {
            $this->imageFile = str_replace(' ', '%20', $this->imageFile);
            $GLOBALS['WiziappLog']->write('debug', "Before resizing image: " . $this->imageFile, "image_resizing.getResizedImage");
            $url = $this->imageFile;
            if (strpos($this->imageFile, get_bloginfo('wpurl')) === 0){
                $url = str_replace(get_bloginfo('wpurl'), WIZI_ABSPATH, $url);
            }
            $url = $this->handler->resize($url, $cacheFile, $width, $height, $type, $allow_up, $this->checkPath());
            $GLOBALS['WiziappLog']->write('debug', "After resizing image: " . $this->imageFile, "image_resizing.getResizedImage");
            $GLOBALS['WiziappLog']->write('debug', "After resizing image URL: " . $url, "image_resizing.getResizedImage");
        }

        /**$thumb = PhpThumbFactory::create($url);  
        $thumb->show();*/
        if ( $url === FALSE || strlen($url) > 0 ){
            $this->handler->show();
        } else {
            // If the image is not local, just redirect to it
            if ( strpos($this->imageFile, 'https://') !== FALSE || strpos($this->imageFile, 'http://') !== FALSE ){
                header('Location: '.$this->imageFile);
                // On this special case we need to halt the functions from moving on
                exit;
            }
        }

        // If we show the image it means the output was sent and we should stop the request
        return true;

        //$imginfo = getimagesize($url);
//        header("Content-Type: " . $imginfo['mime']);
//        return file_get_contents($url);
    }
    
    public function load(){
        /**
        * If the image is local, use the local path.
        * If we will access via the url we might end up stuck with allow_url_open off
        */
        $imagePath = $this->imageFile;
        $calcResize = TRUE; // Try to calc the size of the image, unless remote and allow_url_fopen is off
        if ( strpos($imagePath, get_bloginfo('wpurl')) === 0 ){
            $imagePath = str_replace(get_bloginfo('wpurl'), WIZI_ABSPATH, $imagePath);
        } else {
            // The image is not local, if allow_url_fopen is off throw an alert
            if ( ini_get('allow_url_fopen') != '1' ){
                $calcResize = FALSE; // Will affect the ability to make the image a thumbnail
                $GLOBALS['WiziappLog']->write('error', "allow_url_fopen is turned off, can't check the images size", "WiziappImageHandler.load");                  
            }                                
        }
        $this->handler->load($imagePath, $calcResize);
    }
    public function getNewWidth(){
        $width = $this->handler->getNewWidth();
        if ($width == 0){
            $width = "auto";
        }
        return $width;
    }
    
    public function getNewHeight(){
        $height = $this->handler->getNewHeight();
        if ($height == 0){
            $height = "auto";
        }
        return $height;
    }
    
    
    private function _cacheExists($cacheFile){
        return file_exists($cacheFile);
    }
    
    /**
    * Check which implementaion is available on this server
    * Fallback to wiziapp global services if nothing local was found
    */
    private function _checkImp(){
        if(extension_loaded('gd') || extension_loaded('imagick')){
            $this->imp = 'PhpThumb';
        } 
    }
}