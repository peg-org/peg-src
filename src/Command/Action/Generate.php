<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Command\Action;

use Peg\Application;
use Peg\CommandLine\Error;
use Peg\Utilities\FileSystem;

/**
 * Action taken if the generate command was executed.
 */
class Generate extends \Peg\CommandLine\Action
{
    
    public function OnCall(\Peg\CommandLine\Command $command)
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
