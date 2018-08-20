<?php

namespace Mojo\System;

use Mojo\System\Config;
use Mojo\Data\Mysql\Con;
use Mojo\Data\Mysql\Orm;

class Controller {

    private $db;

    public function __construct() {
        // Autoload libraries
        $libs = Config::get('libraries', 'autoload');
        if (count($libs) > 0) {
            foreach ($libs as $lib) {
                $classname = 'App\\Libraries\\' . $lib;
                $varname = strtolower($lib);
                $this->$varname = new $classname();
            }
        }

        $models = Config::get('models', 'autoload');
        if (count($models) > 0) {
            foreach ($models as $model) {
                $this->loadModel($model);
            }
        }
    }

    public function loadModel($model) {
        $db = $this->_get_db();

        $classname = 'App\\Models\\' . $model;
        $varname = strtolower($model) . '_model';
        $this->$varname = new $classname($db);
    }

    public function _handle_req(&$req, &$res) {
        try {
            return $this->index($req, $res);
        } catch (Exception $e) {
            return false;
        }
    }

    protected function _get_db() {
        $db_conf = Config::getAll('database');

        $con = new Con($db_conf['host'], $db_conf['user'], $db_conf['pass'], $db_conf['database'], $db_conf['prefix']);
        $db = new Orm($con);

        return $db;
    }
}
