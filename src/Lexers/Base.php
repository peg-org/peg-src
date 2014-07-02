<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lexers;

/**
 * Base for a lexer that extract and generates definition
 * files.
 */
abstract class Base extends \Peg\Signals\Signal
{

    /**
     * The path where resides the files with definitions of a c/c++ library.
     * @var string
     */
    public $definitions_path;
    
    /**
     * Full path where the definition c/c++ header files reside, this is 
     * used to correctly resolve a header file name by stripping this path out
     * and only leaving the real header name. This may not be required in all lexers.
     * @var string
     */
    public $headers_path;
    
    /**
     * The symbols object used to store definitions.
     * @var \Peg\Definitions\Symbols
     */
    public $symbols;
    
    /**
     * Object used to create the symbols cache files.
     * @var \Peg\Definitions\Exporter
     */
    public $exporter;
    
    /**
     * Object used to send message signals.
     * @var \Peg\Signals\Data\Lexers\Message
     */
    protected $signal_data;
    
    /**
     * Constructor.
     * @param type $definitions_path
     * @param type $headers_path
     * @throws \Exception If the definitions path does not exists.
     */
    public function __construct($definitions_path, $headers_path="")
    {
        if(!file_exists($definitions_path))
            throw new \Exception(
                t("Definitions directory does not exists.")
            );
        
        $this->definitions_path = $definitions_path;
        
        $this->headers_path = $headers_path;
        
        $this->symbols = new \Peg\Definitions\Symbols();
        
        $this->exporter = new \Peg\Definitions\Exporter($this->symbols);
        
        $this->signal_data = new \Peg\Signals\Data\Lexers\Message();
    }

    /**
     * Generates definition files in a specified path.
     * Can generate definitions of a specific type if the $type is specified
     * using one of the values from \Peg\Definitions\Type
     * @param string $path
     * @param string $type The type of definitions file to generate.
     */
    public function SaveDefinitions(
        $path = null, 
        $type=\Peg\Definitions\Type::JSON
    )
    {
        $this->exporter->definitions_path = $path;
        $this->exporter->export_type = $type;
        
        $this->exporter->Start();
    }
    
    /**
     * Sends a signal with message of current task being performed.
     * @param string $message
     */
    protected function SendMessage($message)
    {
        $this->signal_data->message = $message;
        
        $this->Send(
            \Peg\Signals\Type\Lexers::LEXER_MESSAGE,
            $this->signal_data
        );
    }

    /**
     * Needs to be implemented by classes extending this one in order to
     * begin the parsing process.
     */
    abstract public function Start();
}

?>
