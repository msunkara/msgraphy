<?php 

	function cwlsm_change() {
	
	global $cwlsm_options;

	ob_start(); ?>
	<!-- Login Screen Manager Start -->
	
	<!-- Login Screen Manage : Version 1.1 -->
	
	<?php if(!empty($cwlsm_options["logo_url"])): ?>
	
	<style type="text/css">
	/* Login Screen Manager : Css : Change Logo */
	h1 a { background-image:url(<?php echo$cwlsm_options["logo_url"];  ?>) !important; }
	</style>
	
	<?php endif;?>
	<?php if(!empty($cwlsm_options["body_bg_color"])): ?>
	
	<style type="text/css">
	/* Login Screen Manager : Css : Change Body Background Color*/
	body.login { background-color:<?php echo$cwlsm_options["body_bg_color"];  ?>; }
	</style>
	
	<?php endif;?>
	<?php if(!empty($cwlsm_options["login_form_bg_color"])): ?>
	
	<style type="text/css">
	/* Login Screen Manager : Css : Change Login Form Background Color*/
	#loginform { background-color:<?php echo$cwlsm_options["login_form_bg_color"];  ?>; }
	</style>
	
	<?php endif;?>
	<?php if(!empty($cwlsm_options["label_color"])): ?>
	
	<style type="text/css">
	/* Login Screen Manager : Css : Change Label Color*/
	#loginform label { color:<?php echo$cwlsm_options["label_color"];  ?>; }
	</style>
	
	<?php endif;?>
	<?php if(!empty($cwlsm_options["text_input_color"])): ?>
	
	<style type="text/css">
	/* Login Screen Manager : Css : Change Input Text Color*/
	#loginform input[type="text"],#loginform input[type="password"]{ color:<?php echo$cwlsm_options["text_input_color"];  ?>; }
	</style>
	
	<?php endif;?>
	<?php if(!empty($cwlsm_options["input_bg_color"])): ?>
	
	<style type="text/css">
	/* Login Screen Manager : Css : Change Input Background Color*/
	#loginform input[type="text"],#loginform input[type="password"]{ background-color:<?php echo$cwlsm_options["input_bg_color"];  ?>; }
	</style>
	
	<?php endif;?>
	<?php if(!empty($cwlsm_options["css"])): ?>
	
	<style type="text/css">
	/* Login Screen Manager : Custom Css Start*/
		
		<?php echo $cwlsm_options["css"]; ?>
		
	/* Login Screen Manager : Custom Css End*/
	</style>
	
	<?php endif;?>
	<?php if(!empty($cwlsm_options["fav_icon_url"])): ?>
	
	<!-- Login Favicon Start -->
	
	<link rel="icon" href="<?php echo $cwlsm_options["fav_icon_url"];  ?>" type="image/x-icon" />
	
	<!-- Login Favicon End -->
	
	<?php endif;?>
	<!-- Login Screen Manager End -->
	
	<?php
		echo ob_get_clean();
	}
	add_action('login_head', 'cwlsm_change');
?>