<?php
defined('TYPO3_MODE') or die();

$GLOBALS['TYPO3_CONF_VARS']
    ['SC_OPTIONS']
        ['t3lib/class.t3lib_tcemain.php']
            ['processDatamapClass']
                [1600420737] = \Pagemachine\FlatUrls\Hook\DataHandlerHook::class;

$GLOBALS['TYPO3_CONF_VARS']
    ['SYS']
        ['formEngine']
            ['nodeRegistry']
                [1600432195] = [
                    'nodeName' => 'staticSlug',
                    'priority' => 50,
                    'class' => \Pagemachine\FlatUrls\Backend\Form\Element\StaticSlugElement::class,
                ];
