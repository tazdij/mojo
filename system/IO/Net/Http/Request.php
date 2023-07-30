<?php

namespace Mojo\IO\Net\Http;

class UnsupportedHttpVerbException extends \Exception {}

class Request
{
	/**
	 * The Verbs only benefit from being a bitmask when setting filters
	 * to determine if a verb is handled, allowing or'ing them together
	 * and simplified testing on if handled by a route.
	 */
	const GET		= 0b00000001;
	const POST		= 0b00000010;
	const PUT		= 0b00000100;
	const DELETE	= 0b00001000;
	const HEAD		= 0b00010000;

	/**
	 * Create the request from server variables
	 */
	public static function CreateInitialRequest()
	{
		//print('Print Verbs:' . "\n");
		//print("Get: " . Request::GET . "\n");
		//print("Post: " . Request::POST . "\n");
		//print("Put: " . Request::PUT . "\n");
		//print("Delete: " . Request::DELETE . "\n");

		$verb = null;
		switch ($_SERVER['REQUEST_METHOD']) {
			case 'GET':
				$verb = Request::GET;
				break;

			case 'POST':
				$verb = Request::POST;
				break;

			case 'PUT':
				$verb = Request::PUT;
				break;

			case 'DELETE':
				$verb = Request::DELETE;
				break;

			default:
				throw new UnsupportedHttpVerbException('The HTTP verb (method) "' . $_SERVER['REQUEST_METHOD'] . '" is not supported');
				return;
		}

		$post_data = PostData::CreateFromInitialRequest();

		print_r($post_data);
		$cookie_manager = CookieManager::CreateFromInitialRequest();

		$request = new Request($verb, $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'], $_SERVER['QUERY_STRING'], $post_data, $cookie_manager, isset($_SESSION) ? $_SESSION : array());

		return $request;
	}

	public static function CreateRequest()
	{

	}

	public $Verb = Request::GET;
	public $Hostname = null;
	public $Path = null;

	/* Request.Params
	 *	Contains the parameter of the Route. For example
	 *			RegexRoute: /about/(?<name>[a-zA-Z]+)/?
	 *			Requesting: http://domain.com/about/donduvall
	 *	The Route should populate the Request.Params prior to calling the
	 *	handler function. In this case Params will be:
	 *			Params: [
	 *				1 => "donduvall",
	 *				"name" => "donduvall"
	 *			]
	 */
	public $Params = array();

	/* Request.QueryString
	 *	The URL QueryString, everything following the ? in the Url until the end
	 *	or the Anchor text.
	 */
	public $QueryString = null;

	/* Request.Query
	 *	The QueryString parsed into an Array for easy access to the data
	 */
	public $Query = array();


	public $Cookies = null;
	public $Session = null;

	/* Request.Body
	 *	If Request submitted a body or data, Body will contain an instance of
	 *	PostData containing the actual Posted data and many easy methods for
	 *	accessing the data.
	 */
	public $Body = null;

	public $Headers = array();



	public function __construct($verb, $hostname, $uri, $query_string, $post_data, $cookies=array(), $session=array())
	{
		$this->Path = parse_url($uri, PHP_URL_PATH);
		$this->QueryString = $query_string;
		$this->Hostname = $hostname;
		$this->Verb = $verb;
		$this->Query = array();
		$this->Cookies = $cookies;
		$this->Session = $session;
		$this->Body = $post_data;
		//$this->Headers = getallheaders();

		$headers = [];
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == 'HTTP_') {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		$this->Headers = $headers;
		//var_dump($this->Headers);

		// Build query from QueryString & post values sent into the constructor
		parse_str($this->QueryString, $this->Query);
	}



}
