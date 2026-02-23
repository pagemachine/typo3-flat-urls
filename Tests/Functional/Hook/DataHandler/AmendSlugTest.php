<?php

declare(strict_types=1);

namespace Pagemachine\FlatUrls\Tests\Functional\Hook\DataHandler;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Testcase for Pagemachine\FlatUrls\Hook\DataHandler\AmendSlug
 */
final class AmendSlugTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'redirects',
    ];

    protected array $testExtensionsToLoad = [
        'pagemachine/typo3-flat-urls',
    ];

    #[Test]
    #[DataProvider('pages')]
    public function ensuresFlatUrls(array $changes, int $pageUid, string $expected): void
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
        foreach (['new page', 'hidden new page'] as $hidden => $name) {
            yield $name => [
                [
                    StringUtility::getUniqueId('NEW') => [
                        'pid' => 1,
                        'hidden' => $hidden,
                        'title' => 'Test Page',
                    ],
                ],
                2,
                '/2/test-page',
            ];
        }

        $pageUid = StringUtility::getUniqueId('NEW');

        foreach (['new page translation', 'hidden new page translation'] as $hidden => $name) {
            yield $name => [
                [
                    $pageUid => [
                        'pid' => 1,
                        'hidden' => 0,
                        'title' => 'Test Page',
                    ],
                    StringUtility::getUniqueId('NEW') => [
                        'pid' => 1,
                        'sys_language_uid' => 1,
                        'l10n_parent' => $pageUid,
                        'hidden' => $hidden,
                        'title' => 'Translated Test Page',
                    ],
                ],
                3,
                '/2/translated-test-page',
            ];
        }
    }
}
