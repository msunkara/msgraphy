<?php
/*
	Color Palette Generator v1.2
		by Jeff Minard cpg (aht) jrm.cc
		http://jrm.cc/
		
	Please read and abide by the accompanying license: 
		gpl.txt		
		-or-
		http://creativecommons.org/licenses/GPL/2.0/
*/

/*
		Config Section
*/

$accept_file_types = array('png','gif','jpg','jpeg');

$max_file_size  = 1500; // kb
$max_image_size = 1000; // px tall or wide
$min_image_size = 100; // px tall or wide

$image_dir = 'images'; // user submitted images folder
					   // make sure this directory is chmod 666 or 777
						
$rec_image_dir = '../images/banners'; // recommended images folder
						// make sure this directory is chmod 666 or 777
						
$valid_steps = array(7); // stepping for algorithm
$valid_method = array('precise');

?>