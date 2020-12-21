<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Hook\DataHandler;

use Pagemachine\FlatUrls\Page\MissingPageException;
use Pagemachine\FlatUrls\Page\PageCollection;
use Pagemachine\FlatUrls\Page\Redirect\Conflict\RedirectConflictDetector;
use Pagemachine\FlatUrls\Page\Redirect\Conflict\RedirectConflictResolver;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Handle redirects conflicting with the new slug of pages
 */
final class ResolveRedirectConflict
{
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

        $pageCollection = GeneralUtility::makeInstance(PageCollection::class);

        try {
            $page = $pageCollection->get((int)$uid);
        } catch (MissingPageException $e) {
            return;
        }

        $redirectConflictDetector = GeneralUtility::makeInstance(RedirectConflictDetector::class);
        $conflictRedirects = $redirectConflictDetector->detect($page);

        $redirectConflictResolver = GeneralUtility::makeInstance(RedirectConflictResolver::class);
        $redirectConflictResolver->resolve(...$conflictRedirects);
    }
}
