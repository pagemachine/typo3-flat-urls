<?php

declare(strict_types = 1);

return [
    'frontend' => [
        'pagemachine/typo3-flat-urls/redirect' => [
            'target' => \Pagemachine\FlatUrls\Middleware\FlatUrlRedirect::class,
            'after' => [
                'typo3/cms-frontend/site',
            ],
            'before' => [
                'typo3/cms-frontend/base-redirect-resolver',
                'typo3/cms-frontend/static-route-resolver',
            ],
        ],
    ],
];
