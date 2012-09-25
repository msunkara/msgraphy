<?php
/**
* 
* @package WiziappWordpressPlugin
* @subpackage pending_delete
* @author comobix.com plugins@comobix.com
* 
* @todo remove this file it's too old and mostly not needed anymore, isolate the still in use functions
*/



define("WIZIAPP_TEXT_COMP", "Paragraph");
define("WIZIAPP_MAX_IMAGE_SIZE", 100);

/**
* This function convert the received html code
* to a components array.
* 
* @param string $html the html code as a string
* @returns array $components The componenets array
*/
function wiziapp_post_to_components($html){
    $GLOBALS['WiziappLog']->write('info', "Converting post to components. the html is: \n{$html}\n", "DOM");
    $encoding = get_bloginfo('charset');
    $GLOBALS['WiziappLog']->write('debug', "The blog charset is: {$encoding}", "DOM");
    $document = new wiziappDOM2Array($html, $encoding);
    $dom = $document->getBody();
    //$GLOBALS['WiziappLog']->write('info', "got the body element:\n".print_r($dom,true)."\n", "DOM");
    $components = wiziapp_dom2components($dom);
    $GLOBALS['WiziappLog']->write('info', "got the components, returning", "DOM");
    return $components;    
}

function wiziapp_simpleHTML2Array($html){
    $encoding = get_bloginfo('charset');
    $GLOBALS['WiziappLog']->write('debug', "The blog charset is: {$encoding}", "DOM");
    $document = new wiziappDOM2Array($html, $encoding);
    $dom = $document->getBody();
    return $dom;
}
/**
* Gets an array of components definition and returns the 
* processed components ready to be displayed on a page
* as a json_encoded string
* 
* @param array $components
* @return string
*/
function wiziapp_componenets2page($components){
    $page = array();
    $total = count($components);
    for($c=0;$c<$total;++$c){
        $component = $components[$c];
        $cName = key($component);

        if ( $cName == WIZIAPP_TEXT_COMP ){
            if ( $component[$cName][0] != null ){
                $firstChildName = key($component[$cName][0]);
                $page[] = wiziapp_textComponent($firstChildName, $component[$cName]);   
            }
        } else {
            $page[] = wiziapp_specialComponent($cName, $component[$cName]);
        }
    }
    return json_encode($page);
}

/*
* wiziapp_dom2components
* 
* This function will take a dom array and return a components array.
* in order to achive it, the function will work on all the element
* childs and re-orgenize them according to the components needs.
* for example: a paregraph that contains an image in the middle
* will be splitted to paragraph, image and paraagraph
* 
*/
function wiziapp_dom2components($dom, &$parent=null){
    //echo "Got a request to go over a DOM array\n";
    //echo "A dom array contains all the top level element, and each one has childs of his own\n";
    //echo "Lets go over all the top level childs\n";
    $total = count($dom);
    $components = array();
    for($e=0 ; $e<$total ; ++$e){
        $GLOBALS['WiziappLog']->write('info', "==========================================", "DOM");   
        $GLOBALS['WiziappLog']->write('info', "Got the {$e} child", "DOM");   

        $element = $dom[$e];
        $elementName = key($dom[$e]);
        $elementContent = $dom[$e][$elementName];
        $GLOBALS['WiziappLog']->write('info', "It's a: `{$elementName}`", "DOM");   

        if( isset($elementContent['childs']) && !empty($elementContent['childs']) && $elementName != "#text" ){
            // Process the childs first, we are processing from the bottom to the top
            $returnedElement = wiziapp_dom2components($elementContent['childs'], $element);
            // the element might have been altered or even deleted
            $newElementName = key($element);
            if ( !empty($returnedElement) ){
                $element[$newElementName]['childs'] = $returnedElement;                
            }

            //echo "One of the childs might have altered the entire family tree\n";
            // is the element the same?
            if ( $dom[$e] != $element ){
                // if it is not the same, tell the parent of his reformed child
                if ( $parent != null ){
                    $parentName = key($parent);
                    $parent[$parentName]['childs'][$e] = $element;
                }
            }
        } else { // The element doesn't have children or it's a text element
            //echo "Need to process it by the componenets rules:\n";
            //echo "Rules might apply to next element, parent element\n";
            if ( $elementName != "#text"){
                wiziapp_sortElement($element, $parent, $dom[$e+1], $e);            
            }   
        }
        $GLOBALS['WiziappLog']->write('info', "Done with this round, move on...", "DOM");   
        $GLOBALS['WiziappLog']->write('info', "==========================================", "DOM");   
        if ( $element != null ){
            $components[] = $element;
        }
    } // end first for loop
    /* 
    * The element childs are sorted in this point, but they might need 
    * to be combined and converted to componenets
    */
    if ( !empty($components) ){
        $GLOBALS['WiziappLog']->write('info', "About to combine the elements", "DOM");   
        $components = wiziapp_compactComponents($components);
        $GLOBALS['WiziappLog']->write('info', "The elements are combined", "DOM");   
    }
    $GLOBALS['WiziappLog']->write('info', "Finished going over: ".(($parent!=null)?key($parent):"top level"), "DOM");   
    //echo "Finished going over: ".(($parent!=null)?key($parent):"top level")."\n";
    return $components;
}

