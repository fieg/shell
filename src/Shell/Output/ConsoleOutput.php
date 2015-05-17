<?php

namespace Fieg\Shell\Output;

class ConsoleOutput implements OutputInterface
{
    public function write($text)
    {
        echo $text;
    }

    public function writeln($text)
    {
        $this->write($text . PHP_EOL);
    }
}
