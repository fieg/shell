Interactive shell (cli) component for PHP

[![Build Status](https://travis-ci.org/fieg/shell.png?branch=master)](https://travis-ci.org/fieg/shell)

Getting started
---------------

```php
use Fieg\Shell\Shell;
use Fieg\Shell\ShellEvents;

$shell = new Shell();

// handle some commands
$shell->on(ShellEvents::COMMAND, function ($command) use ($shell) {
    switch ($command) {
        case "help":
            $shell->publish('Available commands:');
            $shell->publish('  help   Print this help');
            $shell->publish('  exit   Exit program');
            break;

        case "exit":
            $shell->stop();
            break;

        // echo everything else the user types
        default:
            $shell->publish('echo: ' . $command);
    }
});

// print some info
$shell->publish("This is an interactive shell.");
$shell->publish("Type 'help' for all available commands.");

// start a prompt so we can receive user input
$shell->prompt();

// statements after this are only executed when `$shell->stop()` is called
$shell->run();

echo "Bye!" . PHP_EOL;
```

This library also comes with a history support. With this you can use the up and down arrows to
browse through the recently typed commands. To enable the history support, just wrap the Shell
class in a HistoryDecorator:

```php
$shell = new HistoryDecorator(new Shell());
```

You can also type the command "history" to see a list of all recently typed commands.
