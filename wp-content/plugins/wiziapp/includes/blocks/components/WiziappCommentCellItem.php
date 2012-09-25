<?php
/**
* The comment cell item component
* 
* The component knows how to return: 
* commentID, postID, user, content, numOfReplies, date, imageURL, actionURL,
* numOfComments, index
* 
* @package WiziappWordpressPlugin
* @subpackage UIComponents
* @author comobix.com plugins@comobix.com
*/
class WiziappCommentCellItem extends WiziappLayoutComponent{
    /**
    * The attribute map
    * 
    * @var array
    */
    var $attrMap = array(
        'L1' => array('commentID', 'postID', 'user', 'content', 'numOfReplies', 'date', 'imageURL', 'actionURL'),
        'L2' => array('date', 'commentID', 'postID', 'content', 'numOfComments', 'actionURL'),
        //'L3' => array('commentID', 'postID', 'user', 'content', 'numOfReplies', 'date', 'imageURL', 'actionURL'),
        'L3' => array('commentID', 'postID', 'user', 'content', 'numOfReplies', 'date', 'imageURL'),
	    'L4' => array('commentID', 'postID', 'user', 'content', 'numOfReplies', 'date', 'imageURL', 'actionURL'),
    );
    
    /**
    * The css classes to attach to the component according to the layout
    * 
    * @var mixed
    */
    var $layoutClasses = array(
        'L1' => 'comment',
        'L2' => 'my_comment',
        'L3' => 'parent_comment',
	    'L4' => 'sub_comment',
    );
    
    /**
    * The base name of the component, the application knows the component by this name
    * 
    * @var string
    */
    var $baseName = 'commentCellItem';
   
    /**
    * constructor 
    * 
    * @uses WiziappLayoutComponent::init()
    * 
    * @param string $layout the layout name
    * @param array $data the data the components relays on
    * @return WiziappCommentCellItem
    */
    function WiziappCommentCellItem($layout='L1', $data){
        parent::init($layout, $data);    
    }
    
    /**
    * Attribute getter method
    * 
    * @return the id of the component
    */
    function get_id_attr(){
        return "comment_{$this->data[0]->comment_ID}";
    }
    
    /**
    * Attribute getter method
    * 
    * @return the postID of the component
    */
    function get_postID_attr(){
        return (int) $this->data[0]->comment_post_ID;
    }
    
    /**
    * Attribute getter method
    * 
    * @return the user of the component
    */
    function get_user_attr(){          
        return "{$this->data[0]->comment_author} ".__('says:', 'wiziapp');
    }
    
    /**
    * Attribute getter method
    * 
    * @return the date of the component
    */
    function get_date_attr(){
        //return human_time_diff(strtotime($this->data[0]->comment_date), current_time('timestamp')) . " " . __('ago');   
	    return $this->data[0]->comment_date;
    }
    
    /**
    * Attribute getter method
    * 
    * @return the numOfReplies of the component
    */
    function get_numOfReplies_attr(){
        return wiziappGetSubCommentsCount($this->data[0]->comment_post_ID, $this->data[0]->comment_ID).__(' Replies', 'wiziapp');
    }
    
    /**
    * Attribute getter method
    * 
    * @return the content of the component
    */
    function get_content_attr(){
        return strip_tags($this->data[0]->comment_content);
    }
    
    /**
    * Attribute getter method
    * 
    * @return the commentID of the component
    */
    function get_commentID_attr(){
        return (int) $this->data[0]->comment_ID;
    }
    
    /**
    * Attribute getter method
    * 
    * @return the imageURL of the component
    */
    function get_imageURL_attr(){
        $img = get_avatar($this->data[0], WiziappConfig::getInstance()->comments_avatar_height);
        $imgArray = wiziapp_simpleHTML2Array($img);
        $imageURL = $imgArray[0]['img']['#attributes']['src'];
        return $imageURL;
    }
    
    /**
    * Attribute getter method
    * 
    * @return the actionURL of the component
    */
    function get_actionURL_attr(){
        return wiziapp_buildPostCommentSubCommentsLink($this->data[0]->comment_post_ID, $this->data[0]->comment_ID);
    }   
    
    /**
    * Attribute getter method
    * 
    * @return the numOfComments of the component
    */
    function get_numOfComments_attr(){
        $post = get_post($this->data[0]->comment_post_ID);
        return $post->comment_count;    
    }
}
