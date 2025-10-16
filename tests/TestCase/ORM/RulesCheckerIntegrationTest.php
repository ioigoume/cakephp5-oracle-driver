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

use Cake\Test\TestCase\ORM\RulesCheckerIntegrationTest as CakeRulesCheckerIntegrationTest;

/**
 * Tests RulesCheckerIntegration class
 *
 */
class RulesCheckerIntegrationTest extends CakeRulesCheckerIntegrationTest
{
    /**
     * Fixtures to be loaded
     *
     * @var array
     */
    protected $fixtures = [
        'core.Articles',
//        'core.ArticlesTags',
        'plugin.CakeDC/OracleDriver.ArticlesTags',
        'core.Authors',
        'core.Comments',
        'core.Tags',
        'core.SpecialTags',
        'core.Categories',
        'core.SiteArticles',
        'core.SiteAuthors',
        'core.Comments',
    ];
}
