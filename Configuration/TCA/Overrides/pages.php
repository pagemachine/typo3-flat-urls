<?php

\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TCA']['pages'], [
    'columns' => [
        'slug' => [
            'config' => [
                'renderType' => 'staticSlug',
                'generatorOptions' => [
                    'prefixParentPageSlug' => false,
                    'postModifiers' => [
                        1600787429 => sprintf(
                            '%s->%s',
                            \Pagemachine\FlatUrls\Page\Slug\PageSlugModifier::class,
                            'prependUid'
                        ),
                    ],
                ],
            ],
        ],
    ],
]);
