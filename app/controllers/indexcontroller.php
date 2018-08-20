<?php

namespace App\Controllers;

use Mojo\System\Controller;

class IndexController extends Controller {

    public function index(&$req, &$res) {
        print('INDEX::INDEX Ran<br><br>');

        $this->session->printer('This is a test<br><br>');

        //$con = new Con('10.138.236.115', 'barberbookyapi', 'MY_GamB1t', 'barberbookyapp', '');
        $this->loadModel('User');

        $providers = $this->user_model->get_providers();
        print_r($providers);

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
