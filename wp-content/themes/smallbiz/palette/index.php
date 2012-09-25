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

require("cpg.php");

if( $_GET['image'] ) // selected image from bookmark or get form
	$file = $_GET['image'];

if( $_FILES['userfile']['tmp_name'] ) // Upload detected captain!
	handle_upload();

// Recommended Image Form Items
$recommended = get_image_list($rec_image_dir);

// User Submitted Image
$user_submitted = get_image_list($image_dir);

// Steps Form Options
$step_options = get_steps_list();

// Methods!
$method_options = get_method_list();

if( $file ) // hoooo buddy, process the image.
	$color_palette = get_color_palette($file);


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Color Palette Generator</title>
	<style type="text/css">@import "app.css";</style>
	<script type="text/javascript" src="funcs.js"></script>
</head>
<body>

<div id="page">

<div id="header">
	<h1>Color Scheme Assistant</h1>
	<p id="tag">Generate a color palette based on an image.</p>
	<hr />
	<p><strong>Ever wonder what colors to use for your text, menu and background that goes well with your header graphic, banner or image?</strong></p>
<br />
<br />
	
	<?php if($errors) { ?>
		<p id="error"> - <?php echo implode('<br> - ', $errors); ?></p>
	<?php } ?>
	
	<?php if($thanks) { ?>
		<p id="thanks"><?php echo $thanks; ?></p>
	<?php } ?>
	
	
	<form enctype="multipart/form-data" action="" method="post">
		<label for="userfile">Option 1: Upload an image:</label> <input name="userfile" id="userfile" type="file" /> 

	

<input style="float:right;margin-right:20px;" type="submit" value="Analyze Uploaded Image" />

	</form>
	
	<br />

	<form action="index.php" method="get">
		<label for="image">Option 2: Select an Image:</label>
		<select name="image" id="image">
			<option value="">SmallBiz Banners:</option>
			<option value="">------------------------------</option>
			
			<?php echo $recommended ?>
			
			<option value="">------------------------------</option>
			<option value="">Your Uploads:</option>
			<option value="">------------------------------</option>
			
			<?php echo $user_submitted; ?>
			</select> 

			
	
<input style="float:right;margin-right:20px;" type="submit" value="Analyze Selected Image" />


	</form>
</div>

<hr />

<div id="lower">
	
	<?php if($color_palette) { ?>
	
		<div id="l1"><div id="l11">
		
			<h2>Color Palette</h2>
			
			<ul class="prow g<?php echo $steps ?>">
				<?php echo $color_palette; ?>
			</ul>
			
			<p>Current color: <br /><span id="disp">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;<span id="disptx">&nbsp;&nbsp;&nbsp;</span></p>
			<p>Selected color: <br /><span id="selc">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;<span id="selctx">&nbsp;&nbsp;&nbsp;</span></p>
		
		</div></div>
		
		<div id="l2"><div id="l22">
		
			<h2>Original Image</h2>
			
			<p><img src="<?php echo $file ?>" alt="image preview" /></p>
			<p><small>Image forced to a width of 300 pixels. </p>
			
		</div></div>
	
	<?php } else { ?>
	
		<div style="padding: 10px;">
			<p>Welcome! Start with one of the following actions:</p>
			<ol>
				<li>Browse for an image (of type <code><?php echo implode(' ', $accept_file_types); ?></code>) and upload it</li>
				<li>Select an image from the drop down menu</li>
			</ol>
		</div>
	
	<?php } ?>
	
</div>


<hr />

<p id="footer">
	<em>Based on Generator by <a href="http://jrm.cc/">Jeff Minard</a> | License <a href="http://creativecommons.org/licenses/GPL/2.0/">CC-GNU GPL</a> | <a href="http://jrm.cc/color-palette-generator/color-palette-generator.zip" target="_blank">Source Code</a></em>
</p>

<!--

<rdf:RDF xmlns="http://web.resource.org/cc/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
<Work rdf:about="">
   <license rdf:resource="http://creativecommons.org/licenses/GPL/2.0/" />
   <dc:type rdf:resource="http://purl.org/dc/dcmitype/Software" />
</Work>

<License rdf:about="http://creativecommons.org/licenses/GPL/2.0/">
   <permits rdf:resource="http://web.resource.org/cc/Reproduction" />
   <permits rdf:resource="http://web.resource.org/cc/Distribution" />
   <requires rdf:resource="http://web.resource.org/cc/Notice" />
   <permits rdf:resource="http://web.resource.org/cc/DerivativeWorks" />
   <requires rdf:resource="http://web.resource.org/cc/ShareAlike" />
   <requires rdf:resource="http://web.resource.org/cc/SourceCode" />
</License>

</rdf:RDF>

-->


</div></body>
</html>
