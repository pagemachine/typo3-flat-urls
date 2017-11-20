<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Page\Collection;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

use Pagemachine\FlatUrls\Page\Page;
use Pagemachine\FlatUrls\Page\PageOverlay;

/**
 * Collection of page overlays suitable for flat URLs
 */
class PageOverlayCollection extends AbstractPageCollection
{
    /**
     * @var Page
     */
    protected $page;

    /**
     * Limit the list of page overlays to a specific page
     *
     * @param Page $page a page
     * @return PageOverlayCollection
     */
    public function forPage(Page $page): self
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Update data of a page overlay
     *
     * @param PageOverlay $pageOverlay
     * @return void
     */
    public function update(PageOverlay $pageOverlay)
    {
        $this->updatePageRecord($pageOverlay);
    }

    /**
     * @return string
     */
    protected function getWhereClause(): string
    {
        $whereClause = parent::getWhereClause();

        if ($this->page !== null) {
            $whereClause .= ' AND pid = ' . (int)$this->page->getUid();
        }

        return $whereClause;
    }

    /**
     * @return string
     */
    protected function getPageType(): string
    {
        return PageOverlay::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string
    {
        return 'pages_language_overlay';
    }
}
