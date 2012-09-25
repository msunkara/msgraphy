<?php
// shared functions to help repair things that get broken:
 $hideSidebar = false; // set from layout when sidebar needs to be hidden
 $this_id = $wp_query->get_queried_object()->ID;

 function smallbiz_do_repair(){
    echo "repair called";
    $home_id = smallbiz_get_page_id_by_title_create_if_needed('Home',-5,'Home ',"index.php");
    update_option("smallbiz_page_on_front",$home_id);
    update_option("page_on_front", $home_id);
    update_option("smallbiz_homepage_id", $home_id); 
 }

 
 if(!smallbiz_get_current_layout()){
      update_option('smallbiz_layout',"classic");
 }
 // Repair our home page being deleted or set to an invalid page:
 if(is_404()){
    function smallbiz_preheader_check_homepage_ok(){
        $checkId = get_option('smallbiz_page_on_front'); // *must* be passed by reference
        $checkPage = get_page($checkId);
        if($checkPage == null || $checkPage->post_status == "trash"){
            return false;           
        }
        return true;
    }
    if(!smallbiz_preheader_check_homepage_ok()){
        smallbiz_do_repair();
    }
    if(smallbiz_preheader_check_homepage_ok()){
        if(!headers_sent()){ 
            header('Location: '.site_url(), true, 307);
        } else {
         ?>
         <script type="text/javascript">window.location.reload();</script>
         <meta http-equiv="refresh" content="0;url=<?php echo site_url(); ?>" />
         <?   
        }
        exit;        
    }
 }
 
 if(is_front_page()){
     if($this_id != get_option('smallbiz_page_on_front') && 
        $this_id != get_option('smallbiz_homepage_id')
         ){
        smallbiz_do_repair();
     }     
 } 

?>