/*
* This function will compact the components array by 
* removing empty components and combining text components 
* when possible
*/
function wiziapp_compactComponents($components){
    $total = count($components);
    $sortedChilds = array();
    $normalIndex = 0;
    for ( $c=0 ; $c < $total ; ++$c ){
        if ( !empty($components[$c]) ){
            $childName = key($components[$c]);
            if ( wiziapp_isElementSpecial($childName) ){
                $sortedChilds[] = $components[$c];
                $normalIndex = count($sortedChilds);
            } else {
                // Prepare the space for the child
                if ( !isset($sortedChilds[$normalIndex]) ){
                    $sortedChilds[$normalIndex] = array(WIZIAPP_TEXT_COMP => array());
                }

                /*
                * if the child element has children of his own
                * they had already been processed and therefore 
                * we might have some WIZIAPP_TEXT_COMP there to combine and split acordingly
                */   

                if ( isset($components[$c][$childName]['childs']) &&
                    !empty($components[$c][$childName]['childs'])){

                    $subChildTotal = count($components[$c][$childName]['childs']);
                    for($s=0;$s<$subChildTotal;++$s){
                        // Since the child can be just the text, check for it
                        if ( $childName == '#text' ){
                            // The text child has only one child and one child only
                            $sortedChilds[$normalIndex][WIZIAPP_TEXT_COMP][] = $components[$c];
                        } else {
                            $subChildName = key($components[$c][$childName]['childs'][$s]);
                            $subComponent = $components[$c][$childName]['childs'][$s];

                            if ( empty($subComponent[$subChildName]) ) continue;

                            if ( $subChildName == WIZIAPP_TEXT_COMP){
                                // The element is like us! lets combine
                                // Since this is an inner loop we need to take care of the new element
                                if ( !isset($sortedChilds[$normalIndex]) ){
                                    $sortedChilds[$normalIndex] = array(WIZIAPP_TEXT_COMP => array());
                                } 

                                // We need to keep the structure of the parent...
                                //$childName = key($components[$c]); 
                                $subChildParent = array($childName => array());
                                if ( isset($components[$c][$childName]['#attributes'])){
                                    $subChildParent[$childName]['#attributes'] = $components[$c][$childName]['#attributes'];
                                }
                                /*if ( isset($components[$c][$childName]['#text'])){
                                    $subChildParent[$childName]['#text'] = $components[$c][$childName]['#text'];
                                } */
                                $subChildParent[$childName]['childs'] = $subComponent[$subChildName];   
                                // Merge our object with the current one
                                $sortedChilds[$normalIndex][$subChildName][] = $subChildParent;

                                //$sortedChilds[$normalIndex][$subChildName] = array_merge($sortedChilds[$normalIndex][$subChildName], $subComponent[$subChildName]);
                            } else {
                                // This is a special one, better treat it this way
                                $sortedChilds[] = $subComponent;
                                $normalIndex = count($sortedChilds);
                            }
                        }
                    }
                } else {
                    /* 
                    * No childrens involve, add this element    
                    * but first make sure there is something to add...
                    */
                    if( wiziapp_NodeIsNotEmpty($components[$c], $childName) ){
                        $sortedChilds[$normalIndex][WIZIAPP_TEXT_COMP][] = $components[$c];
                    }
                }
            }
        }
    }
    // We finished combining what we could to text components
    // If our child childern had a split we need to take care of that...
    // there is no point in saving empty text container to wrap WIZIAPP_TEXT_COMP

    return $sortedChilds;
}

