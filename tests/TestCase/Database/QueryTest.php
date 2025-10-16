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

namespace Portal89\OracleDriver\Test\TestCase\Database;

use Cake\Database\Expression\IdentifierExpression;
use Cake\Database\Query;
use Cake\Test\TestCase\Database\QueryTest as CakeQueryTest;
use Portal89\OracleDriver\Database\FunctionsBuilder;

/**
 * Tests Query class
 *
 */
class QueryTest extends CakeQueryTest
{
    /**
     * @inheritDoc
     */
    public function testSelectAliasedTables()
    {
        $this->loadFixtures('Authors', 'Articles');
        $query = new Query($this->connection);
        $result = $query->select(['text' => FunctionsBuilder::toChar(new IdentifierExpression('a.body')), 'a.author_id'])
            ->from(['a' => 'articles'])->execute();

        $this->assertEquals(['text' => 'First Article Body', 'author_id' => 1], $result->fetch('assoc'));
        $this->assertEquals(['text' => 'Second Article Body', 'author_id' => 3], $result->fetch('assoc'));

        $result = $query->select(['name' => 'b.name'])->from(['b' => 'authors'])
            ->order(['text' => 'desc', 'name' => 'desc'])
            ->execute();
        $this->assertEquals(
            ['text' => 'Third Article Body', 'author_id' => 1, 'name' => 'nate'],
            $result->fetch('assoc')
        );
        $this->assertEquals(
            ['text' => 'Third Article Body', 'author_id' => 1, 'name' => 'mariano'],
            $result->fetch('assoc')
        );
    }

    /**
     * @inheritDoc
     */
    public function testSelectOrderBy()
    {
        $this->loadFixtures('Authors', 'Articles');
        $query = new Query($this->connection);
        $result = $query
            ->select(['id'])
            ->from('articles')
            ->order(['id' => 'desc'])
            ->execute();
        $this->assertEquals(['id' => 3], $result->fetch('assoc'));
        $this->assertEquals(['id' => 2], $result->fetch('assoc'));
        $this->assertEquals(['id' => 1], $result->fetch('assoc'));

        $result = $query->order(['id' => 'asc'])->execute();
        $this->assertEquals(['id' => 1], $result->fetch('assoc'));
        $this->assertEquals(['id' => 2], $result->fetch('assoc'));
        $this->assertEquals(['id' => 3], $result->fetch('assoc'));

        $result = $query->order(['title' => 'asc'])->execute();
        $this->assertEquals(['id' => 1], $result->fetch('assoc'));
        $this->assertEquals(['id' => 2], $result->fetch('assoc'));
        $this->assertEquals(['id' => 3], $result->fetch('assoc'));

        $result = $query->order(['title' => 'asc'], true)->execute();
        $this->assertEquals(['id' => 1], $result->fetch('assoc'));
        $this->assertEquals(['id' => 2], $result->fetch('assoc'));
        $this->assertEquals(['id' => 3], $result->fetch('assoc'));

        $result = $query->order(['title' => 'asc', 'published' => 'asc'], true)
            ->execute();
        $this->assertEquals(['id' => 1], $result->fetch('assoc'));
        $this->assertEquals(['id' => 2], $result->fetch('assoc'));
        $this->assertEquals(['id' => 3], $result->fetch('assoc'));

        $driver = $query->getConnection()->getDriver();
        $idField = $driver->quoteIfAutoQuote('id');
        $expression = $query->newExpr(["MOD(($idField + :offset), 2)"]);
        $result = $query
            ->order([$expression, 'id' => 'desc'], true)
            ->bind(':offset', 1, null)
            ->execute();
        $this->assertEquals(['id' => 3], $result->fetch('assoc'));
        $this->assertEquals(['id' => 1], $result->fetch('assoc'));
        $this->assertEquals(['id' => 2], $result->fetch('assoc'));

        $result = $query
            ->order($expression, true)
            ->order(['id' => 'asc'])
            ->bind(':offset', 1, null)
            ->execute();
        $this->assertEquals(['id' => 1], $result->fetch('assoc'));
        $this->assertEquals(['id' => 3], $result->fetch('assoc'));
        $this->assertEquals(['id' => 2], $result->fetch('assoc'));
    }

