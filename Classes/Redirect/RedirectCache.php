<?php

declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Redirect;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Redirects\Service\RedirectCacheService;

final class RedirectCache
{
    public function rebuild(): void
    {
        $redirectCacheService = GeneralUtility::makeInstance(RedirectCacheService::class);
        $redirectCacheService->rebuild();
    }
}
