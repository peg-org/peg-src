<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Signals\Type;

/**
 * Container for the various signal types sent by the definitions namespace.
 */
class Definitions
{
    /**
     * Signal sent by the definitions importer that can be used in a GUI 
     * frontend to display the current actions performed by the importer.
     * @see \Peg\Lib\Definitions\Importer
     * @see \Peg\Lib\Signals\Data\Definitions\ImportMessage
     */
    const IMPORT_MESSAGE = "definitions_import_message";
    
    /**
     * Signal sent by the definitions exporter that can be used in a GUI 
     * frontend to display the current actions performed by the exporter.
     * @see \Peg\Lib\Definitions\Exporter
     * @see \Peg\Lib\Signals\Data\Definitions\ExportMessage
     */
    const EXPORT_MESSAGE = "definitions_export_message";
}