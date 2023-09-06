<?php

declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Page;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class PageCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var \Doctrine\DBAL\Result
     */
    private $pages;

    /**
     * @var PageRepository
     */
    private $pageRepository;

    public function __construct()
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $this->pages = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->executeQuery();
        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);
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
