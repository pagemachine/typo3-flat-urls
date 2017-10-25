<?php
namespace Pagemachine\FlatUrls\Url;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

use Pagemachine\FlatUrls\Page\Page;
use Pagemachine\FlatUrls\Page\PageInterface;
use Pagemachine\FlatUrls\Page\PageOverlay;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Builder for flat URLs
 */
class FlatUrlBuilder
{
    /**
     * @var DataHandler
     */
    protected $dataHandler;

    /**
     * @param DataHandler|null $dataHandler
     */
    public function __construct(DataHandler $dataHandler = null)
    {
        $this->dataHandler = $dataHandler ?: GeneralUtility::makeInstance(DataHandler::class);
    }

    /**
     * Build flat URL for a page
     *
     * @param Page $page a page
     * @return string
     */
    public function buildForPage(Page $page): string
    {
        $titlePathSegment = $this->convertTitleToPathSegment($page, 'pages');
        $flatUrl = sprintf('%d/%s', $page->getUid(), $titlePathSegment);

        return $flatUrl;
    }

    /**
     * Build flat URL for a page overlay
     *
     * @param PageOverlay $pageOverlay a page overlay
     * @return string
     */
    public function buildForPageOverlay(PageOverlay $pageOverlay): string
    {
        $titlePathSegment = $this->convertTitleToPathSegment($pageOverlay, 'pages_language_overlay');
        $flatUrl = sprintf('%d/%s', $pageOverlay->getPid(), $titlePathSegment);

        return $flatUrl;
    }

    /**
     * Convert a regular title to a path segment
     *
     * @param PageInterface $page a page or page overlay
     * @param string $table a table name
     * @return string
     */
    protected function convertTitleToPathSegment(PageInterface $page, $table): string
    {
        $result = $this->dataHandler->checkValue(
            $table,
            'tx_realurl_pathsegment',
            $page->getTitle(),
            $page->getUid(),
            'dummy',
            $page->getPid(),
            0
        );

        return $result['value'];
    }
}
