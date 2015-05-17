<?php

use Fieg\Shell\Output\InMemoryOutput;
use Fieg\Shell\Output\NullOutput;
use Fieg\Shell\Shell;
use Fieg\Shell\ShellEvents;

class ShellTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $shell = new Shell();

        $this->assertInstanceOf(Shell::class, $shell);
    }

    public function testKeepsRunningUntilStoppedManually()
    {
        $shell = new Shell();

        $time = microtime(true);
        $wasCalled = false;

        // stop after 1 sec
        $shell->schedule(0.1, function() use (&$wasCalled, $shell) {
            $wasCalled = true;

            $shell->stop();
        });

        $shell->run();

        // assert that we have run longer then 1 sec
        $this->assertGreaterThan(0.1, microtime(true) - $time);
        $this->assertTrue($wasCalled);
    }

    public function testCommandEventGetsEmitted()
    {
        $spy = $this->getMockBuilder('stdClass')
            ->setMethods(['onCommand'])
            ->getMockForAbstractClass();

        $spy->expects($this->once())
            ->method('onCommand')
            ->with('test123');

        $shell = new Shell(new NullOutput());

        $shell->on(ShellEvents::COMMAND, [&$spy, 'onCommand']);
        $shell->prompt();

        $shell->submit('test123');
    }

    public function testPublish()
    {
        $shell = new Shell($output = new InMemoryOutput());

        $shell->publish('Hello world!');

        $this->assertEquals("Hello world!" . PHP_EOL, $output->getDisplay());
    }

    public function testPrompt()
    {
        $shell = new Shell($output = new InMemoryOutput());

        $shell->prompt('shell> ');

        $shell->schedule(0.1, function() use ($shell) { $shell->stop(); });
        $shell->run();

        $this->assertEquals(PHP_EOL . "shell> ", $output->getDisplay());
    }
}
