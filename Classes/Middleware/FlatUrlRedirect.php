<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Routing\InvalidRouteArgumentsException;
use TYPO3\CMS\Core\Utility\MathUtility;

final class FlatUrlRedirect implements MiddlewareInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $tail = $request->getAttribute('routing')->getTail();
        $pageUid = trim($tail, '/');

        if (!MathUtility::canBeInterpretedAsInteger($pageUid)) {
            return $handler->handle($request);
        }

        $router = $request->getAttribute('site')->getRouter();

        try {
            $uri = $router->generateUri(
                $pageUid,
                [
                    '_language' => $request->getAttribute('language'),
                ]
            );
        } catch (InvalidRouteArgumentsException $e) {
            $this->logger->warning(sprintf('Could not resolve full URI for "%s": %s', $tail, $e->getMessage()));

            return $handler->handle($request);
        }

        return new RedirectResponse(
            (string)$uri,
            303,
            [
                'X-Redirect-By' => 'Flat URL',
            ]
        );
    }
}
