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
namespace Ioigoume\OracleDriver\Database\Exception;

use Cake\Core\Exception\Exception;

class UnallowedDataTypeException extends Exception
{
    /**
     * {@inheritDoc}
     */
    protected $_messageTemplate = 'Column type %s not supported.';
}
