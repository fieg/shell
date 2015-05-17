<?php

namespace Fieg\Shell;

use Evenement\EventEmitter;
use Fieg\Shell\Output\ConsoleOutput;
use Fieg\Shell\Output\OutputInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

class Shell extends EventEmitter implements ShellInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var string
     */
    protected $buffer = '';

    /**
     * @var string
     */
    protected $specialBuffer = '';

    /**
     * @var bool
     */
    protected $special = false;

    /**
     * @var string
     */
    protected $prompt = '';

    /**
     * @var bool
     */
    protected $hasPrompt = false;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var bool
     */
    protected $running = false;

    /**
     * Constructor.
     *
     * @param OutputInterface $output
     * @param LoopInterface $loop
     */
    public function __construct(OutputInterface $output = null, LoopInterface $loop = null)
    {
        $this->loop = $loop ?: Factory::create();
        $this->output = $output ?: new ConsoleOutput();
    }

    /**
     * Starts shell
     */
    public function run()
    {
        if ($this->running) {
            return;
        }

        $shell = $this;

        $this->loop->addReadStream(
            STDIN,
            function ($n) use ($shell) {
                $c = stream_get_contents($n, 1);

                $shell->read($c);
            }
        );

        readline_callback_handler_install('', function() { });

        $this->running = true;

        $this->loop->run();
    }

    /**
     * Stops shell
     */
    public function stop()
    {
        if (!$this->running) {
            return;
        }

        $this->loop->stop();

        $this->running = false;
    }

    /**
     * Starts a prompt
     *
     * @param string $prompt
     */
    public function prompt($prompt = 'λ ')
    {
        $this->prompt = $prompt;

        $this->hasPrompt = true;

        $this->updatePrompt('');
    }

    /**
     * Writes text above prompt
     *
     * @param string $text
     */
    public function publish($text)
    {
        if ($hadPrompt = $this->hasPrompt) {
            $this->removePrompt();
        }

        $this->output->writeln($text);

        // restore prompt
        if ($hadPrompt) {
            $this->hasPrompt = true;

            $this->updatePrompt($this->buffer);
        }
    }

    /**
     * Submit a command like a user would have typed it
     *
     * @param string $text
     */
    public function submit($text)
    {
        for ($i = 0, $l = strlen($text); $i < $l; $i++) {
            $this->output->write($text[$i]);
            $this->read($text[$i]);
        }

        $this->read(chr(10));
    }

    /**
     * Schedule a callback to be executed after shell run
     *
     * @param int $interval
     * @param callable $callback
     */
    public function schedule($interval, callable $callback)
    {
        $this->loop->addTimer($interval, $callback);
    }

    /**
     * @return string
     */
    public function getBuffer()
    {
        return $this->buffer;
    }

    /**
     * @param string $buffer
     *
     * @return $this
     */
    public function setBuffer($buffer)
    {
        $this->updatePrompt($buffer);

        $this->buffer = $buffer;

        return $this;
    }

    /**
     * @private
     * @internal
     *
     * @param string $char
     */
    public function read($char)
    {
        if (!$this->hasPrompt) {
            return;
        }

        switch ($char) {
            // backspace
            case chr(127):
                $this->buffer = substr($this->buffer, 0, -1);

                $this->updatePrompt($this->buffer);
                break;

            // return
            case chr(10):
                $buffer = $this->buffer;

                $this->buffer = '';

                $this->updatePrompt($buffer);

                $this->emit(ShellEvents::COMMAND, array($buffer));
                break;

            // control
            case chr(27):
                $this->special = true;
                break;

            default:
                if ($this->special) {
                    $this->readSpecial($char);
                } else {
                    $this->buffer .= $char;

                    $this->updatePrompt($this->buffer);

                    $this->historyIndex = 0;

                    $this->emit(ShellEvents::KEY_PRESS, array($char));
                }
                break;
        }
    }

    /**
     * @param string $char
     */
    protected function readSpecial($char)
    {
        $this->specialBuffer .= $char;

        if (strlen($this->specialBuffer) < 2) {
            return;
        }

        switch ($this->specialBuffer) {
            // arrow up
            case "[A":
                $this->emit(ShellEvents::ARROW_UP, array($this->buffer));
                break;

            // arrow down
            case "[B":
                $this->emit(ShellEvents::ARROW_DOWN, array($this->buffer));
                break;
        }

        $this->special = false;
        $this->specialBuffer = '';
    }

    /**
     * @param string $text
     */
    protected function updatePrompt($text)
    {
        if (!$this->hasPrompt) {
            return;
        }

        // remove previous prompt
        $this->removePrompt();

        $this->hasPrompt = true;

        $this->output->write($this->prompt . $text);
    }

    /**
     * Removes prompt
     */
    protected function removePrompt()
    {
        $this->output->write(sprintf("\r%s\r", str_repeat(" ", strlen($this->prompt . $this->buffer))));

        $this->hasPrompt = false;
    }
}
