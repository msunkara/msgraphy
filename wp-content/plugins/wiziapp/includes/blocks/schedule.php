<?php
/**
* We need to make sure we have a weekly and month scheduled tasks
* The interval is in seconds, how many seconds before performing the event...
* 
* @package WiziappWordpressPlugin
* @subpackage Utils
* @author comobix.com plugins@comobix.com
* 
*/
function wiziapp_more_reccurences() {
	return array(
		'weekly' => array('interval' => 604800, 'display' => __('Once Weekly', 'wiziapp')),
		'monthly' => array('interval' => 2592000, 'display' => __('Once every 30 days', 'wiziapp')),
	);
}
