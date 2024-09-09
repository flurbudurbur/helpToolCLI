<?php
echo "running index.php\n";

// Read the raw input
$rawInput = file_get_contents('php://input');
$cli = new CLITools();
$args = $cli->convertWebToCLI($rawInput);
// print_r($args);
if (isset($args)) {
    echo "running with args\n";
    echo "[" . date("d-m-Y H:i:s") . "] ";
    echo $cli->execute($args) ? "success\n" : "failure\n";
} else {
    echo "running without args\n";
    echo "[" . date("d-m-Y H:i:s") . "] ";
    echo $cli->execute() ? "success\n" : "failure\n";
    posix_mkfifo("test.fifo", "w");
}

class CLITools
{
    private $version = '1.0.0';
    private $commands = [
        'help' => [
            'id' => 'help',
            'flags' => ['--help', '-h', '-?', '?'],
            'arg' => null,
            'usage' => '{command} [--help,-h]',
            'required' => false,
            'detailed' => ['When appended to another command, prints detailed help for that command.']
        ],
        'version' => [
            'id' => 'version',
            'flags' => ['--version', '-v'],
            'arg' => null,
            'usage' => '[--version,-v]',
            'required' => false,
            'detailed' => []
        ],
        'month' => [
            'id' => 'month',
            'flags' => ['--month', '-m'],
            'arg' => 'int',
            'usage' => '[--month,-m]=<month>',
            'required' => false,
            'detailed' => ['Sets the month to be used in the script.', 'The month must be a number between 1 and 12.', 'Default is the current month.']
        ],
        'year' => [
            'id' => 'year',
            'flags' => ['--year', '-y'],
            'arg' => 'int',
            'usage' => '[--year,-y]=<year>',
            'required' => false,
            'detailed' => ['Sets the year to be used in the script.', 'Handy for leap years.', 'Default is the current year.']
        ],
        'silent' => [
            'id' => 'silent',
            'flags' => ['--silent'],
            'arg' => null,
            'usage' => '--silent',
            'required' => false,
            'detailed' => ['Silences the output of the script.', 'Useful for running the script in the background.']
        ],
        'script' => [
            'id' => 'script',
            'flags' => ['--script', '-s'],
            'arg' => 'string',
            'usage' => '<command> [--script,-s]=<script> {--month,-m} {--year,-y}',
            'required' => true,
            'detailed' => ['Specifies the script to run.', 'The script must be a valid PHP file.']
        ]
    ];

    /**
     * Call to run script.
     * @param array|null $args Optionally pass arguments directly.
     * @return bool True on success.
     */
    public function execute(array $args = null) : bool
    {
        global $argv;
        $GLOBALS['argcli'] = [];
        // drop the source file from the array

        if (isset($args)) {
            $argv = $args;
        } else {
            array_shift($argv);
        }

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

        // get required arguments and add them to the global array
        foreach ($this->commands as $command) {
            if ($command['required'] && !isset($GLOBALS['argcli'][$command['id']])) {
                echo "--$command[id] is required: $command[usage]\n";
                return false;
            }
        }

        $argcli = $GLOBALS['argcli'];

        print_r($argcli);

        return true;
    }

    /**
     * Validates proper usage of arguments passed along
     * and if determined that an argument wasn't used
     * properly it calls the help message for the given argument.
     * @return bool True if all arguments are valid.
     */
    public function validateArguments() : bool
    {
        global $argv;

        if (count($argv) == 0) {
            echo "No arguments provided\n";
            return false;
        }

        $commands = $this->commands;
        foreach ($argv as $arg) {
            // arg[0] -> flag
            // arg[1] -> value
            $arg = explode('=', $arg);

            // check argument count
            if (count($arg) > 2) {
                echo "Too many arguments for $arg[0]\n";
                return false;
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
                            } else {
                                $GLOBALS['argcli'][$command['id']] = $arg[1];
                            }
                            break;
                        case null:
                            if (isset($arg[1])) {
                                $this->getHelp($command['id'], true);
                                return false;
                            } else {
                                $GLOBALS['argcli'][$command['id']] = true;
                            }
                            break;
                        case 'string':
                            if (count($arg) == 1 || $arg[1] === '') {
                                $this->getHelp($command['id'], true);
                                return false;
                            } else {
                                $GLOBALS['argcli'][$command['id']] = $arg[1];
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
    public function getVersion() : void
    {
        echo "Version: $this->version\n";
    }

    public function getHelp(string $command = null, bool $detailed = false)
    {
        echo "-- '< >' = required, '[ ]' = aliases/commands, '{ }' = optionals --\n";
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

    /**
     * Convert the web input to CLI-compatible arguments.
     * @param string $rawInput The raw input from the web, as received through `php://input`.
     * @return array|null The converted arguments.
     */
    function convertWebToCLI(string $rawInput)
    {
        // Decode the JSON input
        $inputData = json_decode($rawInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        $args = explode(' ', $inputData['command']);
        return $args;
    }

    function consoleLog($data) {
        echo "[" . date("d-m-Y H:i:s") . "] " . $data . "\n";
    }
}
