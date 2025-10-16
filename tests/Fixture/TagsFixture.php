<?php
/**
 * Copyright 2024, Ioigoume (https://Ioigoume.com.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2024, Ioigoume (https://Ioigoume.com.br)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Ioigoume\OracleDriver\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Class TagFixture
 *
 */
class TagsFixture extends TestFixture
{
    /**
     * fields property
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer', 'null' => false],
        'name' => ['type' => 'string', 'null' => false],
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
    ];

    /**
     * records property
     *
     * @var array
     */
    public $records = [
        ['name' => 'tag1'],
        ['name' => 'tag2'],
        ['name' => 'tag3'],
    ];
}
