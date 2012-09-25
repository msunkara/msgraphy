<?php ob_start();
    if (have_posts()) : while (have_posts()) : the_post(); 
        wiziapp_content_get_post_headers(true);
        echo ob_get_contents(); 
        ob_end_clean(); 
    
        include('_content.php');
    ?>
<?php endwhile; else : 
// No content???
endif; 
?>