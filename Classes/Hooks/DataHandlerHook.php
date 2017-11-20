<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Hooks;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

use Pagemachine\FlatUrls\Page\Collection\PageCollection;
use Pagemachine\FlatUrls\Page\Collection\PageOverlayCollection;
use Pagemachine\FlatUrls\Page\Page;
use Pagemachine\FlatUrls\Page\PageOverlay;
use Pagemachine\FlatUrls\Url\FlatUrlBuilder;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Hook for the data handler
 */
class DataHandlerHook
{
    /**
     * @var DatabaseConnection
     */
    protected $databaseConnection;

    /**
     * @var PageCollection
     */
    protected $pageCollection;

    /**
     * @var PageOverlayCollection
     */
    protected $pageOverlayCollection;

    /**
     * @var FlatUrlBuilder
     */
    protected $flatUrlBuilder;

    /**
     * @param DatabaseConnection|null $databaseConnection
     * @param PageCollection|null $pageCollection
     * @param PageOverlayCollection|null $pageOverlayCollection
     * @param FlatUrlBuilder|null $flatUrlBuilder
     */
    public function __construct(
        DatabaseConnection $databaseConnection = null,
        PageCollection $pageCollection = null,
        PageOverlayCollection $pageOverlayCollection = null,
        FlatUrlBuilder $flatUrlBuilder = null
    ) {
        $this->databaseConnection = $databaseConnection ?: $GLOBALS['TYPO3_DB'];
        $this->pageCollection = $pageCollection ?: GeneralUtility::makeInstance(PageCollection::class);
        $this->pageOverlayCollection = $pageOverlayCollection ?: GeneralUtility::makeInstance(PageOverlayCollection::class);
        $this->flatUrlBuilder = $flatUrlBuilder ?: GeneralUtility::makeInstance(FlatUrlBuilder::class);
    }

    /**
     * Override and enforce the path segment for pages
     *
     * @param string $status
     * @param string $table
     * @param int $uid
     * @param array $data
     * @param DataHandler $dataHandler
     * @return void
     */
    public function processDatamap_afterDatabaseOperations($status, $table, $uid, $data, DataHandler $dataHandler)
    {
        if (!in_array($table, ['pages', 'pages_language_overlay'], true)) {
            return;
        }

        if (!isset($data['title'])) {
            return;
        }

        if (!MathUtility::canBeInterpretedAsInteger($uid)) {
            $uid = $dataHandler->substNEWwithIDs[$uid];
        }

        if (!empty($data['pid'])) {
            $pid = $data['pid'];
        } else {
            $row = $this->databaseConnection->exec_SELECTgetSingleRow('pid', $table, 'uid = ' . (int)$uid);
            $pid = $row['pid'];
        }

        if ($table === 'pages') {
            $page = new Page();
            $page->setUid((int)$uid);
            $page->setPid((int)$pid);
            $page->setTitle($data['title']);

            $flatUrl = $this->flatUrlBuilder->buildForPage($page);
            $page->setPathSegment($flatUrl);

            $this->pageCollection->update($page);
        } else {
            $pageOverlay = new PageOverlay();
            $pageOverlay->setUid((int)$uid);
            $pageOverlay->setPid((int)$pid);
            $pageOverlay->setTitle($data['title']);

            $flatUrl = $this->flatUrlBuilder->buildForPageOverlay($pageOverlay);
            $pageOverlay->setPathSegment($flatUrl);

            $this->pageOverlayCollection->update($pageOverlay);
        }
    }
}
