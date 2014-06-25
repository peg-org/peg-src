<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Command;

use Peg\CommandLine\Option;
use Peg\CommandLine\OptionType;

/**
 * Command to generate the extension source code from the cached definition files.
 */
class Generate extends \Peg\CommandLine\Command
{

    public function __construct()
    {
        parent::__construct("generate");

        $this->description = t("Generates the extension source code and configuration files.");

        $this->RegisterAction(new \Peg\Command\Action\Generate());
    }

}

?>
