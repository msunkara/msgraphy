<?php
/**
* @package WiziappWordpressPlugin
* @subpackage AdminDisplay
* @author comobix.com plugins@comobix.com
* 
*/
// Note, if you change the value here, make sure to change it in the uninstall file
function wiziapp_register_settings(){
	register_setting('wiziapp-settings-group', 'wiziapp_settings');
}

function wiziapp_settings_page(){
	?>
<div class="wrap">
<?php
	if ( isset($_GET['updated']) && $_GET['updated']=='true' ){
		echo '<div class="updated fade"><p>Settings updated successfully</p></div>';
	}
?>
<h2>WiziApp Settings</h2>
<form method="post" action="options.php">
	<input type="hidden" name="wiziapp_settings[configured]" value="1" />
    <?php settings_fields('wiziapp-settings-group'); ?>
    <h3>Push Notifications Settings</h3>
    <table class="form-table">
        <tr valign="top">
			<th scope="row">Show badge number</th>
			<td><input type="checkbox" name="wiziapp_settings[show_badge_number]" value="1" <?php checked('1', WiziappConfig::getInstance()->show_badge_number); ?> /></td>
        </tr>
         
        <tr valign="top">
			<th scope="row">Trigger sound notification</th>
			<td><input type="checkbox" name="wiziapp_settings[trigger_sound]" value="1" <?php checked('1', WiziappConfig::getInstance()->trigger_sound); ?> /></td>
        </tr>
        
        <tr valign="top">
			<th scope="row">Show notification text</th>
			<td><input type="checkbox" name="wiziapp_settings[show_notification_text]" value="1" <?php checked('1', WiziappConfig::getInstance()->show_notification_text); ?> /></td>
        </tr>
        
        <tr valign="top">
			<th scope="row">Notify on new posts</th>
			<td><input type="checkbox" name="wiziapp_settings[notify_on_new_post]" value="1" <?php checked('1', WiziappConfig::getInstance()->notify_on_new_post); ?> /></td>
        </tr>
        
        <tr valign="top">
			<th scope="row">Aggregate automatic notifications</th>
			<td><input type="checkbox" name="wiziapp_settings[aggregate_notifications]" value="1" <?php checked('1', WiziappConfig::getInstance()->aggregate_notifications); ?> /></td>
        </tr>
        
        <tr valign="top">
			<th scope="row">Show notification after </th>
			<td><input type="text" name="wiziapp_settings[aggregate_sum]" value="<?php echo WiziappConfig::getInstance()->aggregate_sum; ?>" />&nbsp; notifications</td>
        </tr>
        
        <tr valign="top">
			<th scope="row">Notify for a full period of </th>
			<td>
				<select name="wiziapp_settings[notify_periods]">
					<?php
						$periods = array('day','week','month');
						$html = "";
						for($p=0,$total=count($periods);$p<$total;++$p){
							$val = $periods[$p];
							$selected = "";
							if ( $periods[$p] == WiziappConfig::getInstance()->notify_periods ){
								$selected = 'selected="selected"';
							}
							$html .= "<option value=\"{$val}\" {$selected}>${val}</option>";
						}
						echo $html;
					?>
				</select>
        </tr>
    </table>
    
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>
</form>
</div>
<?php
}
