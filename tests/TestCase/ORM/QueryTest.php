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

use Cake\Database\Driver\Sqlite;
use Cake\Database\Expression\IdentifierExpression;
use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query;
use Cake\Test\TestCase\ORM\QueryTest as CakeQueryTest;

/**
 * Tests Query class
 *
 */
class QueryTest extends CakeQueryTest
{
    /**
     * Fixture to be used
     *
     * @var array
     */
    public $fixtures = [
        'core.Articles',
        'core.Tags',
        //'core.ArticlesTags',
        'plugin.CakeDC/OracleDriver.ArticlesTags',
        'core.Authors',
        'core.Comments',
        'core.Posts',
        'core.Datatypes',
    ];

    /**
     * Test that addFields() works in the basic case.
     *
     * @return void
     */
    public function testAutoFields()
    {
        $table = $this->getTableLocator()->get('Articles');
        $result = $table->find('all')
            ->select(['myField' => '(SELECT 20 FROM DUAL)'])
            ->enableAutoFields(true)
            ->enableHydration(false)
            ->first();

        $this->assertArrayHasKey('myField', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('title', $result);
    }

    /**
     * Test autoFields with auto fields.
     *
     * @return void
     */
    public function testAutoFieldsWithAssociations()
    {
        $table = $this->getTableLocator()->get('Articles');
        $table->belongsTo('Authors');

        $result = $table->find()
            ->select(['myField' => '(SELECT 2 + 2 FROM DUAL)'])
            ->enableAutoFields(true)
            ->enableHydration(false)
            ->contain('Authors')
            ->first();

        $this->assertArrayHasKey('myField', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('author', $result);
        $this->assertNotNull($result['author']);
        $this->assertArrayHasKey('name', $result['author']);
    }

    /**
     * Test autoFields in contain query builder
     *
     * @return void
     */
    public function testAutoFieldsWithContainQueryBuilder()
    {
        $table = $this->getTableLocator()->get('Articles');
        $table->belongsTo('Authors');

        $result = $table->find()
            ->select(['myField' => '(SELECT 2 + 2 FROM DUAL)'])
            ->enableAutoFields(true)
            ->enableHydration(false)
            ->contain([
                'Authors' => function ($q) {
                    return $q->select(['compute' => '(SELECT 2 + 20 FROM DUAL)'])
                             ->enableAutoFields(true);
                },
            ])
            ->first();

        $this->assertArrayHasKey('myField', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('author', $result);
        $this->assertNotNull($result['author']);
        $this->assertArrayHasKey('name', $result['author']);
        $this->assertArrayHasKey('compute', $result);
    }

    /**
     * Test that count() returns correct results with group by.
     *
     * @return void
     */
    public function testCountWithGroup()
    {
        $table = $this->getTableLocator()->get('articles');
        $query = $table->find('all');
        $query
            ->select(['author_id',
                's' => $query->func()->sum(new IdentifierExpression('id')),
           ])
          ->group(['author_id']);
        $result = $query->count();
        $this->assertEquals(2, $result);
    }

    /**
     * Tests that it is possible to nest a notMatching call inside another
     * eagerloader function.
     *
     * @return void
     */
    public function testNotMatchingNested()
    {
        $table = $this->getTableLocator()->get('authors');
        $articles = $table->hasMany('articles');
        $articles->belongsToMany('tags');

        $results = $table->find()
             ->enableHydration(false)
             ->matching('articles', function ($q) {
                 return $q->notMatching('tags', function ($q) {
                    return $q->where(function ($exp) {
                        $e = new QueryExpression();

                        return $exp->add($e->eq(new IdentifierExpression('tags.name'), 'tag3'));
                    });
                 });
             })
             ->order(['authors.id' => 'ASC', 'articles.id' => 'ASC']);

        $expected = [
            'id' => 1,
            'name' => 'mariano',
            '_matchingData' => [
                'articles' => [
                    'id' => 1,
                    'author_id' => 1,
                    'title' => 'First Article',
                    'body' => 'First Article Body',
                    'published' => 'Y',
                ],
            ],
        ];

        $this->assertEquals($expected, $results->first());
    }

    /**
     * Tests that leftJoinWith() creates a left join with a given association and
     * that no fields from such association are loaded.
     *
     * @return void
     */
    public function testLeftJoinWith()
    {
        $table = $this->getTableLocator()->get('authors');
        $table->hasMany('articles');
        $table->articles->deleteAll(['author_id' => 4]);
        $orderFn = function ($q) {
            return $q->order(['id']);
        };
        $results = $table->find()
             ->select(['total_articles' => 'count(articles.id)'])
             ->enableAutoFields(true)
             ->leftJoinWith('articles', $orderFn)
             ->group(['authors.id', 'authors.name']);

        $expected = [
            1 => 2,
            2 => 0,
            3 => 1,
            4 => 0,
        ];
        $this->assertEquals($expected, $results->combine('id', 'total_articles')
                                               ->toArray());
        $fields = ['total_articles', 'id', 'name'];
        $this->assertEquals($fields, array_keys($results->first()->toArray()));

        $results = $table->find()
             ->leftJoinWith('articles', $orderFn)
             ->where(['articles.id IS' => null]);

        $this->assertEquals([2, 4], $results->sortBy('id', SORT_ASC)->extract('id')
                                            ->toList());
        $this->assertEquals(['id', 'name'], array_keys($results->first()
                                                               ->toArray()));

        $results = $table
            ->find()
            ->leftJoinWith('articles', $orderFn)
            ->where(['articles.id IS NOT' => null])
            ->order(['authors.id']);

        $this->assertEquals([1, 1, 3], $results->sortBy('id', SORT_ASC)->extract('id')
                                               ->toList());
        $this->assertEquals(['id', 'name'], array_keys($results->first()
                                                               ->toArray()));
    }

    /**
     * Tests that it is possible to bind arguments to a query and it will return the right
     * results
     *
     * @return void
     */
    public function testCustomBindings()
    {
        $table = $this->getTableLocator()->get('Articles');
        $query = $table->find()->where(['id >' => 1]);
        $query->where(function ($exp) {
            $e = new QueryExpression();

            return $exp->add($e->eq(new IdentifierExpression('author_id'), new IdentifierExpression(':author')));
        });
        $query->bind(':author', 1, 'integer');
        $this->assertEquals(1, $query->count());
        $this->assertEquals(3, $query->first()->id);
    }

    /**
     * Test disabled because on oracle DISTINCT ON syntax does not supported.
     *
     * @return void
     */
    public function testNotMatchingDeep()
    {
        $this->markTestSkipped('Oracle does not support DISTINCT ON');
    }

    /**
     * Tests that leftJoinWith() can be used with select()
     *
     * @return void
     */
    public function testLeftJoinWithSelect()
    {
        $this->markTestSkipped('Oracle does not supported');
    }

    /**
     * Tests that HasMany associations are correctly eager loaded and results
     * correctly nested when no hydration is used
     * Also that the query object passes the correct parent model keys to the
     * association objects in order to perform eager loading with select strategy
     *
     * @dataProvider strategiesProviderHasMany
     * @return void
     */
    public function testHasManyEagerLoadingNoHydration($strategy)
    {
        $table = $this->getTableLocator()->get('authors');
        $this->getTableLocator()->get('articles');
        $table->hasMany('articles', [
            'propertyName' => 'articles',
            'strategy' => $strategy,
            'sort' => ['articles.id' => 'asc'],
        ]);
        $query = new Query($this->connection, $table);

        $results = $query->select()
            ->contain('articles')
            ->enableHydration(false)
            ->toArray();
        $expected = [
            [
                'id' => 1,
                'name' => 'mariano',
                'articles' => [
                    [
                        'id' => 1,
                        'title' => 'First Article',
                        'body' => 'First Article Body',
                        'author_id' => 1,
                        'published' => 'Y',
                    ],
                    [
                        'id' => 3,
                        'title' => 'Third Article',
                        'body' => 'Third Article Body',
                        'author_id' => 1,
                        'published' => 'Y',
                    ],
                ],
            ],
            [
                'id' => 2,
                'name' => 'nate',
                'articles' => [],
            ],
            [
                'id' => 3,
                'name' => 'larry',
                'articles' => [
                    [
                        'id' => 2,
                        'title' => 'Second Article',
                        'body' => 'Second Article Body',
                        'author_id' => 3,
                        'published' => 'Y',
                    ],
                ],
            ],
            [
                'id' => 4,
                'name' => 'garrett',
                'articles' => [],
            ],
        ];
        $this->assertEquals($expected, $results);

        $results = $query->repository($table)
            ->select()
            ->contain(['articles' => ['conditions' => ['articles.id' => 2]]])
            ->enableHydration(false)
            ->toArray();
        $expected[0]['articles'] = [];
        $this->assertEquals($expected, $results);
        $this->assertEquals($table->getAssociation('articles')->getStrategy(), $strategy);
    }

    /**
     * Tests that it is possible to find large numeric values.
     *
     * @return void
     */
    public function testSelectLargeNumbers()
    {
        // Sqlite only supports maximum 16 digits for decimals.
        $this->skipIf($this->connection->getDriver() instanceof Sqlite);

        $this->loadFixtures('Datatypes');

        $big = '1234567890123456789.2';
        $table = $this->getTableLocator()->get('Datatypes');
        $entity = $table->newEntity([]);
        $entity->cost = $big;
        $entity->tiny = 1;
        $entity->small = 10;

        $table->save($entity);
        $out = $table->find()
            ->where([
                'cost' => $big,
            ])
            ->first();
        $this->assertNotEmpty($out, 'Should get a record');
        $this->assertSame($big, $out->cost);

        $small = '0.1234567890123456789';
        $entity = $table->newEntity(['fraction' => $small]);

        $table->save($entity);
        $out = $table->find()
            ->where([
                'fraction' => $small,
            ])
            ->first();
        $this->assertNotEmpty($out, 'Should get a record');
        $this->assertRegExp('/^0?\.1234567890123456789$/', $out->fraction);

        $small = 0.1234567890123456789;
        $entity = $table->newEntity(['fraction' => $small]);

        $table->save($entity);
        $out = $table->find()
            ->where([
                'fraction' => $small,
            ])
            ->first();
        $this->assertNotEmpty($out, 'Should get a record');
        // There will be loss of precision if too large/small value is set as float instead of string.
        $this->assertRegExp('/^0?\.123456789012350*$/', $out->fraction);
    }
}
