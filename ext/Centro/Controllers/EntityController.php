<?php

/*
    Entity controller is the default controller used when a UrlMatch or EntityNode
    are invoked. This will take the request and execute the code to get the entity
    data, then run either the default template or call the supplied template.
*/

namespace Ext\Centro\Controllers;

use Mojo\System\Controller;

class EntityController extends Controller {
    
    public function index(&$req, &$res) {
        die('here');
    }

}
