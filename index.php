<?php


ini_set('html_errors', '1');
ini_set('display_errors', '1');
error_reporting(E_ALL);

use Mojo\System\Config;
use Mojo\System\Connections;
use Mojo\System\Helper;

use Mojo\IO\Net\Http\Request;
use Mojo\System\RequestPipe;
use Mojo\System\Router;
use Mojo\System\Route;
use Mojo\System\RegexRoute;

// quite on favicon request
if ($_SERVER['REQUEST_URI'] == '/favicon.ico') { die(); }


// Insert a test cookie to check if the cookie manager is picking them up.
//setcookie('testcookie', 'testcookievalue', time()+7200);
//setcookie('testcookie2', 'testcookie2value___', time()+7200);

// Require the bootstraper
require_once dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR . 'paths.php';

require_once SYSTEM_DIR . 'bootstrap.php';

// Create the initial Http\Request
$request = Request::CreateInitialRequest();

// Load in the configuration system
Config::load('app');
Config::load('autoload');

// Load and run Extensions
$extensions = Config::get('extensions', 'autoload');
if (count($extensions) > 0) {
    foreach ($extensions as $extension) {
        // Load the extension
        $class_name = "Ext\\" . $extension . "\\" . $extension;
        $obj = new $class_name();
    }
}

// Load Helpers from autoload
$helpers = Config::get('helpers', 'autoload');
if (count($helpers) > 0) {
    foreach ($helpers as $helper) {
        Helper::load($helper);
    }
}

// Load the Routes
Config::load('routes');



class TestController extends \Mojo\System\Controller {

    public function __construct() {
        Router::add(new RegexRoute(Request::GET, '/hm/user/(?<name>[^/]+)/other', array($this, 'user')));
    }


    public function index(&$req, &$res) {
        print('hello');

        return true;
    }

    public function user(&$req, &$res) {
        print('TestController::User blah');

        print_r($req);
        print_r($res);

        return true;
    }

}


/*
Router::add(new Route(Request::GET, '/', function(&$req, &$res) {
	print 'INDEX!' . "\n";
    print Config::get('base_url', 'app');

    return true;
}));
*/
//Router::add(new Route(Request::POST, '/', 'mojo_testPost'));
/*
Router::add(new RegexRoute(Request::POST, '/i/(?<name>[a-zA-Z]+)', 'mojo_testPost'));
Router::add(new RegexRoute(Request::GET | Request::POST, '/about', 'mojo_aboutNewHandler'));
Router::add(new RegexRoute(Request::GET, '/about/(?<name>[a-zA-Z0-9]+)', 'mojo_aboutNewHandler'));
Router::add(new RegexRoute(Request::GET, '/about/(?<name>[a-zA-Z0-9\.\-_]+)', 'mojo_aboutUpdateHandler'));
Router::add(new Route(Request::POST, '/set/about', 'mojo_aboutUpdateHandler'));
Router::add(new RegexRoute(Request::GET, '/cookies', 'mojo_cookiesHandler'));
*/
Router::add(new RegexRoute(Request::GET, '/hm', new TestController()));


//print_r($request);

RequestPipe::processRequest($request);