function wiziapp_NodeIsNotEmpty($component, $childName){
    $notEmpty = FALSE;
    // Some elements are always empty
    if ($childName == 'br' || $childName == 'hr' ){
        $notEmpty = TRUE;
    } else {
        /**
        * The class element can't effect the client since 
        * it doesn't handle more then one class, which is our class
        */
        //if ( !empty($component[$childName]["#text"]) ||
        if (!empty($component[$childName]["#attributes"]["style"])){
            // If there is not text or style, there is nothing to add
            $notEmpty = TRUE;
        }    
    }

    return $notEmpty;
}

/*
* This function process a childless element, and 
* convert it to a component ready element
*/
function wiziapp_sortElement(&$element, &$parent=null, &$next_element=null, $parent_index=0){
    //echo "*****Got an element to process:\n";
    //echo "The element is: ".key($element)."\n";)
    if ( $next_element != null ){
        //  echo "The next one is: ".key($next_element)."\n";    
    }
    $parentName = ($parent!=null)?key($parent):FALSE;

    $elementName = key($element); 
    $GLOBALS['WiziappLog']->write('info', "Sorting element: {$elementName}", "DOM");   
    if ( wiziapp_isElementSpecial($elementName) ){
        // This is a special element, it might require special treatment
        // special case: link with a special element in it
        if( $parentName == 'a' ){
            // Does any other childs exists for this parent
            $pChildsCount = count($parent[$parentName]['childs']);
            $GLOBALS['WiziappLog']->write('info', "Found a special element with a link as a parent, it has {$pChildsCount} childs", "DOM");   
            /* 
            * If it has just one child, which is the current element, 
            * replace the element all toghter
            */
            if ($pChildsCount == 1){
                $element[$elementName]['link'] = $parent[$parentName]['#attributes']['href'];
                $parent = $element;
                $element = null;
            } else {
                if ($pChildsCount == 2){    
                    $oChildIndex = ($parent_index==1)?0:1;
                    $oChildName = key($parent[$parentName]['childs'][$oChildIndex]);
                    $oChild = $parent[$parentName]['childs'][$oChildIndex][$oChildName];
                    if( $oChildName == '#text' ){
                        $trimmedValue = trim(wiziapp_preparePText($oChild));
                        $GLOBALS['WiziappLog']->write('info', "The other child is:{$trimmedValue}", "DOM");       
                        $element[$elementName]['link'] = $parent[$parentName]['#attributes']['href'];

                        if( !empty($trimmedValue) ){
                            $element[$elementName]['text'] = $oChild;
                        } 
                        
                        $parent = $element;
                        $element = null;
                    }
                    
                }
                $GLOBALS['WiziappLog']->write('info', "The childs are:".print_r($parent[$parentName]['childs'], TRUE), "DOM");       
            }
        }
    } else {
        // Since this is a childless element, let's make sure its needed
        if(!wiziapp_NodeIsNotEmpty($element, $elementName)){
            // it's empty not point in keeping it
            $element = null;
        }
    }
}     


function wiziapp_isElementSpecial($tag){
    $specialTags = array(
    'script', 'style', 'img',
    'video', 'object', 'embed','param',
    'form', 'input', 'select',
    'button','table','iframe',
    );
    $isSpecial = in_array($tag, $specialTags);
    return $isSpecial;                       
}

