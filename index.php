<?php
echo "running index.php\n";
array_shift($argv);
$cli = new CLITools();
$cli->execute();

class CLITools
{
    private $version = '1.0.0';
    private $commands = [
        'help' => [
            'flags' => ['--help', '-h', '-?', '?'],
            'arg' => null,
            'usage' => 'Prints this help message',
            'required' => false,
            'detailed' => ['When appended to another command, prints detailed help for that command.']
        ],
        'version' => [
            'flags' => ['--version', '-v'],
            'arg' => null,
            'usage' => 'Prints the current version of the script',
            'required' => false,
            'detailed' => []
        ],
        'month' => [
            'flags' => ['--month', '-m'],
            'arg' => 'int',
            'usage' => 'Sets the month',
            'required' => false,
            'detailed' => ['Sets the month to be used in the script.', 'The month must be a number between 1 and 12.', 'Default is the current month.']
        ],
        'year' => [
            'flags' => ['--year', '-y'],
            'arg' => 'int',
            'usage' => 'Sets the year',
            'required' => false,
            'detailed' => ['Sets the year to be used in the script.', 'Handy for leap years.', 'Default is the current year.']
        ],
        'silent' => [
            'flags' => ['--silent'],
            'arg' => null,
            'usage' => 'Silences the output',
            'required' => false,
            'detailed' => ['Silences the output of the script.', 'Useful for running the script in the background.']
        ],
        'script' => [
            'flags' => ['--script', '-s'],
            'arg' => 'string',
            'usage' => 'Specifies the script to run',
            'required' => true,
            'detailed' => ['Specifies the script to run.', 'The script must be a valid PHP file.']
        ]
    ];

    public function execute()
    {
        global $argv;

        // if last argument is a --help flag or empty, print help message
        if (in_array(end($argv), $this->commands['help']['flags']) || empty($argv)) {
            $this->getHelp();
            exit();
        }
    }

    public function validateArguments(array $argv, bool $silent = false)
    {
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
                                echo "Invalid argument! '$arg[0]' $arg[1], requires a number.\n";
                            }
                            break;
                        case null:
                            if (isset($arg[1])) {
                                exit($arg[0] . " does not take any arguments\n");
                            }
                            break;
                        case 'string':
                            if ($arg[1] === '') {
                                echo "Invalid argument! '$arg[0]' requires a string.\n";
                            }
                            break;
                        default:
                            exit("Invalid argument type: $command[arg]");
                    }
                    $validArg = true;
                    continue;
                }
            }
        }

        if (!$validArg) {
            exit("Invalid argument: $arg[0]");
        }

        return true;
    }

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
