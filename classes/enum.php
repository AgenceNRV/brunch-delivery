<?php

/**
 * enum
 *
 * @package nrvbd/classes
 * @version 0.9.0
 * @since   0.9.0
 *
 * Class used to manage the Enum types.
 */

namespace nrvbd;

if (!class_exists('\nrvbd\enum')) {

    abstract class enum
    {

        /**
         * Used to store constants list
         * @var array
         */
        private static $cache = array();


        /**
         * Check if the constant name exists in the Reflected class
         * @method checkName
         * @param  string    $name                  Name of the wanted constant
         * @param  boolean   $sensitive             Case sensitive ?
         * @return boolean
         */
        public static function checkName(string $name, bool $sensitive = false)
        {
            $constants = self::constants();
            if ($sensitive) {
                return array_key_exists($name, $constants);
            }

            $keys = array_map('strtolower', array_keys($constants));
            return in_array(strtolower($name), $keys);
        }


        /**
         * Check if the constant value exists
         * @method checkValue
         * @param  string     $value                Value to search
         * @param  boolean    $sensitive               Case sensitive ?
         * @return boolean
         */
        public static function checkValue(string $value, bool $sensitive = false)
        {
            return in_array($value, array_values(self::constants()), $sensitive);
        }


        /**
         * Undocumented function
         * @method getValue
         * @param  string $name
         * @return mixed
         */
        public static function getValue(string $name)
        {
            $constants = self::constants();
            return $constants[$name];
        }

        /**
         * Return the constants list of the Reflected Class
         * @method constants
         * @return array
         */
        public static function constants()
        {
            $call = get_called_class();
            if (!array_key_exists($call, self::$cache)) {
                $reflect = new \ReflectionClass($call);
                self::$cache[$call] = $reflect->getConstants();
            }
            return self::$cache[$call];
        }
    }
}
