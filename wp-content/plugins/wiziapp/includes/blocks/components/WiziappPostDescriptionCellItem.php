<?php
/**
* The audio cell item component
* 
* The component knows how to return: title, author, date, numOfComments, numOfUserComments, imageURL, actionURL, contents, categories, rating
* 
* @package WiziappWordpressPlugin
* @subpackage UIComponents
* @author comobix.com plugins@comobix.com
* 
*/
class WiziappPostDescriptionCellItem extends WiziappLayoutComponent{
    /**
    * A wordpress processed post
    * 
    * @var WP_Post
    */
    var $post;
    
    /**
    * The attribute map
    * 
    * @var array
    */
    var $attrMap = array(
        'L1' => array('title', 'author', 'date', 'numOfComments', 'imageURL', 'actionURL', 'contents'),
        'L2' => array('title', 'author', 'date', 'numOfComments', 'numOfUserComments', 'imageURL', 'actionURL', 'contents'),
        'L3' => array('title', 'author', 'date', 'numOfComments', 'categories', 'imageURL', 'actionURL', 'contents'),
        'L4' => array('title', 'date', 'numOfComments', 'imageURL', 'actionURL', 'contents'),
        //'L5' => array('title', 'author', 'description', 'date', 'rating', 'imageURL', 'actionURL', 'contents'),
        'L5' => array('title', 'author', 'date', 'rating', 'imageURL', 'actionURL', 'contents'),
        'L6' => 'L1',
        'L7' => array('title', 'imageURL', 'actionURL', 'contents'),
        'L8' => array('title', 'imageURL', 'actionURL'),
    );

    var $attrIgnoreAddOverride = array(
        'L5' => array('numOfComments'=>FALSE),
    );
    /**
    * The css classes to attach to the component according to the layout
    * 
    * @var mixed
    */
    var $layoutClasses = array(
        'L1' => 'general_post',
        'L2' => 'commented_post',
        'L3' => 'archived_post',
        'L4' => 'user_post',
        'L5' => 'featured_post',
        'L6' => 'video_post',
        'L7' => 'mini_post',
        'L8' => 'mini_post',
    );
    
    /**
    * Possible images sizes according to the layout
    * 
    * @var array
    */
    var $imageSizes = array(
        'default' => 'posts_thumb',
        'L5' => 'featured_post_thumb',
        'L7' => 'mini_post_thumb',
    );
    
    /**
    * Possible thumbnail limits according to the layout
    * 
    * @var array
    */
    var $thumbnailLimits = array(
        'default' => 'limit_posts_thumb',
        'L5' => 'limit_featured_post_thumb',
        'L7' => 'limit_mini_post_thumb',
    );
    
    /**
    * The base name of the component, the application knows the component by this name
    * 
    * @var string
    */
    var $baseName = 'postDescriptionCellItem';
    
    /**
    * constructor 
    * 
    * @uses WiziappLayoutComponent::init()
    * 
    * @param string $layout the layout name
    * @param array $data the data the components relays on
    * @return WiziappPostDescriptionCellItem
    */
    function WiziappPostDescriptionCellItem($layout='L1', $data, $process=TRUE){
        parent::init($layout, $data, $process);    
    }
    
    /**
    * before the wiziappLayoutComponent::process() will kick in, we need to 
    * do a bit of processing ourselfs to get the post information from wordpress.
    * 
    * @uses wiziappLayoutComponent::process();
    */
    function process(){
        $this->post = get_post($this->data[0]);
        parent::process();
    }
    
    /**
    * attribute getter method
    * 
    * @todo refactor this, the app will use a dynamic layout called L0, we still need the layout name
    * to be able to connect to the right attributes and class name
    * @returns the layout map for the component
    */
    function get_layout_attr(){
        return 'L0';
    }
    
    /**
    * Attribute getter method
    * 
    * @return the id rating the component
    */
    function get_rating_attr(){
        return round(wiziapp_get_rating($this->data[0]));
    }
    
    /**
    * Attribute getter method
    * 
    * @return the description of the component
    */
    function get_description_attr(){
        $desc = $this->post->post_excerpt;            
        if ( empty($this->post->post_excerpt) ){
            // No need for the full processing... can be quite expensive
            //$desc = wiziapp_process_content($this->post->post_content);
            
            /**wiziapp_removeKnownfilters();
            $desc = apply_filters('the_content', $this->post->post_content);*/
            
            $desc = preg_replace('/\[(.*?)\]/', '', $this->post->post_content);
        }
        
        return wiziapp_makeShortString(trim(strip_tags($desc)), 45);
    }
    
    /**
    * Attribute getter method
    * 
    * @return the categories of the component
    */
    function get_categories_attr(){
        foreach((get_the_category($this->data[0])) as $category) { 
            $categories[] = $category->cat_name; 
        }   
        
        return implode(",", $categories);       
    }
    
    /**
    * Attribute getter method
    * 
    * @return the contents of the component
    */
    function get_contents_attr(){
        $value = array('headers' => wiziapp_content_get_post_headers(FALSE), 'data'=>'');    
        $contents = $this->data[1];          
        if ( $contents != null ){
            $value['data'] = $contents;
        }
        return $value;
    }
    
    /**
    * Attribute getter method
    * 
    * @return the imageURL of the component
    */
    function get_imageURL_attr(){
        $type = $this->imageSizes['default'];
        if ( isset($this->imageSizes[$this->layout]) ){
            $type = $this->imageSizes[$this->layout];
        }
        $size = WiziappConfig::getInstance()->getImageSize($type);
        $limitSize = WiziappConfig::getInstance()->getImageSize('limit_post_thumb');
    
        return wiziapp_getPostThumbnail($this->post, $size, $limitSize, FALSE);     
    }
    
    /**
    * Attribute getter method
    * 
    * @return the numOfComments of the component
    */
    function get_numOfComments_attr(){
        return $this->post->comment_count;
    }          
    
    /**
    * Attribute getter method
    * 
    * @return the numOfUserComments of the component
    */
    function get_numOfUserComments_attr(){
        return $this->data[1];
    }
    
    /**
    * Attribute getter method
    * 
    * @return the date of the component
    */
    function get_date_attr(){
        $dateStr = wiziapp_formatDate(strip_tags($this->post->post_date));
        //return '| '.$dateStr;
        return $dateStr;
        
    }
    
    /**
    * Attribute getter method
    * 
    * @return the author of the component
    */
    function get_author_attr(){
        $authorInfo = get_userdata($this->post->post_author);
        $authorName = $authorInfo->display_name;
        if ( strlen($authorName) > 15 ){
            $authorName = substr($authorName, 0, 12).'...';
        }
        //return __('By')." {$authorName}";
        /**$prefix = "| ";
        if ( isset($this->themeRemoveAttr) ){
            if ( in_array('date', $this->themeRemoveAttr) ){
                $prefix = '';
            }
        }*/
        $prefix = ' ';
        return "{$prefix}{$authorName}";
    }
    
    /**
    * Attribute getter method
    * 
    * @return the id of the component
    */
    function get_id_attr(){
        return "post_{$this->data[0]}";    
    }
    
    /**
    * Attribute getter method
    * 
    * @return the tile of the component
    */
    function get_title_attr(){
        //return wiziapp_makeShortString(strip_tags($this->post->post_title), 28);
        return strip_tags($this->post->post_title);
    }
    
    /**
    * Attribute getter method
    * 
    * @return the actionURL of the component
    */
    function get_actionURL_attr(){
        $post_id = $this->data[0];
        return wiziapp_buildPostLink($post_id);
    }    
}
