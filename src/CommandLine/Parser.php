<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\CommandLine;

/**
 * Class in charge of parsing the command line options
 */
class Parser
{

    /**
     * Stores the number of arguments passed on command line.
     * @var integer
     */
    private $argument_count;

    /**
     * Stores the values passed on the command line.
     * @var string[]
     */
    private $argument_values;

    /**
     * List of command line options registered on the parser.
     * @var \Peg\Lib\CommandLine\Option[]
     */
    private $options;

    /**
     * List of sub-commands registered on the parser.
     * @var \Peg\Lib\CommandLine\Command[]
     */
    private $commands;

    /**
     * Name of the main application using the command line parser, displayed
     * when printing the help message.
     * @var string
     */
    public $application_name;

    /**
     * Version number of the main application using the command line parser,
     * displayed when printing the help message.
     * @var string
     */
    public $application_version;

    /**
     * Description of the main application using the command line parser,
     * displayed when printing the help message.
     * @var string
     */
    public $application_description;

    /**
     * Initialize the parser.
     */
    public function __construct()
    {
        $this->options = array();
        $this->commands = array();

        $this->application_name = t("Untitled");
        $this->application_version = "0.1";
        $this->application_description = t("Untitled application description.");
    }

    /**
     * Get array of options.
     * @return \Peg\Lib\CommandLine\Option[]
     */
    public function GetOptions()
    {
        return $this->options;
    }

    /**
     * Get an option by its long name.
     * @param string $name
     * @return \Peg\Lib\CommandLine\Option|boolean
     */
    public function GetOption($name)
    {
        if(isset($this->options[$name]))
            return $this->options[$name];

        return false;
    }

    /**
     * Get array of commands
     * @return \Peg\Lib\CommandLine\Command[]
     */
    public function GetCommands()
    {
        return $this->commands;
    }

    /**
     * Get a command object by its name.
     * @param string $name
     * @return \Peg\Lib\CommandLine\Command|boolean
     */
    public function GetCommand($name)
    {
        if(isset($this->commands[$name]))
            return $this->commands[$name];

        return false;
    }

    /**
     * Adds a sub command to the parser.
     * @param \Peg\Lib\CommandLine\Command $command
     * @throws Exception
     */
    public function RegisterCommand(Command $command)
    {
        if(!isset($this->commands[$command->name]))
            $this->commands[$command->name] = $command;
        else
            throw new \Exception(t("Command") . " '{$command->name}' " . t("is already registered."));
    }

    /**
     * Adds an option to the parser.
     * @param \Peg\Lib\CommandLine\Option $option
     * @throws Exception
     */
    public function RegisterOption(Option $option)
    {
        if(!isset($this->options[$option->long_name]))
            $this->options[$option->long_name] = $option;
        else
            throw new \Exception(t("Option") . " '{$option->long_name}' " . t("is already registered."));
    }

    /**
     * Begins the process of reading command line options and calling command
     * actions as needed.
     * @param integer $argc
     * @param array $argv
     */
    public function Start($argc, $argv)
    {
        $this->argument_count = $argc;
        $this->argument_values = $argv;

        if(
                $this->argument_count <= 1 ||
                in_array("--help", $this->argument_values)
        )
        {
            $this->PrintHelp();
        }

        if(in_array("--version", $this->argument_values))
        {
            $this->PrintVersion();
        }

        if($this->IsCommand($this->argument_values[1]))
        {
            $command = $this->commands[$this->argument_values[1]];
            $this->ParseOptions($command->options, $command);
            $command->Execute();

            return;
        }
        else
        {
            $this->ParseOptions($this->options);
        }
    }

    /**
     * Generates and prints the help based on the registered commands and options.
     */
    public function PrintHelp()
    {
        // Store the len of the longest command name
        $max_command_len = 0;

        //Store the len of longest command name
        $max_option_len = 0;

        print $this->application_name . " v" . $this->application_version . "\n";
        print t($this->application_description) . "\n\n";

        print t("Usage:") . "\n";
        print "   " . $this->application_name . " " . t("[options]") . "\n";

        if(count($this->commands) > 0)
        {
            foreach($this->commands as $command)
            {
                if(strlen($command->name) > $max_command_len)
                    $max_command_len = strlen($command->name);

                if(count($command->options) > 0)
                {
                    foreach($command->options as $option)
                    {
                        if(strlen($option->long_name) > $max_option_len)
                            $max_option_len = strlen($option->long_name);
                    }
                }
            }

            print "   " . $this->application_name . " <command> " . t("[options]") . "\n";
        }

        if(count($this->commands) > 0)
        {
            print "\n";
            print t("Commands:") . "\n";
            
            print str_pad("  ", 80, "-", STR_PAD_RIGHT);
            print "\n\n";

            foreach($this->commands as $command)
            {
                $line = "  " . str_pad($command->name, $max_command_len + 2) . t($command->description);
                $line = wordwrap($line, 80);
                $line_array = explode("\n", $line);

                print $line_array[0] . "\n";
                unset($line_array[0]);

                if(count($line_array) > 0)
                {
                    foreach($line_array as $line)
                    {
                        print str_pad($line, strlen($line) + ($max_command_len + 4), " ", STR_PAD_LEFT) . "\n";
                    }
                }

                if(count($command->options) > 0)
                {
                    print "\n";
                    print "    " . t("Options:") . "\n";
                    foreach($command->options as $option)
                    {
                        $line = "      " .
                                str_pad(
                                        "-" . $option->short_name . "  --" . $option->long_name, $max_option_len + 8
                                ) .
                                t($option->description)
                        ;

                        $line = wordwrap($line, 80);
                        $line_array = explode("\n", $line);

                        print $line_array[0] . "\n";
                        unset($line_array[0]);

                        if(count($line_array) > 0)
                        {
                            foreach($line_array as $line)
                            {
                                print str_pad($line, strlen($line) + ($max_option_len + 14), " ", STR_PAD_LEFT) . "\n";
                            }
                        }
                    }
                }

                print "\n";

                print str_pad("  ", 80, "-", STR_PAD_RIGHT);

                print "\n\n";
            }
        }


        exit(0);
    }

