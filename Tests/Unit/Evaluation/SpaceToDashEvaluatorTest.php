<?php
namespace PAGEmachine\FlatUrls\Tests\Unit\Evaluation;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

use Pagemachine\FlatUrls\Evaluation\SpaceToDashEvaluator;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * Testcase for Pagemachine\FlatUrls\Evaluation\SpaceToDashEvaluator
 */
class SpaceToDashEvaluatorTest extends UnitTestCase
{
    /**
     * @var SpaceToDashEvaluator
     */
    protected $spaceToDashEvaluator;

    /**
     * Set up this testcase
     */
    protected function setUp()
    {
        $this->spaceToDashEvaluator = new SpaceToDashEvaluator();
    }

    /**
     * @test
     * @dataProvider values
     *
     * @param string $value
     * @param string $expected
     */
    public function convertsSpacesToDashes($value, $expected)
    {
        $this->assertEquals($expected, $this->spaceToDashEvaluator->evaluateFieldValue($value));
    }

    /**
     * @return array
     */
    public function values()
    {
        return [
            'simple' => ['foo bar', 'foo-bar'],
            'with dashes' => ['working - indeed', 'working-indeed'],
            'with slashes' => ['maybe this / that', 'maybe-this-that'],
        ];
    }
}
