<?php

namespace Ext\Centro\Controllers;


use Mojo\System\Controller;
use Mojo\Template\TemplateEngine;

class AdminController extends Controller {
    
    public function __construct() {
        parent::__construct();
        
    }
    
    public function index(&$req, &$res) {
        print('Admin Index');
        
        $tpl = new TemplateEngine();
    }
    
}