/**
* This function convert the image src urls to something the mobile application can use
* by handling the height and width of the image
* 
* TODO: Add the logic of create images urls
* 
* @param string $src
*/
function wiziapp_mobifyImgUrl($src){
    if ( empty($src) ){ return; }
    $GLOBALS['WiziappLog']->write('info', "Converting the image src from:{$src}", "DOM");
    error_reporting(0);
    // Get the real path of the image
    $blog_url = get_bloginfo('wpurl');
    $GLOBALS['WiziappLog']->write('info', "Checking if the src includes :{$blog_url}", "DOM");
    
    if ( strpos($src, $blog_url) === 0 || strpos($src, "http://") === FALSE ){
        // Make sure that this image url is not for a known plugin that can handle the resize itself
        $handled = FALSE;
        if ( strpos ($src, "index.php") !== FALSE ){
            // This is a callback...
            // try to see if there is something we can use
            $query = substr($src, strpos($src, "index.php")+strlen("index.php")+1);
            $params = explode("&", $query);
            $GLOBALS['WiziappLog']->write('info', "Got a query string like:".print_r($params, true), "DOM");    
            $handled = true;
            for ( $p=0,$total=count($params); $p<$total; ++$p){
                list($key, $val) = explode("=", $params[$p]);
                $GLOBALS['WiziappLog']->write('info', "Got a params key:{$key} and val is:{$val}", "DOM");    
                $tKey = strtolower($key);
                if ( $tKey == 'height' || $tKey == 'width' || $tKey == 'size' ){
                    if ( $val > WIZIAPP_MAX_IMAGE_SIZE ){
                        $params[$p] = $key.'='.WIZIAPP_MAX_IMAGE_SIZE;
                    }
                }
            }
            $query = implode("&", $params);
            $src = "{$blog_url}/index.php?{$query}";
        }
        
        if ( !$handled ){
            $GLOBALS['WiziappLog']->write('info', "The image src contains the blog url:{$blog_url}", "DOM");
            // Remove the blog url so we can try to get the real path of the image
            $sub_src = str_replace($blog_url."/", "", $src);
            $GLOBALS['WiziappLog']->write('info', "The image src without the blog url is:{$sub_src}", "DOM");
            /*if ( strpos($sub_src, "/") !== 0 ){
                $sub_src = "/{$sub_src}";
            }
            $GLOBALS['WiziappLog']->write('info', "The src after the slash check/fix is:{$sub_src}", "DOM");
            */
            $full_path = WIZI_ABSPATH.$sub_src;
            $GLOBALS['WiziappLog']->write('info', "Requesting resize from wordpress for:{$full_path}", "DOM");
            require_once(ABSPATH . '/wp-admin/includes/image.php');
            $new_src = image_resize($full_path, WIZIAPP_MAX_IMAGE_SIZE, WIZIAPP_MAX_IMAGE_SIZE);
            if ( is_wp_error($new_src) ){
                // didn't work...
                $src = $new_src->get_error_message();
                
            } else {
                if ( empty($new_src) ){
                    // There was no need to replace the path since the image is small enough
                } else {
                    // Replace the absolute path with the blog url
                    $sub_src = str_replace(WIZI_ABSPATH, "/", $new_src);
                    $src = $blog_url.$sub_src;        
                }
            }            
        }
    } else { // The image is from an external website...
        // Supply a web address for the admin to handle
        //$src = "http://api.apptelecom.com/application/1/image/".urlencode($src)."/size/".WIZIAPP_MAX_IMAGE_SIZE;        
        //$src = "http://api.apptelecom.com/application/1/image?url=".$src."&size=".WIZIAPP_MAX_IMAGE_SIZE;        
        $src = '';
    }

    return $src;
}

