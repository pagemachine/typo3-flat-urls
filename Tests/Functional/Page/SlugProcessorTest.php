<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Tests\Functional\Page;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Pagemachine\FlatUrls\Page\Page;
use Pagemachine\FlatUrls\Page\SlugProcessor;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for Pagemachine\FlatUrls\Page\SlugProcessor
 */
final class SlugProcessorTest extends FunctionalTestCase
{
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
    public function updatesPageSlugs(array $pages, int $pageUid, string $expected): void
    {
        $this->setUpBackendUserFromFixture(1);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages');

        $connection->bulkInsert('pages',
            $pages,
            [
                'uid',
                'pid',
                'title',
                'slug',
            ]
        );

        $slugProcessor = GeneralUtility::makeInstance(SlugProcessor::class);
        $slugProcessor->update(new Page($pageUid));

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
                        'pid' => 0,
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
        }
    }
}
