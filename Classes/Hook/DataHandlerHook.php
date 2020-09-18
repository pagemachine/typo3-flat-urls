<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Hook;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class DataHandlerHook
{
    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        string $id,
        array $data,
        DataHandler $dataHandler
    ): void {
        if ($status !== 'new' || $table !== 'pages') {
            return;
        }

        $helper = GeneralUtility::makeInstance(
            SlugHelper::class,
            $table,
            'slug',
            $GLOBALS['TCA']['pages']['columns']['slug']['config']
        );
        $data['uid'] = $dataHandler->substNEWwithIDs[$id];
        $data['slug'] = $helper->generate($data, $data['pid']);

        $dataHandler->updateDB($table, $data['uid'], $data);
    }
}
