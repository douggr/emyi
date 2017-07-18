<?php
/*
 * Emyi
 *
 * @link http://github.com/douggr/Emyi for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace Emyi\Db\Driver;

use Emyi\Db\ConnectionManager;

/**
 * Base class for driver description. The descriptors are the central
 * point of abstraction of driver-specific behaviors, features and SQL
 * dialects.
 *
 * @protected
 */
abstract class Descriptor
{
    /**
     * @var string
     * @readonly
     */
    protected $hash;

    /**
     * @param string Connection hash
     * @return Emyi\Db\Driver\Descriptor
     */
    public final function __construct($hash)
    {
        $this->hash = $hash;
    }

    /**
     * Returns the character used for identifier quoting.
     *
     * @return string
     */
    public function getQuoteIdentifierCharacter()
    {
        return '"';
    }

    /**
     * Quotes a string so that it can be safely used as a table or column
     * name, even if it is a reserved word of the driver. This also
     * detects identifier chains separated by dot and quotes them
     * independently.
     *
     * @param string $str The identifier name to be quoted.
     * @return string The quoted identifier string.
     */
    public function quoteIdentifier($str)
    {
        if (false !== strpos($str, '.')) {
            $parts  = array_map([$this, 'quoteIdentifier'], explode('.', $str));
            $quoted = implode('.', $parts);
        } else {
            $quoted = $this->quoteSingleIdentifier($str);
        }

        return $quoted;
    }

    /**
     * Gets the format string, as accepted by the date() function, that describes
     * the format of a stored datetime value of this driver.
     *
     * @return string The format string.
     */
    public function getDateTimeFormatString()
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * Gets the format string, as accepted by the date() function, that describes
     * the format of a stored datetime with timezone value of this driver.
     *
     * @return string The format string.
     */
    public function getDateTimeTzFormatString()
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * Gets the format string, as accepted by the date() function, that describes
     * the format of a stored date value of this driver.
     *
     * @return string The format string.
     */
    public function getDateFormatString()
    {
        return 'Y-m-d';
    }

    /**
     * Gets the format string, as accepted by the date() function, that describes
     * the format of a stored time value of this driver.
     *
     * @return string The format string.
     */
    public function getTimeFormatString()
    {
        return 'H:i:s';
    }

    /**
     * Returns the SQL to calculate the difference in days between the two
     * passed dates.
     *
     * Computes diff = date1 - date2.
     *
     * @param string $date1
     * @param string $date2
     * @return string
     * @throws Emyi\Db\Exception If not supported on the driver
     */
    public function getDateDiffExpression($date1, $date2)
    {
        throw Exception::unsupportedMethod(__METHOD__);
    }

    /**
     * Returns the SQL to add the number of given hours to a date.
     *
     * @param string $date
     * @param integer $hours
     * @return string
     * @throws Emyi\Db\Exception If not supported on the driver
     */
    public function getDateAddHourExpression($date, $hours)
    {
        throw Exception::unsupportedMethod(__METHOD__);
    }

    /**
     * Returns the SQL to subtract the number of given hours to a date.
     *
     * @param string $date
     * @param integer $hours
     * @return string
     * @throws Emyi\Db\Exception If not supported on the driver
     */
    public function getDateSubHourExpression($date, $hours)
    {
        throw Exception::unsupportedMethod(__METHOD__);
    }

    /**
     * Returns the SQL to add the number of given days to a date.
     *
     * @param string $date
     * @param integer $days
     * @return string
     * @throws Emyi\Db\Exception If not supported on the driver
     */
    public function getDateAddDaysExpression($date, $days)
    {
        throw Exception::unsupportedMethod(__METHOD__);
    }

    /**
     * Returns the SQL to subtract the number of given days to a date.
     *
     * @param string $date
     * @param integer $days
     * @return string
     * @throws Emyi\Db\Exception If not supported on the driver
     */
    public function getDateSubDaysExpression($date, $days)
    {
        throw Exception::unsupportedMethod(__METHOD__);
    }

    /**
     * Returns the SQL to add the number of given months to a date.
     *
     * @param string $date
     * @param integer $months
     * @return string
     * @throws Emyi\Db\Exception If not supported on the driver
     */
    public function getDateAddMonthsExpression($date, $months)
    {
        throw Exception::unsupportedMethod(__METHOD__);
    }

    /**
     * Returns the SQL to subtract the number of given months to a date.
     *
     * @param string $date
     * @param integer $months
     * @return string
     * @throws Emyi\Db\Exception If not supported on the driver
     */
    public function getDateSubMonthsExpression($date, $months)
    {
        throw Exception::unsupportedMethod(__METHOD__);
    }

