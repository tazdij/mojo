<?php

namespace Ext\Centro\Controllers;


use Mojo\System\Controller;
use Mojo\Template\TemplateEngine;
use Mojo\Data\SQLDB;
use Mojo\Data\Mysql\Con;
use Mojo\Data\Mysql\Orm;
use Mojo\IO\Net\Http\Request;
use Mojo\IO\Net\Http\Response;

use Ext\Centro\Models;

class AdminController extends Controller {
    
    public function __construct() {
        parent::__construct();
        
    }
    
    public function index(Request &$req, Response &$res) {

        //$db = new Orm(Con::get());
        $db = SQLDB::get(); // Get the default shared Orm object

        $ctxs = $db->select(DB_ALL)->from('context')->exec();

        //print_r($ctxs);

        $engine = $res->getTemplateEngine();
        //var_dump($engine);
        $engine->setTheme('rebelamerica22');
        $res->template('admin', ['title' => 'Dis ist das title.', 'contexts' => $ctxs]);
        
    }
    
}
