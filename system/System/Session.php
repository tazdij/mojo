<?php

namespace Mojo\System;

//class NotInstanceOfRouteException extends \Exception {}
//class DuplicateRouteAdditionException extends \Exception {}

class Session {

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set_userdata($name, $value) {
        //TODO: Create this function
    }


}