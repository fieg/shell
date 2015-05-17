<?php


namespace Fieg\Shell;

interface ShellInterface
{
    /**
     * Starts shell
     */
    public function run();

    /**
     * Stops shell
     */
    public function stop();

    /**
     * Starts a prompt
     *
     * @param string $prompt
     */
    public function prompt($prompt = 'λ ');

    /**
     * Writes text above prompt
     *
     * @param string $text
     */
    public function publish($text);

    /**
     * Submit a command like a user would have typed it
     *
     * @param string $text
     */
    public function submit($text);

    /**
     * @param string $event
     * @param callable $listener
     *
     * @return mixed
     */
    public function on($event, callable $listener);

    /**
     * @return string
     */
    public function getBuffer();

    /**
     * @param string $buffer
     *
     * @return $this
     */
    public function setBuffer($buffer);

    /**
     * @param int $interval
     * @param callable $callback
     */
    public function schedule($interval, callable $callback);
}
