<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

print('here');

require_once(dirname(__FILE__) . '/libsingle.php');

print('HERE');

$db1 = SingleDB::getInstance();

$db1_con = $db1->connect();

var_dump($db1_con);

var_dump($db1->connect());