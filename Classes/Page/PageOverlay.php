<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Page;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

/**
 * Data container for page overlays
 */
class PageOverlay extends AbstractPage
{
    /**
     * @return int
     */
    public function getUrlIdentifier(): int
    {
        return $this->pid;
    }
}
