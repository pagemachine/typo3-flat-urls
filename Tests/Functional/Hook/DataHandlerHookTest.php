<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Tests\Functional\Hook;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Testcase for Pagemachine\FlatUrls\Hook\DataHandlerHook
 */
final class DataHandlerHookTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/flat_urls',
    ];

    /**
     * @test
     */
    public function ensuresFlatUrlOnNewPages(): void
    {
        $this->setUpBackendUserFromFixture(1);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages');
        $connection->insert('pages', [
            'uid' => 1,
            'title' => 'Root',
            'doktype' => PageRepository::DOKTYPE_DEFAULT,
            'is_siteroot' => 1,
        ]);
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([
            'pages' => [
                StringUtility::getUniqueId('NEW') => [
                    'title' => 'Test Page',
                    'pid' => 1,
                    'hidden' => 0,
                ],
            ],
        ], []);

        $dataHandler->process_datamap();

        $page = $connection->select(
            ['slug'],
            'pages',
            ['uid' => 2]
        )->fetch();

        $this->assertEquals('/2/test-page', $page['slug']);
    }
}
