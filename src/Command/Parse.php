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
 * Command to parse files and generate a definitions cache representing them.
 */
class Parse extends \Peg\Lib\CommandLine\Command
{

    public function __construct()
    {
        parent::__construct("parse");

        $this->description = t("Extracts definitions which are then stored in the 'definitions' directory.");

        $this->RegisterAction(new Action\Parse\Doxygen());

        $input_format = new Option(array(
            "long_name"     => "input-format",
            "short_name"    => "f",
            "type"          => OptionType::STRING,
            "required"      => false,
            "description"   => t("The kind of input to parse. Default: doxygen") 
                . "\n" . t("Allowed values:") . " doxygen",
            "default_value" => "doxygen"
        ));
        
        $this->AddOption($input_format);
        
        $output_format = new Option(array(
            "long_name"     => "output-format",
            "short_name"    => "o",
            "type"          => OptionType::STRING,
            "required"      => false,
            "description"   => t("The kind of cached definition files to create. Default: json") 
                . "\n" . t("Allowed values:") . " json, php",
            "default_value" => "json"
        ));

        $this->AddOption($output_format);

        $source = new Option(array(
            "long_name"     => "source",
            "short_name"    => "s",
            "type"          => OptionType::STRING,
            "required"      => true,
            "description"   => t("The path were resides the input to parse."),
            "default_value" => ""
        ));

        $this->AddOption($source);

        $headers = new Option(array(
            "long_name"     => "headers",
            "short_name"    => "h",
            "type"          => OptionType::STRING,
            "required"      => false,
            "description"   => t("The path were resides the header files of the library in order to correctly solve headers include path."),
            "default_value" => ""
        ));

        $this->AddOption($headers);

        $verbose = new Option(array(
            "long_name"     => "verbose",
            "short_name"    => "v",
            "type"          => OptionType::FLAG,
            "required"      => false,
            "description"   => t("Turns verbosity on."),
            "default_value" => "",
        ));

        $this->AddOption($verbose);
    }

}