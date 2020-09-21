<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Page\Redirect;

use Psr\Http\Message\UriInterface;

final class Redirect
{
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
        UriInterface $sourceUri,
        UriInterface $targetUri,
        int $statusCode
    ) {
        $this->sourceUri = $sourceUri;
        $this->targetUri = $targetUri;
        $this->statusCode = $statusCode;
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
