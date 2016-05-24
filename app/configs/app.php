<?php

$protocol = (strncmp($_SERVER['SERVER_PROTOCOL'], 'HTTP/', 5) === 0) ? 'http' : 'https';




return array(
    'base_url' => $protocol . '://' . $_SERVER['HTTP_HOST'] . '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'charset' => 'UTF-8',

);
