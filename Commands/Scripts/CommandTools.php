<?php

namespace HLEA\Commands\Scripts;

class CommandTools
{
    /**
     * Return an associative array by parsing the arguments.
     * The delimiter character of key -> value pairs is the '=' character.
     *
     * @param array $argv The array of arguments to parse.
     *
     * @return array The associative array that contains parsed key -> value pairs.
     */
    public static function parseArgv(array &$argv): array
    {
        $arguments = [];
        $length = count($argv);

        for($i = 1; $i < $length; $i++){
            $parts = explode('=', $argv[$i]);
            if(count($parts) === 1){
                $arguments[self::trimArgvKey($parts[0])] = true;
            }
            if(count($parts) === 2){
                $arguments[self::trimArgvKey($parts[0])] = self::trimArgvValue($parts[1]);
            }
        }

        return $arguments;
    }

    /**
     * Removes all '-' characters from the beginning and end of $key and returns the trimmed key.
     *
     * @param string $key The key to trim.
     *
     * @return string The trimmed key.
     */
    private static function trimArgvKey(string &$key): string
    {
        return trim($key, '-');
    }

    /**
     * Removes all wrapping character ("'„”`´˝) from the beginning and end of $value and returns the trimmed value.
     *
     * @param string $value The value to trim.
     *
     * @return string The trimmed value.
     */
    private static function trimArgvValue(string $value): string
    {
        return trim($value, '"\'„”`´˝');
    }
}