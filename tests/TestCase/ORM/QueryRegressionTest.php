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

use Cake\Database\Expression\IdentifierExpression;
use Cake\Database\Expression\QueryExpression;
use Cake\I18n\Time;
use Cake\ORM\Query;
use Cake\Test\TestCase\ORM\QueryRegressionTest as CakeQueryRegressionTest;

/**
 * Tests QueryRegression class
 *
 */
class QueryRegressionTest extends CakeQueryRegressionTest
{
    /**
     * Fixture to be used
     *
     * @var array
     */
    protected $fixtures = [
        'core.Articles',
        'core.Tags',
        'plugin.CakeDC/OracleDriver.ArticlesTags',
        'core.Authors',
        'core.AuthorsTags',
        'core.Comments',
        'core.FeaturedTags',
        'core.SpecialTags',
        'core.TagsTranslations',
        'core.Translates',
        'core.Users',
    ];

    /**
     * Test expression based ordering with unions.
     *
     * @return void
     */
    public function testComplexOrderWithUnion()
    {
        $this->loadFixtures('Comments');
        $table = $this->getTableLocator()->get('Comments');
            $query = $table->find();
        $inner = $table->find()
           ->select(['content' => 'to_char(comment)'])
           ->where(['id >' => 3]);
        $inner2 = $table->find()
            ->select(['content' => 'to_char(comment)'])
            ->where(['id <' => 3]);

        $order = $query->func()
               ->concat(['content' => 'identifier', 'test']);

        $query->select(['inside.content'])
              ->from(['inside' => $inner->unionAll($inner2)])
              ->orderAsc($order);

        $results = $query->toArray();
        $this->assertCount(5, $results);
    }

