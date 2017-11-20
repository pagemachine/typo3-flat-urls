<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Tests\Functional;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for page processing
 */
class PageTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/realurl',
        'typo3conf/ext/flat_urls',
    ];

    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->setUpBackendUserFromFixture(1);
        \TYPO3\CMS\Core\Core\Bootstrap::getInstance()->initializeLanguageObject();
    }

    /**
     * @test
     */
    public function setsPathSegmentOfPages()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Database/pages.xml');

        $dataMap = [
            'pages' => [
                1 => [
                    'title' => 'Here, check this out! Special & chars/characters: ä',
                ],
                'NEWabc' => [
                    'pid' => 1,
                    'title' => 'Foo Bar',
                ],
            ],
        ];
        /** @var \TYPO3\CMS\Core\DataHandling\DataHandler */
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->stripslashes_values = false;
        $dataHandler->start($dataMap, []);
        $dataHandler->process_datamap();

        $pageRecord1 = $this->getDatabaseConnection()->selectSingleRow('tx_realurl_pathsegment', 'pages', 'uid = 1');

        $this->assertEquals('1/here-check-this-out-special-chars-characters-ae', $pageRecord1['tx_realurl_pathsegment']);

        $pageRecord2 = $this->getDatabaseConnection()->selectSingleRow('tx_realurl_pathsegment', 'pages', 'uid = 2');

        $this->assertEquals('2/foo-bar', $pageRecord2['tx_realurl_pathsegment']);
    }

    /**
     * @test
     */
    public function setsPathSegmentOfPageOverlays()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Database/pages.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/sys_language.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/pages_language_overlay.xml');

        $dataMap = [
            'pages_language_overlay' => [
                1 => [
                    'title' => 'Deutsche Wurzel',
                    'sys_language_uid' => 1,
                ],
                'NEWabc' => [
                    'pid' => 1,
                    'title' => 'Japanese Root',
                    'sys_language_uid' => 2,
                ],
            ],
        ];
        /** @var \TYPO3\CMS\Core\DataHandling\DataHandler */
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->stripslashes_values = false;
        $dataHandler->start($dataMap, []);
        $dataHandler->process_datamap();

        $pageOverlayRecord1 = $this->getDatabaseConnection()->selectSingleRow('tx_realurl_pathsegment', 'pages_language_overlay', 'uid = 1');

        $this->assertEquals('1/deutsche-wurzel', $pageOverlayRecord1['tx_realurl_pathsegment']);

        $pageOverlayRecord2 = $this->getDatabaseConnection()->selectSingleRow('tx_realurl_pathsegment', 'pages_language_overlay', 'uid = 2');

        $this->assertEquals('1/japanese-root', $pageOverlayRecord2['tx_realurl_pathsegment']);
    }
}
