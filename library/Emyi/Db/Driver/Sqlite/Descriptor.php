<?php
/*
 * Emyi
 *
 * @link http://github.com/douggr/Emyi for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace Emyi\Db\Driver\Sqlite;

use Emyi\Db\Driver;

/**
 * Central point of abstraction of driver-specific behaviors, features and
 * SQL dialects for the SQLite driver.
 *
 * @protected
 */
class Descriptor extends Driver\Descriptor
{
    /**
     * {@inheritDoc}
     */
    public function getDateDiffExpression($date1, $date2)
    {
        return 'ROUND(JULIANDAY(' . $date1 . ')-JULIANDAY(' . $date2 . '))';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateAddHourExpression($date, $hours)
    {
        return 'DATETIME(' . $date . ',\'+\' || ' . $hours . ' || \' hour\')';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateSubHourExpression($date, $hours)
    {
        return 'DATETIME(' . $date . ',\'-\' || ' . $hours . ' || \' hour\')';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateAddDaysExpression($date, $days)
    {
        return 'DATE(' . $date . ',\'+\' || '. $days . ' || \' day\')';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateSubDaysExpression($date, $days)
    {
        return 'DATE(' . $date . ',\'-\' || '. $days . ' || \' day\')';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateAddMonthsExpression($date, $months)
    {
        return 'DATE(' . $date . ',\'+\' || '. $months . ' || \' month\')';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateSubMonthsExpression($date, $months)
    {
        return 'DATE(' . $date . ',\'-\' || '. $months . ' || \' month\')';
    }

    /**
     * {@inheritDoc}
     */
    public function getListTablesSQL()
    {
        return '
            SELECT
                name
            FROM
                sqlite_master
            WHERE
                type  = \'table\'
                AND
                name != \'sqlite_sequence\'
                AND
                name != \'geometry_columns\'
                AND
                name != \'spatial_ref_sys\'

            UNION ALL

            SELECT
                name
            FROM
                sqlite_temp_master
            WHERE
                type = \'table\'
            ORDER BY
                name';
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableColumnsSQL($table)
    {
        return 'PRAGMA table_info(' . $this->quoteTable($table) . ')';
    }

    /**
     * {@inheritDoc}
     */
    public function getListViewsSQL()
    {
        return '
            SELECT
                name,
                sql
            FROM
                sqlite_master
            WHERE
                type = \'view\'
                AND
                sql NOT NULL';
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableConstraintsSQL($table)
    {
        return '
            SELECT
                sql
            FROM
                sqlite_master
            WHERE
                type = \'index\'
                AND
                tbl_name = ' . $this->quoteTable($table) . '
                AND
                sql NOT NULL
            ORDER BY
                name';
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableIndexesSQL($table)
    {
        return 'PRAGMA index_list(' . $this->quoteTable($table) . ')';
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableForeignKeysSQL($table)
    {
        return 'PRAGMA foreign_key_list(' . $this->quoteTable($table) . ')';
    }

    /**
     * Quotes the table identifier.
     *
     * @param string $str The identifier name to be quoted.
     * @return string The quoted identifier string.
     */
    protected function quoteTable($str)
    {
        return parent::quote(str_replace('.', '__', $str));
    }
}
