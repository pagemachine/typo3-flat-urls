<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Page\Collection;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

use Pagemachine\FlatUrls\Page\Page;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Collection of pages suitable for flat URLs
 */
class PageCollection extends AbstractPageCollection
{
    /**
     * Update data of a page
     *
     * @param Page $page
     * @return void
     */
    public function update(Page $page)
    {
        $this->updatePageRecord($page, ['tx_realurl_pathoverride' => 1]);
    }

    /**
     * @return string
     */
    protected function getWhereClause(): string
    {
        // RealURL also skips these page types
        $excludedDoktypes = implode(', ', [PageRepository::DOKTYPE_SPACER, PageRepository::DOKTYPE_RECYCLER]);
        $whereClause = parent::getWhereClause() . ' AND doktype NOT IN (' . $excludedDoktypes . ')';

        return $whereClause;
    }

    /**
     * @return string
     */
    protected function getPageType(): string
    {
        return Page::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string
    {
        return 'pages';
    }
}
