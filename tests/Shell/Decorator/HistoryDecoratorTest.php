<?php

use Fieg\Shell\Decorator\HistoryDecorator;
use Fieg\Shell\Output\NullOutput;
use Fieg\Shell\Shell;

class HistoryDecoratorTest extends \PHPUnit_Framework_TestCase
{
    public function testShellRecordsHistory()
    {
        $shell = new Shell(new NullOutput());
        $shell = new HistoryDecorator($shell);

        $shell->prompt();

        $this->assertEquals([], $shell->getHistory());

        $shell->submit('test');

        $this->assertEquals(['test'], $shell->getHistory());

        $shell->submit('123');

        $this->assertEquals(['test', '123'], $shell->getHistory());
    }
}
