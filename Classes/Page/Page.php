<?php

declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Page;

final class Page
{
    /**
     * @var int
     */
    private $uid;

    public function __construct(int $uid)
    {
        $this->uid = $uid;
    }

    public function getUid(): int
    {
        return $this->uid;
    }
}
