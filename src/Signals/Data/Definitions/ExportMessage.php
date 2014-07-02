<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Signals\Data\Definitions;

/**
 * Object sent by the EXPORT_MESSAGE signal. 
 */
class ExportMessage extends \Peg\Signals\SignalData
{
    /**
     * A message representing current task been performed by the exporter.
     * @var string
     */
    public $message;
    
    public function __construct()
    {
        parent::__construct();
    }
}