    /**
     * @inheritDoc
     */
    public function testSelectGroup()
    {
        $this->loadFixtures('Authors', 'Articles');
        $query = new Query($this->connection);
        $result = $query->select(['total' => 'count(author_id)', 'author_id'])
            ->from('articles')
            ->join([
                'table' => 'authors',
                'alias' => 'a',
                'conditions' => $query
                    ->newExpr()
                    ->eq(new IdentifierExpression('author_id'), new IdentifierExpression('a.id')),
            ])
            ->group('author_id')
            ->execute();

        $expected = [['total' => 2, 'author_id' => 1], ['total' => '1', 'author_id' => 3]];
        $this->assertEquals($expected, $result->fetchAll('assoc'));

        $result = $query
            ->select(['total' => 'count(title)', 'name'], true)
            ->group(['name'], true)
            ->order(['total' => 'asc'])
            ->execute();
        $expected = [['total' => 1, 'name' => 'larry'], ['total' => 2, 'name' => 'mariano']];
        $this->assertEquals($expected, $result->fetchAll('assoc'));

        $result = $query
            ->select(['articles.id'])
            ->group(['articles.id'])
            ->execute();
        $this->assertCount(3, $result);
    }

    /**
     * Tests that expression objects can be used as the field in a comparison
     * and the values will be bound correctly to the query
     *
     * @return void
     */
    public function testSelectWhereUsingExpressionInField()
    {
        $this->loadFixtures('Authors', 'Articles', 'Comments');
        $query = new Query($this->connection);
        $subquery = clone $query;
        $result = $query
            ->select(['id'])
            ->from('comments')
            ->where(function ($exp) use ($subquery) {
                $field = clone $exp;
                $field
                    ->add($subquery
                        ->select([
                            $subquery
                                ->func()
                                ->min(new IdentifierExpression('id')),
                        ])
                        ->from('comments'));

                return $exp->eq($field, 100, 'integer');
            })
            ->execute();
        $this->assertCount(0, $result);
    }

    /**
     * Tests that functions are correctly transformed and their parameters are bound
     *
     * @group FunctionExpression
     * @return void
     */
    public function testSQLFunctions()
    {
        $this->loadFixtures('Comments');
        $query = new Query($this->connection);
        $result = $query->select(
            function ($q) {
                return ['total' => $q->func()->count('*')];
            }
        )
            ->from('comments')
            ->execute();
        $expected = [['total' => 6]];
        $this->assertEquals($expected, $result->fetchAll('assoc'));

        $query = new Query($this->connection);
        $result = $query->select([
                'c' => $query->func()->concat([$query->func()->to_char([new IdentifierExpression('comment')]), ' is appended']),
            ])
            ->from('comments')
            ->order(['c' => 'ASC'])
            ->limit(1)
            ->execute();
        $expected = [
            ['c' => 'First Comment for First Article is appended'],
        ];
        $this->assertEquals($expected, $result->fetchAll('assoc'));

        $query = new Query($this->connection);
        $result = $query
            ->select(['d' => $query->func()->dateDiff(['2012-01-05', '2012-01-02'])])
            ->from('DUAL')
            ->execute()
            ->fetchAll('assoc');
        $this->assertEquals(3, abs($result[0]['d']));

        $query = new Query($this->connection);
        $result = $query
            ->select(['d' => $query->func()->now('date')])
            ->from('DUAL')
            ->execute();
        $date = $result->fetchAll('assoc');
        $this->assertEquals([['d' => date('Y-m-d')]], $date);

        $query = new Query($this->connection);
        $result = $query
            ->select(['d' => $query->func()->now('time')])
            ->from('DUAL')
            ->execute();

        $d = $result->fetchAll('assoc')[0]['d'];
        $date1 = date('U');
        $this->assertWithinRange(
            $date1,
            (new \DateTime($d))->format('U'),
            1
        );

        $query = new Query($this->connection);
        $result = $query
            ->select(['d' => $query->func()->now()])
            ->from('DUAL')
            ->execute();
        $this->assertWithinRange(
            date('U'),
            (new \DateTime($result->fetchAll('assoc')[0]['d']))->format('U'),
            1
        );

        $query = new Query($this->connection);
        $created = new IdentifierExpression('created');
        $result = $query
            ->select([
                'd' => $query->func()->datePart('day', $created),
                'm' => $query->func()->datePart('month', $created),
                'y' => $query->func()->datePart('year', $created),
                'de' => $query->func()->extract('day', $created),
                'me' => $query->func()->extract('month', $created),
                'ye' => $query->func()->extract('year', $created),
                'wd' => $query->func()->weekday($created),
                'dow' => $query->func()->dayOfWeek($created),
                'addDays' => $query->func()->dateAdd($created, 2, 'day'),
                'substractYears' => $query->func()->dateAdd($created, -2, 'year'),
            ])
            ->from('comments')
            ->where([
                'created' => FunctionsBuilder::toDate(['2007-03-18 10:45:23']),
            ])
            ->execute()
            ->fetchAll('assoc');
        $result[0]['m'] = ltrim($result[0]['m'], '0');
        $result[0]['me'] = ltrim($result[0]['me'], '0');
        $result[0]['addDays'] = substr($result[0]['addDays'], 0, 10);
        $result[0]['substractYears'] = substr($result[0]['substractYears'], 0, 10);
        $expected = [
            'd' => '18',
            'm' => '3',
            'y' => '2007',
            'de' => '18',
            'me' => '3',
            'ye' => '2007',
            'wd' => '1', // Sunday
            'dow' => '1',
            'addDays' => '2007-03-20',
            'substractYears' => '2005-03-18',
        ];
        $this->assertEquals($expected, $result[0]);
    }

