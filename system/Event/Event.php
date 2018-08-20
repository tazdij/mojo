<?php

namespace Mojo\Event;

class EventParamNotExists extends \Exception {}

/**
 * Event is the base class for all events in Mojave
 *
 * Params are added via bindParam - as all params are only references
 * to the original values that were sent into the event. This allows
 * for events to manipulate the data in the event without the overhead
 * of passing modified objects over the stack.
 */
class Event {

	protected $_params;
	protected $_type;

	public function __construct($type) {
		$this->_params = array();
		$this->_type = $type;
	}

	public function bindParam($name, &$value) {
		$this->_params[$name] =& $value;

		return $this;
	}


	public function setParam($name, $value) {
		if (!isset($this->_params[$name]))
			throw new EventParamNotExists('The parameter "' . $name . '" was not bound to the event');

		$this->_params[$name] = $value;

		return $this;
	}

	public function emit() {
		return EventManager::emit($this);
	}

	/**
	 * getParam returns a reference to the variable passed in to bindParam
	 *
	 * For this reference to work correctly you must use the use the =& getParam('name')
	 * assign by reference syntax
	 */
	public function &getParam($name) {
		if (!isset($this->_params[$name]))
			return null;

		return $this->_params[$name];
	}


	public function getType() {
		return $this->_type;
	}
}
