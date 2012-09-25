<?php $hide = (biz_option('smallbiz_feature_box_disabled')); if($hide == ""){ ?>
<ul id="featured" >
<li class="first featured">
<?php include(TEMPLATEPATH."/footer_left.php");?>
</li>
<li class="featured">
<?php include(TEMPLATEPATH."/footer_middle.php");?>
</li>
<li class="featured">
<?php include(TEMPLATEPATH."/footer_right.php");?>
</li>
</ul>
<?php } else { echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"; /* Safari fix */} ?>		
</div>
<div id="footer">
<p class="footercenter"><?php echo biz_option('smallbiz_cities');?></p>
<p class="footercenter">
<?php echo biz_option('smallbiz_credit');?>
</p>
<div id="footer-mobile-switch">			
<?php
$urlmd5 = md5(get_bloginfo('siteurl'));
 if(get_option('smallbiz_mobile-layout-enabled') && $_COOKIE[$urlmd5."device_type"] == "Mobile"){
 ?>          
<p>     
        View site in: 
        <<?php if($GLOBALS["smartphone"]){ echo "b"; } else { echo "a"; } ?> href="<?php echo get_bloginfo("wpurl"); ?>?ui=m">
           Mobile
        </<?php if($GLOBALS["smartphone"]){ echo "b"; } else { echo "a"; } ?>>
         |
        <<?php if(!$GLOBALS["smartphone"]){ echo "b"; } else { echo "a"; } ?> href="<?php echo get_bloginfo("wpurl"); ?>?ui=f">
           Desktop
        </<?php if(!$GLOBALS["smartphone"]){ echo "b"; } else { echo "a"; } ?>>
        
        </p> <!-- end site-ui-switch-link -->
        <?php
    }
?>
</div>
		</div>
	<!-- </div> -->
<?php wp_footer(); ?>
</div> <!--site wrap closing-->
</body>
</html>