<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Tests\Functional\Hook\DataHandler;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for Pagemachine\FlatUrls\Hook\DataHandler\ResolveRedirectConflict
 */
final class ResolveRedirectConflictTest extends FunctionalTestCase
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
        $expected = [
            [
                'source_path' => '/foo',
                'target' => 't3://page?uid=2',
            ],
            [
                'source_path' => '/2/first-title',
                'target' => 't3://page?uid=2',
            ],
        ];

        $this->assertArraySubset($expected, $redirects);

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
        $expected = [
            [
                'source_path' => '/foo',
                'target' => 't3://page?uid=2',
            ],
            [
                'source_path' => '/2/second-title',
                'target' => 't3://page?uid=2',
            ],
        ];

        $this->assertArraySubset($expected, $redirects);
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

        // Enforce trailing slash for generated page URIs
        $siteConfiguration = GeneralUtility::makeInstance(SiteConfiguration::class, Environment::getConfigPath() . '/sites');
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

        $this->assertEquals(1, $redirectConnection->count('*', 'sys_redirect', []));

        $redirects = $redirectConnection
            ->select(['source_path', 'target'], 'sys_redirect')
            ->fetchAll();

        $expected = [
            [
                'source_path' => '/2/second-title',
                'target' => 't3://page?uid=2',
            ],
        ];

        $this->assertArraySubset($expected, $redirects);
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
