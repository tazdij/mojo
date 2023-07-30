<?php

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_DIR', dirname($_SERVER['SCRIPT_FILENAME'], 2) . DS);
define('SYS_HELPER_DIR', ROOT_DIR . 'helpers' . DS);
define('SYS_LIBRARY_DIR', ROOT_DIR . 'libraries' . DS);
define('APP_DIR', ROOT_DIR . 'app' . DS);
define('SYSTEM_DIR', ROOT_DIR . 'system' . DS);
define('CONFIG_DIR', APP_DIR . 'configs' . DS);
define('HELPER_DIR', APP_DIR . 'helpers' . DS);
define('LIBRARY_DIR', APP_DIR . 'libraries' . DS);


// print(ROOT_DIR . "\n");
// print(SYS_HELPER_DIR . "\n");
// print(SYS_LIBRARY_DIR . "\n");
// print(APP_DIR . "\n");
// print(SYSTEM_DIR . "\n");
// print(CONFIG_DIR . "\n");
// print(HELPER_DIR . "\n");
// print(LIBRARY_DIR . "\n\n");