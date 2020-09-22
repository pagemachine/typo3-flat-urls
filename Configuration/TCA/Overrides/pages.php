<?php

\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TCA']['pages'], [
    'columns' => [
        'slug' => [
            'config' => [
                'renderType' => 'staticSlug',
                'generatorOptions' => [
                    'prefixParentPageSlug' => false,
                ],
            ],
        ],
    ],
]);
