<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Command\Action\Parse;

use Peg\Lib\Parse\DefinitionsType;
use Peg\Lib\Application;

/**
 * Implements a doxygen definitions parser action using \Peg\Lib\Lexers\Doxygen.
 * @see \Peg\Lib\Lexers\Doxygen
 */
class Doxygen extends \Peg\Lib\Command\Action\Parse\Base
{

    /**
     * The lexer that is going to be used for extracting definitions.
     * @var \Peg\Lib\Lexers\Doxygen
     */
    public $lexer;
    
    /**
     * Initialize this action to be of input type doxygen.
     */
    public function __construct()
    {
        parent::__construct("doxygen");
    }

    /**
     * Initializes the parsing/lexing process.
     * @param string $path Were the doxygen xml files reside.
     */
    public function Start($path)
    {
        $this->lexer = new \Peg\Lib\Lexers\Doxygen($path, $this->headers_path);
        
        // Start lexer
        if($this->verbose)
        {
            $this->lexer->Listen(
                \Peg\Lib\Signals\Type\Lexers::LEXER_MESSAGE, 
                function(\Peg\Lib\Signals\Data\Lexers\Message $signal_data){
                    print $signal_data->message . "\n";
                }
            );
        }
        
        $this->lexer->Start();
        
        // Create definitions cache
        if($this->verbose)
        {
            $this->lexer->exporter->Listen(
                \Peg\Lib\Signals\Type\Definitions::EXPORT_MESSAGE, 
                function(\Peg\Lib\Signals\Data\Definitions\ExportMessage $signal_data){
                    print $signal_data->message . "\n";
                }
            );
        }
        
        $this->lexer->SaveDefinitions("definitions", $this->output_format);
    }

}