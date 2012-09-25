<?php
/**
* The video gallery cell item component
* 
* The component knows how to return: itemID, title, date, provider, author, duration, description, thumbnailURL, html, actionURL, detailsURL
* 
* @package WiziappWordpressPlugin
* @subpackage UIComponents
* @author comobix.com plugins@comobix.com
*/
class WiziappVideoGalleryCellItem extends WiziappLayoutComponent{
    /**
    * The attribute map
    * 
    * @var array
    */
    var $attrMap = array(       
        'L1' => array('itemID', 'title', 'date', 'provider', 'author', 'duration', 'description', 'thumbnailURL', 'actionURL', 'scriptName'),
        'L2' => array('itemID', 'title', 'date', 'provider', 'author', 'duration', 'description', 'thumbnailURL', 'scriptName'),
    );
    
    /**
    * The css classes to attach to the component according to the layout
    * 
    * @var mixed
    */
    var $layoutClasses = array(                                                                                                                      
        'L1' => 'video',
        'L2' => 'video_extended',
    );
    
    /**
    * The base name of the component, the application knows the component by this name
    * 
    * @var string
    */
    var $baseName = 'videoGalleryCellItem';

    /**
    * constructor 
    * 
    * @uses WiziappLayoutComponent::init()
    * 
    * @param string $layout the layout name
    * @param array $data the data the components relays on
    * @return WiziappAudioCellItem
    */
    function WiziappVideoGalleryCellItem($layout='L1', $data){
        parent::init($layout, $data);    
    }
    
    /**
    * Attribute getter method
    * 
    * @return the id of the component
    */
    function get_id_attr(){
        return "video_{$this->data[0]['id']}";
    }
    
    /**
    * Attribute getter method
    * 
    * @return the itemID of the component
    */
    function get_itemID_attr(){
        return $this->data[0]['id'];
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
    * @return the date of the component
    */
    function get_date_attr(){
        // TODO: Convert the date to the right format
        return $this->data[0]['date'];
    }
    
    /**
    * Attribute getter method
    * 
    * @return the provider of the component
    * @uses wiziapp_extractProviderFromVideoLink()
    */
    function get_provider_attr(){
        return wiziapp_extractProviderFromVideoLink($this->data[0]['actionURL']);
    }
    
    /**
    * Attribute getter method 
    *  
    * @return the script name in the resource map to inject to the video web page 
    * @uses get_provider_attr() 
    */ 
    function get_scriptName_attr(){ 
        $provider = $this->get_provider_attr(); 
        return '@@@'.strtoupper(str_replace('.', '', $provider)).'_INJECT@@@'; 
    } 
    
    /**
    * Attribute getter method
    * 
    * @return the author of the component
    */
    function get_author_attr(){
        return $this->data[0]['author'];
    }  
    
    /**
    * Attribute getter method
    * 
    * @return the duration of the component
    */
    function get_duration_attr(){
        return round($this->data[0]['duration']/60, 2).__(" minutes");
    } 
    
    /**
    * Attribute getter method
    * 
    * @return the description of the component
    */
    function get_description_attr(){
        return wiziapp_simplifyText($this->data[0]['description']);
    }
    
    /**
    * Attribute getter method
    * 
    * @return the thumbnailURL of the component
    */
    function get_thumbnailURL_attr(){
        $image = new WiziappImageHandler($this->data[0]['thumb']);
        $size = WiziappConfig::getInstance()->getImageSize('video_album_thumb');
        return $image->getResizedImageUrl($this->data[0]['thumb'], $size['width'], $size['height']);
        
        //return $this->data[0]['thumb'];
    }

    /**
    * Attribute getter method
    * 
    * @return the actionURL of the component
    */
    function get_actionURL_attr(){
        //return 'cmd://javascript/playVid()';
        //return wiziapp_convertVideoActionToWebVideo($this->data[0]['actionURL']);
        return wiziapp_getVideoPageLink($this->data[0]['id']);
    }
    
    /**
    * Attribute getter method
    * 
    * @return the detailsURL of the component
    * @uses wiziapp_buildVideoDetailsLink()
    */
    function get_detailsURL_attr(){
        return  wiziapp_buildVideoDetailsLink($this->data[0]['id']);        
    }
}
