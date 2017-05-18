<?php

namespace App\Controllers;

use Mojo\System\Controller;

class IndexController extends Controller {

    public function index(&$req, &$res) {
        print('INDEX::INDEX Ran<br><br>');

        // TEST: Forms helper nonce (used to stop form spam)
        //$token = forms_create_nonce('testing');
        //$data = forms_validate_nonce('testing', $token);

        $data = array();

        $data['username'] = 'dduvall';
        $data['first_name'] = 'Don';
        $data['last_name'] = 'Duvall';

        // Possible rendering
        //Template::render('index/index', $data);


        print_r($data);

        return true;
    }

}
