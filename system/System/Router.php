<?php

namespace Mojo\System;

class NotInstanceOfRouteException extends \Exception {}
class DuplicateRouteAdditionException extends \Exception {}

class Router
{

	private static $Routes = array();

	public static function add(&$route)
	{
		if ( !($route instanceof Mojo\System\Route) ) {

			if (in_array($route, self::$Routes))
				throw new DuplicateRouteAdditionException('Router::add -> route supplied already exists in Router');

			self::$Routes[] = $route;
			return count(self::$Routes)-1;
		} else {
			throw new NotInstanceOfRouteException('Router::add -> route supplied is not instance of Mojo\\System\\Route');
		}
	}

	public static function &matchRequest(&$request)
	{
		$matched = array();

		foreach (self::$Routes as &$route) {
			if (!$route->isMatch($request))
				continue;

			// The route is a match, add to matched list
			$matched[] = $route;
		}

		return $matched;
	}

	public static function routeRequest(&$request)
	{
		foreach (self::$Routes as &$route) {
			if (!$route->isMatch($request))
				continue;

			// The route is a match, continue to start processing
		}
	}

}
