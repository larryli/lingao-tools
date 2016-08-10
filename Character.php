<?php

namespace larryli\lingao;

/**
 * Class Character
 */
class Character
{
    /**
     * @var self[]
     */
    static public $characters = [];
    /**
     * @var string
     */
    public $id;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string[]
     */
    public $stories;

    /**
     * @param string $id
     * @return Character|null
     */
    public static function getCharacterById($id)
    {
        return isset(self::$characters[$id]) ? self::$characters[$id] : null;
    }

    /**
     * @param string $id
     * @param string $name
     */
    public static function addCharacter($id, $name)
    {
        if (!isset(self::$characters[$id])) {
            self::$characters[$id] = new Character($id, $name);
        }
    }

    /**
     * Character constructor.
     * @param string $id
     * @param string $name
     */
    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
        $this->stories = [];
    }

    /**
     * @param string $path
     */
    public function addStory($path)
    {
        $this->stories[] = $path;
    }

    /**
     * @return string
     */
    public function render()
    {
        if (empty($this->stories)) {
            return null;
        }

        $return = <<<EOF
---
collection: characters
layout: post\r
title: {$this->name}
path: {$this->id}.md
---


EOF;
        $return = implode("\r\n", explode("\n", str_replace(["\r\n", "\n\r", "\r"], "\n", $return)));
        foreach ($this->stories as $path) {
            $story = Story::getStoryByPath($path);
            if ($story != null) {
                $text = $story->getMarkdownLink();
                $return .= "- {$text}\r\n";
            }
        }
        return $return;
    }
}
