<?php

declare(strict_types=1);

namespace Pagemachine\FlatUrls\Tests\Functional\Middleware;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class FlatUrlRedirectTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'redirects',
    ];

    protected array $testExtensionsToLoad = [
        'pagemachine/typo3-flat-urls',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnectionPool()->getConnectionForTable('pages')->insert('pages', [
            'uid' => 1,
            'pid' => 0,
            'title' => 'Root',
        ]);
        $this->setUpFrontendRootPage(1, [
            'EXT:flat_urls/Tests/Functional/Middleware/Fixtures/TypoScript/page.typoscript',
        ]);

        if ((new Typo3Version())->getMajorVersion() > 12) {
            $siteWriter = GeneralUtility::makeInstance(SiteWriter::class);
            $siteWriter->createNewBasicSite('1', 1, 'http://localhost/');
        } else {
            $siteConfiguration = GeneralUtility::makeInstance(SiteConfiguration::class);
            $siteConfiguration->createNewBasicSite('1', 1, 'http://localhost/');
        }
    }

    #[Test]
    #[TestWith([
        'http://localhost/2',
        'http://localhost/2/short',
    ])]
    #[TestWith([
        'http://localhost/2/',
        'http://localhost/2/short',
    ])]
    #[TestWith([
        'http://localhost/2?foo=bar',
        'http://localhost/2/short?foo=bar&cHash=c765c3c9c1ad82c5e52692f4132b4b93',
    ])]
    #[TestWith([
        'http://localhost/2/?foo=bar',
        'http://localhost/2/short?foo=bar&cHash=c765c3c9c1ad82c5e52692f4132b4b93',
    ])]
    public function redirectsShortUrls(string $uri, string $expected): void
    {
        $this->getConnectionPool()->getConnectionForTable('pages')->insert('pages', [
            'uid' => 2,
            'pid' => 1,
            'title' => 'Short',
            'slug' => '2/short',
        ]);

        $response = $this->executeFrontendSubRequest(new InternalRequest($uri));

        self::assertSame(301, $response->getStatusCode());
        self::assertSame($expected, $response->getHeaderLine('location'));
    }

    #[Test]
    #[TestWith([
        [
            'uid' => 2,
            'pid' => 1,
            'title' => 'Short',
            'slug' => '2/short',
        ],
        'http://localhost/2/short',
    ])]
    #[TestWith([
        [
            'uid' => 2,
            'pid' => 1,
            'title' => 'Non-flat',
            'slug' => 'non-flat',
        ],
        'http://localhost/non-flat',
    ])]
    public function skipsOtherUrls(array $page, string $uri): void
    {
        $this->getConnectionPool()->getConnectionForTable('pages')->insert('pages', $page);

        $response = $this->executeFrontendSubRequest(new InternalRequest($uri));

        self::assertStringStartsNotWith('3', (string)$response->getStatusCode());
    }
}
