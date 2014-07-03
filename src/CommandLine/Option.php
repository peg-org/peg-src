<?php
/**
 * @author Jefferson González
 * @license MIT
 * @link http://github.com/peg-org/peg-src Source code.
 */

namespace Peg\Lib\CommandLine;

/**
 * Represents a command line option.
 */
class Option
{

    /**
     * The human readable name of the option. Example: --use-something stored
     * on this variable as use-something.
     * @var string
     */
    public $long_name;

    /**
     * A one letter case-sensitive alias for the option. Example: -u stored on
     * this variable as u.
     * @var string
     */
    public $short_name;

    /**
     * Data type of the option represented by one of the constants from
     * \Peg\Lib\CommandLine\OptionType
     * @var integer
     */
    public $type;

    /**
     * Current value of the option.
     * @var integer|string
     */
    public $value;

    /**
     * A default value used in case the user didn't provided one and the option
     * isn't marked as required.
     * @var integer|string
     */
    public $default_value;

    /**
     * Indicates if an option of type FLAG was passed on the command line.
     * @var boolean
     */
    public $active;

    /**
     * Flag that indicates if the option is required or not.
     * @var boolean
     */
    public $required;

    /**
     * Information displayed when the application is executed without
     * options or with: -h, --help
     * @var string
     */
    public $description;

    /**
     * Initilize the option with an optional list of properties defined
     * on an array.
     * @param array $properties
     */
    public function __construct($properties = null)
    {
        if(is_array($properties) && count($properties) == 6)
        {
            $this->long_name = $properties["long_name"];
            $this->short_name = $properties["short_name"];
            $this->type = $properties["type"] ? $properties["type"] : OptionType::STRING;
            $this->default_value = $properties["default_value"] ? $properties["default_value"] : "";
            $this->required = $properties["required"];
            $this->description = $properties["description"];
        }
        else
        {
            $this->type = OptionType::STRING;
            $this->default_value = "";
        }
    }

    /**
     * Checks if the option value is valid.
     * @return boolean
     */
    public function IsValid()
    {
        if($this->required && !isset($this->value))
            return false;

        // Check with user given value
        if(isset($this->value))
        {
            if($this->type == OptionType::INTEGER && is_int($this->value))
                return true;

            if($this->type == OptionType::STRING && is_string($this->value))
                return true;
        }

        // Check with default value
        if($this->type == OptionType::INTEGER && is_int($this->default_value))
            return true;

        if($this->type == OptionType::STRING && is_string($this->default_value))
            return true;

        // Check if flag
        if($this->type == OptionType::FLAG && $this->active)
            return true;

        return false;
    }

    /**
     * Automatically returns the current or default value or null if neither
     * is set.
     * @return int|string
     */
    public function GetValue()
    {
        if(isset($this->value))
            return $this->value;

        elseif(isset($this->default_value))
            return $this->default_value;

        return null;
    }

    /**
     * Useful to add a value while checking if the value isn't in fact another
     * parameter like --other -o
     * @param integer|string $value
     * @return boolean True if value was set otherwise false.
     */
    public function SetValue($value)
    {
        if(ltrim($value, "-") == $value)
        {
            if(
                    $this->type == OptionType::STRING &&
                    $this->required &&
                    strlen($value) < 1
            )
                return false;

            $this->value = $value;
            return true;
        }

        return false;
    }

}