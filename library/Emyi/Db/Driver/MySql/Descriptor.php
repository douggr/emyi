<?php
/*
 * Emyi
 *
 * @link http://github.com/douggr/Emyi for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace Emyi\Db\Driver\MySql;

use Emyi\Db\Driver;
use Emyi\Db\ConnectionManager;

/**
 * Central point of abstraction of driver-specific behaviors, features and
 * SQL dialects for the Postgres driver.
 *
 * @protected
 */
class Descriptor extends Driver\Descriptor
{
    /**
     * {@inheritDoc}
     */
    public function getQuoteIdentifierCharacter()
    {
        return '`';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateDiffExpression($date1, $date2)
    {
        return 'DATEDIFF(' . $date1 . ', ' . $date2 . ')';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateAddHourExpression($date, $hours)
    {
        return 'DATE_ADD(' . $date . ', INTERVAL ' . $hours . ' HOUR)';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateSubHourExpression($date, $hours)
    {
        return 'DATE_SUB(' . $date . ', INTERVAL ' . $hours . ' HOUR)';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateAddDaysExpression($date, $days)
    {
        return 'DATE_ADD(' . $date . ', INTERVAL ' . $days . ' DAY)';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateSubDaysExpression($date, $days)
    {
        return 'DATE_SUB(' . $date . ', INTERVAL ' . $days . ' DAY)';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateAddMonthsExpression($date, $months)
    {
        return 'DATE_ADD(' . $date . ', INTERVAL ' . $months . ' MONTH)';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateSubMonthsExpression($date, $months)
    {
        return 'DATE_SUB(' . $date . ', INTERVAL ' . $months . ' MONTH)';
    }

    /**
     * {@inheritDoc}
     */
    public function modifyLimitQuery($query, $limit, $offset = null)
    {
        return parent::modifyLimitQuery($query, $limit, null === $offset ? 0 : $offset);
    }

    /**
     * {@inheritDoc}
     */
    public function getListDatabasesSQL()
    {
        return 'SHOW DATABASES';
    }

    /**
     * {@inheritDoc}
     */
    public function getListTablesSQL()
    {
        return 'SHOW FULL TABLES WHERE table_type = \'BASE TABLE\'';
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableColumnsSQL($table)
    {
        return 'DESCRIBE ' . $table;
    }

    /**
     * {@inheritDoc}
     */
    public function getListViewsSQL()
    {
        $database = ConnectionManager::getConnection($this->hash)->getDatabase();

        return '
            SELECT
                *
            FROM
                information_schema.views
            WHERE
                table_schema = ' . $this->quote($database);
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableConstraintsSQL($table)
    {
        return 'SHOW INDEX FROM ' . $table;
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableIndexesSQL($table)
    {
        return 'SHOW INDEX FROM ' . $table;
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableForeignKeysSQL($table)
    {
        $table = $this->quote($table);

        // do NOT remove the comments on this SQL
        return '
            SELECT DISTINCT
                k.constraint_name,
                k.column_name,
                k.referenced_table_name,
                k.referenced_column_name /*!50116 , c.update_rule, c.delete_rule */
            FROM
                information_schema.key_column_usage k /*!50116
            INNER JOIN
                information_schema.referential_constraints c
                    ON c.constraint_name = k.constraint_name
                    AND
                    c.table_name = '. $table . ' */
            WHERE
                k.table_name = ' . $table . '
                AND
                k.referenced_column_name IS NOT NULL';
    }
}
