<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Page\Redirect;

use TYPO3\CMS\Core\Http\Uri;

/**
 * Special support for internal "t3://" URIs
 */
final class Typo3Uri extends Uri
{
    /**
     * @var int[] Associative array containing schemes and their default ports.
     */
    protected $supportedSchemes = [
        't3'  => 0,
    ];
}
