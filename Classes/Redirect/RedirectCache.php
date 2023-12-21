<?php

declare(strict_types=1);

namespace Pagemachine\FlatUrls\Redirect;

use TYPO3\CMS\Redirects\Service\RedirectCacheService;

final class RedirectCache
{
    public function __construct(
        private RedirectCacheService $redirectCacheService,
    ) {}

    public function rebuild(): void
    {
        $this->redirectCacheService->rebuildAll();
    }
}
