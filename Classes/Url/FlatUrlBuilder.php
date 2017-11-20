<?php
declare(strict_types=1);

namespace Pagemachine\FlatUrls\Url;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

use Pagemachine\FlatUrls\Page\Page;
use Pagemachine\FlatUrls\Page\PageOverlay;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Builder for flat URLs
 */
class FlatUrlBuilder
{
    /**
     * @var CharsetConverter
     */
    private $charsetConverter;

    /**
     * @param CharsetConverter|null $charsetConverter
     */
    public function __construct(CharsetConverter $charsetConverter = null)
    {
        $this->charsetConverter = $charsetConverter ?: GeneralUtility::makeInstance(CharsetConverter::class);
    }

    /**
     * Build flat URL for a page
     *
     * @param Page $page a page
     * @return string
     */
    public function buildForPage(Page $page): string
    {
        $titlePathSegment = $this->convertToPathSegment($page->getTitle());
        $flatUrl = sprintf('%d/%s', $page->getUid(), $titlePathSegment);

        return $flatUrl;
    }

    /**
     * Build flat URL for a page overlay
     *
     * @param PageOverlay $pageOverlay a page overlay
     * @return string
     */
    public function buildForPageOverlay(PageOverlay $pageOverlay): string
    {
        $titlePathSegment = $this->convertToPathSegment($pageOverlay->getTitle());
        $flatUrl = sprintf('%d/%s', $pageOverlay->getPid(), $titlePathSegment);

        return $flatUrl;
    }

    /**
     * Convert a value to a path segment
     *
     * @param string $value a regular human-readable text
     * @return string
     */
    protected function convertToPathSegment(string $value): string
    {
        // Thanks to Dmitry Dulepov for this code
        $value = mb_strtolower($value, 'UTF-8');
        $value = strip_tags($value);
        $value = preg_replace('~[ \t\x{00A0}\-+_/]+~u', '-', $value);
        $value = $this->charsetConverter->specCharsToASCII('utf-8', $value);
        $value = preg_replace('/[^\p{L}0-9-]/u', '', $value);
        $value = preg_replace('/-{2,}/', '-', $value);
        $value = trim($value, '-');
        $value = strtolower($value);

        return $value;
    }
}
