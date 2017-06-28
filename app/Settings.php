<?php

namespace App;

class Settings
{
    /**
     * Returns an option value from the database
     *
     * @param string $option Option name
     * @param mixed $default Value to return if option does not exist
     * @return mixed Option value
     */
    public static function get($option, $default = false)
    {
        $value = self::retrieveFromDatabase($option);

        if (!$value) {
            return $default;
        }

        $decodedValue = json_decode($value);
        if (json_last_error() == JSON_ERROR_NONE) {
            return $decodedValue;
        }

        return $value;
    }

    /**
     * Sets a value for an option. If the option already exists,
     * overwrites it's current value.
     *
     * @param string $option Option name
     * @param mixed $value Option value
     */
    public static function set($option, $value)
    {
        $currentValue = self::retrieveFromDatabase($option);

        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }

        if ($currentValue == null) {
            \DB::table('settings')
                ->insert(
                    ['option' => $option, 'value' => $value]
                );
        } else {
            \DB::table('settings')
                ->where('option', $option)
                ->update(['value' => $value]);
        }
    }

    /**
     * Removes an option from the database
     *
     * @param $option
     */
    public static function delete($option)
    {
        \DB::table('settings')
            ->where('option', $option)
            ->delete();
    }

    /**
     * Returns option value.
     *
     * @param string $option Option name
     * @return mixed Option value
     */
    public static function retrieveFromDatabase($option)
    {
        $option = \DB::table('settings')
            ->select('value')
            ->where('option', $option)
            ->first();
        if ($option) {
            return $option->value;
        }
        return false;
    }
}