<?php
defined('TYPO3_MODE') or die();

$GLOBALS['TCA']['pages']['columns']['tx_realurl_pathsegment']['label'] = 'LLL:EXT:flat_urls/Resources/Private/Language/locallang_db.xlf:pages.tx_realurl_pathsegment';
$GLOBALS['TCA']['pages']['columns']['tx_realurl_pathsegment']['config']['readOnly'] = true;
unset($GLOBALS['TCA']['pages']['columns']['tx_realurl_pathsegment']['config']['eval']);
$GLOBALS['TCA']['pages']['columns']['tx_realurl_pathoverride']['config']['type'] = 'passthrough';
$GLOBALS['TCA']['pages']['columns']['tx_realurl_exclude']['config']['type'] = 'passthrough';
$GLOBALS['TCA']['pages']['columns']['tx_realurl_pathoverride']['config']['default'] = 1;
