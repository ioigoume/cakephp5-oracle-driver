<?php
declare(strict_types=1);

/**
 * Copyright 2024, Ioigoume (https://Ioigoume.com.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2024, Ioigoume (https://Ioigoume.com.br)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Ioigoume\OracleDriver\Database\Driver;

use Ioigoume\OracleDriver\Database\OCI8\OCI8Connection;
use Ioigoume\OracleDriver\Database\Statement\Method\MethodOracleStatement;
use Ioigoume\OracleDriver\Database\Statement\Method\MethodPDOStatement;
use Cake\Database\DriverFeatureEnum;

class OracleOCI extends OracleBase
{

    /**
     * Starting quote character used for quoted identifiers
     *
     * @var string
     */
    protected string $_startQuote = '"';

    /**
     * Ending quote character used for quoted identifiers
     *
     * @var string
     */
    protected string $_endQuote = '"';

    /**
     * Whether a connection to the database is established
     *
     * @var bool
     */
    public $connected;

    /**
     * @inheritDoc
     */
    protected function _connect(string $dsn, array $config): bool
    {
        $config['flags'] += [
            'charset' => empty($config['encoding']) ? null : $config['encoding'],
            'persistent' => empty($config['persistent']) ? false : $config['persistent'],
        ];
        $connection = new OCI8Connection($dsn, $config['username'], $config['password'], $config['flags']);
        $this->_connection = $connection;

        return true;
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return function_exists('oci_connect');
    }

    /**
     * Prepares a PL/SQL statement to be executed.
     *
     * @param string $queryString The PL/SQL to convert into a prepared statement.
     * @param array $options Statement options.
     * @return \Cake\Database\StatementInterface
     */
    public function prepareMethod($queryString, $options = [])
    {
        $this->connect();
        $innerStatement = $this->_connection->prepare($queryString);
        $statement = new MethodPDOStatement($innerStatement, $this);
        if (!empty($options['bufferResult'])) {
            $statement = new MethodOracleStatement($statement, $this);
        }
        $statement->queryString = $queryString;

        return $statement;
    }

    /**
     * Checks if driver supports OCI layer
     *
     * @return bool True as this driver uses OCI8
     */
    public function isOci()
    {
        return true;
    }

    /**
     * Checks if this driver supports specific database feature
     *
     * @param \Cake\Database\DriverFeatureEnum $feature Feature to check support for
     * @return bool True if feature is supported
     */
    public function supports(DriverFeatureEnum $feature): bool
    {
        return true;
    }
}
