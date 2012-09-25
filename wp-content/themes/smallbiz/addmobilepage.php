<?php
/*
Template Name: Additional Mobile Page
*/
  $hideSidebar = true;
?>

<head>

<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.1//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile11.dtd">
<meta http-equiv="Content-Type" value="application/xhtml+xml" /> 
<meta name="viewport" content="width=320px; initial-scale=1.0; maximum-scale=1.0; minimum-scale=1.0; user-scalable=0;" />
<meta name='robots' content='noindex,nofollow' />
<title><?php echo biz_option('smallbiz_title');?></title>
<meta name="description" content="<?php echo biz_option('smallbiz_description');?>"/>
<meta name="keywords" content="<?php echo biz_option('smallbiz_keywords');?>" />
<?php echo biz_option('smallbiz_analytics');?>	 

<link rel="stylesheet" href="<?php bloginfo('template_url'); ?>/css/mobile.css" type="text/css" media="screen" />

<style>

#headerstrip h1{
font-family:<?php echo biz_option('smallbiz_font_family_header')?>; 
color: #<?php echo biz_option('smallbiz_mobiname_color')?>;
font-size:<?php echo biz_option('smallbiz_font_size_mobiname')?>px;
margin-top: 3px;
}

#headerstrip h2{
font-family:<?php echo biz_option('smallbiz_font_family_header')?>; 
color: #<?php echo biz_option('smallbiz_mobisub_header_color')?>;
font-size:<?php echo biz_option('smallbiz_font_size_mobisubheader')?>px;
}

#mobile-text p {font-family:<?php echo biz_option('smallbiz_font_family_main')?>;
    font-size: 18px;
    margin: 0.1em 0;
     line-height: 1.3em;
        padding-bottom: 9px;}


.tertiary-menu li{
background-color: #<?php echo biz_option('smallbiz_mobile-button-color')?>;
background-image:url('<?php bloginfo('template_directory'); ?>/images/mobile/sheen.png');
background-repeat:no-repeat;
}

.tertiary-menu a{
color: #<?php echo biz_option('smallbiz_mobile-text-button-color')?>;font-family:<?php echo biz_option('smallbiz_font_family_menu')?>;
display:block;
}

#pagewrap{background-color:#<?php echo biz_option('smallbiz_mobile-body-color')?>;
}

#home-link img{
background-color: #<?php echo biz_option('smallbiz_mobile-button-color')?>;}

#combined{
background-color: #<?php echo biz_option('smallbiz_mobile-button-color')?>;
background-image:url('<?php bloginfo('template_directory'); ?>/images/mobile/combined.png');
}

#nomobidirections{display:none;}
</style>

	     
</head>
<body>

<div id="pagewrap">

<div id="headerstrip">

<img src="<?php bloginfo('template_url'); ?>/images/<?php echo biz_option('smallbiz_mobile-bannerhome-image')?>" alt="<?php echo biz_option('smallbiz_name')?>" title="<?php echo biz_option('smallbiz_name')?>" />

<br />

<h1>
<?php echo do_shortcode(biz_option('smallbiz_mobiname'))?>
</h1>

<h2>
<?php echo do_shortcode(biz_option('smallbiz_mobisub_header'))?>
</h2>

</div> <!--close headerstrip-->


<div id="mobile-text">

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

		<div class="post" id="post-<?php the_ID(); ?>">

<?php if(!is_page('')) :?><?php endif; ?>

			<div class="entry">

							<?php global $more; $more = false; ?>

<?php the_content('...Continue Reading'); ?>

<?php $more = true; ?>

				<?php wp_link_pages(array('before' => '<p><strong>Pages:</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); ?>

			</div>

		</div>

		<?php endwhile; endif; ?>

</div> <!--closing mobile text-->

<div id="home-mob-menu">
<!--Mobile Menu-->
<?php wp_nav_menu( array(
'container_class' => 'tertiary-menu', 'theme_location' => 'tertiary-menu', 'fallback_cb' => '' ) ); ?>
</div>

<div id="home-link">
<div id="combined">

<a href="<?php bloginfo('url'); ?>"><img src="<?php bloginfo('url'); ?>/wp-content/themes/smallbiz/images/mobile/home.png" alt="mobile-back-home" style="margin-right:3px;" /></a>

<a href="tel:<?php echo biz_option('smallbiz_countryprefix')?>-<?php echo biz_option('smallbiz_telephone'); ?>"><img src="<?php bloginfo('url'); ?>/wp-content/themes/smallbiz/images/mobile/mobilecall.png" alt="mobile-call-now" style="margin-right:3px;"  /></a>

<a href="mailto:<?php echo biz_option('smallbiz_email')?>" target="_blank"><img src="<?php bloginfo('url'); ?>/wp-content/themes/smallbiz/images/mobile/mail.png" alt="mobile-mail-now" style="margin-right:3px;" /></a>

<!-- Hide header if checkbox is not equal to by adding div id noheader -->

<?php $hide = (biz_option('smallbiz_mobidirections_disabled')); if($hide != ""){ ?>

<div id="nomobidirections"> 		<?php } ?>	

<a href="<?php echo biz_option('smallbiz_mobile-map')?>" ><img src="<?php bloginfo('url'); ?>/wp-content/themes/smallbiz/images/mobile/directions.png" alt="mobile-directions"></a>

<?php $hide = (biz_option('smallbiz_mobidirections_disabled')); if($hide != ""){ ?> </div> <?php } ?>

</div><!--combined-->
</div>

<div id="footerstrip">
<p><?php echo biz_option('smallbiz_credit');?></p>
</div> <!--close footerstrip-->

</div> <!--close pagewrap-->

</body>