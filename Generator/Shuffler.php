<?php

namespace HLEA\Generator;

class Shuffler
{
    /**
     * Shuffles an array using the Fisher-Yates algorithm and a cryptographically secure pseudo-random generator.
     *
     * @param array                                $array The array to shuffle.
     * @param \HLEA\Generator\RandomGenerator|null $rand  An instance of RandomGenerator. If null, a new one will be
     *                                                    created.
     *
     * @return array The shuffled array.
     * @throws \Exception if it was not possible to gather sufficient entropy for the random generator.
     */
    public static function fisherYatesShuffle(array $array, RandomGenerator &$rand = null): array
    {
        $results = [];
        $index = null;

        if($rand === null){
            $rand = new RandomGenerator();
        }

        while(count($array) > 0){
            $index = $rand->randomInt(0, count($array) - 1);
            $results[] = $array[$index];
            array_splice($array, $index, 1);
        }

        return $results;
    }
}