<?php
namespace PAGEmachine\FlatUrls\Tests\Unit\Evaluation;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

use Pagemachine\FlatUrls\Evaluation\PathSegmentEvaluator;
use Prophecy\Argument;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for Pagemachine\FlatUrls\Evaluation\PathSegmentEvaluator
 */
class PathSegmentEvaluatorTest extends UnitTestCase
{
    /**
     * @var PathSegmentEvaluator
     */
    protected $pathSegmentEvaluator;

    /**
     * Set up this testcase
     */
    protected function setUp()
    {
        /** @var CharsetConverter */
        $charsetConverter = $this->prophesize(CharsetConverter::class);
        $charsetConverter->specCharsToASCII('utf-8', Argument::type('string'))->willReturnArgument(1);

        $this->pathSegmentEvaluator = new PathSegmentEvaluator($charsetConverter->reveal());
    }

    /**
     * Tear down this testcase
     */
    protected function tearDown()
    {
        GeneralUtility::purgeInstances();
    }

    /**
     * @test
     * @dataProvider values
     *
     * @param string $value
     * @param string $expected
     */
    public function convertsValueToPathSegment(string $value, string $expected)
    {
        $result = $this->pathSegmentEvaluator->evaluateFieldValue($value);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function values(): array
    {
        return [
            'simple' => ['Foo BAR', 'foo-bar'],
            'with dashes' => ['a - b -- c -', 'a-b-c'],
            'with special characters' => ['a & b / c + d? e, f!', 'a-b-c-d-e-f'],
        ];
    }
}
