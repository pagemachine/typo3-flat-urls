<?php

declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Hook\DataHandler;

use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Ensure slug is refreshed on every page change
 */
final class RefreshSlug
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

        $data['slug'] = '';
    }
}
