<?php

class WiziappError extends WP_Error{
    //return new WP_Error( 'http_request_failed', __( 'User has blocked requests through HTTP.' ) );

    public function getHTML(){
        $html = '';
        foreach( $this->errors as $errorCode => $errors ){
            for ( $e=0, $total=count($errors); $e < $total; ++$e ){
                $html .= "<div class='wiziapp_error'>{$errors[$e]}</div>";
            }
        }

        return $html;
    }
    public static function isError($obj){
        if ( is_object($obj) && is_a($obj, 'WiziappError') ){
            return TRUE;
        }
        return FALSE;
    }
}