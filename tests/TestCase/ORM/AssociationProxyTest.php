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

use Cake\Test\TestCase\ORM\AssociationProxyTest as CakeAssociationProxyTest;

/**
 * Tests AssociationProxy class
 *
 */
class AssociationProxyTest extends CakeAssociationProxyTest
{
    /**
     * Tests that the proxied updateAll will preserve conditions set for the association
     *
     * @return void
     */
    public function testUpdateAllFromAssociation()
    {
        $articles = $this->getTableLocator()->get('articles');
        $comments = $this->getTableLocator()->get('comments');
        $articles->hasMany('comments', ['conditions' => ['published' => 'Y']]);
        $articles->comments->updateAll(['comment' => 'changed'], ['article_id' => 1]);
        $changed = $comments
            ->find()
            ->where(['to_char(comment)' => 'changed'])
            ->count();
        $this->assertEquals(3, $changed);
    }
}
