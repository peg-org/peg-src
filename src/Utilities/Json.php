<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Utilities;

/**
 * Encapsulates json functions to provide indentation
 * as conversion from json to plain php arrays.
 */
class Json
{

    // Disable constructor
    private function __construct(){}

    /**
     * Equivalent of json_encode function but output pretty printed
     * json format to make it possible to edit the output manually.
     * @param array $data
     * @return string
     */
    public static function Encode($data)
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Equivalent to json_decode for json but with associative turned on.
     * This function retreive json objects as associative array.
     * @param string $data Json encoded data.
     * @return array
     */
    public static function Decode($data)
    {
        return json_decode($data, true);
    }

}