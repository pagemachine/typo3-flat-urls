<?php

declare(strict_types=1);

namespace Pagemachine\FlatUrls\Page;

use TYPO3\CMS\Core\Exception;

final class MissingPageException extends Exception
{
    public static function fromPageId(int $pageId): self
    {
        return new self(sprintf('Page with UID "%d" not found', $pageId), 1608559213);
    }
}
