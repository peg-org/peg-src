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
 * Command to generate the extension source code from the cached definition files.
 */
class Generate extends \Peg\Lib\CommandLine\Command
{

    public function __construct()
    {
        parent::__construct("generate");

        $this->description = t("Generates the extension source code and configuration files.");

        $this->RegisterAction(new \Peg\Lib\Command\Action\Generate());
        
        $format = new Option(array(
            "long_name"     => "format",
            "short_name"    => "f",
            "type"          => OptionType::STRING,
            "required"      => false,
            "description"   => t("Format of cached definition files. Default: json") 
                . "\n" . t("Allowed values:") . " json, php",
            "default_value" => "json"
        ));
        
        $this->AddOption($format);
    }

}

?>
