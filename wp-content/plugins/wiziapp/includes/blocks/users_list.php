<?php
/**
* @package WiziappWordpressPlugin
* @subpackage AdminDisplay
* @author comobix.com plugins@comobix.com
*/

/*
 * Adds a column indicating if the user has a valid mobile device token stored
 */
function wiziapp_users_list_cols ($cols)
{
	$cols['wiziapp_got_valid_mobile_token'] = __('Mobile?', 'wiziapp'); 
	return $cols;
}

/*
 * Handle the column value
 */
function wiziapp_handle_column($curr_val, $column_name, $user_id){
	if ( strpos($column_name, 'wiziapp_') !== FALSE ){
		$val = get_usermeta($user_id, $column_name);
		return ( $val!='' ) ? $val : 'NO';
	} 
	
	// We are here so it wasn't our column, return the current value
	return $curr_val;
}
