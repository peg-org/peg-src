<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Signals\Data\Definitions;

/**
 * Object sent by the IMPORT_MESSAGE signal. 
 */
class ImportMessage extends \Peg\Lib\Signals\SignalData
{
    /**
     * A message representing current task been performed by the importer.
     * @var string
     */
    public $message;
    
    public function __construct()
    {
        parent::__construct();
    }
}