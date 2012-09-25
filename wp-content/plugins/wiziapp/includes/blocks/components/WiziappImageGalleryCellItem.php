<?php
/**
* The audio cell item component
* 
* The component knows how to return: itemID, title, description, thumbnailURL, actionURL,relatedPostsURL
* 
* @package WiziappWordpressPlugin
* @subpackage UIComponents
* @author comobix.com plugins@comobix.com
*/
class WiziappImageGalleryCellItem extends WiziappLayoutComponent{
    /**
    * The attribute map
    * 
    * @var array
    */
    var $attrMap = array(
        'L1' => array('itemID', 'title', 'description', 'thumbnailURL', 'actionURL', 'relatedPostsURL'),
    );
    
    /**
    * The css classes to attach to the component according to the layout
    * 
    * @var mixed
    */
    var $layoutClasses = array(
        'L1' => 'gallery_item',
    );
    
    /**
    * A processed image object, for easy retreival of the components attributes
    * 
    * @var wiziappImage
    */
    var $image = array();
    
    /**
    * The base name of the component, the application knows the component by this name
    * 
    * @var string
    */
    var $baseName = 'imageGalleryCellItem';
    
    /**
    * constructor 
    * 
    * @uses WiziappLayoutComponent::init()
    * 
    * @param string $layout the layout name
    * @param array $data the data the components relays on
    * @return WiziappImageGalleryCellItem
    */
    function WiziappImageGalleryCellItem($layout='L1', $data){
        parent::init($layout, $data);  
    }
    
    /**
    * before the wiziappLayoutComponent::process() will kick in, we need to 
    * do a bit of processing ourselfs to get the image information.
    * This component knows how to handle wordpress attachments and predefined
    * images objects that follows wiziappImage structure
    * 
    * @see wiziappImage 
    * @uses wiziappLayoutComponent::process();
    */
    function process(){
        if ( isset($this->data[1]) && $this->data[1] ){
            // We got the image from a plugin, it follows our api....
            $this->image = (object) $this->data[0];
        } else{
            // We got the image from wordpress gallery, fit it to our API
            $attachment_id = $this->data[0]->ID;
            $image = wp_get_attachment_image_src($attachment_id, 'wiziapp-iphone');
            $thumb = wp_get_attachment_image_src($attachment_id, 'wiziapp-thumbnail');
            $imageDetails = get_post($attachment_id);
            $caption = $imageDetails->post_excerpt;
            if ( empty($caption) ){
                $caption = str_replace('&amp;', '&', $imageDetails->post_title);
            }
            $this->image = new wiziappImage($thumb[0],$image[0], $imageDetails->post_content,$attachment_id,$imageDetails->post_parent,$caption);
        }  
        parent::process();
    }
    
    /**
    * Attribute getter method
    * 
    * @return the id of the component
    */
    function get_id_attr(){
        return strtolower("image_{$this->image->pid}");    
    }
    
    /**
    * Attribute getter method
    * 
    * @return the itemID of the component
    */
    function get_itemID_attr(){
        return $this->image->pid;
    }
    
    /**
    * Attribute getter method
    * 
    * @return the title of the component
    */
    function get_title_attr(){
        return $this->image->alttext;
    }
    
    /**
    * Attribute getter method
    * 
    * @return the description of the component
    */
    function get_description_attr(){
        if ( empty($this->image->description) ){
            $this->image->description = ' ';
        }
        return $this->image->description;
    }
    
    /**
    * Attribute getter method
    * 
    * @return the thumbnailURL of the component
    */
    function get_thumbnailURL_attr(){
        $image = new WiziappImageHandler($this->image->imageURL);
        $size = WiziappConfig::getInstance()->getImageSize('images_thumb');
        //return $image->getResizedImageUrl($this->image->imageURL, $size['width'], $size['height']);
        return $image->getResizedImageUrl($this->image->imageURL, $size['width'], 0);
        //return $this->image->thumbURL;
    }
    
    /**
    * Attribute getter method
    * 
    * @return the relatedPostsURL of the component
    */
    function get_relatedPostsURL_attr(){
        $url = '';
        if ( !empty($this->image->relatedPost) ) {
            $url = wiziapp_buildPostLink($this->image->relatedPost);
        } 
        
        if ( empty($url) ){
            // Try to get a page link
            $GLOBALS['WiziappLog']->write('info', "The image is: " . print_r($this->image, TRUE), 
                        'imageGalleryCellItem.get_relatedPostsURL_attr');
            $url = wiziapp_buildPageLink($this->image->relatedPost);
        }
        $GLOBALS['WiziappLog']->write('info', "The url for the related posts is: {$url}", 
                        'imageGalleryCellItem.get_relatedPostsURL_attr');
        return $url;
    }
    
    /**
    * Attribute getter method
    * 
    * @return the actionURL of the component
    */
    function get_actionURL_attr(){
        //$image = new WiziappImageHandler($this->image->imageURL);
//        $size = wiziapp_getImageSize('full_image');
//        return $image->getResizedImageUrl($this->image->imageURL, $size['width'], $size['height'], 'resize');
        return $this->image->imageURL;
    }
    
}

/**
* An image class
* 
* The class contains all the needed information for the imageGalleryCellItem to be able
* to return the image component to the application
* 
* @package WiziappWordpressPlugin
* @subpackage UIComponents
* @author comobix.com plugins@comobix.com
*/
class wiziappImage{
    /**
    * The photo/image id
    * 
    * @var integer
    */
    var $pid;
    /**
    * An URL for a thumbnail of the image
    * 
    * @var string
    */
    var $thumbURL;
    
    /**
    * The url for the full image
    * 
    * @var string
    */
    var $imageURL;
    
    /**
    * The description of the image
    * 
    * @var string
    */
    var $description;
    
    /**
    * A post id related to the image, usually the post the image is in
    * 
    * @var integer
    */
    var $relatedPost;
    
    /**
    * The alttext displayed for the image, will be used as its title
    * 
    * @var string
    */
    var $alttext;
    
    function wiziappImage($thumbURL, $imageURL, $description, $pid, $relatedPost, $alttext){
        $this->pid = $pid;
        $this->thumbURL = $thumbURL;
        $this->imageURL = $imageURL;
        $this->description = $description;
        $this->relatedPost = $relatedPost;
        $this->alttext = $alttext;
    }
}