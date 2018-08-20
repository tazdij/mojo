<?php

namespace App\Controllers;

use Mojo\System\Controller;

class JwtController extends Controller {

    public function __construct() {
        parent::__construct();

        // Check for JWT, Validate
        //  If Expired, send expired error, for renewal

        // Else Reject
    }

}
