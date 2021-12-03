<?php
defined('TYPO3') or die();

\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TYPO3_CONF_VARS'], [
    'SC_OPTIONS' => [
        't3lib/class.t3lib_tcemain.php' => [
            'processDatamapClass' => [
                1600781842 => \Pagemachine\FlatUrls\Hook\DataHandler\AmendSlug::class,
                1600781844 => \Pagemachine\FlatUrls\Hook\DataHandler\RefreshSlug::class,
                1600781845 => \Pagemachine\FlatUrls\Hook\DataHandler\AddRedirect::class,
                1600781926 => \Pagemachine\FlatUrls\Hook\DataHandler\ResolveRedirectConflict::class,
            ],
        ],
    ],
    'SYS' => [
        'formEngine' => [
            'nodeRegistry' => [
                1600432195 => [
                    'nodeName' => 'staticSlug',
                    'priority' => 50,
                    'class' => \Pagemachine\FlatUrls\Backend\Form\Element\StaticSlugElement::class,
                ],
            ],
        ],
    ],
]);
