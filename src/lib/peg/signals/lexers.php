<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Signals;

/**
 * Container for the various signal types sent by the lexers namespace.
 */
class Lexers
{
    /**
     * Signal sent by the definitions importer that can be used in a GUI 
     * frontend to display the current actions performed by the importer.
     * @see \Peg\Lexers\Base
     * @see \Peg\Signals\Lexers\Message
     */
    const LEXER_MESSAGE = "lexers_message";
}