<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Tests\Unit\Hooks;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

use DmitryDulepov\Realurl\Cache\CacheInterface;
use DmitryDulepov\Realurl\Cache\PathCacheEntry;
use DmitryDulepov\Realurl\Decoder\UrlDecoder;
use Pagemachine\FlatUrls\Hooks\UrlDecoderHook;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;

/**
 * Testcase for Pagemachine\FlatUrls\Hooks\UrlDecoderHook
 */
class UrlDecoderHookTest extends UnitTestCase
{
    /**
     * @var UrlDecoderHook|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlDecoderHook;

    /**
     * @var CacheInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    protected $cache;

    /**
     * Set up this testcase
     */
    protected function setUp()
    {
        $this->cache = $this->prophesize(CacheInterface::class);
        $this->urlDecoderHook = $this->getMockBuilder(UrlDecoderHook::class)
            ->setConstructorArgs([$this->cache->reveal()])
            ->setMethods(['redirect'])
            ->getMock();
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
     * @dataProvider urls
     *
     * @param string $url
     */
    public function redirectsFromPageIdToFullUrl(string $url)
    {
        /** @var UrlDecoder */
        $urlDecoder = $this->prophesize(UrlDecoder::class)->reveal();
        $this->inject($urlDecoder, 'rootPageId', 1);
        $_GET['L'] = 2;

        /** @var PathCacheEntry|\Prophecy\Prophecy\ObjectProphecy */
        $pathCacheEntry = $this->prophesize(PathCacheEntry::class);
        $pathCacheEntry->getPagePath()->willReturn('10/my-page/');
        $this->cache->getPathFromCacheByPageId(1, 2, 10, null)->willReturn($pathCacheEntry->reveal());

        $this->urlDecoderHook->expects($this->once())->method('redirect')->with('10/my-page/', HttpUtility::HTTP_STATUS_301);

        $parameters = [
            'URL' => $url,
        ];

        $this->urlDecoderHook->processRedirect($parameters, $urlDecoder);
    }

    /**
     * @return \Traversable
     */
    public function urls(): \Traversable
    {
        yield 'with trailing slash' => ['10/'];
        yield 'without trailing slash' => ['10'];
    }

    /**
     * @test
     */
    public function skipsUrlsWithoutPathCacheEntry()
    {
        /** @var UrlDecoder */
        $urlDecoder = $this->prophesize(UrlDecoder::class)->reveal();
        $this->inject($urlDecoder, 'rootPageId', 1);
        $_GET['L'] = 2;

        $this->cache->getPathFromCacheByPageId(1, 2, 10, null)->willReturn(null);

        $this->urlDecoderHook->expects($this->never())->method('redirect');

        $parameters = [
            'URL' => '10/',
        ];

        $this->urlDecoderHook->processRedirect($parameters, $urlDecoder);
    }
}
