<?php
declare(strict_types=1);

$findRoot = function () {
    $root = dirname(__DIR__);
    if (is_dir($root . '/vendor/cakephp/cakephp')) {
        return $root;
    }

    $root = dirname(dirname(__DIR__));
    if (is_dir($root . '/vendor/cakephp/cakephp')) {
        return $root;
    }

    $root = dirname(dirname(dirname(__DIR__)));
    if (is_dir($root . '/vendor/cakephp/cakephp')) {
        return $root;
    }
};

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

function _define($name, $value)
{
    if (!defined($name)) {
        define($name, $value);
    }
}

_define('ROOT', $findRoot());
_define('APP_DIR', 'App');
_define('WEBROOT_DIR', 'webroot');
_define('APP', ROOT . '/tests/App/');
_define('CONFIG', ROOT . '/tests/Config/');
_define('WWW_ROOT', ROOT . DS . WEBROOT_DIR . DS);
_define('TESTS', ROOT . DS . 'tests' . DS);
_define('TMP', ROOT . DS . 'tmp' . DS);
_define('LOGS', TMP . 'logs' . DS);
_define('CACHE', TMP . 'cache' . DS);
_define('CAKE_CORE_INCLUDE_PATH', ROOT . '/vendor/cakephp/cakephp');
_define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
_define('CAKE', CORE_PATH . 'src' . DS);
_define('CORE_TESTS', CORE_PATH . 'tests' . DS);
_define('CORE_TEST_CASES', CORE_TESTS . 'TestCase');
_define('TEST_APP', CORE_TESTS . 'test_app' . DS);

require_once ROOT . '/vendor/cakephp/cakephp/src/basics.php';
require_once ROOT . '/vendor/autoload.php';

//Cake\Core\Configure::write('App', ['namespace' => 'Portal89\\OracleDriver\\Test\\App']);
Cake\Core\Configure::write('App', [
    'namespace' => 'Portal89\\OracleDriver\\Test\\App',
    'encoding' => 'UTF-8',
    'base' => false,
    'baseUrl' => false,
    'dir' => 'src',
    'webroot' => WEBROOT_DIR,
    'wwwRoot' => WWW_ROOT,
    'fullBaseUrl' => 'http://localhost',
    'imageBaseUrl' => 'img/',
    'jsBaseUrl' => 'js/',
    'cssBaseUrl' => 'css/',
    'paths' => [
        'plugins' => [dirname(APP) . DS . 'plugins' . DS],
        // 'templates' => [TEST_APP . 'templates' . DS]
    ],
]);
Cake\Core\Configure::write('debug', true);
Cake\Core\Configure::write('Error.errorLevel', E_ALL & ~E_USER_DEPRECATED);

$TMP = new \Cake\Filesystem\Folder(TMP);
$TMP->create(TMP . 'cache/models', 0777);
$TMP->create(TMP . 'cache/persistent', 0777);
$TMP->create(TMP . 'cache/views', 0777);

$cache = [
    'default' => [
        'engine' => 'File',
    ],
    '_cake_core_' => [
        'className' => 'File',
        'prefix' => 'oracle_driver_cake_core_',
        'path' => CACHE . 'persistent/',
        'serialize' => true,
        'duration' => '+10 seconds',
    ],
    '_cake_model_' => [
        'className' => 'File',
        'prefix' => 'oracle_driver_cake_model_',
        'path' => CACHE . 'models/',
        'serialize' => 'File',
        'duration' => '+10 seconds',
    ],
    '_cake_method_' => [
        'className' => 'File',
        'prefix' => 'oracle_driver_cake_method_',
        'path' => CACHE . 'models/',
        'serialize' => 'File',
        'duration' => '+10 seconds',
    ],
];

Cake\Cache\Cache::setConfig($cache);
Cake\Core\Configure::write('Session', [
    'defaults' => 'php',
]);

// Cake\Core\Plugin::load('Portal89\\OracleDriver', [
    // 'path' => ROOT . DS,
    // 'autoload' => true
// ]);

// Cake\Routing\DispatcherFactory::add('Routing');
// Cake\Routing\DispatcherFactory::add('ControllerFactory');

// Ensure default test connection is defined
if (!getenv('db_dsn')) {
    putenv('db_dsn=sqlite:///:memory:');
}

Cake\Datasource\ConnectionManager::setConfig('test', [
    'url' => getenv('db_dsn'),
    'timezone' => 'UTC',
]);

// Cake\Core\Configure::write('App.paths.plugins', [
    // TEST_APP . 'Plugin' . DS,
// ]);

class_alias('Portal89\OracleDriver\Test\App\Controller\AppController', 'App\Controller\AppController');

$isCli = PHP_SAPI === 'cli';
if ($isCli) {
    (new Cake\Error\ConsoleErrorHandler(Cake\Core\Configure::read('Error')))->register();
} else {
    (new Cake\Error\ErrorHandler(Cake\Core\Configure::read('Error')))->register();
}
\Cake\Routing\Router::reload();
$application = new \Portal89\OracleDriver\Test\App\Application(CONFIG);
$application->bootstrap();
$application->pluginBootstrap();
