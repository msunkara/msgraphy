<?php
/**
* @package WiziappWordpressPlugin
* @subpackage Utils
* @author comobix.com plugins@comobix.com
*/

/**
* Helper function to send post http requests to wiziapp admin server
* 
* @param array $params the parameters to send
* @param string $url the url to access
* @param string $method the connection method
* @param array $headers the custom headers to send with the request
*
* @return array as follows:
* array(
* 	'headers'=>an array of response headers, such as "x-powered-by" => "PHP/5.2.1",
*   'body'=>the response string sent by the server, as you would see it with you web browser
*   'response'=>an array of HTTP response codes. Typically, you'll want to have array('code'=>200, 'message'=>'OK'),
*   'cookies'=>an array of cookie information
* )
*/
function wiziapp_http_request($params, $path, $method = 'POST', $headers = array()) {
	global $wp_version;
	/* 
	 * Wordpress 2.7 and above have a built in class for that.
	 * Using it will let us be more flexible with our requirements.
	 */
	if(!class_exists('WP_Http', false)) {
		include_once ABSPATH . WPINC . '/http.php';
	}

	$http_host = WiziappConfig::getInstance()->api_server;
    
    $api_url = "https://{$http_host}{$path}";
    
    $GLOBALS['WiziappLog']->write('debug', "Contacting wiziapp server: {$api_url}", 
                                        "http_requests.wiziapp_http_request");
                                        
	$headers = array_merge(WiziappConfig::getInstance()->getCommonApiHeaders(), $headers);
    $headers = array_filter($headers);
    
    $GLOBALS['WiziappLog']->write('debug', "Contacting wiziapp server with headers: " . print_r($headers, TRUE) . " and params: " . print_r($params, TRUE), 
                                        "http_requests.wiziapp_http_request");
                                        
	$request = new WP_Http();
    $params = array(
		'method'    => $method,
		'body'      => $params,
        'timeout'   => 60,
        'blocking'  => TRUE,
        'sslverify' => FALSE, // Avoid issues with self signed certificate
		'headers'   => $headers,
    );

    $params = apply_filters( 'wiziapp_request_params', $params, $http_host);

	$result = $request->request($api_url, $params);
	
    $GLOBALS['WiziappLog']->write('info', "The result was: " . print_r($result, TRUE), 
                                        "http_requests.wiziapp_http_request");
	return $result;
}

/**
* Helper function to send post http requests
* 
* @param array $params the parameters to send
* @param string $url the url to access
* @param string $method the connection method
* @param array $headers the custom headers to send with the request
* 
* @return array as follows:
* array(
*   'headers'=>an array of response headers, such as "x-powered-by" => "PHP/5.2.1",
*   'body'=>the response string sent by the server, as you would see it with you web browser
*   'response'=>an array of HTTP response codes. Typically, you'll want to have array('code'=>200, 'message'=>'OK'),
*   'cookies'=>an array of cookie information
* )
*/
function wiziapp_general_http_request($params, $url, $method = 'POST', $headers = array()) {
    global $wp_version;
    /* 
     * Wordpress 2.7 and above have a built in class for that.
     * Using it will let us be more flexible with our requirements.
     */
    if(!class_exists('WP_Http', false)){
        include_once ABSPATH . WPINC . '/http.php';
    }
    
    // Now, the HTTP request:
    $body = $params;
    
    $headers = array_filter($headers);
    
    $GLOBALS['WiziappLog']->write('debug', "Contacting wiziapp server with headers: " . print_r($headers, TRUE) . " and params: " . print_r($params, TRUE), 
                                        "http_requests.wiziapp_general_http_request");
    
    $request = new WP_Http();
    $result = $request->request($url, array( 
        'method'    => $method, 
        'timeout'   => 5,
        'blocking'  => TRUE,
        'body'      => $body, 
        'sslverify' => FALSE, // Avoid issues with self signed certificate
        'headers'   => $headers) 
    );
    
    $GLOBALS['WiziappLog']->write('info', "The result was: " . print_r($result, TRUE), 
                                        "http_requests.wiziapp_general_http_request");
    
    return $result;
}