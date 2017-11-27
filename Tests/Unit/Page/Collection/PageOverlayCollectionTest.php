<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Tests\Unit\Page\Collection;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

use Pagemachine\FlatUrls\Page\Collection\PageOverlayCollection;
use Pagemachine\FlatUrls\Page\Page;
use Pagemachine\FlatUrls\Page\PageOverlay;
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
 * Testcase for Pagemachine\FlatUrls\Page\Collection\PageOverlayCollection
 */
class PageOverlayCollectionTest extends UnitTestCase
{
    /**
     * @var PageOverlayCollection
     */
    protected $pageOverlayCollection;

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

        $GLOBALS['TCA']['pages_language_overlay']['ctrl']['delete'] = 'deleted';

        if (class_exists(ConnectionPool::class)) {
            /** @var ExpressionBuilder */
            $expressionBuilder = $this->prophesize(ExpressionBuilder::class);
            $expressionBuilder->eq('pages_language_overlay.deleted', 0)->willReturn('pages_language_overlay.deleted=0');

            /** @var QueryBuilder */
            $queryBuilder = $this->prophesize(QueryBuilder::class);
            $queryBuilder->expr()->willReturn($expressionBuilder->reveal());

            /** @var ConnectionPool */
            $connectionPool = $this->prophesize(ConnectionPool::class);
            $connectionPool->getQueryBuilderForTable('pages_language_overlay')->willReturn($queryBuilder->reveal());

            GeneralUtility::addInstance(ConnectionPool::class, $connectionPool->reveal());
        }

        $this->databaseConnection = $this->prophesize(DatabaseConnection::class);
        $this->dataHandler = $this->prophesize(DataHandler::class);
        $this->pageOverlayCollection = new PageOverlayCollection($this->databaseConnection->reveal(), $this->dataHandler->reveal());
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
    public function updatesPageOverlays()
    {
        $pageOverlay = $this->prophesize(PageOverlay::class);
        $pageOverlay->getUid()->willReturn(10);
        $pageOverlay->getPathSegment()->willReturn('test-foo');

        $expected = [
            'pages_language_overlay' => [
                10 => [
                    'tx_realurl_pathsegment' => 'test-foo',
                ],
            ],
        ];

        $this->pageOverlayCollection->update($pageOverlay->reveal());

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

        $this->databaseConnection->prepare_SELECTquery('uid, pid, title, tx_realurl_pathsegment', 'pages_language_overlay', Argument::containingString('pages_language_overlay.deleted=0'))->willReturn($statement->reveal());

        $pageOverlays = [];

        foreach ($this->pageOverlayCollection as $pageOverlay) {
            $pageOverlays[] = $pageOverlay;
        }

        $this->assertCount(2, $pageOverlays);
        $this->assertContainsOnlyInstancesOf(PageOverlay::class, $pageOverlays);

        $this->assertEquals(10, $pageOverlays[0]->getUid());
        $this->assertEquals(8, $pageOverlays[0]->getPid());
        $this->assertEquals('Foo', $pageOverlays[0]->getTitle());
        $this->assertEquals('test-foo', $pageOverlays[0]->getPathSegment());

        $this->assertEquals(11, $pageOverlays[1]->getUid());
        $this->assertEquals('Bar', $pageOverlays[1]->getTitle());
        $this->assertEquals(9, $pageOverlays[1]->getPid());
        $this->assertEquals('test-bar', $pageOverlays[1]->getPathSegment());
    }

    /**
     * @test
     */
    public function limitsIterationToOverlaysOfPage()
    {
        /** @var PreparedStatement */
        $statement = $this->prophesize(PreparedStatement::class);
        $statement->execute()->shouldBeCalled();
        $statement->fetch()->willReturn(
            ['uid' => 10, 'pid' => 8, 'title' => 'Foo', 'tx_realurl_pathsegment' => 'test-foo'],
            false
        );

        $this->databaseConnection->prepare_SELECTquery('uid, pid, title, tx_realurl_pathsegment', 'pages_language_overlay', Argument::allOf(
            Argument::containingString('pages_language_overlay.deleted=0'),
            Argument::containingString('pid = 8')
        ))->willReturn($statement->reveal());

        $page = $this->prophesize(Page::class);
        $page->getUid()->willReturn(8);

        $pageOverlays = [];

        foreach ($this->pageOverlayCollection->forPage($page->reveal()) as $pageOverlay) {
            $pageOverlays[] = $pageOverlay;
        }

        $this->assertCount(1, $pageOverlays);
        $this->assertContainsOnlyInstancesOf(PageOverlay::class, $pageOverlays);

        $this->assertEquals(10, $pageOverlays[0]->getUid());
        $this->assertEquals(8, $pageOverlays[0]->getPid());
        $this->assertEquals('Foo', $pageOverlays[0]->getTitle());
        $this->assertEquals('test-foo', $pageOverlays[0]->getPathSegment());
    }

    /**
     * @test
     */
    public function isCountable()
    {
        $this->databaseConnection->exec_SELECTcountRows('*', 'pages_language_overlay', Argument::containingString('pages_language_overlay.deleted=0'))->willReturn(10);

        $this->assertEquals(10, count($this->pageOverlayCollection));
    }

    /**
     * @test
     */
    public function skipsCountErrors()
    {
        $this->databaseConnection->exec_SELECTcountRows('*', 'pages_language_overlay', Argument::cetera())->willReturn(false);

        $this->assertSame(0, count($this->pageOverlayCollection));
    }

    /**
     * @test
     */
    public function limitsCountToOverlaysOfPage()
    {
        $this->databaseConnection->exec_SELECTcountRows('*', 'pages_language_overlay', Argument::allOf(
            Argument::containingString('pages_language_overlay.deleted=0'),
            Argument::containingString('pid = 8')
        ))->willReturn(1);

        $page = $this->prophesize(Page::class);
        $page->getUid()->willReturn(8);

        $this->assertEquals(1, count($this->pageOverlayCollection->forPage($page->reveal())));
    }
}
