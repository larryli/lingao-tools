<?php

namespace larryli\lingao;

class Story
{
    public static $stories = [];

    public $path;
    public $category;
    public $title;

    public static function getStoryByPath($path)
    {
        return isset(self::$stories[$path]) ? self::$stories[$path] : null;
    }

    public static function addStory($path, $category, $title)
    {
        if (!isset(self::$stories[$path])) {
            self::$stories[$path] = new self($path, $category, $title);
        }
    }

    public function __construct($path, $category, $title)
    {
        $this->path = $path;
        $this->category = $category;
        $this->title = $title;
    }

    public function getMarkdownLink()
    {
        return '[' . $this->getFullTitle() . ']({% post_url ' . $this->getBasePath() . ' %})';
    }

    public function getBasePath()
    {
        return basename($this->path, '.md');
    }

    public function getFullTitle()
    {
        return $this->getCategoryName() . '　' . $this->title;
    }

    public function getCategoryName()
    {
        return Category::getCategoryNameById($this->category);
    }
}
