<?php
namespace Pagemachine\FlatUrls\Tests\Unit\Hooks;

/*
 * This file is part of the PAGEmachine Flat URLs project.
 */

use Pagemachine\FlatUrls\Hooks\DataHandlerHook;
use Pagemachine\FlatUrls\Page\Collection\PageCollection;
use Pagemachine\FlatUrls\Page\Collection\PageOverlayCollection;
use Pagemachine\FlatUrls\Url\FlatUrlBuilder;
use Prophecy\Argument;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Testcase for Pagemachine\FlatUrls\Hooks\DataHandlerHook
 */
class DataHandlerHookTest extends UnitTestCase
{
    /**
     * @var DataHandlerHook
     */
    protected $dataHandlerHook;

    /**
     * @var DatabaseConnection|ObjectProphecy
     */
    protected $databaseConnection;

    /**
     * @var PageCollection|ObjectProphecy
     */
    protected $pageCollection;

    /**
     * @var PageOverlayCollection|ObjectProphecy
     */
    protected $pageOverlayCollection;

    /**
     * @var FlatUrlBuilder|ObjectProphecy
     */
    protected $flatUrlBuilder;

    /**
     * @var DataHandler|ObjectProphecy
     */
    protected $dataHandler;

    /**
     * Set up this testcase
     */
    public function setUp()
    {
        $this->databaseConnection = $this->prophesize(DatabaseConnection::class);
        $this->pageCollection = $this->prophesize(PageCollection::class);
        $this->pageOverlayCollection = $this->prophesize(PageOverlayCollection::class);
        $this->flatUrlBuilder = $this->prophesize(FlatUrlBuilder::class);

        $this->dataHandlerHook = new DataHandlerHook(
            $this->databaseConnection->reveal(),
            $this->pageCollection->reveal(),
            $this->pageOverlayCollection->reveal(),
            $this->flatUrlBuilder->reveal()
        );
        $this->dataHandler = $this->prophesize(DataHandler::class);
    }

    /**
     * @test
     */
    public function updatesPathSegmentOfNewPage()
    {
        $this->dataHandler->reveal()->substNEWwithIDs['NEWabc'] = 10;

        $this->flatUrlBuilder->buildForPage(Argument::allOf(
            Argument::which('getUid', 10),
            Argument::which('getPid', 9),
            Argument::which('getTitle', 'Foo Bar')
        ))->willReturn('10/foo-bar');

        $this->pageCollection->update(Argument::allOf(
            Argument::which('getUid', 10),
            Argument::which('getPathSegment', '10/foo-bar')
        ))->shouldBeCalled();

        $this->dataHandlerHook->processDatamap_afterDatabaseOperations(
            'new',
            'pages',
            'NEWabc',
            ['title' => 'Foo Bar', 'pid' => 9],
            $this->dataHandler->reveal()
        );
    }

    /**
     * @test
     */
    public function updatesPathSegmentOfUpdatedPage()
    {
        $this->databaseConnection->exec_SELECTgetSingleRow('pid', 'pages', 'uid = 10')->willReturn(['pid' => 9]);

        $this->flatUrlBuilder->buildForPage(Argument::allOf(
            Argument::which('getUid', 10),
            Argument::which('getPid', 9),
            Argument::which('getTitle', 'Foo Bar')
        ))->willReturn('10/foo-bar');

        $this->pageCollection->update(Argument::allOf(
            Argument::which('getUid', 10),
            Argument::which('getPathSegment', '10/foo-bar')
        ))->shouldBeCalled();

        $this->dataHandlerHook->processDatamap_afterDatabaseOperations(
            'update',
            'pages',
            10,
            ['title' => 'Foo Bar'],
            $this->dataHandler->reveal()
        );
    }

    /**
     * @test
     */
    public function updatesPathSegmentOfNewPageTranslation()
    {
        $this->databaseConnection->exec_SELECTgetSingleRow('pid', 'pages_language_overlay', 'uid = 10')->willReturn(['pid' => 9]);

        $this->flatUrlBuilder->buildForPageOverlay(Argument::allOf(
            Argument::which('getUid', 10),
            Argument::which('getPid', 9),
            Argument::which('getTitle', 'Foo Bar')
        ))->willReturn('9/foo-bar');

        $this->pageOverlayCollection->update(Argument::allOf(
            Argument::which('getUid', 10),
            Argument::which('getPathSegment', '9/foo-bar')
        ))->shouldBeCalled();

        $this->dataHandlerHook->processDatamap_afterDatabaseOperations(
            'update',
            'pages_language_overlay',
            10,
            ['title' => 'Foo Bar'],
            $this->dataHandler->reveal()
        );
    }

    /**
     * @test
     */
    public function updatesPathSegmentOfUpdatedPageTranslation()
    {
        $this->databaseConnection->exec_SELECTgetSingleRow('pid', 'pages_language_overlay', 'uid = 10')->willReturn(['pid' => 9]);

        $this->flatUrlBuilder->buildForPageOverlay(Argument::allOf(
            Argument::which('getUid', 10),
            Argument::which('getPid', 9),
            Argument::which('getTitle', 'Foo Bar')
        ))->willReturn('9/foo-bar');

        $this->pageOverlayCollection->update(Argument::allOf(
            Argument::which('getUid', 10),
            Argument::which('getPathSegment', '9/foo-bar')
        ))->shouldBeCalled();

        $this->dataHandlerHook->processDatamap_afterDatabaseOperations(
            'update',
            'pages_language_overlay',
            10,
            ['title' => 'Foo Bar'],
            $this->dataHandler->reveal()
        );
    }

    /**
     * @test
     */
    public function skipsOnNonPageTables()
    {
        $this->dataHandlerHook->processDatamap_afterDatabaseOperations(
            'update',
            'other_table',
            10,
            [],
            $this->dataHandler->reveal()
        );
    }

    /**
     * @test
     */
    public function skipsOnEmptyPageTitle()
    {
        $this->dataHandlerHook->processDatamap_afterDatabaseOperations(
            'update',
            'pages',
            10,
            [],
            $this->dataHandler->reveal()
        );
    }
}
