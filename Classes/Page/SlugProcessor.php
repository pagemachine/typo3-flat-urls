<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Page;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class SlugProcessor
{
    /**
     * Value to reset slugs and force generation
     *
     * @see DataHandler::checkValueForSlug()
     */
    private const SLUG_RESET_VALUE = '';

    /**
     * @var DataHandler
     */
    private $dataHandler;

    public function __construct()
    {
        $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
    }

    public function update(Page $page): void
    {
        $data = [
            'pages' => [
                $page->getUid() => [
                    'slug' => self::SLUG_RESET_VALUE,
                ],
            ],
        ];

        $this->dataHandler->start($data, []);
        $this->dataHandler->process_datamap();
    }
}
