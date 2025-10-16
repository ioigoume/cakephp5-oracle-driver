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

use PDO;

class OraclePDO extends OracleBase
{
    /**
     * @inheritdoc
     */
    protected function _connect(string $database, array $config): bool
    {
        $config['flags'] += [
            PDO::NULL_EMPTY_STRING => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => empty($config['persistent']) ? false : $config['persistent'],
            PDO::ATTR_ORACLE_NULLS => true,
        ];
        $database = 'oci:dbname=' . $database;

        return parent::_connect($database, $config);
    }

    /**
     * Returns whether php is able to use this driver for connecting to database
     *
     * @return bool true if it is valid to use this driver
     */
    public function enabled(): bool
    {
          return class_exists('PDO') && in_array('oci', \PDO::getAvailableDrivers(), true);
    }
}
