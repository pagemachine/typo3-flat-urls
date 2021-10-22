<?php

declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Tests\Unit\Middleware;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pagemachine\FlatUrls\Middleware\FlatUrlRedirect;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Routing\InvalidRouteArgumentsException;
use TYPO3\CMS\Core\Routing\RouterInterface;
use TYPO3\CMS\Core\Routing\SiteRouteResult;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * Testcase for Pagemachine\FlatUrls\Middleware\FlatUrlRedirect
 */
final class FlatUrlRedirectTest extends UnitTestCase
{
    /**
     * @var FlatUrlRedirect
     */
    protected $flatUrlRedirect;

    /**
     * Set up this testcase
     */
    protected function setUp(): void
    {
        $this->flatUrlRedirect = new FlatUrlRedirect();
    }

    /**
     * @test
     */
    public function skipsNonNumericPaths(): void
    {
        $request = $this->prophesize(ServerRequestInterface::class);

        $routeResult = $this->prophesize(SiteRouteResult::class);
        $routeResult->getTail()->willReturn('foo/');
        $request->getAttribute('routing')->willReturn($routeResult->reveal());

        $handlerResponse = new Response();
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request->reveal())->willReturn($handlerResponse);

        $response = $this->flatUrlRedirect->process($request->reveal(), $handler->reveal());

        $this->assertSame($handlerResponse, $response);
    }

    /**
     * @test
     */
    public function handlesRoutingErrors(): void
    {
        $request = $this->prophesize(ServerRequestInterface::class);

        $routeResult = $this->prophesize(SiteRouteResult::class);
        $routeResult->getTail()->willReturn('10');
        $request->getAttribute('routing')->willReturn($routeResult->reveal());

        $siteLanguage = $this->prophesize(SiteLanguage::class);
        $request->getAttribute('language')->willReturn($siteLanguage->reveal());

        $site = $this->prophesize(Site::class);
        $request->getAttribute('site')->willReturn($site->reveal());

        $router = $this->prophesize(RouterInterface::class);
        $router->generateUri(Argument::cetera())->willThrow(InvalidRouteArgumentsException::class);
        $site->getRouter()->willReturn($router->reveal());

        $handlerResponse = new Response();
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request->reveal())->willReturn($handlerResponse);

        $this->flatUrlRedirect->setLogger(new NullLogger());

        $response = $this->flatUrlRedirect->process($request->reveal(), $handler->reveal());

        $this->assertSame($handlerResponse, $response);
    }

    /**
     * @test
     * @dataProvider paths
     */
    public function redirectsWithNumericPaths(string $path): void
    {
        $request = $this->prophesize(ServerRequestInterface::class);

        $routeResult = $this->prophesize(SiteRouteResult::class);
        $routeResult->getTail()->willReturn($path);
        $request->getAttribute('routing')->willReturn($routeResult->reveal());

        $siteLanguage = $this->prophesize(SiteLanguage::class);
        $request->getAttribute('language')->willReturn($siteLanguage->reveal());

        $site = $this->prophesize(Site::class);
        $request->getAttribute('site')->willReturn($site->reveal());

        $uri = new Uri('/10/test');

        $router = $this->prophesize(RouterInterface::class);
        $router->generateUri('10', ['_language' => $siteLanguage->reveal()])->willReturn($uri);
        $site->getRouter()->willReturn($router->reveal());

        $response = $this->flatUrlRedirect->process($request->reveal(), $this->prophesize(RequestHandlerInterface::class)->reveal());

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('/10/test', $response->getHeaderLine('location'));
    }

    public function paths(): \Generator
    {
        yield 'with trailing slash' => [
            '10/',
        ];

        yield 'without trailing slash' => [
            '10',
        ];
    }
}
