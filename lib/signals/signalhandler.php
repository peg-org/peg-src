<?php
/**
 * @author Jefferson González
 * @license MIT
 */

namespace Signals;

/**
 * Assist on the management of signals send at a global scope
 * thru the whole system.
 */
class SignalHandler
{

    /**
     * @var array
     */
    private static $listeners = array();

    /**
     * Disable constructor
     */
    private function __construct(){}

    /**
     * Calls all callbacks listening for a given signal type.
     * The $var1-$var6 are optional parameters passed to the callback.
     * @param string $signal_type
     * @param \Signals\SignalData $signal_data
     */
    public static function Send($signal_type, \Signals\SignalData &$signal_data = null)
    {
        if(!isset(self::$listeners[$signal_type]))
            return;

        foreach(self::$listeners[$signal_type] as $callback_data)
        {
            $callback = $callback_data['callback'];

            if(is_object($signal_data))
                $callback($signal_data);
            else
                $callback();
        }
    }

    /**
     * Add a callback that listens to a specific signal.
     * @param string $signal_type
     * @param function $callback
     * @param int $priority
     */
    public static function Listen($signal_type, $callback, $priority = 10)
    {
        if(!isset(self::$listeners[$signal_type]))
            self::$listeners[$signal_type] = array();

        self::$listeners[$signal_type][] = array(
            'callback' => $callback,
            'priority' => $priority
        );

        self::$listeners[$signal_type] = self::Sort(
            self::$listeners[$signal_type], 'priority'
        );
    }

    /**
     * Remove a callback from listening a given signal type.
     * @param string $signal_type
     * @param function $callback
     */
    public static function Unlisten($signal_type, $callback)
    {
        if(!isset(self::$listeners[$signal_type]))
            return;

        if(is_array(self::$listeners[$signal_type]))
        {
            foreach(self::$listeners[$signal_type] as $position => $callback_data)
            {
                $stored_callback = $callback_data['callback'];

                if($callback == $stored_callback)
                {
                    unset(self::$listeners[$signal_type][$position]);
                    break;
                }
            }
        }

        if(count(self::$listeners[$signal_type]) <= 0)
            unset(self::$listeners[$signal_type]);
    }
    
    /**
     * Sorts an array of listener function using bubble sort.
     *
     * @param array $data_array The array to sort in the format returned by data_parser().
     * @param string $field_name The field we are using to sort the array by.
     * @param mixed $sort_method The type of sorting, default is ascending. 
     *
     * @return array The same array but sorted by the given field name.
     */
    public static function Sort($data_array, $field_name, $sort_method = SORT_ASC)
    {
        $sorted_array = array();

        if(is_array($data_array))
        {
            $field_to_sort_by = array();
            $new_id_position = array();

            foreach($data_array as $key=>$fields)
            {
                $field_to_sort_by[$key] = $fields[$field_name];
                $new_id_position[$key] = $key;
            }

            array_multisort($field_to_sort_by, $sort_method, $new_id_position, $sort_method);

            foreach($new_id_position as $id)
            {
                $sorted_array[$id] = $data_array[$id];
            }
        }

        return $sorted_array;
    }

}

?>
