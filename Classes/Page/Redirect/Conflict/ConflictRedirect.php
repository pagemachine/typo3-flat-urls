<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Page\Redirect\Conflict;

final class ConflictRedirect
{
    /**
     * @var int
     */
    private $uid;

    public function __construct(int $uid)
    {
        $this->uid = $uid;
    }

    /**
     * @return int
     */
    public function getUid(): int
    {
        return $this->uid;
    }
}
