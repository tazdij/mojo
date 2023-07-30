<?php

namespace Ext\Centro\Controllers;

use Ext\Centro\System\Controller;

/**
 * NodeController
 * 
 * The Centro default controller, to handle any request, which is matched
 * to the sitetree. This will load only after the concrete (php coded)
 * routes.
 */

class NodeController extends Controller {

    public function __construct()
    {
        parent::__construct();

    }

    // index - the default route to run on a node.
    //
    // 
    public function index() {

    }

}