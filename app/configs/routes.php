<?php

use Mojo\IO\Net\Http\Request;
use Mojo\System\RequestPipe;
use Mojo\System\Router;
use Mojo\System\Route;
use Mojo\System\RegexRoute;
Router::add(new Route(Request::GET, '/template', ['App\Controllers\IndexController', 'template']));
Router::add(new Route(Request::GET, '/', array('App\Controllers\IndexController', 'index')));
Router::add(new Route(Request::POST, '/auth/login', array('AuthController', 'login')));
/*
Router::add(new Route(Request::POST, '/auth/renew', array('AuthController', 'renew')));
Router::add(new Route(Request::POST, '/auth/logout', array('AuthController', 'logout')));
Router::add(new Route(Request::GET, '/blahblah', 'mojo_blahHandler'));
*/


return array();