    /**
     * Test that order() being a string works.
     * Does not work for oracle in escape mode. So test changed to array.
     *
     * @return void
     */
    public function testSelectOrderByString()
    {
        $this->loadFixtures('Articles');
        $query = new Query($this->connection);
        $query->select(['id'])
            ->from('articles')
            ->order(['id' => 'asc']);
        $result = $query->execute();
        $this->assertEquals(['id' => 1], $result->fetch('assoc'));
        $this->assertEquals(['id' => 2], $result->fetch('assoc'));
        $this->assertEquals(['id' => 3], $result->fetch('assoc'));
    }

    /**
     * Tests that having() behaves pretty much the same as the where() method
     *
     * @return void
     */
    public function testSelectHaving()
    {
        $this->loadFixtures('Authors', 'Articles');
        $query = new Query($this->connection);
        $result = $query
            ->select(['total' => 'count(author_id)', 'author_id'])
            ->from('articles')
            ->join(['table' => 'authors', 'alias' => 'a', 'conditions' => $query->newExpr()->equalFields('author_id', 'a.id')])
            ->group('author_id')
            ->having(['count(author_id) <' => 2], ['count(author_id)' => 'integer'])
            ->execute();
        $expected = [['total' => 1, 'author_id' => 3]];
        $this->assertEquals($expected, $result->fetchAll('assoc'));

        $result = $query->having(['count(author_id)' => 2], ['count(author_id)' => 'integer'], true)
            ->execute();
        $expected = [['total' => 2, 'author_id' => 1]];
        $this->assertEquals($expected, $result->fetchAll('assoc'));
    }

