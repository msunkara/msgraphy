<?php

function cwlsm_options_page() {

	global $cwlsm_options;

	ob_start(); ?>
	<div class="wrap">
		<h2>Login Screen Manager</h2>
		<?php
			$input = array(
				"logo_url" => array("text"=>"Logo Image URL","class"=>"cwlsm_file"),
				"fav_icon_url" => array("text"=>"Favicon Url","class"=>"cwlsm_file"),
				"body_bg_color" => array("text"=>"Body Background Color","class"=>"color {hash:true}","default"=>"#000000"),
				"login_form_bg_color" =>  array("text"=>"Login Form Background Color","class"=>"color {hash:true}","default"=>"#FFFFFF"),
				"text_input_color" => array("text"=>"Input Text Color","class"=>"color {hash:true}","default"=>"#000000"),
				"input_bg_color" => array("text"=>"Input Background Color","class"=>"color {hash:true}","default"=>"#FFFFFF"),
				"label_color" =>  array("text"=>"Label Color","class"=>"color {hash:true}","default"=>"#000000")
			);
		?>
		<form method="post" action="options.php">
			<?php settings_fields('cwlsm_settings_group'); ?>
			<table>
				<!-- Media Uploader -->
				<!-- Media Uploader -->
				<?php foreach ($input as $name => $data) : ?>
				<tr>
					<td style="text-align:;"><label class="description" for="cwlsm_settings[<?php echo $name; ?>]"><?php _e($data["text"], 'cwlsm_domain'); ?></label></td>
					<td><input class="<?php echo $data["class"]; ?>" id="cwlsm_settings[<?php echo $name; ?>]" size="45" name="cwlsm_settings[<?php echo $name; ?>]" type="text" value="<?php if(empty($cwlsm_options[$name])){echo $data["default"]; }else{ echo $cwlsm_options[$name];} ?>"/></td>
				</tr>
				<?php endforeach; ?>
				<tr>
					<td><label class="description" for="cwlsm_settings[css]"><?php _e("Custom Css", 'cwlsm_domain'); ?></label></td>
					<td><textarea id="cwlsm_settings[css]" style="height:250px;width:400px;"  name="cwlsm_settings[css]" type="text"><?php echo $cwlsm_options["css"]; ?></textarea></td>
				</tr>
				<tr><td></td><td>Hey Rate this Plugin <a href="http://wordpress.org/extend/plugins/login-screen-manager/" target="_blank">here</a> and visit my Website <a href="http://www.codingwar.com/" target="_blank">CodingWar.com</a></td></tr>
				<tr>
					<td></td>
					<td class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Options', 'cwlsm_domain'); ?>" /></td>
				</tr>
			</table>
		</form>
		
	</div>
	<?php
	echo ob_get_clean();
}

function cwlsm_add_options_link() {
	add_options_page('Login Screen Manager', 'Login Screen', 'manage_options', 'cwlsm-options', 'cwlsm_options_page');
}
add_action('admin_menu', 'cwlsm_add_options_link');

function cwlsm_register_settings() {
	register_setting('cwlsm_settings_group', 'cwlsm_settings');
}
add_action('admin_init', 'cwlsm_register_settings');

?>

<?php

function cwlsm_scripts_method() {
	if(is_admin()){
    wp_enqueue_script('custom_admin_script',  plugins_url('/js/jscolor.js', __FILE__), array('jquery'));
	} 
}    

add_action('init', 'cwlsm_scripts_method');

?>