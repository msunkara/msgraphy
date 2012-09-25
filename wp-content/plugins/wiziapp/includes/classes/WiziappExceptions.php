<?php

class WiziappOperationNotAllowed extends Exception
{
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }
}

class WiziappUnknownType extends Exception
{
    public function __construct($message, $code = 0) {
        parent::__construct($message, $code);
    }
}


// End of file