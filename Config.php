<?php

namespace larryli\lingao;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Config
 */
class Config
{
    /**
     * @param string $path
     */
    public static function init($path)
    {
        $config = Yaml::parse(file_get_contents($path));
        if (isset($config['categories'])) {
            self::initCategories($config['categories']);
        }
        if (isset($config['characters'])) {
            self::initCharacters($config['characters']);
        }
    }

    /**
     * @param string[] $categories
     */
    protected static function initCategories($categories)
    {
        Category::init($categories);
    }

    /**
     * @param string[] $characters
     */
    protected static function initCharacters($characters)
    {
        foreach ($characters as $id => $name) {
            Character::addCharacter($id, $name);
        }
    }

    /**
     * @param string $path
     * @return Finder
     */
    public static function finder($path)
    {
        $finder = new Finder();
        $finder->files()->in($path)->path('_posts')->name('/\d+-\d+-\d+-\d+-\d+\.md$/')
            ->sort(function (\SplFileInfo $a, \SplFileInfo $b) {
                $x = self::getCmpByPath($a->getBasename());
                $y = self::getCmpByPath($b->getBasename());
                if ($x[0] == $y[0]) {
                    if ($x[1] == $y[1]) {
                        return strcmp($x[2], $y[2]);
                    }
                    return $x[1] > $y[1];
                }
                return $x[0] > $y[0];
            });
        return $finder;
    }

    /**
     * @param string $path
     * @return array
     */
    public static function getCmpByPath($path)
    {
        if (preg_match('/(\d+-\d+-\d+)-(\d+)-(\d+)/', $path, $mts)) {
            return [
                strtotime($mts[1]),
                intval($mts[2]),
                $mts[3]
            ];
        }
        return [0, 0, ''];
    }
}
