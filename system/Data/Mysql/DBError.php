<?php

namespace Mojo\Data\Mysql;

class DBError {
    public $_message;
    public $_code;

    public function __construct($error_message, $error_code='500') {
        $this->_message = $error_message;
        $this->_code = $error_code;
    }

    public function getMessage() {
        return $this->_message;
    }

    public function getCode() {
        return $this->_code;
    }
}