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

//TODO: Properly handle the favicon from the context attributes
// quite on favicon request
if ($_SERVER['REQUEST_URI'] == '/favicon.ico') { die(); }


//print_r($_SERVER);


// Insert a test cookie to check if the cookie manager is picking them up.
//setcookie('testcookie', 'testcookievalue', time()+7200);
//setcookie('testcookie2', 'testcookie2value___', time()+7200);

//define('DS', DIRECTORY_SEPARATOR);

//$_SERVER['SCRIPT_FILENAME']

// Require the startup path config & bootstraper
require_once dirname($_SERVER['SCRIPT_FILENAME'], 2) . DIRECTORY_SEPARATOR . 'paths.php';


require_once SYSTEM_DIR . 'bootstrap.php';

//print(ROOT_DIR . "\n");

//print_r($_SERVER);


/**
 * 1.0 CREATE REQUEST OBJECT
 * 
 * 
 */

// Create the initial Http\Request
$request = Request::CreateInitialRequest();




/**
 * 2.0 LOAD CONFIGURATIONS & AUTOLOAD
 * 
 * 
 */

// Load in the configuration system
Config::load('app');
Config::load('autoload');

Config::load('database');





/**
 * 2.1 INSTANSIATE EXTENSIONS
 * 
 * 
 */

// Load and run Extensions
$extensions = Config::get('extensions', 'autoload');
if (count($extensions) > 0) {
    foreach ($extensions as $extension) {
        
        // Load the extension
        $class_name = "Ext\\" . $extension . "\\" . $extension;

        try {
            $obj = new $class_name();
        } catch (Exception $e) {
            //print($e->getMessage());
            throw new Exception("Failed to load extension: " . $extension . "\nError Received: " . $e->getMessage());
        }
    }
}







/**
 * 2.2 AUTOLOAD HELPERS
 * 
 * 
 */
// Load Helpers from autoload
$helpers = Config::get('helpers', 'autoload');
if (count($helpers) > 0) {
    foreach ($helpers as $helper) {
        Helper::load($helper);
    }
}








/**
 * 3.0 LOAD APP ROUTE CONFIGURATIONS
 * 
 * 
 */

// Load the Routes
Config::load('routes');







/*
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
*/

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
//Router::add(new RegexRoute(Request::GET, '/hm', new TestController()));


//print_r($request);

/**
 * 4.0 PROCESS THIS REQUEST
 * 
 * 
 */

RequestPipe::processRequest($request);
