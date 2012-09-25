<?php
/*
 *	Color Palette Generator v1.2
 *		by Jeff Minard cpg (aht) jrm.cc
 *		http://jrm.cc/
 *		
 *	Please read and abide by the accompanying license: 
 *		gpl.txt		
 *		-or-
 *		http://creativecommons.org/licenses/GPL/2.0/
 */
 
// We neeed configsss
require("cpg-config.php");

// Upload Logic
function handle_upload() {
	global $file, $image_dir, $accept_file_types, $max_file_size, $min_image_size, $max_image_size, $errors;
		
	$upload_ext  = strtolower(substr($_FILES['userfile']['name'], -3));
	$upload_size = $_FILES['userfile']['size'] / 1024;
	$upload_img  = getImageSize($_FILES['userfile']['tmp_name']);
	$upload_dest = $image_dir .'/'. time() .'.'. $upload_ext;
	
	$errors = array();
	
	if( ! in_array($upload_ext, $accept_file_types) ) {
		// We don't accept this kind of file
		$errors[] = "Unacceptable file type upload. Please only upload ( <code>" . implode(' ', $accept_file_types) . "</code> ) image files";
	}
		
	if( $upload_size > $max_file_size ) {
		// The image file size is small enough
		$errors[] = "The file was too large in KB. Please reduce to less than $max_file_size KB.";
	}
	
	if( $upload_img[0] < $min_image_size || $upload_img[1] < $min_image_size ) {
		// The image is large enough (px)
		$errors[] = "The image you uploaded was too small. Please make sure it is more than $min_image_size pixels tall and wide.";
	}
			
	if( $upload_img[0] > $max_image_size || $upload_img[1] > $max_image_size ) {
		// The image is small enough (px)
		$errors[] = "The image you uploaded was too large. Please make sure it is less than $max_image_size pixels tall and wide.";
	}
				
	if( ! function_exists('move_uploaded_file') ) {
		// make sure we have a funciton we need
		$errors[] = "PHP can't work with uploaded files. Please fix the missing function, 'move_uploaded_file'.";
	}
				
	if( ! is_writable($image_dir) ) {
		// make sure we can write to the folder
		$errors[] = "The image directory is not writable, this must be fixed for the script to work.";
	}
					
	if( ! is_readable($_FILES['userfile']['tmp_name']) ) {
		// Sanity check to make sure the uploaded file is readable.
		$errors[] = "The uploaded file could not be read from temp -- this must be fixed";
	}
	
	// if there are errors at this point DO NOT accept the file upload.
	if( count($errors) > 0 )
		return;
		
	if( ! move_uploaded_file($_FILES['userfile']['tmp_name'], $upload_dest) ){
		$errors[] = "The file upload failed during the move process. Please notify me!";
	} else {
		$thanks = "Thanks for the file upload!"; 
		$file   = $upload_dest;
	}
						
}


// Generate Form Lists
function get_image_list($dir) {
	global $accept_file_types, $file, $errors;
	
	if( !is_dir($dir) ) {
		$errors[] = "The requested folder does not exist.";
		return false;
	}
		
	$handle = opendir($dir); 
	while (false !== ($current_file = readdir($handle))) { 
		$ext = strtolower(substr($current_file, -3));
		if( in_array($ext, $accept_file_types) ) {
			$insert = ($file == "$dir/$current_file") ? ' selected="selected"' : '';
			$list  .= "<option value=\"$dir/$current_file\"$insert>&nbsp;&nbsp;&nbsp;$current_file</option>\n";
		}
	}
	closedir($handle); 
	
	return $list;
}

function get_steps_list() {
	global $valid_steps;
	foreach($valid_steps as $step) {
		$insert = ($_GET['steps'] == $step) ? ' selected="selected"' : '';
		$step_options .= "<option value=\"$step\" $insert>$step x $step</option>";
	}
	return $step_options;
}

function get_method_list() {
	global $valid_method; 
	foreach($valid_method as $method) {
		$insert = ($_GET['method'] == $method) ? ' selected="selected"' : '';
		$options .= "<option value=\"$method\" $insert>". ucfirst($method) ."</option>";
	}
	return $options;
}

