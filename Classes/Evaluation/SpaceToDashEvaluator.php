<?php
namespace Pagemachine\FlatUrls\Evaluation;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

/**
 * Form value evaluator for URLs
 */
class SpaceToDashEvaluator
{
    /**
     * Convert spaces in value to dashes
     *
     * @param string $value
     * @return string
     */
    public function evaluateFieldValue($value): string
    {
        $value = str_replace([' ', '/'], '-', $value);
        // Avoid multiple subsequent dashes
        $value = preg_replace('/-{2,}/', '-', $value);

        return $value;
    }
}
