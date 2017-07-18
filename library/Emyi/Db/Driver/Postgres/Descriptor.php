<?php
/*
 * Emyi
 *
 * @link http://github.com/douggr/Emyi for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace Emyi\Db\Driver\Postgres;

use Emyi\Db\Driver;

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
    public function getDateDiffExpression($date1, $date2)
    {
        return '(DATE(' . $date1 . ') - DATE(' . $date2 . '))';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateAddHourExpression($date, $hours)
    {
        return '(' . $date . ' + (' . $hours . ' || \' hour\')::interval)';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateSubHourExpression($date, $hours)
    {
        return '(' . $date . ' - (' . $hours . ' || \' hour\')::interval)'; 
    }

    /**
     * {@inheritDoc}
     */
    public function getDateAddDaysExpression($date, $days)
    {
        return '(' . $date . ' + (' . $days . ' || \' day\')::interval)';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateSubDaysExpression($date, $days)
    {
        return '(' . $date . ' - (' . $days . ' || \' day\')::interval)';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateAddMonthsExpression($date, $months)
    {
        return '(' . $date . ' + (' . $months . ' || \' month\')::interval)';
    }

    /**
     * {@inheritDoc}
     */
    public function getDateSubMonthsExpression($date, $months)
    {
        return '(' . $date . ' - (' . $months . ' || \' month\')::interval)';
    }

    /**
     * {@inheritDoc}
     */
    public function getListDatabasesSQL()
    {
        return 'SELECT datname FROM pg_database';
    }

    /**
     * {@inheritDoc}
     */
    public function getListTablesSQL()
    {
        return '
            SELECT
                tablename AS table_name,
                schemaname AS schema_name
            FROM
                pg_tables
            WHERE
                schemaname NOT LIKE \'pg_%\'
                AND
                schemaname != \'information_schema\'
                AND
                tablename != \'geometry_columns\'
                AND
                tablename != \'spatial_ref_sys\'';
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableColumnsSQL($table)
    {
        return '
            SELECT
                a.attnum,
                a.attname AS field,
                t.typname AS type,
                format_type(a.atttypid, a.atttypmod) AS complete_type,
                (
                    SELECT
                        t1.typname
                    FROM
                        pg_catalog.pg_type t1
                    WHERE
                        t1.oid = t.typbasetype
                ) AS domain_type,

                (
                    SELECT
                        format_type(t2.typbasetype, t2.typtypmod)
                    FROM
                        pg_catalog.pg_type t2
                    WHERE
                        t2.typtype = \'d\'
                        AND
                        t2.oid = a.atttypid
                ) AS domain_complete_type,

                a.attnotnull AS isnotnull,
                (
                    SELECT
                        \'t\'
                    FROM
                        pg_index
                    WHERE
                        c.oid = pg_index.indrelid
                        AND
                        pg_index.indkey[0] = a.attnum
                        AND
                        pg_index.indisprimary = \'t\'
                ) AS pri,

                (
                    SELECT
                        pg_attrdef.adsrc
                    FROM
                        pg_attrdef
                    WHERE
                        c.oid = pg_attrdef.adrelid
                        AND
                        pg_attrdef.adnum=a.attnum
                ) AS default,

                (
                    SELECT
                        pg_description.description
                    FROM
                        pg_description
                    WHERE
                        pg_description.objoid = c.oid
                        AND
                        a.attnum = pg_description.objsubid
                ) AS comment

            FROM
                pg_attribute a,
                pg_class c,
                pg_type t,
                pg_namespace n
            WHERE ' . $this->getTableWhereClause($table) . ' AND
                a.attnum > 0
                AND
                a.attrelid = c.oid
                AND
                a.atttypid = t.oid
                AND
                n.oid = c.relnamespace
            ORDER BY
                a.attnum';
    }

    /**
     * {@inheritDoc}
     */
    public function getListViewsSQL()
    {
        return 'SELECT viewname, definition FROM pg_views';
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableConstraintsSQL($table)
    {
        return '
            SELECT
                relname
            FROM
                pg_class
            WHERE
                oid IN (
                    SELECT
                        indexrelid
                    FROM
                        pg_index,
                        pg_class
                    WHERE
                        pg_class.relname = ' . $this->quote($table) . '
                        AND
                        pg_class.oid = pg_index.indrelid
                        AND (indisunique = \'t\' OR indisprimary = \'t\')
                )';
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableIndexesSQL($table)
    {
        return '
            SELECT
                relname,
                pg_index.indisunique,
                pg_index.indisprimary,
                pg_index.indkey,
                pg_index.indrelid
            FROM
                pg_class,
                pg_index
            WHERE
                oid IN (
                    SELECT
                        indexrelid
                    FROM
                        pg_index si,
                        pg_class sc,
                        pg_namespace sn
                    WHERE ' . $this->getTableWhereClause($table, 'sc', 'sn') . ' AND
                        sc.oid = si.indrelid
                        AND sc.relnamespace = sn.oid
                )
                AND
                pg_index.indexrelid = oid';
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableForeignKeysSQL($table)
    {
        return '
            SELECT
                r.conname,
                pg_catalog.pg_get_constraintdef(r.oid, true) as condef
            FROM
                pg_catalog.pg_constraint r
            WHERE
                r.conrelid = (
                    SELECT
                        c.oid
                    FROM
                        pg_catalog.pg_class c,
                        pg_catalog.pg_namespace n
                    WHERE ' . $this->getTableWhereClause($table) . ' AND
                        n.oid = c.relnamespace
                )
                AND r.contype = \'f\'';
    }

    /**
     * @param string $table
     * @param string $class
     * @param string $nsAlias
     * @return string
     */
    private function getTableWhereClause($table, $class = 'c', $nsAlias = 'n')
    {
        $where = $nsAlias . '.nspname NOT IN (\'pg_catalog\', \'information_schema\', \'pg_toast\') AND ';

        if (false !== strpos($table, '.')) {
            list($schema, $table) = explode('.', $table);
            $schema = $this->quote($schema);
        } else {
            $schema = 'ANY(string_to_array((SELECT replace(setting,\'"$user"\', user) FROM pg_catalog.pg_settings WHERE name = \'search_path\'), \', \'))';
        }

        return $where . $class . '.relname = ' . $this->quote($table) . ' AND ' . $nsAlias . '.nspname = ' . $schema;
    }

}
