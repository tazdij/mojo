<?php

namespace Mojo\System;

use Mojo\IO\Net\Http\Request;
use Mojo\System\Controller;

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
		if (is_string($this->Handler) || is_callable($this->Handler)) {
			return call_user_func_array($this->Handler, array(&$req, &$res));
		} elseif ($this->Handler instanceof Controller) {
			return $this->Handler->_handle_req($req, $res);
		} elseif (is_array($this->Handler)) {
			$class = "App\\Controllers\\" . $this->Handler[0];
			$method = $this->Handler[1];
			$ctl = new $class();
			$ctl->$method($req, $res);
		}
	}

}
