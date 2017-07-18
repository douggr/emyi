<?php
/*
 * Emyi
 *
 * @link http://github.com/douggr/Emyi for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace Emyi\Db;


/**
 * Object Representation of a database table
 * @protected
 */
class Table
{
    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var Emyi\Db\Column[]
     */
    protected $columns = [];

    /**
     * @var array
     */
    protected $indexes = [];

    /**
     * @var string
     */
    protected $primaryKeyName = false;

    /**
     * @var array
     */
    protected $foreignKeys = [];

    /**
     * @param string $name The table name
     * @param array $columns
     * @param array $indexes
     * @param array $foreignKeys
     * @throws Emyi\Db\Exception
     */
    public function __construct(
        $name,
        array $columns     = [],
        array $indexes     = [],
        array $foreignKeys = []
    ) {
    }
}
