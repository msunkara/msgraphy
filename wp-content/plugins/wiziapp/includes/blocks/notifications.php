<?php if (!defined('WP_WIZIAPP_BASE')) exit();
/**
* @package WiziappWordpressPlugin
* @subpackage PushNotifications
* @author comobix.com plugins@comobix.com
*/
function wiziapp_notifications_display(){
	if ( $_SERVER['REQUEST_METHOD'] == 'POST' ){
		$sound = isset($_POST['sound']) ? 1 : 0;
		$badge = isset($_POST['badge']) ? 1 : 0;
		$users = array();
		$choose_all = FALSE;
		if ( is_array($_POST['users']) ){
			foreach ( $_POST['users'] as $user ){
				// If the user has choosen all users, no need to enter indeviduals entries
				if ( !$choose_all ){
					$users[] = $user;
				}
				// If the user choose all mark it so we will know
				if ( $user == 'all' ){
					$choose_all = TRUE;
				} 
			}
		}
		
		$users_of_blog = get_users_of_blog();
		$avail_roles = array();
		$avail_roles['all'] = array();
		if ( !$choose_all ){
			foreach ( (array) $users_of_blog as $b_user ) {
				if ( get_usermeta($b_user->ID, 'wiziapp_got_valid_mobile_token') == '1' ){
					$b_roles = unserialize($b_user->meta_value);
					foreach ( (array) $b_roles as $b_role => $val ) {
						if ( !isset($avail_roles[$b_role]) )
							$avail_roles[$b_role] = array();
						$avail_roles[$b_role][] = $b_user->ID;
						$avail_roles['all'] = $b_user->ID;
					}
				}
			}
		}
		
		unset($users_of_blog);
		if ( is_array($_POST['roles'])){
			foreach ( $_POST['roles'] as $role ){
				// Merge all the users that have this role
				if ( isset($avail_roles[$role]) && is_array($avail_roles[$role]) ){
					array_merge($avail_roles[$role], $users);
				}
			}
		}
		
		unset($users_of_blog);
		unset($avail_roles);
		// Avoid sending the same user twice so if the user was selected 
		// already don't readd it 
		$users = array_unique($users);
		
		$request = array(
			'content'=>urlencode(stripslashes($_POST['message'])),
			'type'=>2,
			'sound'=>$sound,
			'badge'=>$badge,
			'users'=>implode(",", $users)
		);
		
		$response = wiziapp_http_request($request, '/push', 'POST');
        if ( ! is_wp_error($response) ) {
            print_r($response);
        } else {
            echo $response['body'];
        }
	}
	?>
	<div class="wrap">
		<h2>Send notifications</h2>
		<form name="fmrCustomNotifications" action="<?php echo $_SERVER["REQUEST_URI"]; ?>" method="POST">
			<p>
				<input name="message" size="50" maxsize="50" type="text" value="" /><input type="submit" class="button-primary" value="Send" />
				<br />
				<label>
					<input type="checkbox" name="sound" checked="checked" />
					<span>Sound?</span>
				</label>
				<label>
					<input type="checkbox" name="badge" checked="checked" />
					<span>Badge?</span>
				</label>
			</p>
			<p>
				<label>Choose Users: </label>
				<br />
				<select name="users[]" multiple="multiple" size="5" style="height:auto;">
					<option value="all">All the users that have a token</option>
					<?php
						$users_of_blog = get_users_of_blog();
						$total_users = count( $users_of_blog );
						$avail_roles = array();
						$html = '';
						foreach ( (array) $users_of_blog as $b_user ) {
							if ( get_usermeta($b_user->ID, 'wiziapp_got_valid_mobile_token') ){
								$html .= "<option value='{$b_user->ID}'>{$b_user->display_name}</option>";
								$b_roles = unserialize($b_user->meta_value);
								foreach ( (array) $b_roles as $b_role => $val ) {
									if ( !isset($avail_roles[$b_role]) )
										$avail_roles[$b_role] = 0;
									$avail_roles[$b_role]++;
								}
							}
						}
						unset($users_of_blog);
						echo $html;
					?>
				</select>
				
			</p>
			<p>
				<label>Send to users with roles: </label>
				<br />
				<small>Only roles that have users with device tokens connected to them will be listed here</small>
				<br />
				<select name="roles[]" multiple="multiple" size="5" style="height:auto;">
					<option value="all">All</option>
					<?php
						$html = '';
						foreach ( (array) $avail_roles as $role_name => $count ) {
							$html .= "<option value='{$role_name}'>{$role_name}</option>";
						}
						echo $html;
					?>
				</select>
				
			</p>
		</form>
	</div>
	<?php
}

/*
* wiziapp_user_push_subscription
* 
* Handles the push notifications subscriptions for the user
* POST /user/track/{key}/{value}
* DELETE /user/track/{key}/{value}
* 
*/ 
function wiziapp_user_push_subscription($key, $val=''){
    // Validate the user
    $user = wiziapp_check_login(TRUE);
    // @todo add validation to key and val
    if ( $user != null && $user !== FALSE){
        // The user is valid and can login to the service, 
        // set his options for him
        if ( !empty($key) ){
            $settings = get_usermeta($user->ID, 'wiziapp_push_settings');
            if ( $_SERVER['REQUEST_METHOD'] == 'POST' ){
                if (!isset($settings[$key])){
                    $settings[$key] = array();
                } 
                $settings[$key][$val] = TRUE;
            } elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE' ){
                if ( isset($settings[$key]) && isset($settings[$key][$val]) ){
                    unset($settings[$key][$val]);
                }
            }
            update_usermeta($user->ID, 'wiziapp_push_settings', $settings);
            // If we are here everything is ok
            $status = TRUE;
            echo json_encode(array('status'=>$status));
            exit;
        }
    }
}