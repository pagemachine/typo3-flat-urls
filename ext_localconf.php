<?php

use Pagemachine\FlatUrls\Backend\Form\Element\StaticSlugElement;
use Pagemachine\FlatUrls\Hook\DataHandler\AddRedirect;
use Pagemachine\FlatUrls\Hook\DataHandler\AmendSlug;
use Pagemachine\FlatUrls\Hook\DataHandler\RefreshSlug;
use Pagemachine\FlatUrls\Hook\DataHandler\ResolveRedirectConflict;
use TYPO3\CMS\Core\Utility\ArrayUtility;

defined('TYPO3') or die();

ArrayUtility::mergeRecursiveWithOverrule($GLOBALS['TYPO3_CONF_VARS'], [
    'SC_OPTIONS' => [
        't3lib/class.t3lib_tcemain.php' => [
            'processDatamapClass' => [
                1600781842 => AmendSlug::class,
                1600781844 => RefreshSlug::class,
                1600781845 => AddRedirect::class,
                1600781926 => ResolveRedirectConflict::class,
            ],
        ],
    ],
    'SYS' => [
        'formEngine' => [
            'nodeRegistry' => [
                1600432195 => [
                    'nodeName' => 'staticSlug',
                    'priority' => 50,
                    'class' => StaticSlugElement::class,
                ],
            ],
        ],
    ],
]);
