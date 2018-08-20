<?php

namespace Mojo\System;

class Helper {

    private static $helpers = array();

    public static function load($name) {

        if (in_array($name, self::$helpers)) {
            return true;
        }

        if (file_exists(HELPER_DIR . $name . '.php')) {
            include_once HELPER_DIR . $name . '.php';
        } elseif (file_exists(SYS_HELPER_DIR . $name . '.php')) {
            include_once SYS_HELPER_DIR . $name . '.php';
        } else {
            return false;
        }


    }

}
