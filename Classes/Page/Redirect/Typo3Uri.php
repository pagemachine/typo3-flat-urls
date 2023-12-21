<?php

declare(strict_types=1);

namespace Pagemachine\FlatUrls\Page\Redirect;

use TYPO3\CMS\Core\Http\Uri;

/**
 * Special support for internal "t3://" URIs
 */
final class Typo3Uri extends Uri
{
    public function __construct(string $uri = '')
    {
        $this->supportedSchemes = [
            't3' => 0,
        ];

        parent::__construct($uri);
    }
}
