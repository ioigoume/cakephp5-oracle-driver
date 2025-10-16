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
namespace Ioigoume\OracleDriver\ORM;

class Request implements RequestInterface
{
    use RequestTrait;

    /**
     * Initializes the internal properties of this request out of the
     * keys in an array.
     *
     * @param array $properties hash of properties to set in this entity
     * @param array $options list of options to use when creating this entity
     */
    public function __construct(array $properties = [], array $options = [])
    {
        $options += [
            'useSetters' => true,
            'markNew' => null,
            'repository' => null,
        ];
        $this->_className = static::class;

        if ($options['markNew'] !== null) {
            $this->isNew($options['markNew']);
        }

        if (!empty($properties)) {
            $this->set($properties, [
                'setter' => $options['useSetters'],
            ]);
        }

        if ($options['repository'] !== null) {
            $this->_repository = $options['repository'];
            $this->_driver = $this->_repository->getConnection()->getDriver();
            $this->applySchema($this->_repository->getSchema());
        }
    }
}
