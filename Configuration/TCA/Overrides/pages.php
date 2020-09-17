<?php

\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TCA']['pages'], [
    'columns' => [
        'slug' => [
            'config' => [
                'generatorOptions' => [
                    'fields' => [
                        'uid',
                        'title',
                    ],
                    'prefixParentPageSlug' => false,
                ],
            ],
        ],
    ],
]);
