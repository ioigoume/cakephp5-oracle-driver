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
namespace Ioigoume\OracleDriver\ORM\Exception;

use Cake\Core\Exception\Exception;

/**
 * Exception raised when an Request could not be found.
 *
 */
class MissingRequestException extends Exception
{
    protected $_messageTemplate = 'Request class %s could not be found.';
}
