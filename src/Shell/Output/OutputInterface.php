<?php

namespace Fieg\Shell\Output;

interface OutputInterface
{ 
    public function write($text);

    public function writeln($text);
}
