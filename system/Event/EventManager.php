<?php

namespace Mojo\Event;

class InvalidEventTypesArrayException extends \Exception {}
class InvalidEventListenerException extends \Exception {}

/**
 * Event\Manager is a singleton or static class that handles
 * the registration of new Events and Listeners
 */

class EventManager {

	/**
	 * $listeners holds the list of all subscribed receivers to each tag
	 *
	 * Data Structure:
	 * 		$listeners['Tag'][...]
	 *
	 * Each tag array holds references to the objects or functions that can be
	 * used as an Event Listener
	 */
	private static $listeners = array();

	// ? not sure about this one yet
	private static $emitters;

	/**
	 * newEvent is a static method allowing for a Factory like Event Creation Model
	 *
	 * Using this function to create Events allows for a much simpler syntax for creation
	 * and the emitting of events.
	 */
	public static function newEvent($type) {
		return new Event($type);
	}

	public static function emit(&$event) {
		$type = $event->getType();

		if (!isset(self::$listeners[$type]))
			return true;

		foreach (self::$listeners[$type] as &$listener) {
			if (!$listener->onEvent($event))
				return false;
		}

		return true;
	}

	public static function subscribe(&$listener, $types) {
		if (!is_array($types) && !is_string($types))
			throw new InvalidEventTypesArrayException("Unable to register listener of '" . get_class($listener) . "'. No types defined to listen to.");

		if ($listener instanceof IListener) {
			if (is_string($types))
				$types = array($types);

			foreach ($types as &$type) {
				self::$listeners[$type][] = $listener;
			}

		} else {
			throw new InvalidEventListenerException("Object of '" . get_class($listener) . "' does not implement Mojo\\Event\\IListener.");
		}
	}

	public static function unsubscribe(&$listener, $types=null) {
		if ($types === null) {
			// load types with every possible event tag
			throw new InvalidEventTypesArrayException("Unable to unregister listener of '" . get_class($listener) . "'. No types defined to remove from.");
		}

		if (is_string($types))
			$types = array($types);

		foreach ($types as &$type) {
			$idx = array_search($listener, self::$listeners[$type]);
			if ($idx !== false) {
				unset(self::$listeners[$type][$idx]);
			}
		}
	}
}
