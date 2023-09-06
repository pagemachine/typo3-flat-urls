<?php

declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Tests\Functional\Hook\DataHandler;

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Testcase for Pagemachine\FlatUrls\Hook\DataHandler\ResolveRedirectConflict
 */
final class ResolveRedirectConflictTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'redirects',
    ];

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/flat_urls',
    ];

    /**
     * @test
     */
    public function resolvesPagePathRedirectConflicts(): void
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

        $siteConfiguration = GeneralUtility::makeInstance(SiteConfiguration::class);
        $siteConfiguration->createNewBasicSite('1', 1, 'http://localhost/');

        $pageConnection->insert('pages', [
            'uid' => 2,
            'pid' => 1,
            'title' => 'First title',
            'slug' => '/2/first-title',
        ]);

        $redirectConnection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_redirect');
        $redirectConnection->insert('sys_redirect', [
            'uid' => 1,
            'source_host' => '*',
            'source_path' => '/foo',
            'target' => 't3://page?uid=2',
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

        $redirects = $redirectConnection
            ->select(['source_path', 'target'], 'sys_redirect', [], [], ['uid' => 'ASC'])
            ->fetchAll();

        $this->assertCount(2, $redirects);
        $this->assertSame('/foo', $redirects[0]['source_path'] ?? null);
        $this->assertSame('t3://page?uid=2', $redirects[0]['target'] ?? null);
        $this->assertSame('/2/first-title', $redirects[1]['source_path'] ?? null);
        $this->assertSame('t3://page?uid=2', $redirects[1]['target'] ?? null);

        // Drop cached page used by PageRepository::getPage() through PageRouter::generateUri()
        GeneralUtility::makeInstance(CacheManager::class)->flushCaches();

        $dataHandler->start([
            'pages' => [
                2 => [
                    'title' => 'First title',
                ],
            ],
        ], []);
        $dataHandler->process_datamap();

        $redirects = $redirectConnection
            ->select(['source_path', 'target'], 'sys_redirect', [], [], ['uid' => 'ASC'])
            ->fetchAll();

        $this->assertCount(2, $redirects);
        $this->assertSame('/foo', $redirects[0]['source_path'] ?? null);
        $this->assertSame('t3://page?uid=2', $redirects[0]['target'] ?? null);
        $this->assertSame('/2/second-title', $redirects[1]['source_path'] ?? null);
        $this->assertSame('t3://page?uid=2', $redirects[1]['target'] ?? null);
    }

    /**
     * @test
     */
    public function normalizesPagePath(): void
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

        $siteConfiguration = GeneralUtility::makeInstance(SiteConfiguration::class);
        $siteConfiguration->createNewBasicSite('1', 1, 'http://localhost/');
        // Enforce trailing slash for generated page URIs
        $siteConfigurationData = $siteConfiguration->load('1');
        $siteConfigurationData['routeEnhancers'] = [
            'pageTypeSuffix' => [
                'type' => 'PageType',
                'default' => '/',
                'index' => '',
                'map' => [
                    '/' => 0,
                ],
            ],
        ];
        $siteConfiguration->write('1', $siteConfigurationData);

        $pageConnection->insert('pages', [
            'uid' => 2,
            'pid' => 1,
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

        // Drop cached page used by PageRepository::getPage() through PageRouter::generateUri()
        GeneralUtility::makeInstance(CacheManager::class)->flushCaches();

        $dataHandler->start([
            'pages' => [
                2 => [
                    'title' => 'First title',
                ],
            ],
        ], []);
        $dataHandler->process_datamap();

        $redirectConnection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_redirect');

        $redirects = $redirectConnection
            ->select(['source_path', 'target'], 'sys_redirect')
            ->fetchAll();

        $expected = [
            [
                'source_path' => '/2/second-title',
                'target' => 't3://page?uid=2',
            ],
        ];

        $this->assertCount(1, $redirects);
        $this->assertSame('/2/second-title', $redirects[0]['source_path'] ?? null);
        $this->assertSame('t3://page?uid=2', $redirects[0]['target'] ?? null);
    }

    /**
     * @test
     */
    public function resolvesUidRedirectConflicts(): void
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
            'title' => 'First title',
            'slug' => '/2',
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
            'title' => 'First title',
            'slug' => '/',
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

        $redirectConnection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_redirect');
        $redirectConnection->insert('sys_redirect', [
            'source_host' => 'www.example.org',
            'source_path' => '/',
        ]);

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

        $this->assertEquals(1, $redirectConnection->count('*', 'sys_redirect', ['source_path' => '/']));
    }
}