// File Processing Logic
function get_color_palette($file) {
	global $valid_steps, $valid_method, $steps, $method, $errors;
	
	if( !is_file($file) ) {
		$errors[] = "That file does not exist";
		return false;
	}

	// verify that the "steps" option is valid.
	if( in_array($_GET['steps'], $valid_steps) ) {
		$steps = $_GET['steps'];
	} else {
		$steps = $valid_steps[0];
	}

	// verify that the "steps" option is valid.
	if( in_array($_GET['method'], $valid_method) ) {
		$method = $_GET['method'];
	} else {
		$method = $valid_method[0];
	}
	
	if( $method == 'average' )
		$palette = get_palette_average($file);
	else 
		$palette = get_palette_precise($file);
		
	if($palette) {
		foreach($palette as $v) {
			$color_palette .= "<li style=\"background-color: #$v;\" onmouseover=\"mo(this);\" onclick=\"cl(this);\"><span>#$v</span></li>\n";
		}
		return $color_palette;
	} else {
		return false;
	}
}


/*
 * This right here, is the the real meat
 * of this project. The actually routine.
 * Pass it a valid local file, and it will
 * return an array of colors to you.
 */
function get_palette_precise($file) {
	global $valid_steps, $steps, $errors;
		
	$file_img = getImageSize($file);
	
	switch ($file_img[2]) {
		case 1: //GIF
			$srcImage = imagecreatefromgif($file);
			break;
		case 2: //JPEG
			$srcImage = imagecreatefromjpeg($file);
			break;
		case 3: //PNG
			$srcImage = imagecreatefrompng($file);
			break;
		
		default:
			$errors[] = "PHP could not create an image to grab colors from. Perplexing.";
			return false;
	}
	
	if(!$srcImage) {
		$errors[] = "PHP could not generate an image to grab colors from. Odd.";
		return false;
	}
	
	$xloop = ceil( ( $file_img[0] - 20 ) / ($steps - 1) );
	$yloop = ceil( ( $file_img[1] - 20 ) / ($steps - 1) );
	
	for ($y=10; $y<$file_img[1]; $y+=$yloop) {
		for ($x=10; $x<$file_img[0]; $x+=$xloop) {
			
			$rgbNow	  = imagecolorat($srcImage, $x, $y);
			$colorrgb = imagecolorsforindex($srcImage,$rgbNow);
	
			foreach($colorrgb as $k => $v) {
				$t[$k] = dechex($v);
				if( strlen($t[$k]) == 1 ) {
					if( is_int($t[$k]) ) {
						$t[$k] = $t[$k] . "0";
					} else {
						$t[$k] = "0" . $t[$k];
					}
				}
			}
	
			$rgb2 = strtoupper($t[red] . $t[green] . $t[blue]);
			$color_set[] = $rgb2;
			
		}
	}
	
	return $color_set;
	
}

/*
 * Updated routine. 
 * Runs smarter by scaling the image down and sampling the colors. 
 */
function get_palette_average($file) {
	global $valid_steps, $steps, $errors;
	
	if( !is_file($file) ) {
		$errors[] = "That file does not exist";
		return false;
	}

	// verify that the "steps" option is valid.
	if( in_array($_GET['steps'], $valid_steps) ) {
		$steps = $_GET['steps'];
	} else {
		$steps = $valid_steps[0];
	}
		
	$file_img = getImageSize($file);
	
	switch ($file_img[2]) {
		case 1: //GIF
			$srcImage = imagecreatefromgif($file);
			break;
		case 2: //JPEG
			$srcImage = imagecreatefromjpeg($file);
			break;
		case 3: //PNG
			$srcImage = imagecreatefrompng($file);
			break;
		
		default:
			$errors[] = "PHP could not create an image to grab colors from. Perplexing.";
			return false;
	}
	
	if(!$srcImage) {
		$errors[] = "PHP could not generate an image to grab colors from. Odd.";
		return false;
	}
	
	$image_dest = imagecreatetruecolor($steps, $steps);
	imagecopyresampled($image_dest, $srcImage, 0, 0, 0, 0, $steps, $steps, $file_img[0], $file_img[1]);
	
	$xloop = 1;
	$yloop = 1;
	
	for ($y=0; $y<$steps; $y+=$yloop) {
		for ($x=0; $x<$steps; $x+=$xloop) {
			
			$rgbNow	  = imagecolorat($image_dest, $x, $y);
			$colorrgb = imagecolorsforindex($image_dest,$rgbNow);
	
			foreach($colorrgb as $k => $v) {
				$t[$k] = dechex($v);
				if( strlen($t[$k]) == 1 ) {
					if( is_int($t[$k]) ) {
						$t[$k] = $t[$k] . "0";
					} else {
						$t[$k] = "0" . $t[$k];
					}
				}
			}
	
			$rgb2 = strtoupper($t[red] . $t[green] . $t[blue]);
			$color_set[] = $rgb2;
			
			// emergency break out ...
			if( count($color_set) > $steps*$steps + 10 )
				break(2);
			
		}
	}
	
	return $color_set;
	
}

?>