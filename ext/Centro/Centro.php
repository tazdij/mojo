<?php

/*
    The main class of the Centro CMS. This is sort of the Index.php file for Centro.
    It adds the needed functionality to override Mojo's default routing system,
    as well as adding Centro Controllers into the routes.
*/

namespace Ext\Centro;

use Mojo\IO\Net\Http\Request;
use Mojo\System\RequestPipe;
use Mojo\System\Router;
use Mojo\System\Route;
use Mojo\System\RegexRoute;
use Mojo\System\Extension;

class Centro extends Extension {

    public function __construct() {
        // Load the centro config file from App
        
        $this->setupRoutes();
    }

    protected function setupRoutes() {
        //Router::add(new RegexRoute(Request::GET, '/hm/user/(?<name>[^/]+)/other', array($this, 'user')));
        Router::add(new Route(Request::GET, '/Centro/Admin', ['Ext\Centro\Controllers\AdminController', 'index']));
        /*Router::add(new Route(Request::POST, '/auth/login', array('AuthController', 'login')));
        Router::add(new Route(Request::POST, '/auth/renew', array('AuthController', 'renew')));
        Router::add(new Route(Request::POST, '/auth/logout', array('AuthController', 'logout')));
        Router::add(new Route(Request::GET, '/blahblah', 'mojo_blahHandler'));
        */
    }

}
