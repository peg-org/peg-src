<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Command\Action\Generate;

use Peg\Lib\Application;
use Peg\Lib\CommandLine\Error;

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
     * Name of the engine for which the source code is going to be generated.
     * @var string
     */
    protected $engine;
    
    /**
     * Flag that indicates if the generator should output messages of its 
     * current status.
     * @var bool
     */
    protected $verbose;

    /**
     * Your derived class should override this and set the 
     * engine name of your generator so it is called apropiately
     * when this variable is set.
     * @param string $engine Name of the engine.
     */
    public function __construct($engine)
    {
        $this->engine = $engine;
    }

    /**
     * You shouldn't override this method, instead write a Start() implementation.
     * @param \Peg\Lib\CommandLine\Command $command
     */
    public function OnCall(\Peg\Lib\CommandLine\Command $command)
    {
        if(!Application::ValidExtension())
            Error::Show(t("The current directory is not a valid peg managed extension."));

        if(!file_exists(Application::GetCwd() . "/templates"))
            Error::Show(t("Template files are missing."));

        if($command->GetOption("engine")->GetValue() == $this->engine)
        {
            $this->command = $command;

            $this->verbose = $command->GetOption("verbose")->active;

            $this->Start();
        }
    }

    /**
     * Needs to be implemented by classes extending this one in order to
     * begin the generator process when the engine matches that of the
     * action been called.
     */
    abstract public function Start();
}