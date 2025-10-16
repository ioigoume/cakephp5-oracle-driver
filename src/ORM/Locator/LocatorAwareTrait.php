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

use Portal89\OracleDriver\ORM\MethodRegistry;

/**
 * Contains method for setting and accessing LocatorInterface instance
 */
trait LocatorAwareTrait
{
    /**
     * Method locator instance
     *
     * @var \Portal89\OracleDriver\ORM\Locator\LocatorInterface
     */
    protected $_methodLocator;

    /**
     * Sets the method locator.
     * If no parameters are passed, it will return the currently used locator.
     *
     * @param \Portal89\OracleDriver\ORM\Locator\LocatorInterface|null $methodLocator LocatorInterface instance.
     * @return \Portal89\OracleDriver\ORM\Locator\LocatorInterface
     */
    public function methodLocator(?LocatorInterface $methodLocator = null)
    {
        if ($methodLocator !== null) {
            $this->_methodLocator = $methodLocator;
        }
        if (!$this->_methodLocator) {
            $this->_methodLocator = MethodRegistry::locator();
        }

        return $this->_methodLocator;
    }
}
