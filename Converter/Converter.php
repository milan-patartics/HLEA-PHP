<?php

namespace HLEA\Converter;

class Converter
{
    /**
     * Converts a single binary string character to an unsigned byte.
     *
     * @param string $binary The binary string character to convert. If it is longer than one character, everything
     *                       after the first character will be ignored.
     *
     * @return int The unsigned byte that represents the value of the binary string character.
     */
    public static function binaryToByte(string $binary): int
    {
        return unpack('C', $binary[0])[1];
    }

    /**
     * Converts an unsigned byte to a single binary string character.
     *
     * @param int $byte The unsigned byte to convert to a binary string character.
     *
     * @return string The binary string character that represents the value of the unsigned byte.
     */
    public static function byteToBinary(int $byte): string
    {
        return pack('C', $byte);
    }

    /**
     * Converts a binary string to an array of unsigned bytes.
     *
     * @param string $binary The binary string to convert.
     *
     * @return array The array of unsigned bytes that represents the value of the binary string.
     */
    public static function binaryToByteArray(string $binary): array
    {
        $length = strlen($binary);
        $byteArray = [];

        for($i = 0; $i < $length; $i++){
            $byteArray[] = self::binaryToByte($binary[$i]);
        }

        return $byteArray;
    }

    /**
     * Converts an array of unsigned bytes to a binary string.
     *
     * @param array $byteArray The unsigned byte array to convert.
     *
     * @return string The binary string that represents the value of the unsigned byte array.
     */
    public static function byteArrayToBinary(array $byteArray): string
    {
        $binary = '';
        foreach($byteArray as $byte){
            $binary .= self::byteToBinary($byte);
        }

        return $binary;
    }

    /**
     * Converts a binary string to an array of unsigned 16 bit integers.
     *
     * @param string $binary The binary string to convert.
     *
     * @return array The array of unsigned 16 bit integers that represents the value of the binary string.
     */
    public static function binaryToUInt16Array(string $binary): array
    {
        $length = strlen($binary);
        $uInt16Array = [];

        for($i = 0; $i < $length; $i += 2){
            $uInt16Array[] = self::bytePairToUInt16([
                self::binaryToByte($binary[$i]),
                self::binaryToByte($binary[$i + 1]),
            ]);
        }

        return $uInt16Array;
    }

    /**
     * Converts an array of unsigned 16 bit integers to a binary string.
     *
     * @param array $uInt16Array The array of 16 bit integers to convert.
     *
     * @return string The binary string that represents the value of the array of unsigned 16 bit integers.
     */
    public static function uInt16ArrayToBinary(array $uInt16Array): string
    {
        $binary = '';
        foreach($uInt16Array as $uInt16){
            $binary .= self::byteArrayToBinary(
                self::uInt16ToBytePair($uInt16)
            );
        }

        return $binary;
    }

    /**
     * Converts a binary string to an unsigned 32 bit integer.
     *
     * @param string $binary The binary string to convert. If it is longer than four characters (4 bytes = 32
     *                       bits), everything after the first four characters will be ignored.
     *
     * @return int The unsigned 32 bit integer that represents the value of the binary string.
     */
    public static function binaryToUInt32(string $binary): int
    {
        return unpack('V', $binary[0] . $binary[1] . $binary[2] . $binary[3])[1];
    }

    /**
     * Converts an unsigned 32 bit integer to a binary string.
     *
     * @param int $uInt32 The unsigned 32 bit integer to convert.
     *
     * @return string The binary string that represents the value of the unsigned 32 bit integer.
     */
    public static function uInt32ToBinary(int $uInt32): string
    {
        return pack('V', $uInt32);
    }

    /**
     * Converts an unsigned 16 bit integer to a pair of unsigned bytes.
     *
     * @param int $uInt16 The unsigned 16 bit integer to convert.
     *
     * @return array The array of the unsigned byte pair that represents the value of the unsigned 16 bit integer.
     */
    public static function uInt16ToBytePair(int $uInt16): array
    {
        $binary = pack('n', $uInt16);

        return [
            self::binaryToByte($binary[0]),
            self::binaryToByte($binary[1]),
        ];
    }

    /**
     * Converts a pair of unsigned bytes to an unsigned 16 bit integer.
     *
     * @param array $bytes The array of the unsigned byte pair to convert.
     *
     * @return int The unsigned 16 bit integer that represents the value of the array of the unsigned byte pair.
     */
    public static function bytePairToUInt16(array $bytes): int
    {
        return unpack('n', self::byteToBinary($bytes[0]) . self::byteToBinary($bytes[1]))[1];
    }
}