function wiziapp_buildComponentParams($tag, $element){
    $GLOBALS['WiziappLog']->write('info', "Building params for:{$tag}", "DOM");
    $params = array();

    $params['id'] = isset($element['id'])?$element['id']:'';
    switch($tag){
        case 'img':
            $params['url'] = wiziapp_mobifyImgUrl($element['#attributes']['src']); 
            $title = '';
            if ( isset($element['#attributes']['title']) ){
                $title = $element['#attributes']['title'];
            } else {
                if ( isset($element['#attributes']['alt']) ){
                    $title = $element['#attributes']['alt'];
                }
            }
            $params['title'] = $title;
            $params['link'] = isset($element['link'])?$element['link']:'';
            break;
        case 'navigation':
            /**
            *  Navigation params are in a loop of links
            * therefore we need to build an array here
            * Navigation is one of the components that are
            * built manually should contain everything as needed
            */
            $params['links'] = $element[$tag]['links'];
            unset($params['id']);
            break;
        default:
            // Should never happen
    }
    /* The client only support one class
    if(isset($element['#attributes']['class'])){
    $params['cssClasses'] = $element['#attributes']['class'].' '.$tag.'_css';   
    } else {*/
    $params['cssClasses'] = isset($element['class']) ? $element['class'] : $tag.'_css';
    //$GLOBALS['WiziappLog']->write('info', "The params are:".print_r($params, TRUE), "DOM");
    //$GLOBALS['WiziappLog']->write('info', "For element:".print_r($element, TRUE), "DOM");
    //}
    return $params;
}

function wiziapp_specialComponent($tag, $element){
    // Get the markup template for the application
    $params = wiziapp_buildComponentParams($tag, $element);

    /**
    * Extract the associative array to local variables
    * by having the params defined this way they will 
    * automatically be injected into the markup string
    */
    extract($params);
    
    /**
    * Get the application configuration for the component 
    * and the layout
    * 
    * TODO: Move components layout to a configuration file
    * that can be refreshed from the admin
    */
    $componentLayouts = array(
        "img" => array("name"=>"image", "layout"=>"L1"),
        "navigation" => array("name"=>"navigation", "layout"=>"L2"),
        "text" => array("name"=>"text", "layout"=>"L1"),
        "links" => array("name"=>"links", "layout"=>"L1"),
        "buttons" => array("name"=>"buttonBar", "layout"=>"L1"),
    );

    $layoutName = $componentLayouts[$tag]['layout'];
    $component = $componentLayouts[$tag]['name'];

    // TODO: After we know we handled the needed tags, remove this.
    if ( empty($layoutName) || empty($component) ) return "<TBD tag={$tag}/>";

    $layoutsDir = dirname(__FILE__).DIRECTORY_SEPARATOR.'layouts';
    // Override layout by the existing parameters
    $rules_path = $layoutsDir.DIRECTORY_SEPARATOR.$component.'_layout_rules.php';
    if ( is_readable($rules_path) ){
        include($rules_path);    
    }

    // Load the layout file
    require $layoutsDir.DIRECTORY_SEPARATOR.$component.DIRECTORY_SEPARATOR.$layoutName.".php";
    // return the layout string
    if ( !isset($id) ){
        $id = md5(uniqid(rand(), true));      
    }
    
    $markup = array(
        ucfirst($component) => array(
            "layout" => "{$layoutName}",
            "id" => $id,
            "class" => "{$cssClasses}",
            "params" => $layoutParams,
        )
    );
    return $markup;
}

/*
* A text component have just one layer to display the text
* it supports a variation of (x)html tags
* TODO: Add the tags support
*/
function wiziapp_textComponent($name, $element){
    // A text element might have several elements under it.
    $css = 'paragraph_css';
    // Make sure it's not empty
    if ( $element == null ) return FALSE;
    
    $object = array(
        "Paragraph" => array(
            "class" => "{$css}",
            "params" => array(
                "CDATA" => wiziapp_buildElementMarkup($element),            
            )
        )
    );
    
    return $object;
}

/**
* This function converts urls so we will be able to use them 
* in the mobile application. It works with the application url 
* scheme. 
* 
* @param string $url
*/
function wiziapp_mobifyUrl($url){
    $GLOBALS['WiziappLog']->write('info', "Converting the url from:{$url}", "DOM");
    $post_id = url_to_postid($url);
    if ( $post_id ){
        // Check if this is a page or a post
        $post = get_post($post_id);
        // Its a post convert this link to our callback
        // TODO: Change according to the client protocol, this link will be used for internal navigation
        $url = get_bloginfo('wpurl')."/wiziapp/content/{$post->post_type}/{$post_id}";
    } 
    return $url;
}