    /**
     * Generates and prints the help based on the registered commands and options.
     */
    public function PrintVersion()
    {
        print "v" . $this->application_version . "\n";

        exit(0);
    }

    /**
     * Checks if a given name is registered as a command.
     * @param string $name
     * @return boolean
     */
    private function IsCommand($name)
    {
        return isset($this->commands[$name]);
    }

    /**
     * Checks if a given option exists on a given options array
     * @param type $name
     * @param \Peg\Lib\CommandLine\Option[] $options
     */
    private function OptionExists($name, $options)
    {
        foreach($options as $option)
        {
            if($option->long_name == $name || $option->short_name == $name)
                return true;
        }

        return false;
    }

    /**
     * Parses the command line options depending on a set of given options.
     * The given options are updated with the values assigned on the
     * command line.
     * @param \Peg\Lib\CommandLine\Option[] $options
     * @param \Peg\Lib\CommandLine\Command $command
     */
    private function ParseOptions(&$options, \Peg\Lib\CommandLine\Command $command = null)
    {
        // In case command doesn't has any options just copy any values passed to it
        if($command)
        {
            if(count($command->options) <= 0)
            {
                $argi = 2;

                for($argi; $argi < $this->argument_count; $argi++)
                {
                    $argument = $this->argument_values[$argi];

                    if(ltrim($argument, "-") == $argument)
                    {
                        $command->value = trim($command->value . " " . $argument);
                        continue;
                    }
                    else
                    {
                        Error::Show(t("Invalid parameter") . " '$argument'");
                    }
                }

                return;
            }
        }

        // Store values passed to the command to prevent repetition
        $command_values = array();

        //Parse every option
        foreach($options as $index => $option)
        {
            if($option->required)
            {
                if(
                        !in_array("--" . $option->long_name, $this->argument_values) &&
                        !in_array("-" . $option->short_name, $this->argument_values)
                )
                    Error::Show(t("Missing required option") . " '--{$option->long_name}'");
            }

            $argi = 1;

            // If command passed start parsing after it.
            if($command)
                $argi = 2;

            for($argi; $argi < $this->argument_count; $argi++)
            {
                $argument_original = $this->argument_values[$argi];
                $argument = $argument_original;
                $argument_next = "";

                if($argi + 1 < $this->argument_count)
                {
                    $argument_next = $this->argument_values[$argi + 1];
                }

                if(ltrim($argument, "-") != $argument)
                {
                    $argument = ltrim($argument, "-");

                    if($this->OptionExists($argument, $options))
                    {
                        if(
                                $argument == $option->long_name ||
                                $argument == $option->short_name
                        )
                        {
                            switch($option->type)
                            {
                                case OptionType::FLAG:
                                    $option->active = true;
                                    break;

                                default:
                                    if($option->SetValue($argument_next))
                                        $argi++; //Forward to next argument
                            }

                            if($option->IsValid())
                            {
                                $options[$index] = $option;
                            }
                            else
                            {
                                Error::Show(t("Invalid value supplied for") . " '$argument_original'");
                            }
                        }
                    }
                    elseif(!$this->IsCommand($argument))
                    {
                        Error::Show(t("Invalid parameter") . " '$argument_original'");
                    }
                }
                else
                {
                    if($command)
                    {
                        $previous_argument = "";

                        if(isset($this->argument_values[$argi - 1]))
                            $previous_argument = $this->argument_values[$argi - 1];

                        $previous_command_is_flag = false;

                        if(ltrim($previous_argument, "-") != $previous_argument)
                        {
                            if($previous_command = $command->GetOption(ltrim($previous_argument, "-")))
                            {
                                if($previous_command->type == OptionType::FLAG)
                                {
                                    $previous_command_is_flag = true;
                                }
                            }
                        }

                        if(
                                (
                                ltrim($argument, "-") == $argument &&
                                ltrim($previous_argument, "-") == $previous_argument
                                ) ||
                                $previous_command_is_flag
                        )
                        {
                            if(!in_array($argument, $command_values))
                            {
                                $command_values[] = $argument;
                                $command->value = trim($command->value . " " . $argument);
                                continue;
                            }
                        }
                    }
                }
            }
        }
    }

}