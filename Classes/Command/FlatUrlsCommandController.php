<?php
declare(strict_types=1);

namespace Pagemachine\FlatUrls\Command;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

use Pagemachine\FlatUrls\Page\Collection\PageCollection;
use Pagemachine\FlatUrls\Page\Collection\PageOverlayCollection;
use Pagemachine\FlatUrls\Url\FlatUrlBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Commands for flat URL tasks
 */
class FlatUrlsCommandController extends CommandController
{
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
     * @param PageCollection|null $pageCollection
     * @param PageOverlayCollection|null $pageOverlayCollection
     * @param FlatUrlBuilder|null $flatUrlBuilder
     */
    public function __construct(
        PageCollection $pageCollection = null,
        PageOverlayCollection $pageOverlayCollection = null,
        FlatUrlBuilder $flatUrlBuilder = null
    ) {
        $this->pageCollection = $pageCollection ?: GeneralUtility::makeInstance(PageCollection::class);
        $this->pageOverlayCollection = $pageOverlayCollection ?: GeneralUtility::makeInstance(PageOverlayCollection::class);
        $this->flatUrlBuilder = $flatUrlBuilder ?: GeneralUtility::makeInstance(FlatUrlBuilder::class);
    }

    /**
     * Update flat URLs of all pages
     *
     * @return void
     */
    public function updateCommand()
    {
        $this->output->progressStart(count($this->pageCollection));

        foreach ($this->pageCollection as $page) {
            $flatUrl = $this->flatUrlBuilder->buildForPage($page);
            $page->setPathSegment($flatUrl);

            $this->pageCollection->update($page);

            foreach ($this->pageOverlayCollection->forPage($page) as $pageOverlay) {
                $flatUrl = $this->flatUrlBuilder->buildForPageOverlay($pageOverlay);
                $pageOverlay->setPathSegment($flatUrl);

                $this->pageOverlayCollection->update($pageOverlay);
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->output->outputLine();
    }
}
