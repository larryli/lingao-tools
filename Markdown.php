<?php

namespace larryli\lingao;

use Symfony\Component\Yaml\Yaml;

class Markdown
{
    public $path;
    public $header;
    public $lines;
    public $references;

    public function __construct($text)
    {
        $text = str_replace(["\r\n", "\n\r", "\r"], "\n", $text);
        $lines = explode("\n", $text);
        if (isset($lines[0]) && $lines[0] == '---') {
            $headers = [];
            unset($lines[0]);
            foreach ($lines as $key => $line) {
                unset($lines[$key]);
                if ($line == '---') {
                    break;
                }
                $headers[] = $line;
            }
            $this->header = implode("\r\n", $headers);
            $headers = Yaml::parse($this->header);
            $this->path = @$headers['path'];
            Story::addStory($this->path, @$headers['category'], @$headers['title']);
        }

        $this->references = [];
        foreach ($lines as $key => $line) {
            if (preg_match('/^ {0,3}\[(.+?)\]:\s*(.+?)(?:\s+[\(\'"](.+?)[\)\'"])?\s*$/', $line, $matches)) {
                unset($lines[$key]);
                $label = strtolower($matches[1]);
                $this->references[$label] = $line;
            }
        }
        if (!empty(end($lines))) {
            $lines[] = '';
        }
        $this->lines = $lines;
    }

    public function update()
    {
        foreach (array_keys($this->references) as $key) {
            $char = Character::getCharacterById($key);
            if ($char != null) {
                $char->addStory($this->path);
            }
        }
    }

    public function add($search, $replace)
    {
        foreach ($this->lines as $m => $line) {
            $spans = $this->splitLine($line);
            foreach ($spans as $n => $span) {
                if (@$span{0} != '[') {
                    $spans[$n] = str_replace($search, $replace, $span);
                }
            }
            $this->lines[$m] = implode($spans);
        }
    }

    public function fix()
    {
        foreach ($this->lines as $line) {
            $spans = $this->splitLine($line);
            foreach ($spans as $span) {
                if (@$span{0} == '[') {
                    $id = substr($span, 1, -1);
                    $char = Character::getCharacterById($id);
                    if ($char != null) {
                        $this->addReference($id, $char->name);
                    }
                }
            }
        }
    }

    public function render()
    {
        return $this->renderHeader() . $this->renderLines() . $this->renderReferences();
    }

    protected function addReference($key, $value)
    {
        if (!isset($this->references[$key])) {
            $this->references[$key] = "[{$key}]: /characters/{$key} \"{$value}\"";
        }
    }

    protected function renderHeader()
    {
        return "---\r\n" . $this->header . "\r\n---\r\n";
    }

    protected function renderLines()
    {
        return implode("\r\n", $this->lines);
    }

    protected function renderReferences()
    {
        return empty($this->references) ? '' : implode("\r\n", $this->references) . "\r\n";
    }

    protected function splitLine($line)
    {
        $spans = [];
        $offset = 0;
        $c = true;
        for (;;) {
            $pos = strpos($line, $c ? '[' : ']', $offset);
            if ($pos !== false) {
                $pos += ($c ? 0 : 1);
                if ($pos > $offset) {
                    $spans[] = substr($line, $offset, $pos - $offset);
                }
                $offset = $pos;
                $c = !$c;
            } else {
                $spans[] = substr($line, $offset);
                break;
            }
        }
        return $spans;
    }
}
