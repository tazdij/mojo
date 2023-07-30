<?php

namespace Mojo\System;

use Mojo\IO\Net\Http\Response;
use Mojo\Template\TemplateEngine;
use Mojo\Template\Plates\PlatesEngine;

class RequestPipe
{

	/* processRequest(Request $request)
	 *	to process the request, test all routes to find any that can accept the
	 *	Request (via isMatch() in each registered Route object). The Pipeline
	 *	will call the Routes in order from first to last (0..len) until the
	 *	first Route which returns true. Once the request is handled and the loop
	 *	is exited, the response should be sent back to the browser.
	 *
	 */
	public static function processRequest(&$request, $return = FALSE)
	{

		// We should default to Plates (it is super lightweight and just php)

		//TEMP: Create a template instance for all responses?
		$tpl = new TemplateEngine(new PlatesEngine());

		$response = new Response(array(), $request->Cookies, $tpl);

		// Get the list of Routes this matches
		$routes = Router::matchRequest($request);

		foreach ($routes as &$route) {
			if ($route->callHandler($request, $response)) {
				break;
			}
		}


		//TODO: Check if the response has a reprocess request
		//	If so, there should be a request object inside of
		//	the response. This request should be pulled out,
		//	the response deleted and then new request passed
		//	into the processRequest pipeline.
		//NOTE: this is not the same as a redirect which is
		//	an HTTP Response Header value.
		//print_r($response);


		//TODO: Process the final response and output
		// Response Set Cookies

		// Response Set Headers
		// Response Send Output


		//$handled = false;
		//$i = 0;
		//do {
		//	$handled =
		//} while(!$handled);

		// If not a return, send to browser
		if (!$return)
    		$response->send();
    	else
    	    // Return to requestor (probably a sub request)
    	    return $response;


	}

    

}
