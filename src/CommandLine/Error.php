<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\CommandLine;

/**
 * Functions to throw error messages.
 */
class Error
{

    /**
     * Displays a message and exits the application with error status code.
     * @param string $message The message to display before exiting the application.
     */
    public static function Show($message)
    {
        fwrite(STDERR, t("Error:") . " " . $message . "\n");
        exit(1);
    }

}