    /**
     * Tests that Query::andHaving() can be used to concatenate conditions with AND
     * in the having clause
     *
     * @return void
     */
    public function testSelectAndHaving()
    {
        $this->loadFixtures('Authors', 'Articles');
        $query = new Query($this->connection);
        $result = $query
            ->select(['total' => 'count(author_id)', 'author_id'])
            ->from('articles')
            ->join(['table' => 'authors', 'alias' => 'a', 'conditions' => $query->newExpr()->equalFields('author_id', 'a.id')])
            ->group('author_id')
            ->having(['count(author_id) >' => 2], ['count(author_id)' => 'integer'])
            ->andHaving(['count(author_id) <' => 2], ['count(author_id)' => 'integer'])
            ->execute();
        $this->assertCount(0, $result);

        $query = new Query($this->connection);
        $result = $query
            ->select(['total' => 'count(author_id)', 'author_id'])
            ->from('articles')
            ->join(['table' => 'authors', 'alias' => 'a', 'conditions' => $query->newExpr()->equalFields('author_id', 'a.id')])
            ->group('author_id')
            ->having(['count(author_id)' => 2], ['count(author_id)' => 'integer'])
            ->andHaving(['count(author_id) >' => 1], ['count(author_id)' => 'integer'])
            ->execute();
        $expected = [['total' => 2, 'author_id' => 1]];
        $this->assertEquals($expected, $result->fetchAll('assoc'));
    }

    /**
     * Tests parameter binding
     *
     * @return void
     */
    public function testBind()
    {
        $this->loadFixtures('Authors', 'Articles', 'Comments');
        $query = new Query($this->connection);
        $driver = $query->getConnection()->getDriver();
        $createdField = $driver->quoteIfAutoQuote('created');
        $results = $query->select(['id', 'comment'])
            ->from('comments')
            ->where(["$createdField BETWEEN :foo AND :bar"])
            ->bind(':foo', new \DateTime('2007-03-18 10:50:00'), 'datetime')
            ->bind(':bar', new \DateTime('2007-03-18 10:52:00'), 'datetime')
            ->execute();
        $expected = [['id' => '4', 'comment' => 'Fourth Comment for First Article']];
        $this->assertEquals($expected, $results->fetchAll('assoc'));

        $query = new Query($this->connection);
        $results = $query->select(['id', 'comment'])
            ->from('comments')
            ->where(["$createdField BETWEEN :foo AND :bar"])
            ->bind(':foo', '2007-03-18 10:50:00')
            ->bind(':bar', '2007-03-18 10:52:00')
            ->execute();
        $this->assertEquals($expected, $results->fetchAll('assoc'));
    }

    /**
     * Test that insert can use expression objects as values.
     *
     * @return void
     */
    public function testInsertExpressionValues()
    {
        $this->loadFixtures('Authors', 'Articles', 'Comments');
        $query = new Query($this->connection);
        $query->insert(['title', 'author_id'])
            ->into('articles')
            ->values(['title' => $query->newExpr("SELECT 'jose' FROM DUAL"), 'author_id' => 99]);

        $result = $query->execute();
        $result->closeCursor();

        //PDO_SQLSRV returns -1 for successful inserts when using INSERT ... OUTPUT
        if (!$this->connection->getDriver() instanceof \Cake\Database\Driver\Sqlserver) {
            $this->assertCount(1, $result);
        }

        $result = (new Query($this->connection))->select('*')
            ->from('articles')
            ->where(['author_id' => 99])
            ->execute();
        $this->assertCount(1, $result);
        $expected = [
            'id' => 4,
            'title' => 'jose',
            'body' => null,
            'author_id' => '99',
            'published' => 'N',
        ];
        $this->assertEquals($expected, $result->fetch('assoc'));

        $subquery = new Query($this->connection);
        $subquery->select(['name'])
            ->from('authors')
            ->where(['id' => 1]);

        $query = new Query($this->connection);
        $query->insert(['title', 'author_id'])
            ->into('articles')
            ->values(['title' => $subquery, 'author_id' => 100]);
        $result = $query->execute();
        $result->closeCursor();
        //PDO_SQLSRV returns -1 for successful inserts when using INSERT ... OUTPUT
        if (!$this->connection->getDriver() instanceof \Cake\Database\Driver\Sqlserver) {
            $this->assertCount(1, $result);
        }

        $result = (new Query($this->connection))->select('*')
            ->from('articles')
            ->where(['author_id' => 100])
            ->execute();
        $this->assertCount(1, $result);
        $expected = [
            'id' => 5,
            'title' => 'mariano',
            'body' => null,
            'author_id' => '100',
            'published' => 'N',
        ];
        $this->assertEquals($expected, $result->fetch('assoc'));
    }

