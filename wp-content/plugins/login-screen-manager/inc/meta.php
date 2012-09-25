<?php 

	function cwlsm_meta() {
	
	global $cwlsm_options;

	ob_start(); ?>
		<meta name="Login Screen Manager" content="1.1" />
	<?php
		echo ob_get_clean();
	}
	add_action('wp_head', 'cwlsm_meta');
?>