    /**
     * Whether the driver supports sequences.
     *
     * @return boolean
     */
    public function supportsSequences()
    {
        return false;
    }

    /**
     * Whether the driver supports identity columns.
     *
     * Identity columns are columns that receive an auto-generated value
     * from the database on insert of a row.
     *
     * @return boolean
     */
    public function supportsIdentityColumns()
    {
        return false;
    }

    /**
     * Whether the driver supports indexes.
     *
     * @return boolean
     */
    public function supportsIndexes()
    {
        return true;
    }

    /**
     * Whether the driver supports transactions.
     *
     * @return boolean
     */
    public function supportsTransactions()
    {
        return true;
    }

    /**
     * Create an driver-specific LIMIT clause to the query.
     *
     * @param string $query
     * @param integer $limit
     * @param integer|null $offset
     * @return string
     */
    public function modifyLimitQuery($query, $limit, $offset = null)
    {
        $query .= ' LIMIT ' . (int) $limit;

        if ($offset !== null) {
            $query .= ' OFFSET ' . (int) $offset;
        }

        return $query;
    }

    /**
     * @return string
     * @throws Emyi\Db\Exception If not supported on the driver
     */
    public function getListDatabasesSQL()
    {
        throw Exception::unsupportedMethod(__METHOD__);
    }

    /**
     * @return string
     * @throws Emyi\Db\Exception If not supported on the driver
     */
    public function getListTablesSQL()
    {
        throw Exception::unsupportedMethod(__METHOD__);
    }

    /**
     * @param string $table
     * @return string
     * @throws Emyi\Db\Exception If not supported on the driver
     */
    public function getListTableColumnsSQL($table)
    {
        throw Exception::unsupportedMethod(__METHOD__);
    }

    /**
     * @return string
     * @throws Emyi\Db\Exception If not supported on the driver
     */
    public function getListViewsSQL()
    {
        throw Exception::unsupportedMethod(__METHOD__);
    }

    /**
     * @param string $table
     * @return string
     * @throws Emyi\Db\Exception If not supported on the driver
     */
    public function getListTableConstraintsSQL($table)
    {
        throw Exception::unsupportedMethod(__METHOD__);
    }

    /**
     * Returns the list of indexes for the current database.
     *
     * @param string $table
     * @return string
     * @throws Emyi\Db\Exception If not supported on the driver
     */
    public function getListTableIndexesSQL($table)
    {
        throw Exception::unsupportedMethod(__METHOD__);
    }

    /**
     * @param string $table
     * @return string
     * @throws Emyi\Db\Exception If not supported on the driver
     */
    public function getListTableForeignKeysSQL($table)
    {
        throw Exception::unsupportedMethod(__METHOD__);
    }

    /**
     * Gets the table columns definition.
     *
     * @param string $table
     * @return Emyi\Db\Column[]
     */
    //public abstract function getColumnDefinitions($table);

    /**
     * Returns the database types mapping. Specific descriptors should
     * append the unknown datatypes
     *
     * @return array
     */
    public function getDatabaseTypes()
    {
        return [
            'int'               => 'integer',
            'tinyint'           => 'integer',
            'smallint'          => 'integer',
            'mediumint'         => 'integer',
            'bigint'            => 'integer',
            'integer'           => 'integer',
            'serial'            => 'integer',

            'float'             => 'decimal',
            'double'            => 'decimal',
            'double precision'  => 'decimal',
            'real'              => 'decimal',
            'decimal'           => 'decimal',
            'numeric'           => 'decimal',

            'bool'              => 'boolean',
            'boolean'           => 'boolean',

            'text'              => 'string',
            'char'              => 'string',
            'varchar'           => 'string',
            'character varying' => 'string',

            'date'              => 'date',
            'time'              => 'date',
            'datetime'          => 'datetime',
            'timestamp'         => 'timestamp',

            'blob'              => 'string',
        ];
    }

    /**
     * Quotes a string for use in a query
     *
     * @param string
     * @return string
     */
    protected function quote($str)
    {
        return ConnectionManager::getConnection($this->hash)->quote($str);
    }

    /**
     * Quotes a single identifier (no dot chain separation).
     *
     * @param string $str The identifier name to be quoted.
     * @return string The quoted identifier string.
     */
    private function quoteSingleIdentifier($str)
    {
        $char = $this->getDescriptor()->getQuoteIdentifierCharacter();
        return $char . str_replace($char, $char . $char, $str) . $char;
    }
}
