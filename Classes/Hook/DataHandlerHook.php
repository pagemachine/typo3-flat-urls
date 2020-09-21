<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Hook;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class DataHandlerHook
{
    public function processDatamap_preProcessFieldArray(
        array &$data,
        string $table,
        string $uid,
        DataHandler $dataHandler
    ): void {
        if ($table !== 'pages') {
            return;
        }

        // Ensure slug is refreshed on every change
        $data['slug'] = '';
    }

    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        string $uid,
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
        $data['uid'] = $dataHandler->substNEWwithIDs[$uid];
        $data['slug'] = $helper->generate($data, $data['pid']);

        $dataHandler->updateDB($table, $data['uid'], $data);
    }
}
