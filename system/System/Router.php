<?php

namespace Mojo\System;

//use Mojo\System\Route;


class NotInstanceOfRouteException extends \Exception {}
class DuplicateRouteAdditionException extends \Exception {}



class Router
{

	private static $Routes = array();

	public static function add($route)
	{
		// print('ADDING ROUTE:' . "\n");
		// print("Current routes loaded:\n");
		// print_r(self::$Routes);

		// print("\n\n");
		// print("Adding following route:\n");
		// var_dump($route);
		// print("\n\n");
		// $is_inst = $route instanceof \Mojo\System\Route;
		// print("is_inst:\n");
		// var_dump($is_inst);
		// print("\n");
		if ( $route instanceof \Mojo\System\Route ) {

			if (in_array($route, self::$Routes))
				throw new DuplicateRouteAdditionException('Router::add -> route supplied already exists in Router');

			self::$Routes[] = $route;
			return count(self::$Routes)-1;
		} else {
			print("NOT instance of Route?\n");
			throw new NotInstanceOfRouteException('Router::add -> route supplied is not instance or descendant of Mojo\\System\\Route');
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

	/* ?Is this even used? */
	public static function routeRequest(&$request)
	{
		foreach (self::$Routes as &$route) {
			if (!$route->isMatch($request))
				continue;

			// The route is a match, continue to start processing
		}
	}

}
