<?php

declare(strict_types=1);

namespace Pagemachine\FlatUrls\Page\Redirect;

use Pagemachine\FlatUrls\Page\Page;
use Psr\Http\Message\UriInterface;

final class Redirect
{
    public function __construct(
        private Page $page,
        private UriInterface $sourceUri,
        private UriInterface $targetUri,
        private int $statusCode,
    ) {}

    public function getPage(): Page
    {
        return $this->page;
    }

    public function getSourceUri(): UriInterface
    {
        return $this->sourceUri;
    }

    public function getTargetUri(): UriInterface
    {
        return $this->targetUri;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
