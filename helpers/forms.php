<?php

use Mojo\System\JWT;
use Mojo\System\Config;

if (!function_exists('forms_create_nonce')) {
    function forms_create_nonce($name, $expires_minutes=30) {
        $data = array();
        $data['create_time'] = time();
        $data['expires_time'] = time() + (60 * $expires_minutes);
        $data['name'] = $name;

        $token = JWT::encode($data, Config::get('encryption_salt', 'app'));

        return $token;
    }
}

if (!function_exists('forms_validate_nonce')) {
    function forms_validate_nonce($name, $token) {
        $data = JWT::decode($token, Config::get('encryption_salt', 'app'), array('HS256'));
        return $data;
    }
}
