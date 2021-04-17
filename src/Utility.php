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

/**
* Returns a GUIDv4 string
*
* Uses the best cryptographically secure method
* for all supported pltforms with fallback to an older,
* less secure version.
*
* @param bool $trim
* @return string
*/
public static function GUIDv4 ($trim = true)
{
    // Windows
    if (function_exists('com_create_guid') === true) {
        if ($trim === true)
            return trim(com_create_guid(), '{}');
        else
            return com_create_guid();
    }

    // OSX/Linux
    if (function_exists('openssl_random_pseudo_bytes') === true) {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    // Fallback (PHP 4.2+)
    mt_srand((double)microtime() * 10000);
    $charid = strtolower(md5(uniqid(rand(), true)));
    $hyphen = chr(45);                  // "-"
    $lbrace = $trim ? "" : chr(123);    // "{"
    $rbrace = $trim ? "" : chr(125);    // "}"
    $guidv4 = $lbrace.
              substr($charid,  0,  8).$hyphen.
              substr($charid,  8,  4).$hyphen.
              substr($charid, 12,  4).$hyphen.
              substr($charid, 16,  4).$hyphen.
              substr($charid, 20, 12).
              $rbrace;
    return $guidv4;
}
}