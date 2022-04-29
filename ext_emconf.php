<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Flat URLs',
    'description' => 'Flat URLs (like Stackoverflow) for TYPO3',
    'category' => 'misc',
    'author' => 'Mathias Brodala',
    'author_email' => 'mbrodala@pagemachine.de',
    'author_company' => 'Pagemachine AG',
    'state' => 'stable',
    'version' => '3.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.5.99',
        ],
    ],
];
