<?php

declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Page\Redirect;

use Pagemachine\FlatUrls\Redirect\RedirectCache;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

final class RedirectCollection
{
    private RedirectCache $redirectCache;

    public function __construct(
        RedirectCache $redirectCache
    ) {
        $this->redirectCache = $redirectCache;
    }

    public function add(Redirect $redirect): void
    {
        $data = [
            'sys_redirect' => [
                StringUtility::getUniqueId('NEW') => [
                    'pid' => 0,
                    'source_host' => $redirect->getSourceUri()->getAuthority() ?: '*',
                    'source_path' => $redirect->getSourceUri()->getPath(),
                    'target' => (string)$redirect->getTargetUri(),
                    'target_statuscode' => $redirect->getStatusCode(),
                ],
            ],
        ];

        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start($data, []);
        $dataHandler->process_datamap();

        $this->redirectCache->rebuild();
    }
}
