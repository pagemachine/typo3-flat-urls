<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Flat URLs',
    'description' => 'Flat URLs (like Stackoverflow) for TYPO3',
    'category' => 'misc',
    'author' => 'Mathias Brodala',
    'author_email' => 'mbrodala@pagemachine.de',
    'author_company' => 'Pagemachine AG',
    'state' => 'stable',
    'version' => '1.0.3',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.0-8.7.99',
            'realurl' => '2.0.0-2.99.99',
        ],
    ],
];
