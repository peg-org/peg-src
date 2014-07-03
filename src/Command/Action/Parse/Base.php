<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Command\Action\Parse;

use Peg\Lib\Application;
use Peg\Lib\CommandLine\Error;
use Peg\Lib\Utilities\FileSystem;

/**
 * Declares the base for a parse action that extract and generates definition
 * files.
 */
abstract class Base extends \Peg\Lib\CommandLine\Action
{
    /**
     * Reference to command that called this action.
     * @var \Peg\Lib\CommandLine\Command
     */
    protected $command;
    
    /**
     * Format of the files to parse/lex.
     * @var string
     */
    protected $input_format;
    
    /**
     * Format used to store the parsed definition files.
     * @see \Peg\Lib\Definitions\Type
     * @var string
     */
    protected $output_format;
    
    /**
     * This is used optionally by a children implementing this class in order
     * for them to correctly resolve a header file. For example: doxygen xml
     * files store the full path to the header file where a symbol was found, eg:
     * /home/user/libs/wx/frame.h, if this variable is set to /home/user/libs/
     * the final header file will be stored on the symbols object as wx/frame.h
     * @var string
     */
    protected $headers_path;
    
    /**
     * Flag that indicates if the lexer/parser should output messages of its 
     * current status.
     * @var bool
     */
    protected $verbose;

    /**
     * You derived class should override this and set the 
     * input_format name of your parser/lexer so it is called apropiately
     * when this variable is set.
     * @param string $input_format Format of files to parse, eg: doxygen
     */
    public function __construct($input_format)
    {
        $this->input_format = $input_format;
    }

    /**
     * You shouldn't override this method, instead write a Start() implementation.
     * @param \Peg\Lib\CommandLine\Command $command
     */
    public function OnCall(\Peg\Lib\CommandLine\Command $command)
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