<?php

declare(strict_types=1);

namespace Pagemachine\FlatUrls\Tests\Functional\Page;

use Pagemachine\FlatUrls\Page\Page;
use Pagemachine\FlatUrls\Page\PageCollection;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Testcase for Pagemachine\FlatUrls\Page\PageCollection
 */
final class PageCollectionTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'redirects',
    ];

    protected array $testExtensionsToLoad = [
        'pagemachine/typo3-flat-urls',
    ];

    #[Test]
    public function collectsAllPages(): void
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages');

        $connection->bulkInsert(
            'pages',
            [
                [
                    'uid' => 1,
                    'title' => 'Regular page',
                    'hidden' => 0,
                    'deleted' => 0,
                ],
                [
                    'uid' => 2,
                    'title' => 'Deleted page',
                    'hidden' => 0,
                    'deleted' => 1,
                ],
                [
                    'uid' => 3,
                    'title' => 'Hidden page',
                    'hidden' => 1,
                    'deleted' => 0,
                ],
            ],
            [
                'uid',
                'title',
                'deleted',
                'hidden',
            ]
        );

        $pages = GeneralUtility::makeInstance(PageCollection::class);

        self::assertCount(2, $pages);

        $pagesList = iterator_to_array($pages);

        self::assertContainsOnlyInstancesOf(Page::class, $pagesList);
        self::assertEquals(1, $pagesList[0]->getUid());
        self::assertEquals(3, $pagesList[1]->getUid());
    }
}
