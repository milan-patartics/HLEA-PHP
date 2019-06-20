<?php

namespace HLEA\Generator;

use HLEA\Converter\Converter;

class RandomGenerator
{
    /**
     * Generates a cryptographically secure pseudo-random integer between $min and $max (inclusive).
     *
     * @param int $min The lowest value to be returned, which must be PHP_INT_MIN or higher.
     * @param int $max The highest value to be returned, which must be less than or equal to PHP_INT_MAX.
     *
     * @return int The cryptographically secure pseudo-random integer.
     * @throws \Exception if it was not possible to gather sufficient entropy for the random generator.
     */
    public function randomInt(int $min, int $max): int
    {
        return random_int($min, $max);
    }

    /**
     * Generates a cryptographically secure pseudo-random unsigned byte.
     *
     * @return int The cryptographically secure pseudo-random unsigned byte.
     * @throws \Exception if it was not possible to gather sufficient entropy for the random generator.
     */
    public function randomByte(): int
    {
        return self::randomInt(0, 255);
    }

    /**
     * Generates a cryptographically secure pseudo-random unsigned 16 bit integer.
     *
     * @return int The cryptographically secure pseudo-random unsigned 16 bit integer.
     * @throws \Exception if it was not possible to gather sufficient entropy for the random generator.
     */
    public function randomUInt16(): int
    {
        return self::randomInt(0, 65535);
    }

    /**
     * Generates a cryptographically secure pseudo-random binary string.
     *
     * @param int $length The number of binary string characters to generate.
     *
     * @return string The cryptographically secure pseudo-random binary string.
     * @throws \Exception if it was not possible to gather sufficient entropy for the random generator.
     */
    public function randomBinary(int $length): string
    {
        $binary = '';
        for($i = 0; $i < $length; $i++){
            $binary .= Converter::byteToBinary(
                self::randomByte()
            );
        }

        return $binary;
    }
}