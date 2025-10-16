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

use Cake\Test\TestCase\ORM\MarshallerTest as CakeMarshallerTest;

/**
 * Tests Marshaller class
 *
 */
class MarshallerTest extends CakeMarshallerTest
{
    protected $fixtures = [
        'core.Articles',
//        'core.ArticlesTags',
        'plugin.CakeDC/OracleDriver.ArticlesTags',
        'core.Comments',
        'core.SpecialTags',
        'core.Tags',
        'core.Users',
    ];
}
