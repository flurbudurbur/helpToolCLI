<?php
echo "running index.php\n";
$cli = new CLITools();
echo $cli->execute();

class CLITools
{
    private $version = '1.0.0';
    private $commands = [
        'help' => [
            'id' => 'help',
            'flags' => ['--help', '-h', '-?', '?'],
            'arg' => null,
            'usage' => 'Prints this help message',
            'required' => false,
            'detailed' => ['When appended to another command, prints detailed help for that command.']
        ],
        'version' => [
            'id' => 'version',
            'flags' => ['--version', '-v'],
            'arg' => null,
            'usage' => 'Prints the current version of the script',
            'required' => false,
            'detailed' => []
        ],
        'month' => [
            'id' => 'month',
            'flags' => ['--month', '-m'],
            'arg' => 'int',
            'usage' => 'Sets the month',
            'required' => false,
            'detailed' => ['Sets the month to be used in the script.', 'The month must be a number between 1 and 12.', 'Default is the current month.']
        ],
        'year' => [
            'id' => 'year',
            'flags' => ['--year', '-y'],
            'arg' => 'int',
            'usage' => 'Sets the year',
            'required' => false,
            'detailed' => ['Sets the year to be used in the script.', 'Handy for leap years.', 'Default is the current year.']
        ],
        'silent' => [
            'id' => 'silent',
            'flags' => ['--silent'],
            'arg' => null,
            'usage' => 'Silences the output',
            'required' => false,
            'detailed' => ['Silences the output of the script.', 'Useful for running the script in the background.']
        ],
        'script' => [
            'id' => 'script',
            'flags' => ['--script', '-s'],
            'arg' => 'string',
            'usage' => 'Specifies the script to run',
            'required' => true,
            'detailed' => ['Specifies the script to run.', 'The script must be a valid PHP file.']
        ]
    ];

    /**
     * Call to run script.
     * @param array|null $args Optionally pass arguments directly.
     * @return bool True on success.
     */
    public function execute(array $args = null)
    {
        global $argv;
        // drop the source file from the array
        array_shift($argv);

        if (isset($args)) {
            $argv = $args;
        }
        echo "argv: ";
        print_r($argv);

        if (in_array(end($argv), $this->commands['help']['flags'])) {
            if (count($argv) > 1 && in_array(end($argv), $this->commands['help']['flags'])) {
                $help = explode("=", $argv[count($argv) - 2]);
                $help = preg_replace('(--)', '', $help[0]);
                $this->getHelp($help, true);
            } elseif (count($argv) == 1) {
                $this->getHelp();
            }
            return false;
        }

        if (!$this->validateArguments()) {
            // no reason needs to be provided because
            // the validateArguments function already
            // does so.
            return false;
        }

        return true;
    }

    /**
     * Validates proper usage of arguments passed along
     * and if determined that an argument wasn't used
     * properly it calls the help message for the given argument.
     * @return bool True if all arguments are valid.
     */
    public function validateArguments()
    {
        global $argv;

        $commands = $this->commands;
        foreach ($argv as $arg) {
            // arg[0] -> flag
            // arg[1] -> value
            $arg = explode('=', $arg);

            // check argument count
            if (count($arg) > 2) {
                exit("Too many arguments for $arg[0]\n");
            }

            $validArg = false;
            foreach ($commands as $command) {
                if (in_array($arg[0], $command['flags'])) {
                    // verify proper value type
                    switch ($command['arg']) {
                        case 'int':
                            if (!is_numeric($arg[1])) {
                                $this->getHelp($command['id'], true);
                                return false;
                            }
                            break;
                        case null:
                            if (isset($arg[1])) {
                                $this->getHelp($command['id'], true);
                                return false;
                            }
                            break;
                        case 'string':
                            if ($arg[1] === '') {
                                $this->getHelp($command['id'], true);
                                return false;
                            }
                            break;
                        default:
                            echo "unknown argument\n";
                            $this->getHelp();
                            return false;
                    }
                    $validArg = true;
                    continue;
                }
            }
        }

        if (!$validArg) {
            echo "Invalid argument: $arg[0]\n";
            return false;
        }

        return true;
    }

    /**
     * Get the version of the script.
     * @return void
     */
    public function getVersion()
    {
        echo "Version: $this->version\n";
    }

    public function getHelp(string $command = null, bool $detailed = false)
    {
        if ($command) {
            if (array_key_exists($command, $this->commands)) {
                echo "Usage for $command: " . $this->commands[$command]['usage'] . "\n";
            }
        } else {
            foreach ($this->commands as $command => $data) {
                echo "\t> --$command\n";
                if ($detailed) {
                    if (empty($data['detailed'])) {
                        echo "\t$data[usage]\n";
                        echo "\n";
                        continue;
                    }
                    foreach ($data['detailed'] as $details) {
                        echo "\t$details\n";
                    }
                } else {
                    echo "\t$data[usage]\n";
                }
                if (count($data['flags']) > 1) {
                    echo "\tAliases: ";
                    for ($i = 1; $i < count($data['flags']); $i++) {
                        echo $data['flags'][$i];
                        if ($i < count($data['flags']) - 1) {
                            echo ', ';
                        }
                    }
                    echo "\n";
                }
                echo "\n";
            }
        }
    }
}
