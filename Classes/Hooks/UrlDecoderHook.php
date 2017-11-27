<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Hooks;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

use DmitryDulepov\Realurl\Cache\CacheFactory;
use DmitryDulepov\Realurl\Cache\CacheInterface;
use DmitryDulepov\Realurl\Decoder\UrlDecoder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Hook for the UrlDecoder
 */
class UrlDecoderHook
{
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @param CacheInterface|null $cache
     */
    public function __construct(CacheInterface $cache = null)
    {
        $this->cache = $cache ?: CacheFactory::getCache();
    }

    /**
     * Redirects from "10/" to "10/my-page/"
     *
     * @param array $parameters
     * @param UrlDecoder $urlDecoder
     * @return void
     */
    public function processRedirect(array $parameters, UrlDecoder $urlDecoder)
    {
        $pageId = $this->getPageIdFromUrl($parameters['URL']);

        if ($pageId < 1) {
            return;
        }

        $rootPageId = $this->getRootPageId($urlDecoder);
        $languageId = $this->getLanguageId();
        $pathCacheEntry = $this->cache->getPathFromCacheByPageId($rootPageId, $languageId, $pageId, '');

        if ($pathCacheEntry === null) {
            return;
        }

        $this->redirect($pathCacheEntry->getPagePath(), HttpUtility::HTTP_STATUS_301);
    }

    /**
     * Retrieve the page ID from a given URL
     *
     * @param string $url
     * @return int
     */
    protected function getPageIdFromUrl(string $url): int
    {
        $pageId = 0;

        // Only process urls like "42" or "42/"
        if (MathUtility::canBeInterpretedAsInteger($url) || MathUtility::canBeInterpretedAsInteger(substr($url, 0, -1))) {
            $pageId = (int)$url;
        }

        return $pageId;
    }

    /**
     * Retrieve the current rootpage ID
     *
     * @param UrlDecoder $urlDecoder
     * @return int
     */
    protected function getRootPageId(UrlDecoder $urlDecoder): int
    {
        // See https://ocramius.github.io/blog/accessing-private-php-class-members-without-reflection/
        // And https://github.com/dmitryd/typo3-realurl/issues/566
        $rootPageIdGetter = function (UrlDecoder $urlDecoder): int {
            return $urlDecoder->rootPageId;
        };
        $rootPageIdGetter = \Closure::bind($rootPageIdGetter, null, $urlDecoder);
        $rootPageId = $rootPageIdGetter($urlDecoder);

        return $rootPageId;
    }

    /**
     * Retrieve the current language ID
     *
     * @return int
     */
    protected function getLanguageId(): int
    {
        $languageId = (int)GeneralUtility::_GP('L');

        return $languageId;
    }

    /**
     * Small wrapper for tests
     *
     * @param string $url
     * @param string $httpStatus
     * @return void
     */
    protected function redirect(string $url, string $httpStatus)
    {
        HttpUtility::redirect($url, $httpStatus);
    }
}
