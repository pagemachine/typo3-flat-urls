<?php

declare(strict_types=1);

namespace Pagemachine\FlatUrls\Tests\Functional\Hook\DataHandler;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
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
        'pagemachine/typo3-flat-urls',
    ];

    #[Test]
    public function resolvesPagePathRedirectConflicts(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/be_users.csv');
        $backendUser = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($backendUser);

        $pageConnection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages');
        $pageConnection->insert('pages', [
            'uid' => 1,
            'title' => 'Root',
            'is_siteroot' => 1,
        ]);
        $this->setUpFrontendRootPage(1);

        $siteWriter = GeneralUtility::makeInstance(SiteWriter::class);
        $siteWriter->createNewBasicSite('1', 1, '/');

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
            ->fetchAllAssociative();

        self::assertCount(2, $redirects);
        self::assertSame('/foo', $redirects[0]['source_path'] ?? null);
        self::assertSame('t3://page?uid=2', $redirects[0]['target'] ?? null);
        self::assertSame('/2/first-title', $redirects[1]['source_path'] ?? null);
        self::assertSame('t3://page?uid=2', $redirects[1]['target'] ?? null);

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
            ->fetchAllAssociative();

        self::assertCount(2, $redirects);
        self::assertSame('/foo', $redirects[0]['source_path'] ?? null);
        self::assertSame('t3://page?uid=2', $redirects[0]['target'] ?? null);
        self::assertSame('/2/second-title', $redirects[1]['source_path'] ?? null);
        self::assertSame('t3://page?uid=2', $redirects[1]['target'] ?? null);
    }

    #[Test]
    public function normalizesPagePath(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/be_users.csv');
        $backendUser = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($backendUser);

        $pageConnection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('pages');
        $pageConnection->insert('pages', [
            'uid' => 1,
            'title' => 'Root',
            'is_siteroot' => 1,
        ]);
        $this->setUpFrontendRootPage(1);

        $siteWriter = GeneralUtility::makeInstance(SiteWriter::class);
        $siteWriter->createNewBasicSite('1', 1, 'http://localhost/');
        // Enforce trailing slash for generated page URIs
        $siteConfiguration = GeneralUtility::makeInstance(SiteConfiguration::class);
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
        $siteWriter->write('1', $siteConfigurationData);

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
            ->fetchAllAssociative();

        $expected = [
            [
                'source_path' => '/2/second-title',
                'target' => 't3://page?uid=2',
            ],
        ];

        self::assertCount(1, $redirects);
        self::assertSame('/2/second-title', $redirects[0]['source_path'] ?? null);
        self::assertSame('t3://page?uid=2', $redirects[0]['target'] ?? null);
    }

    #[Test]
    public function resolvesUidRedirectConflicts(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/be_users.csv');
        $backendUser = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($backendUser);

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

        self::assertEquals(0, $redirectConnection->count('*', 'sys_redirect', []));
    }

    #[Test]
    public function skipsPagesWithoutSite(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/be_users.csv');
        $backendUser = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($backendUser);

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

        self::assertEquals(0, $redirectConnection->count('*', 'sys_redirect', []));
    }

    #[Test]
    public function skipsInactivePages(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/be_users.csv');
        $backendUser = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($backendUser);

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

        self::assertEquals(1, $redirectConnection->count('*', 'sys_redirect', ['source_path' => '/']));
    }
}