    /**
     * Tests that it is possible to select distinct rows, even filtering by one column
     * this is testing that there is a specific implementation for DISTINCT ON
     *
     * @return void
     */
    public function testSelectDistinctON()
    {
        $this->markTestSkipped('Distinct on not supported in Oracle');
    }

    /**
     * Tests that it is possible to one or multiple UNION statements in a query
     *
     * @return void
     */
    public function testUnion()
    {
        $this->loadFixtures('Authors', 'Articles', 'Comments');
        $union = (new Query($this->connection))->select(['id', 'title'])->from(['a' => 'articles']);
        $query = new Query($this->connection);
        $result = $query->select(['id', FunctionsBuilder::toChar(new IdentifierExpression('comment'))])
            ->from(['c' => 'comments'])
            ->union($union)
            ->execute();
        $rows = $result->fetchAll();
        $this->assertCount(self::COMMENT_COUNT + self::ARTICLE_COUNT, $rows);

        $union->select(['foo' => 'id', 'bar' => 'title']);
        $union = (new Query($this->connection))
            ->select(['id', 'name', 'other' => 'id', 'nameish' => 'name'])
            ->from(['b' => 'authors'])
            ->where(['id ' => 1]);

        $query->select(['foo' => 'id', 'bar' => FunctionsBuilder::toChar(new IdentifierExpression('comment'))])->union($union);
        $result = $query->execute();
        $rows2 = $result->fetchAll();
        $this->assertCount(self::COMMENT_COUNT + self::AUTHOR_COUNT, $rows2);
        $this->assertNotEquals($rows, $rows2);

        $union = (new Query($this->connection))
            ->select(['id', 'title'])
            ->from(['c' => 'articles']);
        $query->select(['id', FunctionsBuilder::toChar(new IdentifierExpression('comment'))], true)->union($union, true);
        $result = $query->execute();
        $rows3 = $result->fetchAll();
        $this->assertCount(self::COMMENT_COUNT + self::ARTICLE_COUNT, $rows3);
        $this->assertEquals($rows, $rows3);
    }

    /**
     * Tests that it is possible to run unions with order statements
     *
     * @return void
     */
    public function testUnionOrderBy()
    {
        $this->skipIf(
            ($this->connection->getDriver() instanceof \Portal89\OracleDriver\Database\Driver\OracleBase),
            'Driver does not support ORDER BY in UNIONed queries.'
        );
        parent::testUnionOrderBy();
    }

    /**
     * Tests that UNION ALL can be built by setting the second param of union() to true
     *
     * @return void
     */
    public function testUnionAll()
    {
        $this->loadFixtures('Authors', 'Articles', 'Comments');
        $union = (new Query($this->connection))->select(['id', 'title'])->from(['a' => 'articles']);
        $query = new Query($this->connection);
        $result = $query->select(['id', FunctionsBuilder::toChar(new IdentifierExpression('comment'))])
            ->from(['c' => 'comments'])
            ->union($union)
            ->execute();
        $rows = $result->fetchAll();
        $this->assertCount(self::ARTICLE_COUNT + self::COMMENT_COUNT, $rows);

        $union->select(['foo' => 'id', 'bar' => 'title']);
        $union = (new Query($this->connection))
            ->select(['id', 'name', 'other' => 'id', 'nameish' => 'name'])
            ->from(['b' => 'authors'])
            ->where(['id ' => 1]);

        $query->select(['foo' => 'id', 'bar' => FunctionsBuilder::toChar(new IdentifierExpression('comment'))])->unionAll($union);
        $result = $query->execute();
        $rows2 = $result->fetchAll();
        $this->assertCount(1 + self::COMMENT_COUNT + self::ARTICLE_COUNT, $rows2);
        $this->assertNotEquals($rows, $rows2);
    }

