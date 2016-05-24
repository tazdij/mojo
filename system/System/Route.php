<?php

namespace Mojo\System;

use Mojo\IO\Net\Http\Request;

class Route
{

	protected $Pattern;
	protected $Verbs;
	protected $Handler;

	public function __construct($verbs, $pattern, $handler)
	{
		$this->Verbs = $verbs;
		$this->Pattern = $pattern;
		$this->Handler = $handler;
	}

	/**
	 * Checks if the request is a match to this route
	 */
	public function isMatch(&$request)
	{
		// If Verb not handled exit false
		if (!$this->handlesVerb($request->Verb))
			return false;

		// Check if pattern is a match
		if ($request->Path == $this->Pattern) {
			return true;
		}

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
		//print("callHandler. \n");
		if (is_string($this->Handler) || is_callable($this->Handler)) {
			return call_user_func_array($this->Handler, array(&$req, &$res));
		}
	}

}
