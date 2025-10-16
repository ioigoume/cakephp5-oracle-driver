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
namespace Ioigoume\OracleDriver\ORM\Exception;

use Cake\Core\Exception\Exception;

/**
 * Exception raised when a Table could not be found.
 *
 */
class MissingMethodClassException extends Exception
{
    protected $_messageTemplate = 'Method class %s could not be found.';
}
