<?php
namespace GravityLegal\GravityLegalAPI;
use Exception;

class Utility
{
    /**
         * Convert an object to a specific class.
         * @param object $object
         * @param string $class_name The class to cast the object to
         * @return object
         */
    public static function cast($object, $class_name)
    {
        if ($object === false) {
            return false;
        }
        if (class_exists($class_name)) {
            $ser_object     = serialize($object);
            $obj_name_len     = strlen(get_class($object));
            $start             = $obj_name_len + strlen($obj_name_len) + 6;
            $new_object      = 'O:' . strlen($class_name) . ':"' . $class_name . '":';
            $new_object     .= substr($ser_object, $start);
            $new_object     = unserialize($new_object);
            /**
             * The new object is of the correct type but
             * is not fully initialized throughout its graph.
             * To get the full object graph (including parent
             * class data, we need to create a new instance of
             * the specified class and then assign the new
             * properties to it.
             */
            $graph = new $class_name;
            foreach ($new_object as $prop => $val) {
                $graph->$prop = $val;
            }
            return $graph;
        } else {
            throw new Exception("Could not find class $class_name for casting in DB::cast");
            return false;
        }
    }
}