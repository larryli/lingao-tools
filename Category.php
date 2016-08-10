<?php

namespace larryli\lingao;

/**
 * Class Category
 */
class Category
{
    /**
     * @var string[]
     */
    static public $categories = [];

    /**
     * @param string[] $categories
     */
    public static function init($categories)
    {
        self::$categories = $categories;
    }

    /**
     * @param integer $id
     * @return Category|string
     */
    public static function getCategoryNameById($id)
    {
        return isset(self::$categories[$id]) ? self::$categories[$id] : '';
    }
}
