<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Tests\Unit\Page\Slug;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pagemachine\FlatUrls\Page\Slug\PageSlugModifier;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for Pagemachine\FlatUrls\Page\Slug\PageSlugModifier
 */
final class PageSlugModifierTest extends UnitTestCase
{
    /**
     * @var PageSlugModifier
     */
    protected $pageSlugModifier;

    /**
     * Set up this testcase
     */
    protected function setUp()
    {
        $this->pageSlugModifier = new PageSlugModifier();
    }

    /**
     * Tear down this testcase
     */
    protected function tearDown()
    {
        GeneralUtility::purgeInstances();
    }

    /**
     * @test
     */
    public function skipsUnrelatedTables(): void
    {
        $parameters = [
            'slug' => '/test',
            'tableName' => 'sys_category',
        ];

        $result = $this->pageSlugModifier->prependUid($parameters);

        $this->assertEquals('/test', $result);
    }

    /**
     * @test
     * @dataProvider pagesWithoutUid
     */
    public function skipsNewPagesWithoutUid(array $page): void
    {
        $parameters = [
            'slug' => '/test',
            'tableName' => 'pages',
        ];

        $result = $this->pageSlugModifier->prependUid($parameters);

        $this->assertEquals('/test', $result);
    }

    public function pagesWithoutUid(): \Generator
    {
        yield 'new page' => [
            [
                'sys_language_uid' => 0,
            ],
        ];

        yield 'new translated page' => [
            [
                'sys_language_uid' => 1,
                'l10n_parent' => 10,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider pages
     */
    public function prependsPageUid(array $page): void
    {
        $parameters = [
            'slug' => '/test',
            'tableName' => 'pages',
            'record' => $page,
        ];

        $result = $this->pageSlugModifier->prependUid($parameters);

        $this->assertEquals('/10/test', $result);
    }

    public function pages(): \Generator
    {
        yield 'page' => [
            [
                'uid' => 10,
                'sys_language_uid' => 0,
            ],
        ];

        yield 'translated page' => [
            [
                'uid' => 11,
                'sys_language_uid' => 1,
                'l10n_parent' => 10,
            ],
        ];
    }

    /**
     * @test
     */
    public function respectsFieldSeparator(): void
    {
        $parameters = [
            'slug' => ':test',
            'tableName' => 'pages',
            'record' => [
                'uid' => 10,
                'sys_language_uid' => 0,
            ],
            'configuration' => [
                'generatorOptions' => [
                    'fieldSeparator' => ':',
                ],
            ],
        ];

        $result = $this->pageSlugModifier->prependUid($parameters);
        $expected = ':10:test';

        $this->assertEquals($expected, $result);
    }
}
