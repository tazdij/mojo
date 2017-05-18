<?php

namespace Mojo\IO\Net\Http;

class CookieManager {

    public static function CreateFromInitialRequest() {
        $cookie_manager = new CookieManager();

        foreach ($_COOKIE as $name => $val) {
            $cookie_manager->Cookies[$name] = $val;
        }

        return $cookie_manager;
    }

    // Array of cookies that were received by the request
    protected $Cookies = array();

    public function __construct() {

    }

    public function get($name) {
        // Get the cookie from the basket and return it's value
        // or Null if not set
        if (!isset($this->Cookies[$name])) {
            return null;
        }

        return $this->Cookies[$name];
    }

    public function set($name, $value, $expires=7200) {
        //NOTE: Do we want the cookies to be set here, or placed in the
        //  UnsentCookies container, which can be sent at the end of the
        //  request pipeline, prior to the response be sent?

        // TEMP: we will just send the cookie now, for simplicities sake
        setcookie($name, $value, time()+$expires);
        $this->Cookies[$name] = $value;
    }

    public function delete($name) {
        setcookie($name, '', -1);
        unset($this->Cookies[$name]);
    }

}
