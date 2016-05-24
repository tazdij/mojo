<?php

namespace Mojo\Event;

class FuncListenerNotCallableException extends \Exception {}

class FuncListener implements IListener {

	private $func;
	
	public function __construct($func) {
		if (!is_callable($func)) {
			throw new FuncListenerNotCallableException("The argument supplied to FuncListener is not Callable");
		}

		$this->func = $func;
	}

	public function onEvent(&$event) {
		return call_user_func_array($this->func, array(&$event));
	}

}
