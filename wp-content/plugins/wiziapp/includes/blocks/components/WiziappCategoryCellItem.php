<?php
/**
* The category cell item component
* 
* The component knows how to return: title, numOfPosts, numOfLinks, actionURL
* 
* @package WiziappWordpressPlugin
* @subpackage UIComponents
* @author comobix.com plugins@comobix.com
*/
class WiziappCategoryCellItem extends WiziappLayoutComponent{
    /**
    * The attribute map
    * 
    * @var array
    */
    var $attrMap = array(
        'L1' => array('title', 'numOfPosts', 'actionURL'),
        'L2' => array('title', 'numOfLinks', 'actionURL'),  
        'L3' => array('title', 'actionURL'),
    );
    
    /**
    * The css classes to attach to the component according to the layout
    * 
    * @var mixed
    */
    var $layoutClasses = array(
        //'L1' => 'category',
        'L1' => 'category',
        'L2' => 'category',
        'L3' => 'tag',
    );
    
    /**
    * The base name of the component, the application knows the component by this name
    * 
    * @var string
    */
    var $baseName = 'categoryCellItem';
    
     /**
    * constructor 
    * 
    * @uses WiziappLayoutComponent::init()
    * 
    * @param string $layout the layout name
    * @param array $data the data the components relays on
    * @return WiziappCategoryCellItem
    */
    function WiziappCategoryCellItem($layout='L1', $data){
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
    * @returns the css class of the component
    */
    function get_class_attr(){
        $class = $this->layoutClasses[$this->layout];
        if ( WiziappConfig::getInstance()->zebra_lists ){
            // If the index was supplied and it is even alter the class
            $index = $this->data[1];
            if ( !empty($index) && $index%2 == 0){
                $class = $class."_even";
            }
        }
        return $class;
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
    
    /**
    * Attribute getter method
    * 
    * @return the numOfLinks of the component
    */
    function get_numOfLinks_attr(){
        $cat = $this->data[0];
        return "{$cat->count} ".__('links');
    }
    
    /**
    * Attribute getter method
    * 
    * @return the numOfPosts of the component
    */
    function get_numOfPosts_attr(){
        $cat = $this->data[0];
        return "{$cat->category_count} ".__('posts');
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
        } elseif ( $this->layout == 'L3' ){
            $link = wiziapp_buildTagLink($cat->term_id);     
        }
        return $link;
    }
    
}
