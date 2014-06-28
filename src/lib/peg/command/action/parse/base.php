<?php
/**
 * Base class for implementing a definitions extractor.
 *
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Command\Action\Parse;

use Peg\Application;
use Peg\CommandLine\Error;
use Peg\Utilities\FileSystem;

/**
 * Declares the base for a parse action that extract and generates definition
 * files.
 */
abstract class Base extends \Peg\CommandLine\Action
{

    protected $command;
    protected $input_format;
    protected $output_format;
    protected $headers_path;
    protected $verbose;

    public function __construct($input_format)
    {
        $this->input_format = $input_format;
    }

    public function OnCall(\Peg\CommandLine\Command $command)
    {
        if(!Application::ValidExtension())
            Error::Show(t("The current directory is not a valid peg managed extension."));

        if(!file_exists(Application::GetCwd() . "/definitions"))
            FileSystem::MakeDir(Application::GetCwd() . "/definitions");

        if($command->GetOption("input-format")->GetValue() == $this->input_format)
        {
            $this->command = $command;
            
            $this->output_format = $command->GetOption("output-format")->GetValue();

            if(trim($command->GetOption("headers")->GetValue()) != "")
            {
                $this->headers_path = rtrim(
                    str_replace(
                        "\\", 
                        "/", 
                        $command->GetOption("headers")->GetValue()
                    ), 
                    "/"
                ) . "/";
            }

            $this->verbose = $command->GetOption("verbose")->active;

            $this->Start($command->GetOption("source")->GetValue());
        }
    }

    /**
     * Needs to be implemented by classes extending this one in order to
     * begin the parsing process when the input format matches that of the
     * action been called.
     */
    abstract public function Start($path);
}

?>
