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

namespace Portal89\OracleDriver\Test\TestCase\Database\Schema;

use Portal89\OracleDriver\Database\Schema\MethodSchema;
use Portal89\OracleDriver\ORM\MethodRegistry;
use Portal89\OracleDriver\TestSuite\TestCase;

/**
 * Test case for Method
 */
class MethodTest extends TestCase
{
    public $codeFixtures = [
        'plugin.CakeDC/OracleDriver.Calc',
    ];

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        MethodRegistry::clear();
        parent::tearDown();
    }

    /**
     * Test construction with parameters
     *
     * @return void
     */
    public function testConstructWithParameters()
    {
        $parameters = [
            'a' => [
                'type' => 'float',
                'in' => true,
            ],
            'b' => [
                'type' => 'float',
                'in' => true,
            ],
        ];
        $method = new MethodSchema('CALC.SUM', $parameters);
        $this->assertEquals(['a', 'b'], $method->parameters());
    }
}