/*
* wiziapp_transformValidAttributes
* Accept the parameters by reference since it needs to convert them to 
* a valid format. The function will check if the attribute name is valid
* and if it require special handling like rebuilding the css style and will
* reformat what ever is nessecary to reach a valid attribute pair for the 
* mobild application. Since the mobile application treats links differently then the web
* this function will also request href convertion
* Input:
*  &$attr_name @type string the attibute name, valid attributes are: id, style, class
*  &$attr_value $type string the attribute value, it will be converted as nessecary
*/
function wiziapp_transformValidAttributes(&$attr_name, &$attr_value){
    /** 
    * The client application can't handle multiple classes so
    * class is invalid attribute. the id is also ours to support 
    * future events
    */
    //$validAttributes = array("id","style","class", "href");
    $validAttributes = array("style","href");
    if ( in_array($attr_name, $validAttributes)){
        // Handle special cases
        if ( $attr_name == 'href' ){
            // Convert the href to our format
            $attr_value = wiziapp_mobifyUrl($attr_value);
        } elseif ( $attr_name == 'style' ){
            /* 
            * Only certain css is valid...
            * break down the css and built it again with only valid elements
            * The names of the css attribtes might need to be replaced
            */
            $validInlineCSS = array("color"=>FALSE,"font-size"=>"size");
            $cssAttributes = explode(";", $attr_value);
            // In case we have some empty values, remove them
            $cssAttributes = array_filter($cssAttributes);  
            for($a=0,$total=count($cssAttributes);$a<$total;++$a){
                // inline css is like: style="color:#ff00ff;font-size:12px; extract the current name and value"
                list($cssName, $cssValue) = explode(':', $cssAttributes[$a]);
                if(isset($validInlineCSS[$cssName])) {
                    // The css attribute is set so it's valid, now check if it needs replacement
                    if ( $validInlineCSS[$cssName] !== FALSE ){
                        $cssName = $validInlineCSS[$cssName];
                        $cssAttributes[$a] = "{$cssName}:{$cssValue}";
                    }
                } else {
                    /* 
                    * It's not valid so we need to remove it from the array
                    * Since really removing the element from the array can hurt the for loop we are
                    * just marking it with null 
                    */
                    $cssAttributes[$a] = null;
                }
            } 
            /* 
            * Now that the loop is over and the array contain valid attributes with valid name
            * rebuild the attribute value
            */
            // first remove the nulls by using array filter with no callback
            $cssAttributes = array_filter($cssAttributes);
            if ( count($cssAttributes) > 0 ){
                $attr_value = implode(";", $cssAttributes).";";    
            } else {
                // If we have no style attribute value there is no point in keeping this attribute, save a few bits
                $attr_name = "";
            }

        }
    } else {
        // The attribute is not valid, clear it out
        $attr_name = "";
    }
}

/**
* wiziapp_preparePText
* 
* Since the client will format the text by the html tags
* and won't display the source there is no point in keeping 
* the problematic special text formating chars. 
* This function removes them from the text
* 
* TODO: Refactor this for better performance 
* 
* @param string $str    The text from the html elements
* @return string        The string without special text formating chars
*/
function wiziapp_preparePText($str){
    $str = str_replace("\r\n", '', $str);
    $str = str_replace("\n", '', $str);
    $str = str_replace("\t", '', $str); 
    return $str;
}
function wiziapp_buildElementMarkup($element_array){
    // Might get one or more elements, under the main element
    $total = count($element_array);
    $markup = '';

    for($e=0;$e<$total;++$e){
        $name = key($element_array[$e]);
        $element = $element_array[$e][$name];
        if ( $name == '#text' ){
            $markup .= wiziapp_preparePText($element);
        } else {
            $attributes = '';
            if ( isset($element['#attributes']) ){
                foreach($element['#attributes'] as $attr_name => $attr_value){            
                    wiziapp_transformValidAttributes($attr_name, $attr_value);
                    if ( !empty($attr_name) ){
                        $attributes .= " {$attr_name}=\"{$attr_value}\"";   
                    }
                }
            }
            $id = isset($element["id"])?$element['id']:md5(uniqid(rand(), true));
            $markup .= "<{$name} id=\"{$id}\" class=\"{$name}_css\"{$attributes}>";   
            if ( isset($element['childs']) ){
                $markup .= wiziapp_buildElementMarkup($element['childs']);
            } 
            $markup .= "</{$name}>";   
        }
    }
    return $markup; 
}

