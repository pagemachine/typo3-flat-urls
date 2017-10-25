<?php
namespace Pagemachine\FlatUrls\Page\Collection;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

use Pagemachine\FlatUrls\Page\PageInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * Base class for page collections
 */
abstract class AbstractPageCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var DatabaseConnection
     */
    protected $databaseConnection;

    /**
     * @param DatabaseConnection|null $databaseConnection
     */
    public function __construct(DatabaseConnection $databaseConnection = null)
    {
        $this->databaseConnection = $databaseConnection ?: $GLOBALS['TYPO3_DB'];
    }

    /**
     * @return \Traversable|PageInterface[]
     */
    public function getIterator(): \Traversable
    {
        $statement = $this->databaseConnection->prepare_SELECTquery('uid, pid, title, tx_realurl_pathsegment', $this->getTableName(), $this->getWhereClause());
        $statement->execute();
        $pageType = $this->getPageType();

        while (($row = $statement->fetch()) !== false) {
            $page = new $pageType();
            $page->setUid($row['uid']);
            $page->setPid($row['pid']);
            $page->setTitle($row['title']);
            $page->setPathSegment($row['tx_realurl_pathsegment']);

            yield $page;
        }
    }

    /**
     * @return int
     */
    public function count(): int
    {
        $count = $this->databaseConnection->exec_SELECTcountRows('*', $this->getTableName(), $this->getWhereClause());

        return $count;
    }

    /**
     * Update the database record for a page
     *
     * @param PageInterface $page a page
     * @param array $extraData additional record data
     * @return void
     */
    protected function updatePageRecord(PageInterface $page, array $extraData = [])
    {
        $data = ['tx_realurl_pathsegment' => $page->getPathSegment()];
        $data = array_replace($data, $extraData);

        $this->databaseConnection->exec_UPDATEquery($this->getTableName(), 'uid = ' . (int)$page->getUid(), $data);
    }

    /**
     * Get the where clause to retrieve records
     *
     * @return string
     */
    protected function getWhereClause(): string
    {
        $whereClause = '1=1' . BackendUtility::deleteClause($this->getTableName());

        return $whereClause;
    }

    /**
     * Get the collection page type
     *
     * @return string
     */
    abstract protected function getPageType(): string;

    /**
     * Get the database table name
     *
     * @return string
     */
    abstract protected function getTableName(): string;
}
