<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Command\Action;

use Peg\Lib\Application;
use Peg\Lib\CommandLine\Error;
use Peg\Lib\Utilities\FileSystem;

/**
 * Action taken if the generate command was executed.
 */
class Generate extends \Peg\Lib\CommandLine\Action
{
    
    public function OnCall(\Peg\Lib\CommandLine\Command $command)
    {
        foreach(Application::GetDefinitions()->headers as $file_name=>$file)
        {
            foreach($file->namespaces as $namespace_name=>$namespace)
            {
                if(!$namespace->HasConstants())
                    continue;
                
                foreach($namespace->constants as $constant_name=>$constant)
                {
                    //print $constant_name . $namespace->is_global . "\n";
                }
            }
        }
        
        foreach(Application::GetDefinitions()->headers as $file_name=>$header)
        {
            //print $file_name . "\n";
        }
    }
    
}

?>
