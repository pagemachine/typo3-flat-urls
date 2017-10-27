<?php
namespace Pagemachine\FlatUrls\Functional;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for Page processing
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
        $rootPath = dirname(__FILE__, 3);
        if (!is_link(ORIGINAL_ROOT . 'typo3conf/ext/flat_urls')) {
            symlink($rootPath, ORIGINAL_ROOT . 'typo3conf/ext/flat_urls');
        }

        parent::setUp();
    }

    /**
     * Avoid serlialization of the test system object
     *
     * @see https://github.com/Nimut/testing-framework/pull/49
     * @return array
     */
    public function __sleep()
    {
        $objectVars = parent::__sleep();
        unset($objectVars['database']);

        return $objectVars;
    }

    /**
     * @test
     */
    public function setsPathSegmentBasedOnPageTitle()
    {
        $this->importDataSet('ntf://Database/pages.xml');
        $this->setUpBackendUserFromFixture(1);

        $dataMap = [
            'pages' => [
                1 => [
                    'title' => 'Here, check this out! Special & chars/characters: ä',
                ],
            ],
        ];
        /** @var \TYPO3\CMS\Core\DataHandling\DataHandler */
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->stripslashes_values = false;
        $dataHandler->start($dataMap, []);
        $dataHandler->process_datamap();

        $pageRecord = $this->getDatabaseConnection()->selectSingleRow('tx_realurl_pathsegment', 'pages', 'uid = 1');

        $this->assertEquals('1/here-check-this-out-special-chars-characters-ae', $pageRecord['tx_realurl_pathsegment']);
    }
}
