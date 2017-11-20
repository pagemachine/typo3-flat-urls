<?php
declare(strict_types = 1);

namespace PAGEmachine\FlatUrls\Tests\Unit\Page;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

use Pagemachine\FlatUrls\Page\PageOverlay;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Testcase for Pagemachine\FlatUrls\Page\PageOverlay
 */
class PageOverlayTest extends UnitTestCase
{
    /**
     * @test
     */
    public function hasUid()
    {
        $page = new PageOverlay();
        $page->setUid(10);

        $this->assertEquals(10, $page->getUid());
    }
    /**
     * @test
     */
    public function hasPid()
    {
        $page = new PageOverlay();
        $page->setPid(9);

        $this->assertEquals(9, $page->getPid());
    }

    /**
     * @test
     */
    public function hasTitle()
    {
        $page = new PageOverlay();
        $page->setTitle('Foo');

        $this->assertEquals('Foo', $page->getTitle());
    }

    /**
     * @test
     */
    public function hasPathSegment()
    {
        $page = new PageOverlay();
        $page->setPathSegment('Foo');

        $this->assertEquals('Foo', $page->getPathSegment());
    }
}
