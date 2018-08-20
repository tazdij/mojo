<?php

namespace App\Libraries;

use Mojo\System\Session as MojoSession;

class Session extends MojoSession {

    public function __construct() {
        parent::__construct();
    }

    public function printer($msg) {
        print('Session -> Printer: ' . $msg);
    }

}