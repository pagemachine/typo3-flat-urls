<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Tests\Unit\Command;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

use Pagemachine\FlatUrls\Command\FlatUrlsCommandController;
use Pagemachine\FlatUrls\Page\Collection\PageCollection;
use Pagemachine\FlatUrls\Page\Collection\PageOverlayCollection;
use Pagemachine\FlatUrls\Page\Page;
use Pagemachine\FlatUrls\Page\PageOverlay;
use Pagemachine\FlatUrls\Url\FlatUrlBuilder;
use Prophecy\Argument;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Extbase\Mvc\Cli\ConsoleOutput;

/**
 * Testcase for Pagemachine\FlatUrls\Command\FlatUrlsCommandController
 *
 * @todo turn this into a functional test
 */
class FlatUrlsCommandControllerTest extends UnitTestCase
{
    /**
     * @var FlatUrlsCommandController
     */
    protected $flatUrlsCommandController;

    /**
     * @var PageCollection|\Prophecy\Prophecy\ObjectProphecy
     */
    protected $pageCollection;

    /**
     * @var PageOverlayCollection|\Prophecy\Prophecy\ObjectProphecy
     */
    protected $pageOverlayCollection;

    /**
     * @var FlatUrlBuilder|\Prophecy\Prophecy\ObjectProphecy
     */
    protected $flatUrlBuilder;

    /**
     * Set up this testcase
     */
    protected function setUp()
    {
        $this->pageCollection = $this->prophesize(PageCollection::class);
        $this->pageOverlayCollection = $this->prophesize(PageOverlayCollection::class);
        $this->flatUrlBuilder = $this->prophesize(FlatUrlBuilder::class);
        $this->flatUrlsCommandController = new FlatUrlsCommandController(
            $this->pageCollection->reveal(),
            $this->pageOverlayCollection->reveal(),
            $this->flatUrlBuilder->reveal()
        );

        $this->inject($this->flatUrlsCommandController, 'output', $this->prophesize(ConsoleOutput::class)->reveal());
    }

    /**
     * @test
     */
    public function updatesAllPages()
    {
        $page1 = $this->prophesize(Page::class);
        $page1->setPathSegment('1/page')->shouldBeCalled();
        $page2 = $this->prophesize(Page::class);
        $page2->setPathSegment('2/page')->shouldBeCalled();

        $this->flatUrlBuilder->buildForPage($page1->reveal())->willReturn('1/page');
        $this->flatUrlBuilder->buildForPage($page2->reveal())->willReturn('2/page');

        $this->pageCollection->count()->willReturn(2);
        $this->pageCollection->getIterator()->willReturn(new \ArrayObject([
            $page1->reveal(),
            $page2->reveal(),
        ]));
        $this->pageCollection->update($page1->reveal())->shouldBeCalled();
        $this->pageCollection->update($page2->reveal())->shouldBeCalled();

        $this->pageOverlayCollection->forPage(Argument::any())->willReturn($this->pageOverlayCollection->reveal());
        $this->pageOverlayCollection->getIterator()->willReturn(new \ArrayObject());

        $this->flatUrlsCommandController->updateCommand();
    }

    /**
     * @test
     */
    public function updatesAllPageOverlaysOfPages()
    {
        $page = $this->prophesize(Page::class);
        $page->setPathSegment('1/page')->shouldBeCalled();
        $pageOverlay1 = $this->prophesize(PageOverlay::class);
        $pageOverlay1->setPathSegment('1/page-overlay')->shouldBeCalled();
        $pageOverlay2 = $this->prophesize(PageOverlay::class);
        $pageOverlay2->setPathSegment('2/page-overlay')->shouldBeCalled();

        $this->flatUrlBuilder->buildForPage($page->reveal())->willReturn('1/page');
        $this->flatUrlBuilder->buildForPage($pageOverlay1->reveal())->willReturn('1/page-overlay');
        $this->flatUrlBuilder->buildForPage($pageOverlay2->reveal())->willReturn('2/page-overlay');

        $this->pageCollection->count()->willReturn(1);
        $this->pageCollection->getIterator()->willReturn(new \ArrayObject([$page->reveal()]));
        $this->pageCollection->update($page->reveal())->shouldBeCalled();

        $this->pageOverlayCollection->forPage($page->reveal())->willReturn($this->pageOverlayCollection->reveal());
        $this->pageOverlayCollection->getIterator()->willReturn(new \ArrayObject([
            $pageOverlay1->reveal(),
            $pageOverlay2->reveal(),
        ]));
        $this->pageOverlayCollection->update($pageOverlay1->reveal())->shouldBeCalled();
        $this->pageOverlayCollection->update($pageOverlay2->reveal())->shouldBeCalled();

        $this->flatUrlsCommandController->updateCommand();
    }
}
