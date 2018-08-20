<?php

namespace Mojo\System;


class ConfigFileNotFoundException extends \Exception {}
class ConfigFileNotLoadedException extends \Exception {}
class ConfigNotFoundException extends \Exception {}

/**
 * Provides a static class to allow for loading config files
 */
class Config {
	protected static $_configs;

	public static function load($name, $alias=null) {

		if ($alias === null)
			$alias = $name;

		if (isset(self::$_configs[$alias]))
			return false;

		try {
			self::$_configs[$alias] = include CONFIG_DIR . $name . '.php';
			return true;
		} catch (Exception $e) {
			throw new ConfigFileNotFoundException('The config file "' . $name . '.php' . '" not found in the app/configs directory.');
		}
	}

	public static function get($name, $file='app') {
		if (!isset(self::$_configs[$file]))
			if (!self::load($file))
				throw new ConfigFileNotLoadedException('The config file "' . $file . '" has not been loaded yet.');


		if (!isset(self::$_configs[$file][$name]))
			throw new ConfigNotFoundException('The option "' . $name . '" in file "' . $file . '" not found.');

		return self::$_configs[$file][$name];
	}

	public static function getAll($file='app') {
		if (!isset(self::$_configs[$file]))
			if (!self::load($file))
				throw new ConfigFileNotLoadedException('The config file "' . $file . '" has not been loaded yet.');


		if (!isset(self::$_configs[$file]))
			throw new ConfigNotFoundException('The file "' . $file . '" was not found.');

		return self::$_configs[$file];
	}
}
