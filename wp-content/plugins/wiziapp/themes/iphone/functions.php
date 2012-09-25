<?php


function wiziapp_get_header( $name = null ) {
	do_action( 'get_header', $name );

	$templates = array();
	if ( isset($name) )
		$templates[] = "header-{$name}.php";

	$templates[] = "header.php";

	// Backward compat code will be removed in a future release
    // The 3rd  param is require_once code, we need it to be false....
	if ('' == locate_template($templates, TRUE, FALSE))
		load_template( ABSPATH . WPINC . '/theme-compat/header.php');
}

function wiziapp_get_footer( $name = null ) {
	do_action( 'get_footer', $name );

	$templates = array();
	if ( isset($name) )
		$templates[] = "footer-{$name}.php";

	$templates[] = "footer.php";

	// Backward compat code will be removed in a future release
    // The 3rd  param is require_once code, we need it to be false....
	if ('' == locate_template($templates, TRUE, FALSE))
		load_template( ABSPATH . WPINC . '/theme-compat/footer.php');
}