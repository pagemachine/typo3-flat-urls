<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Hook\DataHandler;

use Pagemachine\FlatUrls\Page\Page;
use Pagemachine\FlatUrls\Page\Redirect\RedirectBuilder;
use Pagemachine\FlatUrls\Page\Redirect\RedirectCollection;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Add redirect on page slug change
 */
final class AddRedirect
{
    public function processDatamap_postProcessFieldArray(
        string $status,
        string $table,
        string $uid,
        array $data,
        DataHandler $dataHandler
    ): void {
        if ($status !== 'update' || $table !== 'pages') {
            return;
        }

        if (empty($data['slug'] ?? null)) {
            return;
        }

        $redirectBuilder = GeneralUtility::makeInstance(RedirectBuilder::class);
        $redirect = $redirectBuilder->build(new Page((int)$uid));
        $redirectCollection = GeneralUtility::makeInstance(RedirectCollection::class);
        $redirectCollection->add($redirect);
    }
}
