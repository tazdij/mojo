<?php

namespace App\Models;

use Mojo\System\Model;
use Mojo\System\Config;

class User extends Model {

    public function get_providers() {
        $res = $this->db->query('SELECT * FROM providers');

        return $res;
    }

    public function validateLogin($username, $password) {
        return $this->hashPassword($password);
    }

    private function hashPassword($password) {
        $salt = Config::get('encryption_salt');

        $hash = sha1($password . $salt);

        return $hash;
    }

}