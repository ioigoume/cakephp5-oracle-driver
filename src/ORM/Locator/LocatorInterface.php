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
namespace Ioigoume\OracleDriver\ORM\Locator;

use Portal89\OracleDriver\ORM\Method;

/**
 * Registries for Method objects should implement this interface.
 */
interface LocatorInterface
{
    /**
     * Stores a list of options to be used when instantiating an object
     * with a matching alias.
     *
     * @param string|null $alias Name of the alias
     * @param array|null $options list of options for the alias
     * @return array The config data.
     */
    public function config($alias = null, $options = null);

    /**
     * Get a method instance from the registry.
     *
     * @param string $alias The alias name you want to get.
     * @param array $options The options you want to build the method with.
     * @return \Portal89\OracleDriver\ORM\Method
     */
    public function get($alias, array $options = []);

    /**
     * Check to see if an instance exists in the registry.
     *
     * @param string $alias The alias to check for.
     * @return bool
     */
    public function exists($alias);

    /**
     * Set an instance.
     *
     * @param string $alias The alias to set.
     * @param \Portal89\OracleDriver\ORM\Method $object The method to set.
     * @return \Portal89\OracleDriver\ORM\Method
     */
    public function set($alias, Method $object);

    /**
     * Clears the registry of configuration and instances.
     *
     * @return void
     */
    public function clear();

    /**
     * Removes an instance from the registry.
     *
     * @param string $alias The alias to remove.
     * @return void
     */
    public function remove($alias);
}