    /**
     * Tests that joins can be aliased using array keys
     *
     * @return void
     */
    public function testSelectAliasedJoins()
    {
        $this->loadFixtures('Authors', 'Articles', 'Comments');
        $query = new Query($this->connection);
        $result = $query
            ->select(['title', 'name'])
            ->from('articles')
            ->join(['a' => 'authors'])
            ->order(['name' => 'desc', 'articles.id' => 'asc'])
            ->execute();
        $this->assertEquals(['title' => 'First Article', 'name' => 'nate'], $result->fetch('assoc'));
        $this->assertEquals(['title' => 'Second Article', 'name' => 'nate'], $result->fetch('assoc'));
        $result->closeCursor();

        $query = new Query($this->connection);
        $conditions = $query->newExpr()->equalFields('author_id', 'a.id');
        $result = $query
            ->select(['title', 'name'])
            ->from('articles')
            ->join(['a' => ['table' => 'authors', 'conditions' => $conditions]])
            ->order(['title' => 'asc'])
            ->execute();
        $this->assertEquals(['title' => 'First Article', 'name' => 'mariano'], $result->fetch('assoc'));
        $this->assertEquals(['title' => 'Second Article', 'name' => 'larry'], $result->fetch('assoc'));
        $result->closeCursor();

        $query = new Query($this->connection);
        $time = new \DateTime('2007-03-18 10:45:23');
        $types = ['created' => 'datetime'];
        $result = $query
            ->select(['title', 'name' => 'c.comment'])
            ->from('articles')
            ->join(['c' => ['table' => 'comments', 'conditions' => ['created' => $time]]], $types)
            ->execute();
        $this->assertEquals(['title' => 'First Article', 'name' => 'First Comment for First Article'], $result->fetch('assoc'));
        $result->closeCursor();
    }

    /**
     * Test selecting rows using the page() method and ordering the results
     * by a calculated column.
     *
     * @return void
     */
    public function testSelectPageWithOrder()
    {
        $this->loadFixtures('Comments');
        $query = new Query($this->connection);

        $driver = $query->getConnection()->getDriver();
        $userIdField = $driver->quoteIfAutoQuote('user_id');
        $articleIdField = $driver->quoteIfAutoQuote('article_id');
        $addExpression = $query->newExpr(["$userIdField + $articleIdField"]);
        $result = $query
            ->select([
                'id',
                'ids_added' => $query->newExpr()->add($addExpression),
            ])
            ->from('comments')
            ->order(['ids_added' => 'asc'])
            ->limit(2)
            ->page(3)
            ->execute();
        $this->assertCount(2, $result);
        $this->assertEquals(
            [
                ['id' => '6', 'ids_added' => '4'],
                ['id' => '2', 'ids_added' => '5'],
            ],
            $result->fetchAll('assoc')
        );
    }

    /**
     * Tests Query::whereNull()
     *
     * @return void
     */
    public function testSelectWhereNull()
    {
        $this->loadFixtures('MenuLinkTrees');

        $query = new Query($this->connection);
        $result = $query
            ->select(['id', 'parent_id'])
            ->from('menu_link_trees')
            ->whereNull(['parent_id'])
            ->execute();
        $this->assertCount(5, $result);
        $result->closeCursor();

        $query = new Query($this->connection);
        $result = $query
            ->select(['id'])
            ->from('menu_link_trees')
            ->whereNull('parent_id')
            ->execute();
        $this->assertCount(5, $result);
        $result->closeCursor();
    }

    /**
     * Tests Query::whereNotNull()
     *
     * @return void
     */
    public function testSelectWhereNotNull()
    {
        $this->loadFixtures('MenuLinkTrees');

        $query = new Query($this->connection);
        $result = $query
            ->select(['id', 'parent_id'])
            ->from('menu_link_trees')
            ->whereNotNull(['parent_id'])
            ->execute();
        $this->assertCount(13, $result);
        $result->closeCursor();

        $query = new Query($this->connection);
        $result = $query
            ->select(['id'])
            ->from('menu_link_trees')
            ->whereNotNull('parent_id')
            ->execute();
        $this->assertCount(13, $result);
        $result->closeCursor();
    }
}
