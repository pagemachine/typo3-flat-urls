<?php

declare(strict_types=1);

namespace Pagemachine\FlatUrls\Hook\DataHandler;

use Pagemachine\FlatUrls\Page\MissingPageException;
use Pagemachine\FlatUrls\Page\PageCollection;
use Pagemachine\FlatUrls\Page\Redirect\BuildFailureException;
use Pagemachine\FlatUrls\Page\Redirect\RedirectBuilder;
use Pagemachine\FlatUrls\Page\Redirect\RedirectCollection;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Add redirect on page slug change
 */
final class AddRedirect implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private PageCollection $pageCollection,
        private RedirectBuilder $redirectBuilder,
        private RedirectCollection $redirectCollection,
    ) {}

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

        try {
            $page = $this->pageCollection->get((int)$uid);
        } catch (MissingPageException $e) {
            return;
        }

        try {
            $redirect = $this->redirectBuilder->build($page);
        } catch (BuildFailureException $e) {
            $this->logger->error($e->getMessage(), ['page' => $page->getUid()]);

            return;
        }

        $this->redirectCollection->add($redirect);
    }
}
