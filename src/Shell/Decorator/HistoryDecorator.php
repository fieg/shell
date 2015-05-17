<?php

namespace Fieg\Shell\Decorator;

use Fieg\Shell\ShellEvents;
use Fieg\Shell\ShellInterface;

class HistoryDecorator implements ShellInterface
{
    /**
     * @var ShellInterface
     */
    protected $shell;

    /**
     * @var string[]
     */
    protected $history = [];

    /**
     * @var int
     */
    protected $historyIndex = 0;

    /**
     * Constructor.
     *
     * @param ShellInterface $shell
     */
    public function __construct(ShellInterface $shell)
    {
        $this->shell = $shell;

        $this->shell->on(ShellEvents::COMMAND, [&$this, 'onCommand']);
        $this->shell->on(ShellEvents::ARROW_UP, [&$this, 'onArrowUp']);
        $this->shell->on(ShellEvents::ARROW_DOWN, [&$this, 'onArrowDown']);
    }

    /**
     * Starts shell
     */
    public function run()
    {
        $this->shell->run();
    }

    /**
     * @private
     * @internal
     *
     * @param $command
     */
    public function onCommand($command)
    {
        // we selected an entry from the history
        if ($this->historyIndex > 0) {
            array_pop($this->history);

            $this->historyIndex = 0;
        }

        if (trim($command) && (0 === count($this->history) || trim($command) !== $this->history[count($this->history) - 1])) {
            $this->history[] = $command;
        }

        if ('history' === $command) {
            $history = $this->getHistory();
            array_pop($history);

            foreach ($history as $entry) {
                $this->shell->publish($entry);
            }
        }
    }

    /**
     * @private
     * @internal
     *
     * @param $buffer
     */
    public function onArrowUp($buffer)
    {
        if (0 === $this->historyIndex) {
            $this->history[] = $buffer; // add to history
        }

        if (0 < (count($this->history) - 1) - $this->historyIndex) {
            $this->historyIndex++;

            $buffer = $this->history[(count($this->history) - 1) - $this->historyIndex];
            $this->shell->setBuffer($buffer);
        }
    }

    /**
     * @private
     * @internal
     *
     * @param $buffer
     */
    public function onArrowDown($buffer)
    {
        if (0 < $this->historyIndex) {
            $this->historyIndex--;

            $buffer = $this->history[(count($this->history) - 1) - $this->historyIndex];
            $this->shell->setBuffer($buffer);

            if (0 === $this->historyIndex) {
                array_pop($this->history);
            }
        }
    }

    /**
     * Stops shell
     */
    public function stop()
    {
        $this->shell->stop();
    }

    /**
     * Starts a prompt
     *
     * @param string $prompt
     */
    public function prompt($prompt = 'λ ')
    {
        $this->shell->prompt($prompt);
    }

    /**
     * Writes text above prompt
     *
     * @param string $text
     */
    public function publish($text)
    {
        $this->shell->publish($text);
    }

    /**
     * Submit a command like a user would have typed it
     *
     * @param string $text
     */
    public function submit($text)
    {
        $this->shell->submit($text);
    }

    /**
     * @param string $event
     * @param callable $listener
     *
     * @return mixed
     */
    public function on($event, callable $listener)
    {
        $this->shell->on($event, $listener);
    }

    /**
     * @return mixed
     */
    public function getBuffer()
    {
        return $this->shell->getBuffer();
    }

    /**
     * @param $buffer
     */
    public function setBuffer($buffer)
    {
        $this->shell->setBuffer($buffer);
    }

    /**
     * @return string[]
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @param int $interval
     * @param callable $callback
     */
    public function schedule($interval, callable $callback)
    {
        $this->shell->schedule($interval, $callback);
    }
}
