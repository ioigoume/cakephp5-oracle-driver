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

use Portal89\OracleDriver\Database\Driver\OraclePDO;
use Portal89\OracleDriver\ORM\MethodRegistry;
use Portal89\OracleDriver\TestSuite\TestCase;

/**
 * Tests Method class
 *
 */
class MethodTest extends TestCase
{
    public $codeFixtures = ['plugin.CakeDC/OracleDriver.Calc'];

    /**
     * Method call test
     *
     * @return void
     */
    public function testMethodCall()
    {
        $method = MethodRegistry::get('CalcSum', ['method' => 'CALC.SUM']);

        $this->skipIf(
            $method->getConnection()->getDriver() instanceof OraclePDO,
            'OraclePDO does not support the requirements of this test.'
        );

        $request = $method->newRequest(['A' => 5, 'B' => 10]);
        $this->assertTrue($request->isNew());
        $this->assertTrue($method->execute($request));
        $this->assertFalse($request->isNew());
        $this->assertEquals($request[':result'], 15);
        $this->assertEquals($request->result(), 15);
    }

    /**
     * Output parameter method call test
     *
     * @return void
     */
    public function testOutParameterMethodCall()
    {
        $method = MethodRegistry::get('CalcTwice', ['method' => 'CALC.TWICE']);

        $this->skipIf(
            $method->getConnection()->getDriver() instanceof OraclePDO,
            'OraclePDO does not support the requirements of this test.'
        );

        $request = $method->newRequest(['A' => 5]);
        $this->assertTrue($request->isNew());
        $this->assertTrue($method->execute($request));
        $this->assertFalse($request->isNew());

        $this->assertEquals($request->get('B'), 10);
    }
}
