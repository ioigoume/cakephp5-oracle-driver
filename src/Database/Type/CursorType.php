<?php
declare(strict_types=1);

/**
 * Copyright 2024, Portal89 (https://portal89.com.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2024, Portal89 (https://portal89.com.br)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Ioigoume\OracleDriver\Database\Type;

use Cake\Database\Type\BaseType;
use Cake\Database\Driver;
use PDO;

/**
 * Provides behavior for the cursors type
 */
class CursorType extends BaseType
{
    protected ?string $_name = null;
    /**
     * Casts given value from a PHP type to one acceptable by database
     *
     * @param mixed $value value to be converted to database equivalent
     * @param \Cake\Database\DriverInterface $driver object from which database preferences and configuration will be extracted
     * @return mixed
     */
    public function toDatabase(mixed $value, Driver $driver): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value;
    }

    /**
     * Marshalls request data into a PHP string
     *
     * @param mixed $value The value to convert.
     * @return string|null Converted value.
     */
    public function marshal(mixed $value): mixed
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function toStatement(mixed $value, Driver $driver): int
    {
        return PDO::PARAM_STMT;
    }

    /**
     * @inheritDoc
     */
    public function toPHP(mixed $value, Driver $driver): mixed
    {
        return $value;
    }
}
