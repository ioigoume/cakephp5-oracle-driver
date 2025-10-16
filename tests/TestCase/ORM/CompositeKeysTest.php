<?php
declare(strict_types=1);

namespace Portal89\OracleDriver\Test\TestCase\ORM;

use Cake\Test\TestCase\ORM\CompositeKeysTest as CakeCompositeKeysTest;

/**
 * Integration tests for table operations involving composite keys
 */
class CompositeKeysTest extends CakeCompositeKeysTest
{
    /**
     * Test that saving into composite primary keys where one column is missing & autoIncrement works.
     *
     * SQLite is skipped because it doesn't support autoincrement composite keys.
     *
     * @group save
     * @return void
     */
    public function testSaveNewCompositeKeyIncrement()
    {
        $this->markTestSkipped();
    }

    /**
     * Tests that HasMany associations are correctly eager loaded and results
     * correctly nested when multiple foreignKeys are used
     *
     * @dataProvider strategiesProviderHasMany
     * @return void
     */
    public function testHasManyEager($strategy)
    {
        $this->markTestSkipped();
    }

    /**
     * Tests that BelongsToMany associations are correctly eager loaded when multiple
     * foreignKeys are used
     *
     * @dataProvider strategiesProviderBelongsToMany
     * @return void
     */
    public function testBelongsToManyEager($strategy)
    {
        $this->markTestSkipped();
    }

    /**
     * Helper method to skip tests when connection is SQLite.
     *
     * @return void
     */
    public function skipIfOracle()
    {
        $this->skipIf(
            $this->connection->getDriver() instanceof OracleBase,
            'Oracle does not support the requirements of this test or test not ready yet.'
        );
    }
}
