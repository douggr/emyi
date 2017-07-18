<?php
/*
 * Emyi
 *
 * @link http://github.com/douggr/Emyi for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace Emyi\Db;

use PDO;
use PDOException;

/**
 * Represents a (minimalist) connection between PHP and a database server.
 * @protected
 */
abstract class Connection extends PDO
{
    /**
     * The connection attributes
     * @var array
     */
    private $attributes = [
        'driver'    => null,
        'username'  => null,
        'host'      => null,
        'database'  => null,
        'port'      => null,
    ];

    /**
     * The driver descriptor
     * @var Emyi\Db\Driver\Descriptor[]
     */
    private static $descriptors = [];

    /**
     * Attempts to create a connection with the database.
     *
     * @param array $params All connection parameters passed by the user.
     * @param string $username The username to use when connecting.
     * @param string $password The password to use when connecting.
     * @return Emyi\Db\Connection The database connection.
     * @throws Emyi\Db\ConnectionException on failure
     */
    public final function __construct(
        $params,
        $username = null,
        $password = null,
        array $options = []
    ) {
        $this->attributes = array_merge($this->attributes, $params);

        // hide this one
        if (isset($this->attributes['password'])) {
            unset($this->attributes['password']);
        }

        // forces username
        if (!isset($this->attributes['username'])) {
            $this->attributes['username'] = $username;
        }

        try {
            parent::__construct(
                $this->buildDSN($params),
                $username,
                $password,
                $this->getConnectionOptions($this->getAttribute('hash')));

            $this->setDescriptor();
        } catch (PDOException $e) {
            throw new ConnectionException($e->getMessage(), (float) $e->getCode());
        }
    }

    /**
     * Returns the Emyi\Db\Descriptor for this driver
     *
     * @return Emyi\Db\Descriptor
     */
    public function getDescriptor()
    {
        return self::$descriptors[$this->getAttribute('hash')];
    }

    /**
     * Gets the name of the driver.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getAttribute('driver');
    }

    /**
     * Gets the name of the database connected to for this driver.
     *
     * @return string
     */
    public function getDatabase()
    {
        return $this->getAttribute('database');
    }

    /**
     * Gets the hostname of the currently connected database.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->getAttribute('host');
    }

    /**
     * Gets the port of the currently connected database.
     *
     * @return mixed
     */
    public function getPort()
    {
        return $this->getAttribute('port');
    }

    /**
     * Gets the user used by this connection.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->getAttribute('username');
    }

    /**
     * Retrieve a database connection attribute.
     *
     * @param mixed One of the PDO::ATTR_* constants OR any database
     *      connection attribute from configuration. 
     * @return mixed A successful call returns the value of the requested
     *      attribute. An unsuccessful call returns null.
     */
    public function getAttribute($index)
    {
        if (is_int($index)) {
            // Any of the PDO::ATTR_* constants
            try {
                return parent::getAttribute($index);
            } catch (PDOException $e) {
                return null;
            }
        } else  {
            return isset($this->attributes[$index])
                ? $this->attributes[$index]
                : null;
        }
    }

