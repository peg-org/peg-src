<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Command\Action;

use Peg\Lib\Application;

/**
 * Action taken when help is called.
 */
class Help extends \Peg\Lib\CommandLine\Action
{

    public function OnCall(\Peg\Lib\CommandLine\Command $command)
    {
        if(strlen($command->value) > 0)
        {
            if($help_command = Application::GetCLIParser()->GetCommand($command->value))
            {
                $this->PrintHelp($help_command);
            }
            else
            {
                \Peg\Lib\CommandLine\Error::Show(t("Invalid command supplied."));
            }
        }
        else
        {
            Application::GetCLIParser()->PrintHelp();
        }
    }

    /**
     * Prints help for a specific command.
     * $param \Peg\Lib\CommandLine\Command $command
     */
    public function PrintHelp(\Peg\Lib\CommandLine\Command $command)
    {
        // Store the lenght of longest command name
        $max_command_len = 0;

        // Store the lenght of longest option name
        $max_option_len = 0;

        $parser = Application::GetCLIParser();

        print $parser->application_name . " v" . $parser->application_version . "\n";
        print t($parser->application_description) . "\n\n";

        print t("Usage:") . "\n";

        if(strlen($command->name) > $max_command_len)
            $max_command_len = strlen($command->name);

        if(count($command->options) > 0)
        {
            foreach($command->options as $option)
            {
                if(strlen($option->long_name) > $max_option_len)
                    $max_option_len = strlen($option->long_name);
            }

            print "  peg {$command->name} " . t("[options]") . "\n\n";
        }
        else
        {
            print "  peg {$command->name}\n\n";
        }

        print t("Description:") . "\n";

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
            print t("Options:") . "\n";
            foreach($command->options as $option)
            {
                $line = "  " .
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
                        print str_pad($line, strlen($line) + ($max_option_len + 10), " ", STR_PAD_LEFT) . "\n";
                    }
                }
            }
        }

        print "\n";
    }

}