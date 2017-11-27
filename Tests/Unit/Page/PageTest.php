<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Tests\Unit\Page;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

use Pagemachine\FlatUrls\Page\Page;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Testcase for Pagemachine\FlatUrls\Page\Page
 */
class PageTest extends UnitTestCase
{
    /**
     * @test
     */
    public function hasUid()
    {
        $page = new Page();
        $page->setUid(10);

        $this->assertEquals(10, $page->getUid());
    }
    /**
     * @test
     */
    public function hasPid()
    {
        $page = new Page();
        $page->setPid(9);

        $this->assertEquals(9, $page->getPid());
    }

    /**
     * @test
     */
    public function hasTitle()
    {
        $page = new Page();
        $page->setTitle('Foo');

        $this->assertEquals('Foo', $page->getTitle());
    }

    /**
     * @test
     */
    public function hasPathSegment()
    {
        $page = new Page();
        $page->setPathSegment('Foo');

        $this->assertEquals('Foo', $page->getPathSegment());
    }
}
