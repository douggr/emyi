<?php
/*
 * Emyi
 *
 * @link http://github.com/douggr/Emyi for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace Emyi\Db;

use PDOException;
use Emyi\Util\Config;

/**
 * Manage connections between PHP and a database server.
 */
final class ConnectionManager
{
    /**
     *
     */
    private static $instances = [];

    /**
     * List of supported drivers and their mappings to the driver class.
     * @var array
     */
    private static $driver_map = [
        'mysql'  => 'Emyi\Db\Driver\MySql\Adapter',
        //'mysqli' => 'Emyi\Db\Driver\MySqli\Adapter',
        'sqlite' => 'Emyi\Db\Driver\Sqlite\Adapter',
        'pgsql'  => 'Emyi\Db\Driver\Postgres\Adapter',
        //'ibm'    => 'Emyi\Db\Driver\Ibm\Adapter',
        //'oci'    => 'Emyi\Db\Driver\Oracle\Adapter',
        //'sqlsrv' => 'Emyi\Db\Adapter\SqlServer\Adapter',
    ];

    /**
     * Creates a connection object based on the specified configuration
     * parameters.
     *
     * This method returns a Emyi\Db\Connection which wraps the underlying
     * driver connection.
     *
     * The data returned from Config::get($config_entry) must contain the
     * following:
     *  driver: with one of the $driver_map value;
     *  username: The username to use when connecting (This parameter is
     *      optional for some drivers);
     *  password: The password to use when connecting (This parameter is
     *      optional for some drivers);
     *  host: The host to connect to;
     *  database: The database name;
     *  port: The port to use when connecting;
     *
     * @param string $config_entry The database configuration entry in
     *      application.ini
     * @return Emyi\Db\Connection
     * @throws Emyi\Db\Exception
     */
    public static function getConnection($config_entry = null)
    {
        // if the $config_entry is a hash, try to locate and return it
        // instead of another load
        if (isset(self::$instances[$config_entry])) {
            return self::$instances[$config_entry];
        }

        if ('' !== trim($config_entry)) {
            $config_entry = '.' . $config_entry;
        }

        $hash = md5($config_entry);

        if (isset(self::$instances[$hash])) {
            return self::$instances[$hash];
        }

        self::checkConfigData($data = Config::get('database' . $config_entry));

        if (false !== $hasClass = isset($data['class'])) {
            $class = $data['class'];
        } else {
            $class = self::$driver_map[$data['driver']];
        }

        $conn = new $class(
            array_merge($data, ['hash' => $hash]),
            isset($data['username']) ? $data['username'] : null,
            isset($data['password']) ? $data['password'] : null);

        if ($hasClass && !$conn instanceof Connection) {
            throw new Exception(
                'The given class "' . $data['class'] . '" has to ' .
                'inherit the Emyi\Db\Connection class.');
        }

        return self::$instances[$hash] = $conn;
    }

    /**
     * Drop/close a connection
     *
     * @param string $config_entry The database configuration entry in
     *      application.ini
     * @return void
     */
    public static function dropConnection($config_entry = null)
    {
        $hash = md5($config_entry);

        if (isset(self::$instances[$hash])) {
            unset(self::$instances[$hash]);
        }
    }

    /**
     * Register a unknown driver to use within the Emyi\Db\Connection.
     * If you want to register any driver, you must call this function
     * before getConnection.
     *
     * @param string $driver The driver name
     * @param string $class The class to map to the given driver
     * @return void
     */
    public static function registerDriver($driver, $class)
    {
        self::$driver_map[$driver] = $class;
    }

    /**
     * Returns the list of supported drivers.
     * @return array
     */
    public static function getAvailableDrivers()
    {
        return array_keys(self::$driver_map);
    }

    /**
     * Check existence of mandatory parameters within the config entry.
     * @return void
     */
    private static function checkConfigData(array $data)
    {
        // driver
        if (!isset($data['driver'])) {
            throw new Exception('The option "driver" is mandatory');
        }

        $available = self::getAvailableDrivers();
        if (!in_array($data['driver'], $available)) {
            throw new Exception(
                'The given driver "' . $data['driver'] . '" is unknown, '.
                'supported drivers are: ' . implode(', ', $available));
        }
    }

    /**
     * PHP classes cannot be final and abstract at the same time.
     */
    private function __construct()
    {
    }
}
