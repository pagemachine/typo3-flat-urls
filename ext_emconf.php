<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Flat URLs',
    'description' => 'Flat URLs (like Stackoverflow) for TYPO3',
    'category' => 'misc',
    'author' => 'Mathias Brodala',
    'author_email' => 'mbrodala@pagemachine.de',
    'author_company' => 'Pagemachine AG',
    'state' => 'stable',
    'version' => '2.0.3',
    'constraints' => [
        'depends' => [
            'php' => '7.4.0-7.99.99',
            'typo3' => '9.5.0-11.5.99',
        ],
    ],
];
