<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Signals\Data\Lexers;

/**
 * Object sent by the LEXER_MESSAGE signal. 
 */
class Message extends \Peg\Signals\SignalData
{
    /**
     * A message representing current task been performed by the lexer.
     * @var string
     */
    public $message;
    
    public function __construct()
    {
        parent::__construct();
    }
}