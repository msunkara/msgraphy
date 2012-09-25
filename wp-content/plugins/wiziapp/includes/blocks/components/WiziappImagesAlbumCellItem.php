<?php
/**
* The images album cell item component
* 
* The component knows how to return: title, images array, numOfImages, actionURL
* 
* @package WiziappWordpressPlugin
* @subpackage UIComponents
* @author comobix.com plugins@comobix.com
*/
class WiziappImagesAlbumCellItem extends WiziappLayoutComponent{
    /**
    * The attribute map
    * 
    * @var array
    */
    var $attrMap = array(
        'L1' => array('title', 'images', 'numOfImages', 'actionURL'),
        'L2' => array('title', 'imageURL', 'numOfImages', 'actionURL'),
    );
    
    /**
    * The css classes to attach to the component according to the layout
    * 
    * @var mixed
    */
    var $layoutClasses = array(
        'L1' => 'album_item',
        'L2' => 'album_item'
    );
    
    /**
    * The base name of the component, the application knows the component by this name
    * 
    * @var string
    */
    var $baseName = 'imagesAlbumCellItem';
   
    /**
    * constructor 
    * 
    * @uses WiziappLayoutComponent::init()
    * 
    * @param string $layout the layout name
    * @param array $data the data the components relays on
    * @return WiziappImagesAlbumCellItem
    */
    function WiziappImagesAlbumCellItem($layout='L1', $data){
        if ( count($data[0]['images']) < 2 ){
            return FALSE;
        }
        parent::init($layout, $data);   
    }
    
    /**
    * Attribute getter method
    * 
    * @return the id of the component
    */
    function get_id_attr(){
        return "album_{$this->data[0]['postID']}_{$this->data[0]['id']}";
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
    * @return the images array of the component
    */
    function get_images_attr(){
	    $images = array();
	    if ( !empty($this->data[0]['images']) && !empty($this->data[0]['images'][0])) {
            if ( $this->layout == 'L2')
		        $images = $this->data[0]['images'];
	    }
        return $images;
    }
    
    function get_imageURL_attr(){
        $GLOBALS['WiziappLog']->write('info', "The preview image is:" . $this->data[0]['images'][0], 
                        'imageGalleryCellItem.get_imageURL_attr');
        $image = new WiziappImageHandler($this->data[0]['images'][0]);
        $size = WiziappConfig::getInstance()->getImageSize('album_thumb');
        return $image->getResizedImageUrl($this->data[0]['images'][0], $size['width'], $size['height']);
        
        //return $this->data[0]['images'][0];    
    }
    
    /**
    * Attribute getter method
    * 
    * @return the numOfImages of the component
    */
    function get_numOfImages_attr(){
        return "{$this->data[0]['numOfImages']} " . __(' photos');
    }
    
    /**
    * Attribute getter method
    * 
    * @return the actionURL of the component
    */
    function get_actionURL_attr(){
        $url = '';
        if ( $this->data[0]['plugin'] == 'bypost' ){
            $url = wiziapp_buildPostImagesGalleryLink($this->data[0]['content_id']);
        } else {
            $url = wiziapp_buildPluginAlbumLink($this->data[0]['plugin'], $this->data[0]['id']);
        }
        return $url;
    }   
}