/*
* This class will create an array from a given html string
* The input string should include only the body content since
* the class will wrap the html with a full html header in order to 
* handle the encoding correctly.
* 
* Requires: php5+ since it uses DOMDocument
* Usage: 
* $document = new wiziappDOM2Array($html, $encoding);
* $dom = $document->getBody();
*/

class wiziappDOM2Array{
    /**
    * Used for the encoding of the xml document before 
    * converting to the array
    * 
    * @var string
    */
    var $encoding;

    /**
    * Used to save the html loaded as XML
    * 
    * @var DOMDocument
    */
    var $dom;

    /**
    * Old-school Constructor
    * 
    * @param string $html       The html block, not a full document
    * @param string $encoding   The encoding used for the html block
    * @return wiziappDOM2Array   The html element as an array
    */
    function wiziappDOM2Array($html='', $encoding=''){
        if ( !empty($encoding) ){
            // TODO: Not all encoding are supported, add a check
            $this->encoding = $encoding;
        }
        $this->encoding = $encoding;        
        if (!empty($html)){
            // Remove new lines and special chars from the string
            $html = str_replace("\r\n", '', $html);
            $html = str_replace("\n", '', $html);
            $html = str_replace("\t", '', $html);            
            // Wrap the html block with a full document to handle the encoding right
            $fullHtml = "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=\"{$this->encoding}\"/></head><body>{$html}</body></html>";
            $this->dom = new DOMDocument();
            @$this->dom->loadHTML($fullHtml);
            $this->dom->preserveWhiteSpace = false; 
        }
        return;
    }

    /**
    * The main processing function, will transform the received elements to an array
    * 
    * @param mixed $node
    */
    function _process($node){
        $arr = null;
        if ( $node->nodeName == 'head' || $node == null ){
            // We added the head, no need to process it
            return;
        }

        if($node->nodeType == XML_TEXT_NODE) {
            // Handle text
            return $this->_handleText($node->nodeValue);
        } else {
            if($node->hasChildNodes()){
                $children = $node->childNodes;
                for($i=0,$total=$children->length; $i<$total; $i++) {
                    $child = $children->item($i);
                    if ( !isset($arr['childs'])) {
                        $arr['childs'] = array(); 
                    }
                    if($child->nodeName != '#text') {
                        $childElement = $this->_process($child);
                        if ( $childElement != null ){
                            $arr['childs'][] = array($child->nodeName => $childElement);       
                        }
                    } else {
                        // The child is text, is it along? do we need to wrap it?
                        $val = $this->_handleText($child->nodeValue);  
                        if ( $node->nodeName == 'body' ){
                            // We can't have direct text under the body, need to wrap it in a span
                            $arr['childs'][] = array('span' => array(
                                'childs' => array(array('#text'=>$val)),
                                'id' => $this->_generateUniqueId(),
                            ));
                        } else {
                            $arr['childs'][] = array('#text' => $val);       
                        }
                    }
                }
            }
            if($node->hasAttributes()) { 
                $attributes = $node->attributes;
                if(!is_null($attributes)) {
                    $arr["#attributes"] = array();
                    foreach ($attributes as $key => $attr) {
                        $arr["#attributes"][$attr->name] = $attr->value;
                    }
                }
            }
            // Add a unique id  
            if ( $arr != null ){
                $arr["id"] = $this->_generateUniqueId(); 
            }
        }

        return $arr;
    }

    function _handleText($str){
        $str = htmlentities($str, ENT_COMPAT, $this->encoding);   
        return $str;
    }

    function _generateUniqueId(){
        return md5(uniqid(rand(), true));    
    }

    /**
    * getBody
    * 
    * triggers the processing of the dom and returns all the childs under
    * the body element. meaning it will omit the wrapping the constractor added.
    * 
    */
    function getBody(){
        // Process the dom
        $dom = $this->_process($this->dom);
        return $dom["childs"][0]["html"]["childs"][0]["body"]["childs"];  
    }
}