<?php

declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Tests\Functional\Hook\DataHandler;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for Pagemachine\FlatUrls\Hook\DataHandler\AddRedirect
 */
final class AddRedirectTest extends FunctionalTestCase
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
     * @dataProvider redirectPages
     */
    public function addsRedirectsOnSlugChange(array $pages, array $changes, array $expected): void
    {
        $this->setUpBackendUserFromFixture(1);

        $pageConnection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages');
        $pageConnection->insert('pages', [
            'uid' => 1,
            'title' => 'Root',
            'is_siteroot' => 1,
        ]);
        $this->setUpFrontendRootPage(1);

        $pageConnection->bulkInsert(
            'pages',
            $pages,
            array_keys($pages[0])
        );

        $redirectConnection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_redirect');

        $this->assertEquals(0, $redirectConnection->count('*', 'sys_redirect', []));

        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([
            'pages' => $changes,
        ], []);
        $dataHandler->process_datamap();

        $this->assertEquals(1, $redirectConnection->count('*', 'sys_redirect', []));

        $redirect = $redirectConnection
            ->select(['*'], 'sys_redirect')
            ->fetch();

        foreach ($expected as $field => $value) {
            $this->assertSame($value, $redirect[$field] ?? null);
        }
    }

    public function redirectPages(): \Generator
    {
        yield 'page' => [
            [
                [
                    'uid' => 2,
                    'pid' => 1,
                    'title' => 'Old Page',
                    'slug' => '/2/old-page',
                ],
            ],
            [
                2 => [
                    'title' => 'New Page',
                ],
            ],
            [
                'source_host' => '*',
                'source_path' => '/2/old-page',
                'target' => 't3://page?uid=2',
                'target_statuscode' => 307,
            ],
        ];

        yield 'translated page' => [
            [
                [
                    'uid' => 2,
                    'pid' => 1,
                    'sys_language_uid' => 0,
                    'l10n_parent' => 0,
                    'title' => 'Test Page',
                    'slug' => '/2/test-page',
                ],
                [
                    'uid' => 3,
                    'pid' => 1,
                    'sys_language_uid' => 1,
                    'l10n_parent' => 2,
                    'title' => 'Old Translated Page',
                    'slug' => '/3/old-translated-page',
                ],
            ],
            [
                3 => [
                    'title' => 'New Translated Page',
                ],
            ],
            [
                'source_host' => '*',
                'source_path' => '/da/3/old-translated-page',
                'target' => 't3://page?uid=3',
                'target_statuscode' => 307,
            ],
        ];
    }

    /**
     * @test
     */
    public function skipsPagesWithoutSite(): void
    {
        $this->setUpBackendUserFromFixture(1);

        $pageConnection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages');
        $pageConnection->insert('pages', [
            'uid' => 2,
            'title' => 'Old Root',
            'is_siteroot' => 1,
        ]);

        $redirectConnection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_redirect');

        $this->assertEquals(0, $redirectConnection->count('*', 'sys_redirect', []));

        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([
            'pages' => [
                2 => [
                    'title' => 'New Root',
                ],
            ],
        ], []);
        $dataHandler->process_datamap();

        $this->assertEquals(0, $redirectConnection->count('*', 'sys_redirect', []));
    }

    /**
     * @test
     */
    public function skipsInactivePages(): void
    {
        $this->setUpBackendUserFromFixture(1);

        $pageConnection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages');
        $pageConnection->insert('pages', [
            'uid' => 1,
            'title' => 'Root',
            'is_siteroot' => 1,
        ]);
        $this->setUpFrontendRootPage(1);

        $pageConnection->insert('pages', [
            'uid' => 2,
            'pid' => 1,
            'hidden' => 1,
            'title' => 'First title',
            'slug' => '/2/first-title',
        ]);

        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([
            'pages' => [
                2 => [
                    'title' => 'Second title',
                ],
            ],
        ], []);
        $dataHandler->process_datamap();

        $redirectConnection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_redirect');

        $this->assertEquals(0, $redirectConnection->count('*', 'sys_redirect', []));
    }
}
