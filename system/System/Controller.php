<?php

namespace Mojo\System;

class Controller {
    public function __construct() {

    }

    public function _handle_req(&$req, &$res) {
        try {
            return $this->index($req, $res);
        } catch (Exception $e) {
            return false;
        }
    }
}
