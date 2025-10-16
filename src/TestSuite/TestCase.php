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
namespace Ioigoume\OracleDriver\TestSuite;

use Cake\TestSuite\TestCase as CakeTestCase;
use Exception;

/**
 * CakeDC Oracle TestCase class
 *
 */
abstract class TestCase extends CakeTestCase
{
    /**
     * The class responsible for managing the creation, loading and removing of fixtures
     *
     * @var \Ioigoume\OracleDriver\TestSuite\Fixture\OracleFixtureManager
     */
    public $methodFixtureManager = null;

    /**
     * Chooses which fixtures to load for a given test
     *
     * Each parameter is a code module name that corresponds to a fixture, i.e. package, procedure or function name.
     *
     * @return void
     * @throws \Exception when no fixture manager is available.
     */
    public function loadMethodFixtures()
    {
        if (empty($this->methodFixtureManager)) {
            throw new Exception('No fixture manager to load the test fixture');
        }
        $args = func_get_args();
        foreach ($args as $class) {
            $this->methodFixtureManager->loadSingle($class, null, $this->dropTables);
        }
    }
}
