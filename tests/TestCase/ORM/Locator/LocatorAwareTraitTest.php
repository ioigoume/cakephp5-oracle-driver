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

namespace Portal89\OracleDriver\Test\TestCase\ORM\Locator;

use Cake\TestSuite\TestCase;
use Portal89\OracleDriver\ORM\MethodRegistry;

/**
 * LocatorAwareTrait test case
 *
 */
class LocatorAwareTraitTest extends TestCase
{
    /**
     * setup
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->getObjectForTrait('Portal89\OracleDriver\ORM\Locator\LocatorAwareTrait');
    }

    /**
     * Tests methodLocator method
     *
     * @return void
     */
    public function testMethodLocator()
    {
        $methodLocator = $this->subject->methodLocator();
        $this->assertSame(MethodRegistry::locator(), $methodLocator);
        /*
        $newLocator = $this->getMock('Portal89\OracleDriver\ORM\Locator\LocatorInterface');
        $subjectLocator = $this->subject->methodLocator($newLocator);
        $this->assertSame($newLocator, $subjectLocator);
        */
    }
}