    /**
     * Executes a function in a transaction.
     *
     * The function gets passed this Connection instance as an (optional)
     * parameter.
     *
     * If an exception occurs during execution of the function or
     * transaction commit, the transaction is rolled back and the exception
     * re-thrown.
     *
     * @param \Closure $callable The function to execute transactionally.
     * @return void
     * @throws Emyi\Db\Exception
     */
    public function transactional(\Closure $callable)
    {
        try {
            $this->beginTransaction();
            $callable($this);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Default PDO options for this connection.
     *
     * @return array
     */
	protected function getConnectionOptions($hash)
	{
	    return [
            static::ATTR_CASE               => static::CASE_LOWER,
            static::ATTR_ERRMODE            => static::ERRMODE_EXCEPTION,
            static::ATTR_ORACLE_NULLS       => static::NULL_NATURAL,
            static::ATTR_STRINGIFY_FETCHES  => false,
            static::ATTR_DEFAULT_FETCH_MODE => static::FETCH_ASSOC,
            static::ATTR_STATEMENT_CLASS    => ['Emyi\Db\Statement', [$hash]],
        ];
    }
    
    /**
     * Constructs the PDO DSN to use within this Connection.
     *
     * @param array
     * @return string The built DSN
     */
    protected function buildDSN(array $params)
    {
        $dsn = $params['driver'] . ':';

        if ($this->isValid($params['host'])) {
            $dsn .= 'host=' . $params['host'] . ';';
        }

        if ($this->isValid($params['port'])) {
            $dsn .= 'port=' . $params['port'] . ';';
        }

        if ($this->isValid($params['database'])) {
            $dsn .= 'dbname=' . $params['database'] . ';';
        }

        return $dsn;
    }

    /**
     * Returns if the given value is set and non-empty
     *
     * @param string The value to check
     * @return boolean
     */
    protected final function isValid($value)
    {
        return isset($value) && '' !== trim($value);
    }

    /**
     * Returns an array containing all of the result set rows
     *
     * @param string $sql The SQL statement to prepare and execute.
     * @param mixed $input_values An array of values with as many elements
     *      as there are bound parameters in the SQL statement being
     *      executed OR any other value (will be casted to array)
     * @return array
     * @throws Emyi\Db\ConnectionException on failure
     */
    public function fetchAll($sql, $params = null)
    {
        if (null !== $params && !is_array($params)) {
            $params = [$params];
        }

        return $this->query($sql, $params)->fetchAll();
    }

    // Overloading PDO methods
    /**
     * Executes an SQL statement, returning a result set as a
     * Emyi\Db\Statement object
     */
    public function query()
    {
        $sqld = [func_get_arg(0)];

        if (1 !== func_num_args()) {
            $stmt = $this->wrap('prepare', $sqld);

            try {
                $stmt->execute((array) func_get_arg(1));
            } catch (PDOException $e) {
                throw new Exception($e->getMessage(), (int) $e->getCode());
            }
        } else {
            $stmt = $this->wrap('query', $sqld);
        }

        return $stmt;
    }

    /**
     *
     */
    public function beginTransaction()
    {
        if ($this->getDescriptor()->supportsTransactions()) {
            return $this->wrap('beginTransaction');
        }
    }

    /**
     *
     */
    public function commit()
    {
        if ($this->getDescriptor()->supportsTransactions()) {
            return $this->wrap('commit');
        }
    }

    /**
     *
     */
    public function rollBack()
    {
        if ($this->getDescriptor()->supportsTransactions()) {
            return $this->wrap('rollBack');
        }
    }

    /**
     *
     */
    public function inTransaction()
    {
        if ($this->getDescriptor()->supportsTransactions()) {
            return $this->wrap('inTransaction');
        }

        return false;
    }

    /**
     *
     */
    public function exec()
    {
        return $this->wrap('exec', func_get_args());
    }

    /**
     *
     */
    public function lastInsertId()
    {
        return $this->wrap('lastInsertId', func_get_args());
    }

    /**
     *
     */
    public function prepare()
    {
        return $this->wrap('prepare', func_get_args());
    }

    /**
     *
     */
    public function quote()
    {
        return $this->wrap('quote', func_get_args());
    }

    /**
     * Wrap PDO methods into Emyi\Db\Connection
     *
     * @param string $method method to wrap
     * @param array $args arguments to pass into the wrapped method
     * @throws Emyi\Db\ConnectionException on failure
     */
    private function wrap($method, array $args = [])
    {
        try {
            return call_user_func_array("parent::$method", $args);
        } catch (PDOException $e) {
            throw new ConnectionException($e->getMessage(), (int) $e->getCode());
        }
    }

    /**
     * Set the Emyi\Db\Descriptor for this driver
     *
     * @return void
     * @internal
     */
    private function setDescriptor()
    {
        $hash = $this->getAttribute('hash');

        if (isset(self::$descriptors[$hash]))
            return true;

        /// This is really ugly, but since the Descriptor rely on the same
        /// directory as the Adapter class, this is the easiest way to
        /// achiev.
        /// I could have an abstract Connection::getDriverDescriptor()
        /// and return the instance, but I don't want to repeat this very
        /// same code for each driver.
        ///
        /// @todo Find a better way to achiev this
        $class = str_replace('\\Adapter', '\\Descriptor', get_called_class());
        self::$descriptors[$hash] = new $class($hash);
    }
}
