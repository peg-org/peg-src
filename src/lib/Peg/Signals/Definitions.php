<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Signals;

/**
 * Container for the various signal types sent by the definitions namespace.
 */
class Definitions
{
    /**
     * Signal sent by the definitions importer that can be used in a GUI 
     * frontend to display the current actions performed by the importer.
     * @see \Peg\Definitions\Importer
     * @see \Peg\Signals\Definitions\ImportMessage
     */
    const IMPORT_MESSAGE = "definitions_import_message";
    
    /**
     * Signal sent by the definitions exporter that can be used in a GUI 
     * frontend to display the current actions performed by the exporter.
     * @see \Peg\Definitions\Exporter
     * @see \Peg\Signals\Definitions\ExportMessage
     */
    const EXPORT_MESSAGE = "definitions_export_message";
}