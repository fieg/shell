<?php

namespace Fieg\Shell\Output;

class InMemoryOutput implements OutputInterface
{
    protected $lines = [];

    public function write($text)
    {
        $lastLine = &$this->lines[count($this->lines)];

        if (false !== strpos($text, "\r")) {
            $lastLine = '';
        } else {
            $lastLine .= $text;
        }
    }

    public function writeln($text)
    {
        $this->write($text);
        $this->lines[] = '';
    }

    /**
     * @return string
     */
    public function getDisplay()
    {
        return implode("\n", $this->lines);
    }
}
