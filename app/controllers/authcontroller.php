<?php

namespace App\Controllers;

use Mojo\System\Controller;

class AuthController extends Controller {

    public function login(&$req, &$res) {
        // Get body, decode from JSON

        $this->loadModel('User');

        $hash = $this->user_model->validateLogin($req->Body->get('username'), $req->Body->get('password'));

        $res->json(array('passhash'=>$hash));

        return true;
    }

}
