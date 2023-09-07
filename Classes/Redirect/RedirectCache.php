<?php

declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Redirect;

use TYPO3\CMS\Redirects\Service\RedirectCacheService;

final class RedirectCache
{
    private RedirectCacheService $redirectCacheService;

    public function __construct(
        RedirectCacheService $redirectCacheService
    ) {
        $this->redirectCacheService = $redirectCacheService;
    }

    public function rebuild(): void
    {
        $this->redirectCacheService->rebuildAll();
    }
}
