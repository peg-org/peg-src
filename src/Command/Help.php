<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Command;

use Peg\Lib\Application;

/**
 * Display overall help or for a given command.
 */
class Help extends \Peg\Lib\CommandLine\Command
{

    public function __construct()
    {
        parent::__construct("help");

        $this->description = t("Display a help message for a specific command.");

        $this->description .= "\n" . t("Example:") . " " .
            Application::GetCLIParser()->application_name . " help <command>"
        ;

        $this->RegisterAction(new Action\Help());
    }

}