<?php

namespace Mojo\Data;

use Mojo\System\Config;

use Mojo\Data\Mysql\Con;
use Mojo\Data\Mysql\Orm;


class DatabaseConfigIdentifierNotExistsException extends \Exception {}

class SQLDB {

    // Contains all the instantiated (shared) connections (used to create new Orm)
    protected static $_connections = array();



    public static function get($ident='default', $force_new=FALSE) {
        if (array_key_exists($ident, self::$_connections) && !$force_new) {
            // Return the existing connection

            // Connections shared between multiple Orm objects
            // Create a new Orm with the
            return new Orm(self::$_connections[$ident]);
        }

        $settings = Config::getAll('database');

        // get the settings from config for requested connection
        if (!array_key_exists($ident, $settings)) {
            throw new DatabaseConfigIdentifierNotExistsException("The database connection '${ident}' was not found in ${CONFIG_DIR}database.php");
        }

        // Get the settings by ident for this connection
        $conf = $settings[$ident];

        //print_r($conf);

        $cons = new Con($conf['host'],
                          $conf['port'],
                          $conf['user'],
                          $conf['pass'],
                          $conf['database'],
                          $conf['charset'],
                          $conf['prefix']);

        // Cache connection for future use
        self::$_connections[$ident] =& $cons;

        // Return a new Orm instance, using shared connection.
        return new Orm($cons);
    }

    // convenience method for query into database
    public static function query($str, $args=array(), $db_ident='default') {
        die('SQLDB::query called, but not implemented');
    }

}
