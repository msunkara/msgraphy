<?php
/*
Template Name: Contact Form Page
*/
if (is_front_page() && (get_option('smallbiz_mobile-layout-enabled') && $GLOBALS["smartphone"])){
    include("page.php");
} else { 
?>
<?php get_header(); ?>
		<div class="post" id="post-<?php the_ID(); ?>">
<?php
if(isset($_POST['Submit']) && $_POST['Submit'] == "Send Message")
{
	$name = $_POST['wpcf_your_name'];
	$email = $_POST['wpcf_email'];
	$message = $_POST['wpcf_msg'];
	$subject = $_POST['wpcf_subject'];
	/* To send HTML mail, you can set the Content-type header. */
	$header  = "MIME-Version: 1.0\r\n";
	$header .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$header .= "From: ". $name. "<". $email .">\r\n";
	$content = "
		<html>
		<body>
			<table>
				<tr><th>Name</th><td>$name</td></tr>
				<tr><th>Email</th><td>$email</td></tr>
				<tr><th>Subject</th><td>$subject</td></tr>
				<tr><th>Message</th><td>$message</td></tr>
			</table>
		</body>
		</html>
		";
	mail(biz_option('smallbiz_email'), 'You Got a Message from '. biz_option('smallbiz_name'), $content, $header);
	echo '<div style="color:#000"> Your message has been sent. <br /> Thank You <br /><br /></div>';
}
?>
		<h2><span style="padding-left:7px">Contact</span></h2>
			<div class="entry">
					<p>Please use this form to contact us by email. We'll respond as quickly as possible! <br/>Or feel free to call us at: <strong><a href="tel:<?php echo biz_option('smallbiz_countryprefix')?>-<?php echo biz_option('smallbiz_telephone')?>"><?php echo biz_option('smallbiz_telephone')?></a></strong></p>
					
				
				<div class="contactform" id="c_form_2">
		<form action="" method="post">
			<div class="contactleft"><label for="wpcf_your_name">Your Name: </label></div>
			<div class="contactright">
				<input type="text" name="wpcf_your_name" id="wpcf_your_name" size="30" maxlength="50" value="" /> (required)
			</div>
			<div class="contactleft"><label for="wpcf_email">Your Email:</label></div>
			<div class="contactright">
				<input type="text" name="wpcf_email" id="wpcf_email" size="30" maxlength="50" value="" /> (required)</div>
			<div class="contactleft"><label for="wpcf_subject">Subject:</label></div>
			<div class="contactright">
				<input type="text" name="wpcf_subject" id="wpcf_subject" size="30" maxlength="50" value="" /> (required)</div>
			<div class="contactleft"><label for="wpcf_msg">Your Message: </label></div>
			<div class="contactright"><textarea name="wpcf_msg" id="wpcf_msg" cols="30" rows="8" ></textarea></div>
			<div class="contactright">
				<input type="submit" name="Submit" value="Send Message" id="contactsubmit" />
				<input type="hidden" name="wpcf_stage" value="process" /></div>
		</form>
				</div>
			</div>
		</div>
<?php include(TEMPLATEPATH."/sidebar-contact.php");?>
<?php get_footer(); ?>
<?php } ?>
