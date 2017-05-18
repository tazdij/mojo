<?php

$protocol = (strncmp($_SERVER['SERVER_PROTOCOL'], 'HTTP/', 5) === 0) ? 'http' : 'https';




return array(
    'base_url' => $protocol . '://' . $_SERVER['HTTP_HOST'] . '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'charset' => 'UTF-8',
    'encryption_salt' => 'B5C0C09BEC30499CE0982A3DA61A14A0966EFC4BC483305416868B749A9E0B41',
);
