<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

use Mojo\System\Config;
use Mojo\System\Connections;

use Mojo\IO\Net\Http\Request;
use Mojo\System\RequestPipe;
use Mojo\System\Router;
use Mojo\System\Route;
use Mojo\System\RegexRoute;

// quite on favicon request
if ($_SERVER['REQUEST_URI'] == '/favicon.ico') { die(); }

//TEST: Testing Session Start
session_start();

if (!isset($_SESSION['count'])) {
    $_SESSION['count'] = 0;
} else {
    $_SESSION['count']++;
}
//END TEST: End Testing Session

// Insert a test cookie to check if the cookie manager is picking them up.
setcookie('testcookie', 'testcookievalue', time()+7200);
setcookie('testcookie2', 'testcookie2value___', time()+7200);

// Require the bootstraper
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

// Create the initial Http\Request
$request = Request::CreateInitialRequest();

// Load in the configuration system
Config::load('app');

function mojo_testPost(&$req, &$res) {
    print('mojo_testPost.' . "\n");
    print_r($_COOKIE);

    print("test getting post data. \n");
    print("field: " . $req->Body->get('field') . "\n");

    print_r($req);


    return true;
}

function mojo_aboutNewHandler(&$req, &$res) {
	print 'About -> New Handler' . "\n";

    return true;
}

function mojo_aboutUpdateHandler(&$req, &$res) {
	print 'About -> Update Handler' . "\n";

    return true;
}



Router::add(new Route(Request::GET, '/', function(&$req, &$res) {
	print 'INDEX!' . "\n";
    print Config::get('base_url', 'app');

    return true;
}));

Router::add(new Route(Request::POST, '/', 'mojo_testPost'));
Router::add(new RegexRoute(Request::POST, '/i/(?<name>[a-zA-Z]+)', 'mojo_testPost'));
Router::add(new RegexRoute(Request::GET | Request::POST, '/about', 'mojo_aboutNewHandler'));
Router::add(new RegexRoute(Request::GET, '/about/(?<name>[a-zA-Z0-9]+)', 'mojo_aboutNewHandler'));
Router::add(new RegexRoute(Request::GET, '/about/(?<name>[a-zA-Z0-9\.\-_]+)', 'mojo_aboutUpdateHandler'));
Router::add(new Route(Request::POST, '/set/about', 'mojo_aboutUpdateHandler'));


//print_r($request);

RequestPipe::processRequest($request);
