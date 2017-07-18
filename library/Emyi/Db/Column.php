<?php
/*
 * Emyi
 *
 * @link http://github.com/douggr/Emyi for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace Emyi\Db;


/**
 * Object representation of a database column.
 */
class Column
{
    //PDO::PARAM_BOOL (integer)
    //  Represents a boolean data type. 

    //PDO::PARAM_NULL (integer)
    //  Represents the SQL NULL data type. 

    //PDO::PARAM_INT (integer)
    //  Represents the SQL INTEGER data type. 

    //PDO::PARAM_STR (integer)
    //  Represents the SQL CHAR, VARCHAR, or other string data type. 

    //PDO::PARAM_LOB (integer)
    //  Represents the SQL large object data type. 

    //PDO::PARAM_STMT (integer)
    //  Represents a recordset type. Not currently supported by any drivers. 

    //PDO::PARAM_INPUT_OUTPUT (integer) 
    //  Specifies that the parameter is an INOUT parameter for a stored
    //  procedure. You must bitwise-OR this value with an explicit
    //  PDO::PARAM_* data type.

    /**
     * Gets the (preferred) binding type for values of this type that
     * can be used when binding parameters to prepared statements.
     *
     * This method should return one of the PDO::PARAM_* constants, that is, one of:
     *  PDO::PARAM_BOOL
     *  PDO::PARAM_NULL
     *  PDO::PARAM_INT
     *  PDO::PARAM_STR
     *  PDO::PARAM_LOB
     *
     * @return integer
     */
    public function getBindingType()
    {
    }
}
