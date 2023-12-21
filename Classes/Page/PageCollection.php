<?php

declare(strict_types=1);

namespace Pagemachine\FlatUrls\Page;

use Doctrine\DBAL\Result;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class PageCollection implements \IteratorAggregate, \Countable
{
    private Result $pages;

    public function __construct(
        ConnectionPool $connectionPool,
        private PageRepository $pageRepository,
    ) {
        $queryBuilder = $connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $this->pages = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->executeQuery();
    }

    /**
     * @return \Generator|Page[]
     */
    public function getIterator(): \Generator
    {
        foreach ($this->pages->iterateAssociative() as $page) {
            yield new Page($page['uid']);
        }
    }

    public function count(): int
    {
        return $this->pages->rowCount();
    }

    /**
     * @throws MissingPageException if no page was found
     */
    public function get(int $pageId): Page
    {
        $page = $this->pageRepository->getPage($pageId);

        if (empty($page)) {
            throw MissingPageException::fromPageId($pageId);
        }

        return new Page((int)$page['uid']);
    }
}
