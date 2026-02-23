<?php

declare(strict_types=1);

namespace Pagemachine\FlatUrls\Tests\Functional\Hook\DataHandler;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Testcase for Pagemachine\FlatUrls\Hook\DataHandler\RefreshSlug
 */
final class RefreshSlugTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'redirects',
    ];

    protected array $testExtensionsToLoad = [
        'pagemachine/typo3-flat-urls',
    ];

    /**
     * @test
     * @dataProvider pages
     */
    public function ensuresFlatUrls(array $pages, array $changes, int $pageUid, string $expected): void
    {
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/be_users.csv');
        $backendUser = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($backendUser);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages');
        $connection->insert('pages', [
            'uid' => 1,
            'title' => 'Root',
            'is_siteroot' => 1,
        ]);
        $this->setUpFrontendRootPage(1);

        $connection->bulkInsert(
            'pages',
            $pages,
            array_keys($pages[0])
        );

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
            ->executeQuery()
            ->fetchAssociative();

        self::assertEquals($expected, $page['slug']);
    }

    public static function pages(): \Generator
    {
        foreach (['updated page', 'hidden updated page'] as $hidden => $name) {
            yield $name => [
                [
                    [
                        'uid' => 2,
                        'pid' => 1,
                        'hidden' => $hidden,
                        'title' => 'Old Page',
                        'slug' => '/2/old-page',
                    ],
                ],
                [
                    2 => [
                        'title' => 'New Page',
                    ],
                ],
                2,
                '/2/new-page',
            ];
        }

        foreach (['translated page', 'hidden translated page'] as $hidden => $name) {
            yield $name => [
                [
                    [
                        'uid' => 2,
                        'pid' => 1,
                        'hidden' => 0,
                        'sys_language_uid' => 0,
                        'l10n_parent' => 0,
                        'title' => 'Page',
                        'slug' => '/2/page',
                    ],
                    [
                        'uid' => 3,
                        'pid' => 1,
                        'hidden' => $hidden,
                        'sys_language_uid' => 1,
                        'l10n_parent' => 2,
                        'title' => 'Old Translated Page',
                        'slug' => '/2/old-translated-page',
                    ],
                ],
                [
                    3 => [
                        'title' => 'New Translated Page',
                    ],
                ],
                3,
                '/2/new-translated-page',
            ];
        }
    }
}
