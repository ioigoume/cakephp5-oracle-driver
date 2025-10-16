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

namespace Portal89\OracleDriver\Test\TestCase\ORM;

use Cake\TestSuite\TestCase;
use Portal89\OracleDriver\ORM\Locator\LocatorInterface;
use Portal89\OracleDriver\ORM\Locator\MethodLocator;
use Portal89\OracleDriver\ORM\Method;
use Portal89\OracleDriver\ORM\MethodRegistry;

/**
 * Test case for MethodRegistry
 */
class MethodRegistryTest extends TestCase
{
    /**
     * Original MethodLocator.
     *
     * @var \Portal89\OracleDriver\ORM\Locator\LocatorInterface
     */
    protected $_originalLocator;

    /**
     * Remember original instance to set it back on tearDown() just to make sure
     * other tests are not broken.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->_originalLocator = MethodRegistry::locator();
    }

    /**
     * tear down
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        MethodRegistry::locator($this->_originalLocator);
    }

    /**
     * Sets and returns mock LocatorInterface instance.
     *
     * @return \Portal89\OracleDriver\ORM\Locator\LocatorInterface
     */
    protected function _setMockLocator()
    {
        $locator = $this->getMockBuilder(LocatorInterface::class)->getMock();
        MethodRegistry::locator($locator);

        return $locator;
    }

    /**
     * Test locator() method.
     *
     * @return void
     */
    public function testLocator()
    {
        $this->assertInstanceOf(LocatorInterface::class, MethodRegistry::locator());

        $locator = $this->_setMockLocator();

        $this->assertSame($locator, MethodRegistry::locator());
    }

    /**
     * Test that locator() method is returing MethodLocator by default.
     *
     * @return void
     */
    public function testLocatorDefault()
    {
        $locator = MethodRegistry::locator();
        $this->assertInstanceOf(MethodLocator::class, $locator);
    }

    /**
     * Test config() method.
     *
     * @return void
     */
    public function testConfig()
    {
        $locator = $this->_setMockLocator();
        $locator->expects($this->once())->method('config')->with('Test', []);

        MethodRegistry::config('Test', []);
    }

    /**
     * Test the get() method.
     *
     * @return void
     */
    public function testGet()
    {
        $locator = $this->_setMockLocator();
        $locator->expects($this->once())->method('get')->with('Test', []);

        MethodRegistry::get('Test', []);
    }

    /**
     * Test the get() method.
     *
     * @return void
     */
    public function testSet()
    {
        $method = $this->getMockBuilder(Method::class)->getMock();

        $locator = $this->_setMockLocator();
        $locator->expects($this->once())->method('set')->with('Test', $method);

        MethodRegistry::set('Test', $method);
    }

    /**
     * Test the remove() method.
     *
     * @return void
     */
    public function testRemove()
    {
        $locator = $this->_setMockLocator();
        $locator->expects($this->once())->method('remove')->with('Test');

        MethodRegistry::remove('Test');
    }

    /**
     * Test the clear() method.
     *
     * @return void
     */
    public function testClear()
    {
        $locator = $this->_setMockLocator();
        $locator->expects($this->once())->method('clear');

        MethodRegistry::clear();
    }
}
