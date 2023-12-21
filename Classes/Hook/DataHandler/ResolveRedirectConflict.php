<?php

declare(strict_types=1);

namespace Pagemachine\FlatUrls\Hook\DataHandler;

use Pagemachine\FlatUrls\Page\MissingPageException;
use Pagemachine\FlatUrls\Page\PageCollection;
use Pagemachine\FlatUrls\Page\Redirect\Conflict\RedirectConflictDetector;
use Pagemachine\FlatUrls\Page\Redirect\Conflict\RedirectConflictResolver;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Handle redirects conflicting with the new slug of pages
 */
final class ResolveRedirectConflict
{
    public function __construct(
        private PageCollection $pageCollection,
        private RedirectConflictDetector $redirectConflictDetector,
        private RedirectConflictResolver $redirectConflictResolver,
    ) {}

    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        string $uid,
        array $data,
        DataHandler $dataHandler
    ): void {
        if ($status !== 'update' || $table !== 'pages') {
            return;
        }

        try {
            $page = $this->pageCollection->get((int)$uid);
        } catch (MissingPageException $e) {
            return;
        }

        $conflictRedirects = $this->redirectConflictDetector->detect($page);

        $this->redirectConflictResolver->resolve(...$conflictRedirects);
    }
}
