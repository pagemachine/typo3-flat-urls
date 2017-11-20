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
     * @param int $uid
     * @return void
     */
    public function setUid(int $uid)
    {
        $this->uid = $uid;
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
     * @param int $pid
     * @return void
     */
    public function setPid(int $pid)
    {
        $this->pid = $pid;
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
     * @param string $title
     * @return void
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
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
