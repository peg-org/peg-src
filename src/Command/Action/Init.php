<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\Command\Action;

use Peg\Lib\Settings;
use Peg\Lib\Application;
use Peg\Lib\CommandLine\Error;
use Peg\Lib\Utilities\FileSystem;

/**
 * Action taken if the init command was executed.
 */
class Init extends \Peg\Lib\CommandLine\Action
{

    public function OnCall(\Peg\Lib\CommandLine\Command $command)
    {
        // Get option values
        $authors = $command->GetOption("authors")->GetValue();
        $contributors = $command->GetOption("contributors")->GetValue();
        $name = $command->GetOption("name")->GetValue();
        $version = $command->GetOption("initial-version")->GetValue();
        $force = $command->GetOption("force")->active;
        $config_type = $command->GetOption("config-type")->GetValue();

        // Set output directory
        $extension_dir = Application::GetCwd();

        if(strlen($command->value) > 0)
            $extension_dir .= "/" . $command->value;

        if(!file_exists($extension_dir))
            FileSystem::MakeDir($extension_dir, 0755, true);

        // Get extension name
        $dir_parts = explode("/", str_replace("\\", "/", $extension_dir));

        $extension = $dir_parts[count($dir_parts) - 1];

        if(strlen($name) > 0)
            $extension = $name;

        $files = FileSystem::GetDirContent($extension_dir);

        if(!$force)
        {
            if(count($files) > 0)
                Error::Show(t("The directory you are trying to initialize is not empty."));
        }
        
        // Create configuration file
        if($config_type == "json")
        {
            Settings::SetBackEnd(new \Peg\Lib\Config\JSON);
            Settings::Load($extension_dir, "peg.json");
        }
        else
        {
            Settings::SetBackEnd(new \Peg\Lib\Config\INI);
            Settings::Load($extension_dir, "peg.conf");
        }

        $this->CopySkeleton($extension_dir, $extension, $version, $authors, $contributors);
    }

    /**
     * Takes the actions needed to generate the extension skeleton.
     * @param string $directory Path to extension
     * @param string $extension Name of the extension
     * @param string $authors Comman sperated list of authors
     * @param string $contributors Comma seperated list of contributors
     */
    private function CopySkeleton($directory, $extension, $version, $authors, $contributors)
    {
        Settings::SetExtensionName($extension);
        Settings::SetAuthors($authors);
        Settings::SetContributors($contributors);
        Settings::SetVersion($version);

        FileSystem::RecursiveCopyDir(Application::GetSkeletonPath(), $directory);

        // Modify CHANGES template
        $date = date("F d Y");

        ob_start();
        include($directory . "/CHANGES");

        $changes_content = ob_get_contents();
        ob_end_clean();

        file_put_contents($directory . "/CHANGES", $changes_content);

        // Modify CREDITS template
        $developers = trim($authors . ", " . $contributors, ", ");

        ob_start();
        include($directory . "/CREDITS");

        $credits_content = ob_get_contents();
        ob_end_clean();

        file_put_contents($directory . "/CREDITS", $credits_content);
    }

}