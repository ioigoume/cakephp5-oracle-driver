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
namespace Ioigoume\OracleDriver\Database\Statement;

use Portal89\OracleDriver\Database\OCI8\OCI8Statement as Statement;

class Oci8Statement extends Statement
{
    /**
     * {@inheritDoc}
     */
    public function closeCursor()
    {
        $this->_sth->closeCursor();

        return empty($this->_sth);
    }

    /**
     * {@inheritDoc}
     */
    public function __destruct()
    {
        if (is_resource($this->_sth)) {
            oci_free_statement($this->_sth);
        }
    }
}
