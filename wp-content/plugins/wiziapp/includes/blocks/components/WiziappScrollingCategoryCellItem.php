<?php
/**
* The scrolling category cell item component
* 
* The component knows how to return: title, numOfPosts, numOfLinks, actionURL
* 
* @package WiziappWordpressPlugin
* @subpackage UIComponents
* @author comobix.com plugins@comobix.com
*/
class WiziappScrollingCategoryCellItem extends WiziappLayoutComponent{
    /**
    * The attribute map
    * 
    * @var array
    */
    var $attrMap = array(
        'L1' => array('title', 'items'),
    );
    
    /**
    * The css classes to attach to the component according to the layout
    * 
    * @var mixed
    */
    var $layoutClasses = array(
        'L1' => 'scrolling_category',
    );
    
    /**
    * The base name of the component, the application knows the component by this name
    * 
    * @var string
    */
    var $baseName = 'scrollingCategoryCellItem';
    
     /**
    * constructor 
    * 
    * @uses WiziappLayoutComponent::init()
    * 
    * @param string $layout the layout name
    * @param array $data the data the components relays on
    * @return WiziappCategoryCellItem
    */
    function WiziappScrollingCategoryCellItem($layout='L1', $data){
        parent::init($layout, $data);    
    }
    
    /**
    * Attribute getter method
    * 
    * @return the id of the component
    */
    function get_id_attr(){
        $cat = $this->data[0];
        $id = '';
        if ( !empty($cat->cat_ID) ){
            $id = $cat->cat_ID;
        } else {
            $id = $cat->term_id;
        }
        return "cat_{$id}";    
    }
    
    /**
    * Attribute getter method
    * 
    * @return the title of the component
    */
    function get_title_attr(){
        $cat = $this->data[0];
        $name = '';
        if ( !empty($cat->cat_name) ){
            $name = $cat->cat_name;
        } else {
            $name = $cat->name;
        }
        return wiziapp_makeShortString($name, 22);
    }
    
    function get_items_attr(){
        $items = $this->data[1];
        
        return $items;
    }
    
    /**
    * Attribute getter method
    * 
    * @return the actionURL of the component
    */
    function get_actionURL_attr(){
        $link = '';
        $cat = $this->data[0];
        if ( $this->layout == 'L1' ){
            $link = wiziapp_buildCategoryLink($cat->cat_ID);     
        } elseif ($this->layout == 'L2' ){
            $link = wiziapp_buildLinksByCategoryLink($cat->term_id);     
        }
        return $link;
    }
    
}
