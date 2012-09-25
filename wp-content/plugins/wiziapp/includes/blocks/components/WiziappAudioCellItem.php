<?php
/**
* The audio cell item component
* 
* The component knows how to return: title, duration, imageURL, actionURL
* 
* @package WiziappWordpressPlugin
* @subpackage UIComponents
* @author comobix.com plugins@comobix.com
*/
class WiziappAudioCellItem extends WiziappLayoutComponent{
    /**
    * The attribute map
    * 
    * @var array
    */
    var $attrMap = array(
        'L1' => array('title', 'duration', 'imageURL', 'actionURL'),
    );
    
    /**
    * The css classes to attach to the component according to the layout
    * 
    * @var mixed
    */
    var $layoutClasses = array(
        'L1' => 'audio',
    );
    
    /**
    * The base name of the component, the application knows the component by this name
    * 
    * @var string
    */
    var $baseName = 'audioCellItem';
    
    /**
    * constructor 
    * 
    * @uses WiziappLayoutComponent::init()
    * 
    * @param string $layout the layout name
    * @param array $data the data the components relays on
    * @return WiziappAudioCellItem
    */
    function WiziappAudioCellItem($layout='L1', $data){
        parent::init($layout, $data);    
    }

    /**
    * Attribute getter method
    * 
    * @return the id of the component
    */
    function get_id_attr(){
        return "audio_{$this->data[0]['id']}";    
    }
    
    /**
    * Attribute getter method
    * 
    * @return the duration of the component
    */
    function get_duration_attr(){
        return $this->data[0]['duration'];
    }
    
    /**
    * Attribute getter method
    * 
    * @return the title of the component
    */
    function get_title_attr(){
        return $this->data[0]['title'];
    }
    
    /**
    * Attribute getter method
    * 
    * @return the imageURL of the component
    */
    function get_imageURL_attr(){
        $image = new WiziappImageHandler($this->data[0]['imageURL']);
        $size = WiziappConfig::getInstance()->getImageSize('audio_thumb');
        return $image->getResizedImageUrl(htmlspecialchars_decode($this->data[0]['imageURL']), $size['width'], $size['height']);
        
//        return $this->data[0]['imageURL'];
    }
    
    /**
    * Attribute getter method
    * 
    * @return the actionURL of the component
    */
    function get_actionURL_attr(){
        return $this->data[0]['actionURL'];
    }
    
}