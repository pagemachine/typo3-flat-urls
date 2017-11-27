<?php
declare(strict_types = 1);

namespace Pagemachine\FlatUrls\Page;

/*
 * This file is part of the Pagemachine Flat URLs project.
 */

/**
 * Base class for pages
 */
abstract class AbstractPage implements PageInterface
{
    /**
     * Create a new page
     *
     * @param int $uid
     * @param int $pid
     * @param string $title
     * @param string|null $pathSegment
     */
    public function __construct(int $uid, int $pid, string $title, string $pathSegment = null)
    {
        $this->uid = $uid;
        $this->pid = $pid;
        $this->title = $title;
        $this->pathSegment = $pathSegment;
    }

    /**
     * @var int $uid
     */
    protected $uid;

    /**
     * @return int
     */
    public function getUid(): int
    {
        return $this->uid;
    }

    /**
     * @var int $pid
     */
    protected $pid;

    /**
     * @return int
     */
    public function getPid(): int
    {
        return $this->pid;
    }

    /**
     * @return int
     */
    public function getUrlIdentifier(): int
    {
        return $this->uid;
    }

    /**
     * @var string $title
     */
    protected $title;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @var string $pathSegment
     */
    protected $pathSegment;

    /**
     * @return string
     */
    public function getPathSegment(): string
    {
        return $this->pathSegment;
    }

    /**
     * @param string $pathSegment
     * @return void
     */
    public function setPathSegment(string $pathSegment)
    {
        $this->pathSegment = $pathSegment;
    }
}
