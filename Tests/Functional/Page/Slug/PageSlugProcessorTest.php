<?php

declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Tests\Functional\Page\Slug;

use Pagemachine\FlatUrls\Page\Page;
use Pagemachine\FlatUrls\Page\Slug\PageSlugProcessor;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Testcase for Pagemachine\FlatUrls\Page\Slug\PageSlugProcessor
 */
final class PageSlugProcessorTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'redirects',
    ];

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/flat_urls',
    ];

    /**
     * @test
     * @dataProvider pages
     */
    public function updatesPageSlugs(array $pages, int $pageUid, string $expected): void
    {
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/be_users.csv');
        $this->setUpBackendUser(1);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages');

        $connection->bulkInsert(
            'pages',
            $pages,
            array_keys($pages[0])
        );

        $this->setUpFrontendRootPage(1);

        $pageSlugProcessor = GeneralUtility::makeInstance(PageSlugProcessor::class);
        $pageSlugProcessor->update(new Page($pageUid));

        $updatedPage = $connection->select(
            ['slug'],
            'pages',
            ['uid' => $pageUid]
        )->fetch();

        $this->assertEquals($expected, $updatedPage['slug']);
    }

    public function pages(): \Generator
    {
        foreach (['', '/test/'] as $slug) {
            $without = $slug ? 'with' : 'without';

            yield sprintf('root page %s slug', $without) => [
                [
                    [
                        'uid' => 1,
                        'title' => 'Root Page',
                        'slug' => $slug,
                    ],
                ],
                1,
                '/',
            ];

            yield sprintf('page %s slug', $without) => [
                [
                    [
                        'uid' => 1,
                        'pid' => 0,
                        'title' => 'Root Page',
                        'slug' => '',
                    ],
                    [
                        'uid' => 2,
                        'pid' => 1,
                        'title' => 'Test page',
                        'slug' => $slug,
                    ],
                ],
                2,
                '/2/test-page',
            ];

            yield sprintf('nested page %s slug', $without) => [
                [
                    [
                        'uid' => 1,
                        'pid' => 0,
                        'title' => 'Root Page',
                        'slug' => '',
                    ],
                    [
                        'uid' => 2,
                        'pid' => 1,
                        'title' => 'Test page',
                        'slug' => '',
                    ],
                    [
                        'uid' => 3,
                        'pid' => 2,
                        'title' => 'Nested page',
                        'slug' => $slug,
                    ],
                ],
                3,
                '/3/nested-page',
            ];

            yield sprintf('translated page %s slug', $without) => [
                [
                    [
                        'uid' => 1,
                        'pid' => 0,
                        'sys_language_uid' => 0,
                        'l10n_parent' => 0,
                        'title' => 'Root Page',
                        'slug' => '',
                    ],
                    [
                        'uid' => 2,
                        'pid' => 1,
                        'sys_language_uid' => 0,
                        'l10n_parent' => 0,
                        'title' => 'Test page',
                        'slug' => '/2/test-page',
                    ],
                    [
                        'uid' => 3,
                        'pid' => 2,
                        'sys_language_uid' => 1,
                        'l10n_parent' => 2,
                        'title' => 'Translated page',
                        'slug' => $slug,
                    ],
                ],
                3,
                '/2/translated-page',
            ];
        }
    }
}
