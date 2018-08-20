<?php

namespace Mojo\System;

use Mojo\IO\Net\Http\Request;
use Mojo\IO\Net\Http\Response;

class RegexRoute extends Route
{

	//protected $Pattern;
	//protected $Verbs;
	//protected $Handler;

	/*
		The regex pattern is a custom syntax which should be simple
		Pattern Examples:
		/about/(?<name>[a-zA-Z0-9])/
	*/

	public function __construct($verbs, $pattern, $handler)
	{
		$this->Verbs = $verbs;

		if (($temp = strlen($pattern) - strlen("/?")) >= 0 && strpos($pattern, "/?", $temp) !== FALSE) {
			// remove '/?' at the end of the string
			$this->Pattern = substr($pattern, 0, strlen($pattern) - 2);
			//print($this->Pattern . "\n");
		} else {
			$this->Pattern = $pattern;
		}
		$this->Handler = $handler;
	}

	/**
	 * Checks if the request is a match to this route using the preg_match
	 *	function. All subpatterns are extracted into variables which are added
	 *	to the request object. This allows for named parameters if used in the
	 *	regex string. If the regex does not contain named subpatterns then they
	 *	will be added to the request object, indexed by their position in the
	 *	regex string.
	 */
	public function isMatch(&$request)
	{
		// If Verb not handled exit false
		if (!$this->handlesVerb($request->Verb))
			return false;

		// Check if pattern is a match
		$matches = array();
		if (preg_match_all('#^' . $this->Pattern . '/?$#', $request->Path, $matches)) {
			//print_r($matches);
			return true;
		}

		return false;
	}

	/**
	 * Checks if this Route will handle the specified HTTP verb (method)
	 */
	private function handlesVerb($verb)
	{
		if ($this->Verbs & $verb) {
			return true;
		}
		return false;
	}

	/**
	 * Call the handler passing all of the arguments from URL (The request object)
	 */
	public function callHandler(&$req, &$res)
	{
		// Before calling handler we will need to parse the arguments and place
		// the params into the Request.Params Array

		$matches = array();
		if (preg_match_all('#^' . $this->Pattern . '/?$#', $req->Path, $matches)) {
			//print_r($matches);
			foreach ($matches as $key => $val) {
				//print('Param: (' . $key . ') => ' . $val[0] . "\n");

				// Skip Zero, it is the path reiterated
				if ($key === 0) { continue; }

				$req->Params[$key] = $val[0];
			}

		} else {
			throw new \Exception("Route called but unable to handle Request '" . $req->Path . "'");
		}


		// Send to parent class handler
		return parent::callHandler($req, $res);
	}

}
