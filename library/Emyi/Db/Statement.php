<?php
/*
 * Emyi
 *
 * @link http://github.com/douggr/Emyi for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace Emyi\Db;

use PDOStatement;

/**
 * Represents a prepared statement and, after the statement is executed,
 * an associated result set.
 * 
 * @protected
 */
class Statement extends PDOStatement
{
    /**
     * @var string
     * @readonly
     */
    private $hash;

    /**
     * Private constructor.
     *
     * @param string Connection hash
     * @internal
     */
    private function __construct($hash)
    {
        $this->hash = $hash;
    }

    /**
     * Executes a prepared statement 
     */
    public function execute(array $data = [])
    {
        $conn = ConnectionManager::getConnection($this->hash);
        $bind = false;
        $desc = $conn->getDescriptor();

        foreach ($data as $index => &$param) {
            if (is_int($index)) {
                break;
            }

            if ($param === $desc->getNowExpressionSQL()     ||
                $param === $desc->getCurrentDateSQL()       ||
                $param === $desc->getCurrentTimeSQL()       ||
                $param === $desc->getCurrentTimestampSQL()
            ) {
                $this->bindParam($index, $param, Connection::PARAM_STR);
                continue;
            }

            switch (gettype($param)) {
                case 'boolean':
                    $this->bindParam($index, $param, Connection::PARAM_BOOL);
                    break;

                case 'integer':
                case 'double':
                case 'float':
                    $this->bindParam($index, $param, Connection::PARAM_INT);
                    break;

                case 'array':
                    break;

                case 'NULL':
                    $this->bindParam($index, $param, Connection::PARAM_NULL);
                    break;

                default:
                    $this->bindParam($index, $param, Connection::PARAM_STR);
            }

            $bind = true;
        }

        return $bind ? parent::execute() : parent::execute($data);
    }
}
