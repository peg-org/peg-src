<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Utilities;

/**
 * Function to manage files and directories
 */
class FileSystem
{

    // Disable constructor
    private function __construct(){}

    /**
     * Get all the files and directories available on a specified path.
     * @param string $path
     * @return array List of files found.
     */
    public static function GetDirContent($path)
    {
        $files = array();
        $directory = opendir($path);

        while(($file = readdir($directory)) !== false)
        {
            $full_path = $path . "/" . $file;

            if(is_file($full_path))
            {
                $files[] = $full_path;
            }
            elseif($file != "." && $file != ".." && is_dir($full_path))
            {
                $files[] = $full_path;
                $files = array_merge($files, self::GetDirContent($full_path));
            }
        }

        closedir($directory);

        return $files;
    }

    /**
     * Same as php mkdir() but adds Operating system check and replaces
     * every / by \ on windows.
     * @param string $directory The directory to create.
     * @param integer $mode the permissions granted to the directory.
     * @param bool $recursive Recurse in to the path creating neccesary directories.
     * @return bool true on success false on fail.
     */
    public static function MakeDir($directory, $mode = 0755, $recursive = false)
    {
        if("" . strpos(PHP_OS, "WIN") . "" != "")
        {
            $directory = str_replace("/", "\\", $directory);
        }

        return mkdir($directory, $mode, $recursive);
    }

    /**
     * Copy a directory and its content to another directory replacing any file
     * on the target directory if already exist.
     * @param string $source The directory to copy.
     * @param string $target The copy destination.
     * @return bool true on success or false on fail.
     */
    public static function RecursiveCopyDir($source, $target)
    {
        $source_dir = opendir($source);

        //Check if source directory exists
        if(!$source_dir)
        {
            return false;
        }

        //Create target directory in case it doesnt exist
        if(!file_exists($target))
        {
            self::MakeDir($target, 0755, true);
        }

        while(($item = readdir($source_dir)) !== false)
        {
            $source_full_path = $source . "/" . $item;
            $target_full_path = $target . "/" . $item;

            if($item != "." && $item != "..")
            {
                //copy source files
                if(is_file($source_full_path))
                {
                    if(!copy($source_full_path, $target_full_path))
                    {
                        return false;
                    }
                }
                else if(is_dir($source_full_path))
                {
                    self::RecursiveCopyDir($source_full_path, $target_full_path);
                }
            }
        }

        closedir($source_dir);

        return true;
    }

    /**
     * Remove a directory that is not empty by deleting all its content.
     * @param string $directory The directory to delete with all its content.
     * @param string $empty Removes all directory contents keeping only itself.
     * @return bool True on success or false.
     */
    public static function RecursiveRemoveDir($directory, $empty = false)
    {
        // if the path has a slash at the end we remove it here
        if(substr($directory, -1) == '/')
        {
            $directory = substr($directory, 0, -1);
        }

        // if the path is not valid or is not a directory ...
        if(!file_exists($directory) || !is_dir($directory))
        {
            return false;

            // ... if the path is not readable
        }
        elseif(!is_readable($directory))
        {
            return false;
        }
        else
        {
            $handle = opendir($directory);

            while(false !== ($item = readdir($handle)))
            {
                if($item != '.' && $item != '..')
                {
                    // we build the new path to delete
                    $path = $directory . '/' . $item;

                    // if the new path is a directory
                    if(is_dir($path))
                    {
                        self::RecursiveRemoveDir($path);

                        // if the new path is a file
                    }
                    else
                    {
                        if(!unlink($path))
                        {
                            return false;
                        }
                    }
                }
            }

            closedir($handle);

            if($empty == false)
            {
                if(!rmdir($directory))
                {
                    return false;
                }
            }

            return true;
        }
    }
    
    /**
     * Only saves content to a file if the new content is not the same
     * as the original. This is helpful to prevent an unneccesary timestamp 
     * modification which is used by compilers to decide wether the file
     * needs recompilation.
     * @param string $file Path to file.
     * @param string $content New content of file.
     * @return bool True on success or false if file content is the same.
     */
    function WriteFileIfDifferent($file, &$contents)
    {
        $actual_file_content = "";

        if(file_exists($file))
            $actual_file_content = file_get_contents($file);

        if(crc32($actual_file_content) != crc32($contents))
        {
            file_put_contents($file, $contents);

            return true;
        }

        print false;
    }

}