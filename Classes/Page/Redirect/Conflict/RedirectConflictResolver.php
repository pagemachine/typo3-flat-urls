<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Page\Redirect\Conflict;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Redirects\Service\RedirectCacheService;

final class RedirectConflictResolver
{
    public function resolve(ConflictRedirect ...$conflictRedirects): void
    {
        $commands = [];

        foreach ($conflictRedirects as $conflictRedirect) {
            $commands['sys_redirect'][$conflictRedirect->getUid()]['delete'] = 1;
        }

        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([], $commands);
        $dataHandler->process_cmdmap();

        $redirectCacheService = GeneralUtility::makeInstance(RedirectCacheService::class);
        $redirectCacheService->rebuild();
    }
}
