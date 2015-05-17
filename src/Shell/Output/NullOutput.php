<?php

namespace Fieg\Shell\Output;

class NullOutput implements OutputInterface
{
    public function write($text)
    {
    }

    public function writeln($text)
    {
    }
}
