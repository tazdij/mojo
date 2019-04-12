<?php

namespace App\Controllers;

use Mojo\System\Controller;
use Ext\Centro\Utils\ArrayWrite;
use Ext\Centro\Utils\ArrayParse;

class IndexController extends Controller {

    public function index(&$req, &$res) {
        //die('here');
    
        print('INDEX::INDEX Ran<br><br>');
        $this->session->printer('This is a test<br><br>');

        //$con = new Con('10.138.236.115', 'barberbookyapi', 'MY_GamB1t', 'barberbookyapp', '');
        //$this->loadModel('User');

        //$providers = $this->user_model->get_providers();
        //print_r($providers);

        // TEST: Forms helper nonce (used to stop form spam)
        //$token = forms_create_nonce('testing');
        //$data = forms_validate_nonce('testing', $token);
        
        

        $data = array();

        $data['username'] = 'dduvall';
        $data['first_name'] = 'Don';
        $data['last_name'] = 'Duvall';
        $data['testarr'] = array('Sub'=>TRUE, 'count' => 0);

        ArrayWrite::SaveAs('testwrite.php', $data);
        
        $arr = ArrayParse::Load('testwrite.php');
        
        $arr['testarr']['count']++;
        
        ArrayWrite::SaveAs('testwrite.php', $arr);
        

        // Possible rendering
        //Template::render('index/index', $data);


        print_r($data);
        print("\n");
        print_r($arr);

        return true;
    }

}
