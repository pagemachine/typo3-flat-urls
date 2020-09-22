<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Page\Redirect;

use Pagemachine\FlatUrls\Page\Page;
use Psr\Http\Message\UriInterface;

final class Redirect
{
    /**
     * @var Page
     */
    private $page;

    /**
     * @var UriInterface
     */
    private $sourceUri;

    /**
     * @var UriInterface
     */
    private $targetUri;

    /**
     * @var int
     */
    private $statusCode;

    public function __construct(
        Page $page,
        UriInterface $sourceUri,
        UriInterface $targetUri,
        int $statusCode
    ) {
        $this->page = $page;
        $this->sourceUri = $sourceUri;
        $this->targetUri = $targetUri;
        $this->statusCode = $statusCode;
    }

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
