<?php
namespace Pagemachine\FlatUrls\Evaluation;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Form value evaluator for path segments
 */
class PathSegmentEvaluator
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
     * Convert value to a suitable path segment
     *
     * @param string $value
     * @return string
     */
    public function evaluateFieldValue($value): string
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
