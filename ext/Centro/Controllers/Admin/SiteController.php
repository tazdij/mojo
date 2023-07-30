<?php

namespace Ext\Centro\Controllers\Admin;


use Mojo\System\Controller;
use Mojo\Template\TemplateEngine;
use Mojo\Data\SQLDB;
use Mojo\Data\Mysql\Con;
use Mojo\Data\Mysql\Orm;

use Ext\Centro\Models\Contexts;
use Mojo\Data\Mysql\DBError;

class SiteController extends Controller {
    
    public function __construct() {
        parent::__construct();
        
    }
    
    public function index(&$req, &$res) {
        
        ob_start();
        print('Admin Site Index');

        //$db = new Orm(Con::get());
        $db = SQLDB::get(); // Get the default shared Orm object

        $ctxs = $db->select(DB_ALL)->from('context')->exec();

        
        print_r($ctxs);

        
        $output = ob_get_clean();
        $res->html($output);
        
    }

    public function list(&$req, &$res) {
        //
         
    }

    public function create(&$req, &$res) {
        //$this->loadModel('Contexts');

        //$next = $this->contexts_model->getNextPriority();
        //$next = Contexts::Inst()->getNextPriority();

        $inserted = Contexts::Inst()->Create(
            'rebelacrossamerica.com',
            'Rebel Across America',
            'Rebel Across America',
            NULL,
            'rebel22',
            'This context is dedicated to bringing people together in spite'
        );

        if ($inserted instanceof DBError) {
            print($inserted->getMessage());
        }



        //var_dump($next);
        print('DONE');
    }
    
}