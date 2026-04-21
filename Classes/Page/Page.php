<?php

declare(strict_types=1);

namespace Pagemachine\FlatUrls\Page;

final readonly class Page
{
    public function __construct(
        private int $uid,
    ) {}

    public function getUid(): int
    {
        return $this->uid;
    }
}
