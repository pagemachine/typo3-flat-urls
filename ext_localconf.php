<?php
defined('TYPO3_MODE') or die();

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['flat_urls'] = \Pagemachine\FlatUrls\Hooks\DataHandlerHook::class;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl']['decodeSpURL_preProc']['flat_urls'] = \Pagemachine\FlatUrls\Hooks\UrlDecoderHook::class . '->processRedirect';

if (TYPO3_MODE === 'BE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = \Pagemachine\FlatUrls\Command\FlatUrlsCommandController::class;
}
