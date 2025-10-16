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

namespace Ioigoume\OracleDriver\Panel;

use Cake\Datasource\ConnectionManager;
use Portal89\OracleDriver\Database\Log\DebugMethodLog;
use Portal89\OracleDriver\Database\OracleConnection;
use Portal89\OracleDriver\ORM\MethodRegistry;
use DebugKit\DebugPanel;

/**
 * Provides debug information on the Method logs and provides links to an ajax explain interface.
 *
 */
class MethodLogPanel extends DebugPanel
{
    public $plugin = 'OracleDriver';

    /**
     * Loggers connected
     *
     * @var array
     */
    protected $_loggers = [];

    /**
     * Initialize hook - configures logger.
     *
     * This will unfortunately build all the connections, but they
     * won't connect until used.
     *
     * @return void
     */
    public function initialize()
    {
        $configs = ConnectionManager::configured();
        foreach ($configs as $name) {
            $connection = ConnectionManager::get($name);
            if (!$connection instanceof OracleConnection) {
                continue;
            }
            if ($connection->configName() === 'debug_kit') {
                continue;
            }
            $logger = null;
            if ($connection->isQueryLoggingEnabled()) {
                $logger = $connection->methodLogger();
            }

            if ($logger instanceof DebugMethodLog) {
                continue;
            }
            $logger = new DebugMethodLog($logger, $name);

            $connection->enableQueryLogging(true);
            $connection->methodLogger($logger);
            $this->_loggers[] = $logger;
        }
    }

    /**
     * Get the data this panel wants to store.
     *
     * @return array
     */
    public function data()
    {
        return [
            'methods' => array_map(function ($method) {
                return $method->method();
            }, MethodRegistry::genericInstances()),
            'loggers' => $this->_loggers,
        ];
    }

    /**
     * Get summary data from the methods run.
     *
     * @return string
     */
    public function summary()
    {
        $count = $time = 0;
        foreach ($this->_loggers as $logger) {
            $count += count($logger->queries());
            $time += $logger->totalTime();
        }
        if (!$count) {
            return '0';
        }

        return "$count / $time ms";
    }
}
