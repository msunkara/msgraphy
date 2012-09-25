<?php
/**
* The videos album cell item component
* 
* The component knows how to return: title, htmls array, numOfVideos, actionURL
* 
* @package WiziappWordpressPlugin
* @subpackage UIComponents
* @author comobix.com plugins@comobix.com
*/
class WiziappVideosAlbumCellItem extends WiziappLayoutComponent{
    /**
    * The attribute map
    * 
    * @var array
    */
    var $attrMap = array(
        'L1' => array('title', 'htmls', 'numOfVideos', 'actionURL'),
        'L2' => array('title', 'images', 'numOfVideos', 'actionURL'),
        'L3' => array('title', 'imageURL', 'numOfVideos', 'actionURL'),
    );
    
    /**
    * The css classes to attach to the component according to the layout
    * 
    * @var mixed
    */
    var $layoutClasses = array(
        'L1' => 'album_item',
        'L2' => 'album_item',
        'L3' => 'video_album',
    );
    
    /**
    * The base name of the component, the application knows the component by this name
    * 
    * @var string
    */
    var $baseName = 'videosAlbumCellItem';
   
    /**
    * constructor 
    * 
    * @uses WiziappLayoutComponent::init()
    * 
    * @param string $layout the layout name
    * @param array $data the data the components relays on
    * @return WiziappVideosAlbumCellItem
    */
    function WiziappVideosAlbumCellItem($layout='L2', $data){
        parent::init($layout, $data);    
    }
    
    /**
    * Attribute getter method
    * 
    * @return the id of the component
    */
    function get_id_attr(){
        return "album_{$this->data[0]['id']}";
    }
    
    /**
    * Attribute getter method
    * 
    * @return the title of the component
    */
    function get_title_attr(){
        return $this->data[0]['name'];
    }
    
    /**
    * Attribute getter method
    * 
    * @return the htmls array of the component
    */
    function get_htmls_attr(){
        return $this->data[0]['html'];
    }
    
    /**
    * Attribute getter method
    * 
    * @return the numOfVideos of the component
    */
    function get_numOfVideos_attr(){
        return "{$this->data[0]['numOfVideos']}\n ".__('videos');
    }
    
    /**
    * Attribute getter method
    * 
    * @return the images array of the component
    */
    function get_images_attr(){
        return $this->data[0]['images'];
    }
    
    function get_imageURL_attr(){
        $image = new WiziappImageHandler($this->data[0]['images'][0]);
        $size = WiziappConfig::getInstance()->getImageSize('video_album');
        return $image->getResizedImageUrl($this->data[0]['images'][0], $size['width'], $size['height']);
        
        //return $this->data[0]['images'][0];    
    }
    
    /**
    * Attribute getter method
    * 
    * @return the actionURL of the component
    */
    function get_actionURL_attr(){
        return wiziapp_buildPluginAlbumLink($this->data[0]['plugin'], $this->data[0]['id']);
    }   
}
