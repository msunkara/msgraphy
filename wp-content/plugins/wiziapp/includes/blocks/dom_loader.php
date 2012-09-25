<?php
/**
* Takes an HTML string an convert it to an array of elements
* @package WiziappWordpressPlugin
* @subpackage DOMParser
* @author comobix.com plugins@comobix.com
*/
class WiziappDOMLoader{
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
    * @return WiziappDOMLoader   The html element as an array
    */
    function WiziappDOMLoader($html='', $encoding='UTF-8'){
        $this->encoding = $encoding;
        
        if (!empty($html)){
            $html = $this->prepareHTMLString($html);
            $this->dom = new DOMDocument('1.0', $this->encoding);
            libxml_use_internal_errors(true);
            @$this->dom->loadHTML($html); 
            $this->dom->encoding = $this->encoding;
            libxml_clear_errors(); 
            
            $this->dom->preserveWhiteSpace = false; 
        }
        return;
    }
    
    function prepareHTMLString($html){
        // Remove new lines and special chars from the string
        $html = str_replace("\r\n", '', $html);
        $html = str_replace("\n", '', $html);
        $html = str_replace("\t", '', $html);            
        /** 
        * Wrap the html block with a full document to handle the encoding right
        * Add the < ? xml tag to force the encoding.... silly but needed in order to use the DOM objects functions like
        * saveHTML
        */
        return '<?xml encoding="'.$this->encoding.'">'."<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=\"{$this->encoding}\"/></head><body>{$html}</body></html>";
    }
    
    /**
    * getNodeAsHTML
    * 
    * After processing the html to an array of DOM elements we need
    * we need to be able to save the html content of a certain element
    * this method will allow us to do that by converting back to a clean 
    * html string
    * 
    * @param array $node
    * @return string
    */
    function getNodeAsHTML($node){
        $html = '';
        if ( !empty($node) ){
            for ( $n=0,$total=count($node); $n < $total; ++$n){
                $tagName = key($node[$n]);
                $element = $node[$n][$tagName];
                if ( !is_array($element) && $tagName == 'text' ){
                    $html .= "{$element}";
                } else {
                    // Process the attributes
                    $attributes = isset($element['attributes'])?$element['attributes']:array();
                    $tmpAttr = array();  
                    foreach($attributes as $attrName => $attrVal){
                        $tmpAttr[] = "{$attrName}=\"{$attrVal}\"";
                    }
                    $attributesStr = ' '.implode(' ', $tmpAttr);
                    
                    // Get the text
                    $text = '';
                    if ( isset($element['text']) ){
                        $text = $element['text'];
                    }
                    $childs = $this->getNodeAsHTML(isset($element['childs'])?$element['childs']:array());
                    // Take into account self closing tags
                    if ( in_array($tagName, array('area', 'base', 'basefont', 'br', 'hr', 'input', 'img'))
                        && empty($childs) && empty($text) ){
                        $html .= "<{$tagName}{$attributesStr} />";
                    } else {
                        
                        $html .= "<{$tagName}{$attributesStr}>{$text}{$childs}</{$tagName}>";
                    }
                }
            }    
        } 
        return $html;
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
                        } else {
                            // The child might be without any childs of it's own and without any attributes
                            if ( in_array($child->nodeName, array('br', 'hr')) ){
                                $arr['childs'][] = array($child->nodeName => array());           
                            }
                        }
                    } else {
                        // The child is text, is it along? do we need to wrap it?
                        $val = $this->_handleText($child->nodeValue);  
                        if ( $node->nodeName == 'body' ){
                            // We can't have direct text under the body, need to wrap it in a span
                            $arr['childs'][] = array('span' => array(
                                'childs' => array(array('text'=>$val)),
                            ));
                        } else {
                            $arr['childs'][] = array('text' => $val);       
                        }
                    }
                }
            }
            if($node->hasAttributes()) { 
                $attributes = $node->attributes;
                if(!is_null($attributes)) {
                    $arr["attributes"] = array();
                    foreach ($attributes as $key => $attr) {
                        $arr["attributes"][$attr->name] = $attr->value;
                    }
                }
            }
        }
        return $arr;
    }

    function _handleText($str){
        $str = htmlentities($str, ENT_COMPAT, $this->encoding);   
        return $str;
    }

    /**
    * getBody
    * 
    * triggers the processing of the dom and returns all the childs under
    * the body element. meaning it will omit the wrapping the constractor added.
    * 
    * @return array
    */
    function getBody(){
        // Process the dom
        $dom = $this->_process($this->dom);
        return $dom["childs"][0]["html"]["childs"][0]["body"]["childs"];  
    }
}
