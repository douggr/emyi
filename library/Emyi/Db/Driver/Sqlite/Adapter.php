<?php
/*
 * Emyi
 *
 * @link http://github.com/douggr/Emyi for the canonical source repository
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace Emyi\Db\Driver\Sqlite;

use Emyi\Db;
use Emyi\Db\Connection;

/**
 * Represents a connection between PHP and a sqlite database.
 */
class Adapter extends Connection
{
    /**
     * {@inheritdoc}
     */
	protected function getConnectionOptions($hash)
	{
	    $options = parent::getConnectionOptions($hash);

	    if ($this->getAttribute('persistent')) {
	        $options[static::ATTR_PERSISTENT] = true;
	    }

	    return $options;
    }

    /**
     * {@inheritdoc}
     */
    protected function buildDSN(array $params)
    {
        if (!isset($params['database'])) {
            throw new Db\Exception('Database file must be specified');
        }

        if (':memory:' === $params['database']) {
            return 'sqlite::memory:';
        } else {
            $dbfile = realpath($params['database']);

            if (!is_writable(dirname($dbfile))) {
                throw new Db\Exception('The directory ' . dirname($dbfile) . ' must be writeable by the server.');
            }

            return 'sqlite:' . $dbfile;
        }
    }
}
