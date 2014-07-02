<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
*/

namespace Peg\Plugins;

/**
 * A basic plugin loader.
 */
class Loader
{

    /**
     * A list of loaded plugins.
     * @var \Peg\Plugins\Base[]
     */
    public $plugins;

    /**
     * Create the plugins loader object.
     */
    public function __construct()
    {
        $this->plugins = array();
    }

    /**
     * Scans a given path for valid plugin files and load them if possible.
     * This method should be called after everything else in the application
     * has been properly initialized.
     * @param string $path
     */
    public function Start($path)
    {
        if(!is_dir($path))
            return;

        $path = rtrim($path, "/\\") . "/";

        $elements = scandir($path);

        foreach($elements as $file_name)
        {
            if(is_file($path . $file_name))
            {
                $file_parts = explode(".", $file_name, 2);

                if(count($file_parts) < 2)
                    continue;

                $class_name = $file_parts[0];
                $class_name_ns = "Peg\\Plugins\\$class_name";
                $file_extension = $file_parts[1];

                if($file_extension == "php")
                {
                    include($path . $file_name);

                    if(in_array($class_name_ns, get_declared_classes()))
                    {
                        $this->plugins[$class_name] = new $class_name_ns;

                        $this->plugins[$class_name]->name = $class_name;

                        $this->plugins[$class_name]->path = $path . $file_name;

                        $this->plugins[$class_name]->OnInit();
                    }
                }
            }
        }
    }

}