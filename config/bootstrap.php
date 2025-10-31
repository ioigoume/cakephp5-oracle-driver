<?php
/**
 * Copyright 2015 - 2016, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2015 - 2016, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

use Cake\Database\TypeFactory;
use Ioigoume\OracleDriver\Database\Type\CursorType;
use Ioigoume\OracleDriver\Database\Type\BoolType;

TypeFactory::map('cursor', CursorType::class);
TypeFactory::map('boolean', BoolType::class);
