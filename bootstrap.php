<?php

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_DIR', dirname(__FILE__) . DS);
define('APP_DIR', ROOT_DIR . 'app' . DS);
define('SYSTEM_DIR', ROOT_DIR . 'system' . DS);
define('CONFIG_DIR', APP_DIR . 'configs' . DS);

class FileNotExistsException extends \Exception {}
/**
 * Autoload handling for all namespaced files, including application and theme
 */
function mojo_autoload($class_name)
{
	// Split into parts
	$parts = explode('\\', $class_name);

	// False if no namespace
	if (count($parts) < 2)
		return false;

	$prefix = array_shift($parts);

	switch ($prefix) {
		case 'Mojo':
			$base = SYSTEM_DIR;
			break;

		case 'App':
			$base = APP_DIR;
			break;

		default:
			return false;
			break;
	}

	$path = $base . implode(DS, $parts) . '.php';

	if (file_exists($path))
		include_once($path);
	else
		throw new FileNotExistsException("The class " . $class_name . " tried to be found in " . $path . " which doesn't exist");

	return true;
}
spl_autoload_register('mojo_autoload');
