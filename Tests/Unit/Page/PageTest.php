<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Tests\Unit\Page;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pagemachine\FlatUrls\Page\Page;

/**
 * Testcase for Pagemachine\FlatUrls\Page\Page
 */
class PageTest extends UnitTestCase
{
    /**
     * @var Page
     */
    protected $page;

    /**
     * Set up this testcase
     */
    public function setUp()
    {
        $this->page = new Page(10, 9, 'Foo');
    }

    /**
     * @test
     */
    public function hasUid()
    {
        $this->assertEquals(10, $this->page->getUid());
    }
    /**
     * @test
     */
    public function hasPid()
    {
        $this->assertEquals(9, $this->page->getPid());
    }

    /**
     * @test
     */
    public function hasTitle()
    {
        $this->assertEquals('Foo', $this->page->getTitle());
    }

    /**
     * @test
     */
    public function hasPathSegment()
    {
        $this->page->setPathSegment('Foo');

        $this->assertEquals('Foo', $this->page->getPathSegment());
    }
}
