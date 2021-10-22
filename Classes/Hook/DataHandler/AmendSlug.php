<?php

declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Hook\DataHandler;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Re-generate slug after storing page (now including UID)
 */
final class AmendSlug
{
    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        string $uid,
        array $data,
        DataHandler $dataHandler
    ): void {
        if ($table !== 'pages' || $status !== 'new') {
            return;
        }

        $helper = GeneralUtility::makeInstance(
            SlugHelper::class,
            $table,
            'slug',
            $GLOBALS['TCA']['pages']['columns']['slug']['config']
        );
        $data['uid'] = $dataHandler->substNEWwithIDs[$uid];
        $data['slug'] = $helper->generate($data, $data['pid']);

        $dataHandler->updateDB($table, $data['uid'], $data);
    }
}
