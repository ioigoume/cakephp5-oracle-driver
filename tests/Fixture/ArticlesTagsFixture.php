<?php
/**
 * Copyright 2024, Portal89 (https://portal89.com.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2024, Portal89 (https://portal89.com.br)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Portal89\OracleDriver\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Short description for class.
 *
 */
class ArticlesTagsFixture extends TestFixture
{
    /**
     * fields property
     *
     * @var array
     */
    public $fields = [
        'article_id' => ['type' => 'integer', 'null' => false],
        'tag_id' => ['type' => 'integer', 'null' => false],
        '_constraints' => [
            'unique_tag' => ['type' => 'primary', 'columns' => ['article_id', 'tag_id']],
            // 'tag_idx' => [
                // 'type' => 'foreign',
                // 'columns' => ['tag_id'],
                // 'references' => ['tags', 'id'],
               // 'update' => 'cascade',
                // 'delete' => 'cascade',
            // ],
        ],
    ];

    /**
     * records property
     *
     * @var array
     */
    public $records = [
        ['article_id' => 1, 'tag_id' => 1],
        ['article_id' => 1, 'tag_id' => 2],
        ['article_id' => 2, 'tag_id' => 1],
        ['article_id' => 2, 'tag_id' => 3],
    ];
}
