<?php
namespace Pagemachine\FlatUrls\Tests\Unit\Page\Collection;

/*
 * This file is part of the PAGEmachine Flat URLs project.
 */

use Pagemachine\FlatUrls\Page\Collection\PageCollection;
use Pagemachine\FlatUrls\Page\Page;
use Prophecy\Argument;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Database\PreparedStatement;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for Pagemachine\FlatUrls\Page\Collection\PageCollection
 */
class PageCollectionTest extends UnitTestCase
{
    /**
     * @var PageCollection
     */
    protected $pageCollection;

    /**
     * @var DatabaseConnection|\Prophecy\Prophecy\ObjectProphecy
     */
    protected $databaseConnection;

    /**
     * @var DataHandler|\Prophecy\Prophecy\ObjectProphecy
     */
    protected $dataHandler;

    /**
     * @var array|null
     */
    protected $tcaBackup;

    /**
     * Set up this testcase
     */
    protected function setUp()
    {
        $this->tcaBackup = $GLOBALS['TCA'];

        $GLOBALS['TCA']['pages']['ctrl']['delete'] = 'deleted';

        if (class_exists(ConnectionPool::class)) {
            /** @var ExpressionBuilder */
            $expressionBuilder = $this->prophesize(ExpressionBuilder::class);
            $expressionBuilder->eq('pages.deleted', 0)->willReturn('pages.deleted=0');

            /** @var QueryBuilder */
            $queryBuilder = $this->prophesize(QueryBuilder::class);
            $queryBuilder->expr()->willReturn($expressionBuilder->reveal());

            /** @var ConnectionPool */
            $connectionPool = $this->prophesize(ConnectionPool::class);
            $connectionPool->getQueryBuilderForTable('pages')->willReturn($queryBuilder->reveal());

            GeneralUtility::addInstance(ConnectionPool::class, $connectionPool->reveal());
        }

        $this->databaseConnection = $this->prophesize(DatabaseConnection::class);
        $this->dataHandler = $this->prophesize(DataHandler::class);
        $this->pageCollection = new PageCollection($this->databaseConnection->reveal(), $this->dataHandler->reveal());
    }

    /**
     * Tear down this testcase
     */
    protected function tearDown()
    {
        if (empty($this->tcaBackup)) {
            unset($GLOBALS['TCA']);
        } else {
            $GLOBALS['TCA'] = $this->tcaBackup;
        }

        GeneralUtility::purgeInstances();
    }

    /**
     * @test
     */
    public function updatesPages()
    {
        $page = $this->prophesize(Page::class);
        $page->getUid()->willReturn(10);
        $page->getPathSegment()->willReturn('test-foo');

        $expected = [
            'pages' => [
                10 => [
                    'tx_realurl_pathsegment' => 'test-foo',
                    'tx_realurl_pathoverride' => 1,
                ],
            ],
        ];

        $this->pageCollection->update($page->reveal());

        $this->dataHandler->start($expected, [])->shouldHaveBeenCalled();
        $this->dataHandler->process_datamap()->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function isIterable()
    {
        /** @var PreparedStatement */
        $statement = $this->prophesize(PreparedStatement::class);
        $statement->execute()->shouldBeCalled();
        $statement->fetch()->willReturn(
            ['uid' => 10, 'pid' => 8, 'title' => 'Foo', 'tx_realurl_pathsegment' => 'test-foo'],
            ['uid' => 11, 'pid' => 9, 'title' => 'Bar', 'tx_realurl_pathsegment' => 'test-bar'],
            false
        );

        $this->databaseConnection->prepare_SELECTquery('uid, pid, title, tx_realurl_pathsegment', 'pages', Argument::allOf(
            Argument::containingString('doktype NOT IN (199, 255)'),
            Argument::containingString('pages.deleted=0')
        ))->willReturn($statement->reveal());

        $pages = [];

        foreach ($this->pageCollection as $page) {
            $pages[] = $page;
        }

        $this->assertCount(2, $pages);
        $this->assertContainsOnlyInstancesOf(Page::class, $pages);

        $this->assertEquals(10, $pages[0]->getUid());
        $this->assertEquals(8, $pages[0]->getPid());
        $this->assertEquals('Foo', $pages[0]->getTitle());
        $this->assertEquals('test-foo', $pages[0]->getPathSegment());

        $this->assertEquals(11, $pages[1]->getUid());
        $this->assertEquals('Bar', $pages[1]->getTitle());
        $this->assertEquals(9, $pages[1]->getPid());
        $this->assertEquals('test-bar', $pages[1]->getPathSegment());
    }

    /**
     * @test
     */
    public function isCountable()
    {
        $this->databaseConnection->exec_SELECTcountRows('*', 'pages', Argument::allOf(
            Argument::containingString('doktype NOT IN (199, 255)'),
            Argument::containingString('pages.deleted=0')
        ))->willReturn(10);

        $this->assertEquals(10, count($this->pageCollection));
    }
}
