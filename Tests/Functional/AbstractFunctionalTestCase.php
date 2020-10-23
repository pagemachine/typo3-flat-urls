<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Tests\Functional;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

abstract class AbstractFunctionalTestCase extends FunctionalTestCase
{
    /**
     * @var string
     */
    protected $backendUserFixture = __DIR__ . '/Fixtures/Database/be_users.xml';
}
