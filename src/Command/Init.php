<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Command;

use Peg\Lib\CommandLine\Option;
use Peg\Lib\CommandLine\OptionType;

/**
 * In charge of initializing a directory to produce an extension.
 */
class Init extends \Peg\Lib\CommandLine\Command
{

    public function __construct()
    {
        parent::__construct("init");

        $this->description = t("Populates a directory with skeleton files in preparation for generating an extension source code.");

        $this->RegisterAction(new Action\Init());

        $author = new Option(array(
            "long_name"     => "authors",
            "short_name"    => "a",
            "type"          => OptionType::STRING,
            "required"      => true,
            "description"   => t("A comma seperated list of main authors going to be working on the extension.") . "\n" .
            t("Example:") . " --authors \"" . t("Author1, Author2") . "\"",
            "default_value" => ""
        ));

        $this->AddOption($author);

        $contributors = new Option(array(
            "long_name"     => "contributors",
            "short_name"    => "c",
            "type"          => OptionType::STRING,
            "required"      => false,
            "description"   => t("A comma seperated list of contributors.") . "\n" .
            t("Example:") . " --contributors \"" . t("Contributor1, Contributor2") . "\"",
            "default_value" => ""
        ));

        $this->AddOption($contributors);

        $name = new Option(array(
            "long_name"     => "name",
            "short_name"    => "n",
            "type"          => OptionType::STRING,
            "required"      => false,
            "description"   => t("A name for the extension that overrides current working directory name."),
            "default_value" => ""
        ));

        $this->AddOption($name);

        $version = new Option(array(
            "long_name"     => "initial-version",
            "short_name"    => "i",
            "type"          => OptionType::STRING,
            "required"      => false,
            "description"   => t("Set the extension version. Default: 0.1."),
            "default_value" => "0.1"
        ));

        $this->AddOption($version);
        
        $config_type = new Option(array(
            "long_name"     => "config-type",
            "short_name"    => "t",
            "type"          => OptionType::STRING,
            "required"      => false,
            "description"   => t("Set the configuration file type. Default: json.")
                . "\n" . t("Allowed values:") . " json, ini",
            "default_value" => "json"
        ));

        $this->AddOption($config_type);

        $force = new Option(array(
            "long_name"     => "force",
            "short_name"    => "f",
            "type"          => OptionType::FLAG,
            "required"      => false,
            "description"   => t("Forces the initialization of a directory by overriding all it's content. Use with caution."),
            "default_value" => ""
        ));

        $this->AddOption($force);
    }

}