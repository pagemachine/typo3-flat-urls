<?php
namespace PAGEmachine\FlatUrls\Tests\Unit\Url;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

use Pagemachine\FlatUrls\Page\Page;
use Pagemachine\FlatUrls\Page\PageOverlay;
use Pagemachine\FlatUrls\Url\FlatUrlBuilder;
use Prophecy\Argument;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Testcase for Pagemachine\FlatUrls\Url\FlatUrlBuilder
 */
class FlatUrlBuilderTest extends UnitTestCase
{
    /**
     * @var FlatUrlBuilder
     */
    protected $flatUrlBuilder;

    /**
     * @var DataHandler|ObjectProphecy
     */
    protected $dataHandler;

    /**
     * Set up this testcase
     */
    protected function setUp()
    {
        $this->dataHandler = $this->prophesize(DataHandler::class);
        $this->flatUrlBuilder = new FlatUrlBuilder($this->dataHandler->reveal());
    }

    /**
     * @test
     */
    public function buildsFlatUrlForPages()
    {
        $page = $this->prophesize(Page::class);
        $page->getUid()->willReturn(10);
        $page->getPid()->willReturn(9);
        $page->getTitle()->willReturn('The Page');

        $this->dataHandler->checkValue('pages', 'tx_realurl_pathsegment', 'The Page', 10, Argument::any(), 9, Argument::any())->willReturn([
            'value' => 'the-page',
        ]);

        $this->assertEquals('10/the-page', $this->flatUrlBuilder->buildForPage($page->reveal()));
    }

    /**
     * @test
     */
    public function buildsFlatUrlForPageOverlays()
    {
        $pageOverlay = $this->prophesize(PageOverlay::class);
        $pageOverlay->getUid()->willReturn(11);
        $pageOverlay->getPid()->willReturn(10);
        $pageOverlay->getTitle()->willReturn('Die Seite');

        $this->dataHandler->checkValue('pages_language_overlay', 'tx_realurl_pathsegment', 'Die Seite', 11, Argument::any(), 10, Argument::any())->willReturn([
            'value' => 'die-seite',
        ]);

        $this->assertEquals('10/die-seite', $this->flatUrlBuilder->buildForPageOverlay($pageOverlay->reveal()));
    }
}
