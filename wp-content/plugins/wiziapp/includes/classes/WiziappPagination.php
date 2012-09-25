<?php

class WiziappPagination{
    public $maxItems;
    public $page;
    public $offset;
    public $showMore = FALSE;
    public $leftToShow = 0;
    public $total;
    
    function __construct($total, $max_items = 0){
        $this->total = $total;
        
        if ($max_items == 0){
            $this->maxItems = WiziappConfig::getInstance()->posts_list_limit;
        } else {
            $this->maxItems = $max_items;
        }
        
        $this->page = isset($_GET['wizipage']) ? $_GET['wizipage'] : 0;
    
        if (!empty($this->page)){
            $this->offset = $this->maxItems * $this->page;
        } else {
            $this->offset = 0;
        }
        
        // With the offset which post have we reached?
        $totalShown = $this->maxItems + ($this->maxItems * $this->page);
        
        // Find the total number of posts in the blog
        if ($totalShown < $total) {
            $leftToShow = $total - $totalShown;
            $this->showMore = $leftToShow < $this->maxItems ? $leftToShow : $this->maxItems;
            $this->leftToShow = $leftToShow > $this->maxItems ? $this->maxItems : $leftToShow;
        } else {
            $this->showMore = FALSE;
        }
    }
    
    function shouldShowMore(){
        return $this->showMore ? TRUE : FALSE;
    }
    
    function getOffset(){
        return $this->offset;
    }
    
    function setOffset($offset){
        $this->offset = $offset;        
        $this->refreshShowMore();
    }
    
    function refreshShowMore(){
        if ( $this->total > $this->maxItems ){
            $leftToShow = $this->total - $this->maxItems;
            if ( $leftToShow > 0 ){
                $this->showMore = TRUE;
                $this->leftToShow = $leftToShow;   
            }
        }
    }
    
    function getPage(){
        return $this->page;
    }
    
    function getItemsLeft(){
        return $this->leftToShow;
    }
    
    function extractCurrentPage($items, $keep_keys = FALSE){
        //return array_slice($items, $this->offset, $this->maxItems, true);
        return array_slice($items, $this->offset, $this->maxItems, $keep_keys);
    }
    
    function addMoreCell($title, &$cPage){
        if ($this->shouldShowMore()){
            // Now increase the current page so it will point to the next
            $obj = new WiziappMoreCellItem('L1', array(sprintf($title, $this->leftToShow), $this->page + 1));
            $moreComponent = $obj->getComponent();
            $cPage[] = $moreComponent;

            if ( isset($GLOBALS['WiziappEtagOverride']) ){
                $GLOBALS['WiziappEtagOverride'] .= serialize($moreComponent);
            } 
        }
    }
}