<?php

/**
 * The  following block will enable us to pass debugging sessions to the admin
 * only in the dev environments while we are debugging
 *
 * This file is removed in the build so it will not open the server to attacks
 *
 */
//@todo make sure I removed this from the build, before committing
add_filter( 'wiziapp_request_params', 'wiziappAddRemoteDebugging', 10, 2);
function wiziappAddRemoteDebugging($params, $http_host){
    if ( isset($_COOKIE['XDEBUG_SESSION']) ){
        $params['cookie'] = array(
            new WP_Http_Cookie(array(
                'name' => 'XDEBUG_SESSION',
                'value'=> $_COOKIE['XDEBUG_SESSION'],
                'path' => '/',
                'domain' => $http_host)
            )
        );
    }

    return $params;
}