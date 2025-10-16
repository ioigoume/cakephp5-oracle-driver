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
namespace Ioigoume\OracleDriver\Database\OCI8;

use Cake\Core\Exception\CakeException;

class OCI8Exception extends CakeException
{
    /**
     * OCI Error builder.
     *
     * @param array $error Error information that includes error message and code.
     * @return \Ioigoume\OracleDriver\Database\OCI8\OCI8Exception
     */
    public static function fromErrorInfo($error)
    {
        return new self($error['message'], $error['code']);
    }
}
