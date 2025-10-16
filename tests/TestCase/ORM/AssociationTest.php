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

namespace Ioigoume\OracleDriver\Test\TestCase\ORM;

use Cake\Test\TestCase\ORM\AssociationTest as CakeAssociationTest;

/**
 * Tests Association class
 *
 */
class AssociationTest extends CakeAssociationTest
{
    /**
     * Test that warning is shown if property name clashes with table field.
     *
     * @return void
     */
    public function testPropertyNameClash()
    {
        $this->markTestSkipped();
    }
}
