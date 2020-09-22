<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Tests\Functional\Hook\DataHandler;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

/**
 * Testcase for Pagemachine\FlatUrls\Hook\DataHandler\AmendSlug
 */
final class AmendSlugTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $coreExtensionsToLoad = [
        'redirects',
    ];

    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/flat_urls',
    ];

    /**
     * @test
     * @dataProvider pages
     */
    public function ensuresFlatUrls(array $changes, int $pageUid, string $expected): void
    {
        $this->setUpBackendUserFromFixture(1);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages');
        $connection->insert('pages', [
            'uid' => 1,
            'title' => 'Root',
            'is_siteroot' => 1,
        ]);
        $this->setUpFrontendRootPage(1);

        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([
            'pages' => $changes,
        ], []);
        $dataHandler->process_datamap();

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $page = $queryBuilder
            ->select('slug')
            ->from('pages')
            ->where($queryBuilder->expr()->eq('uid', $pageUid))
            ->execute()
            ->fetch();

        $this->assertEquals($expected, $page['slug']);
    }

    public function pages(): \Generator
    {
        foreach (['new page', 'hidden new page'] as $hidden => $name) {
            yield $name => [
                [
                    StringUtility::getUniqueId('NEW') => [
                        'title' => 'Test Page',
                        'pid' => 1,
                        'hidden' => $hidden,
                    ],
                ],
                2,
                '/2/test-page',
            ];
        }
    }
}