    /**
     * Test that save() works with entities containing expressions
     * as properties.
     *
     * @return void
     */
    public function testSaveWithExpressionProperty()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $article = $articles->newEntity([]);
        $article->title = new \Cake\Database\Expression\QueryExpression("SELECT 'jose' from DUAL");
        $this->assertSame($article, $articles->save($article));
    }

    /**
     * such syntax is not supported and leads to ORA-00937
     */
    public function testSubqueryInSelectExpression()
    {
        $this->markTestSkipped();
    }

    /**
     * Tests that subqueries can be used with function expressions.
     *
     * @return void
     */
    public function testFunctionExpressionWithSubquery()
    {
        $this->loadFixtures('Articles');
        $table = $this->getTableLocator()->get('Articles');

        $query = $table
            ->find()
            ->select(function (Query $q) use ($table) {
                return [
                    'value' => $q
                        ->func()
                        ->ABS([
                            $table
                                ->getConnection()
                                ->newQuery()
                                ->select(-1)
                                ->from('DUAL'),
                        ])
                        ->setReturnType('integer'),
                ];
            });

        $result = $query->first()->get('value');
        $this->assertEquals(1, $result);
    }

    /**
     * Tests that subqueries can be used with multi argument function expressions.
     *
     * @return void
     */
    public function testMultiArgumentFunctionExpressionWithSubquery()
    {
        $this->loadFixtures('Articles', 'Authors');
        $table = $this->getTableLocator()->get('Articles');

        $query = $table
            ->find()
            ->select(function (Query $q) use ($table) {
                return [
                    'value' => $q
                        ->func()
                        ->ROUND(
                            [
                                $table
                                    ->getConnection()
                                    ->newQuery()
                                    ->select(1.23456)
                                    ->from('DUAL'),
                                2,
                            ],
                            [null, 'integer']
                        )
                        ->setReturnType('float'),
                ];
            });

        $result = $query->first()->get('value');
        $this->assertEquals(1.23, $result);
    }

    /**
     * We can use only union all with queries with clob fields
     *
     * @see https://asktom.oracle.com/pls/apex/f?p=100:11:0::::P11_QUESTION_ID:498299691850
     * @return void
     */
    public function testCountWithUnionQuery()
    {
        $this->loadFixtures('Articles');
        $table = $this->getTableLocator()->get('Articles');
        $query = $table->find()
                       ->where(['id' => 1]);
        $query2 = $table->find()
                        ->where(['id' => 2]);
        $query->unionAll($query2);
        $this->assertEquals(2, $query->count());

        $fields = [
            'id',
            'author_id',
            'title',
            'body' => 'to_char(body)',
            'published',
        ];
        $query = $table->find()
                       ->select($fields)
                       ->where(['id' => 1]);
        $query2 = $table->find()
                        ->select($fields)
                        ->where(['id' => 2]);
        $query->union($query2);
        $this->assertEquals(2, $query->count());
    }

    /**
     * Tests that EagerLoader does not try to create queries for associations having no
     * keys to compare against
     *
     * @return void
     */
    public function testEagerLoadingFromEmptyResults()
    {
        $this->loadFixtures('Articles', 'Tags', 'ArticlesTags');
        $table = $this->getTableLocator()->get('Articles');
        $table->belongsToMany('ArticlesTags');
        $results = $table->find()->where(['id >' => 100])->contain('ArticlesTags')->toArray();
        $this->assertEmpty($results);
    }

    /**
     * Tests that getting the count of a query with bind is correct
     *
     * @see https://github.com/cakephp/cakephp/issues/8466
     * @return void
     */
    public function testCountWithBind()
    {
        $this->loadFixtures('Articles');
        $table = $this->getTableLocator()->get('Articles');
        $query = $table->find();
        $query->select(['title', 'id'])
            ->where($query->newExpr()->like(new IdentifierExpression('title'), ':val'))
            ->group(['id', 'title'])
            ->bind(':c0', '%Second%');
        $count = $query->count();
        $this->assertEquals(1, $count);
    }

    /**
     * Tests that bind in subqueries works.
     *
     * @return void
     */
    public function testSubqueryBind()
    {
        $this->loadFixtures('Articles');
        $table = $this->getTableLocator()->get('Articles');
        $sub = $table->find();
        $sub->select(['id'])
            ->where($sub->newExpr()->like(new IdentifierExpression('title'), ':val'))
            ->bind(':c0', 'Second %');

        $query = $table
            ->find()
            ->select(['title'])
            ->where(['id NOT IN' => $sub]);
        $result = $query->toArray();
        $this->assertCount(2, $result);
        $this->assertSame('First Article', $result[0]->title);
        $this->assertSame('Third Article', $result[1]->title);
    }

    /**
     * Test selecting with aliased aggregates and identifier quoting
     * does not emit notice errors.
     *
     * @see https://github.com/cakephp/cakephp/issues/12766
     * @return void
     */
    public function testAliasedAggregateFieldTypeConversionSafe()
    {
        $this->loadFixtures('Articles');
        $articles = $this->getTableLocator()->get('Articles');

        $driver = $articles->getConnection()->getDriver();
        $restore = $driver->isAutoQuotingEnabled();

        $driver->enableAutoQuoting(true);
        $query = $articles->find();
        $query->select([
            'sumUsers' => $articles->find()->func()->sum(new IdentifierExpression('author_id')),
        ]);
        $driver->enableAutoQuoting($restore);

        $result = $query->execute()->fetchAll('assoc');
        $this->assertArrayHasKey('sumUsers', $result[0]);
    }

    /**
     * Test that the typemaps used in function expressions
     * create the correct results.
     *
     * @return void
     */
    public function testTypemapInFunctions2()
    {
        $this->loadFixtures('Comments');
        $table = $this->getTableLocator()->get('Comments');
        $query = $table->find();
        $query->select([
            'max' => $query->func()->max(new IdentifierExpression('created'), ['datetime']),
        ]);
        $result = $query->all()->first();
        $this->assertEquals(new Time('2007-03-18 10:55:23'), $result['max']);
    }

    /**
     * Tests that correlated subqueries can be used with function expressions.
     *
     * @return void
     */
    public function testFunctionExpressionWithCorrelatedSubquery()
    {
        $this->loadFixtures('Articles', 'Authors');
        $table = $this->getTableLocator()->get('Articles');
        $table->belongsTo('Authors');

        $query = $table
            ->find()
            ->select(function (Query $q) use ($table) {
                return [
                    'value' => $q->func()->UPPER([
                        $table
                            ->getAssociation('Authors')
                            ->find()
                            ->select(['Authors.name'])
                            ->where(function (QueryExpression $exp) {
                                return $exp->equalFields('Authors.id', 'Articles.author_id');
                            }),
                    ]),
                ];
            });

        $result = $query->first()->get('value');
        $this->assertEquals('MARIANO', $result);
    }
}
