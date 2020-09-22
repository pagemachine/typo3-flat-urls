<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Page;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class PageCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var \Doctrine\DBAL\Driver\Statement
     */
    private $pages;

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
            ->execute();
    }

    /**
     * @return \Generator|Page[]
     */
    public function getIterator(): \Generator
    {
        foreach ($this->pages as $page) {
            yield new Page($page['uid']);
        }
    }

    public function count(): int
    {
        return $this->pages->rowCount();
    }
}
