<?php

\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TCA']['pages'], [
    'columns' => [
        'slug' => [
            'config' => [
                'renderType' => 'staticSlug',
                'generatorOptions' => [
                    'fields' => [
                        ['l10n_parent', 'uid'],
                        'title',
                    ],
                    'prefixParentPageSlug' => false,
                ],
            ],
        ],
    ],
]);
