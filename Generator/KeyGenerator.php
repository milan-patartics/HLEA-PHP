<?php

namespace HLEA\Generator;

use HLEA\Exceptions\InvalidShuffleIterationCount;
use HLEA\Key\ByteSwapTable;
use HLEA\Key\Key;
use HLEA\Key\UInt16SwapTable;

class KeyGenerator
{
    private $rand = null;

    public const DEFAULT_BYTE_SWAP_TABLE_SHUFFLE_ITERATION_COUNT = 8192;
    public const DEFAULT_BYTE_STREAM_LENGTH = 306004;
    public const DEFAULT_UINT16_SWAP_TABLE_SHUFFLE_ITERATION_COUNT = 4;
    public const DEFAULT_UINT16_STREAM_LENGTH = 239954;

    /**
     * KeyGenerator constructor.
     */
    public function __construct()
    {
        $this->rand = new RandomGenerator();
    }

    /**
     * Generates a new HLEA Key object.
     *
     * @param int $primaryByteSwapTableShuffleIterationCount     The count of shuffling iterations of the primary
     *                                                           ByteSwapTable (optional). Must be greater than zero.
     * @param int $byteStreamLength                              The length of the ByteStream (optional).
     * @param int $secondaryByteSwapTableShuffleIterationCount   The count of shuffling iterations of the secondary
     *                                                           ByteSwapTable (optional). Must be greater than zero.
     * @param int $primaryUInt16SwapTableShuffleIterationCount   The count of shuffling iterations of the primary
     *                                                           UInt16SwapTable (optional). Must be greater than zero.
     * @param int $uInt16StreamLength                            The length of the UInt16Stream (optional).
     * @param int $secondaryUInt16SwapTableShuffleIterationCount The count of shuffling iterations of the secondary
     *                                                           UInt16SwapTable (optional). Must be greater than zero.
     *
     * @return \HLEA\Key\Key The generated HLEA key.
     * @throws \HLEA\Exceptions\InvalidShuffleIterationCount if any of the shuffle iteration count parameters is not
     *                                                       greater than zero.
     * @throws \Exception if it was not possible to gather sufficient entropy for the random generator.
     */
    public function generateKey(
        int $primaryByteSwapTableShuffleIterationCount = self::DEFAULT_BYTE_SWAP_TABLE_SHUFFLE_ITERATION_COUNT,
        int $byteStreamLength = self::DEFAULT_BYTE_STREAM_LENGTH,
        int $secondaryByteSwapTableShuffleIterationCount = self::DEFAULT_BYTE_SWAP_TABLE_SHUFFLE_ITERATION_COUNT,

        int $primaryUInt16SwapTableShuffleIterationCount = self::DEFAULT_UINT16_SWAP_TABLE_SHUFFLE_ITERATION_COUNT,
        int $uInt16StreamLength = self::DEFAULT_UINT16_STREAM_LENGTH,
        int $secondaryUInt16SwapTableShuffleIterationCount = self::DEFAULT_UINT16_SWAP_TABLE_SHUFFLE_ITERATION_COUNT): Key
    {
        $primaryByteSwapTable = $this->generateByteSwapTableArray($primaryByteSwapTableShuffleIterationCount);
        $byteStream = $this->generateByteStreamArray($byteStreamLength);
        $secondaryByteSwapTable = $this->generateByteSwapTableArray($secondaryByteSwapTableShuffleIterationCount);

        $primaryUInt16SwapTable = $this->generateUInt16SwapTableArray($primaryUInt16SwapTableShuffleIterationCount);
        $uInt16Stream = $this->generateUInt16StreamArray($uInt16StreamLength);
        $secondaryUInt16SwapTable = $this->generateUInt16SwapTableArray($secondaryUInt16SwapTableShuffleIterationCount);

        $key = new Key();
        $key->loadFromArrays(
            $primaryByteSwapTable,
            $byteStream,
            $secondaryByteSwapTable,

            $primaryUInt16SwapTable,
            $uInt16Stream,
            $secondaryUInt16SwapTable
        );

        return $key;
    }

    /**
     * Generates a data array for a ByteSwapTable.
     *
     * @param int $shuffleIterationCount The count of shuffling iterations (optional). Must be greater than zero.
     *
     * @return array The data array for the ByteSwapTable.
     * @throws \HLEA\Exceptions\InvalidShuffleIterationCount if $shuffleIterationCount is not greater than zero.
     */
    public function generateByteSwapTableArray(int $shuffleIterationCount = self::DEFAULT_BYTE_SWAP_TABLE_SHUFFLE_ITERATION_COUNT): array
    {
        return $this->generateSwapTableArray(
            ByteSwapTable::SIZE,
            $shuffleIterationCount
        );
    }

    /**
     * Generates an array of cryptographically secure unsigned bytes for a ByteStream.
     *
     * @param int $streamLength The number of unsigned bytes to generate (optional).
     *
     * @return array The array of the unsigned bytes.
     * @throws \Exception if it was not possible to gather sufficient entropy for the random generator.
     */
    public function generateByteStreamArray(int $streamLength = self::DEFAULT_BYTE_STREAM_LENGTH): array
    {
        $byteStream = [];
        for($i = 0; $i < $streamLength; $i++){
            $byteStream[] = $this->rand->randomByte();
        }

        return $byteStream;
    }

    /**
     * Generates a data array for a UInt16SwapTable.
     *
     * @param int $shuffleIterationCount The count of shuffling iterations (optional). Must be greater than zero.
     *
     * @return array The data array for the UInt16SwapTable.
     * @throws \HLEA\Exceptions\InvalidShuffleIterationCount if $shuffleIterationCount is not greater than zero.
     */
    public function generateUInt16SwapTableArray(int $shuffleIterationCount = self::DEFAULT_UINT16_SWAP_TABLE_SHUFFLE_ITERATION_COUNT): array
    {
        return $this->generateSwapTableArray(
            UInt16SwapTable::SIZE,
            $shuffleIterationCount
        );
    }

    /**
     * Generates an array of cryptographically secure unsigned 16 bit integers for a UInt16Stream.
     *
     * @param int $streamLength Number of unsigned 16 bit integers to generate (optional).
     *
     * @return array The array of the unsigned 16 bit integers.
     * @throws \Exception if it was not possible to gather sufficient entropy for the random generator.
     */
    public function generateUInt16StreamArray(int $streamLength = self::DEFAULT_UINT16_STREAM_LENGTH): array
    {
        $uInt16Stream = [];
        for($i = 0; $i < $streamLength; $i++){
            $uInt16Stream[] = $this->rand->randomUInt16();
        }

        return $uInt16Stream;
    }

    /**
     * Generates a data array for a SwapTable.
     *
     * @param int $tableSize             Size of the SwapTable to generate (for example: 256 for bytes, 65536 for 16
     *                                   bit integers).
     * @param int $shuffleIterationCount The count of shuffling iterations. Must be greater than zero.
     *
     * @return array The data array for the SwapTable.
     * @throws \HLEA\Exceptions\InvalidShuffleIterationCount if $shuffleIterationCount is not greater than zero.
     */
    private function generateSwapTableArray(int $tableSize, int &$shuffleIterationCount): array
    {
        $swapTableArray = [];
        for($i = 0; $i < $tableSize; $i++){
            $swapTableArray[] = $i;
        }

        return $this->shuffle($swapTableArray, $shuffleIterationCount);
    }

    /**
     * Shuffles an array using the Fisher-Yates algorithm and a cryptographically secure pseudo-random generator.
     *
     * @param array $array          The array to shuffle.
     * @param int   $iterationCount The count of shuffling iterations. Must be greater than zero.
     *
     * @return array The shuffled array.
     * @throws \HLEA\Exceptions\InvalidShuffleIterationCount if $iterationCount is not greater than zero.
     * @throws \Exception if it was not possible to gather sufficient entropy for the random generator.
     */
    private function shuffle(array $array, int &$iterationCount): array
    {
        if($iterationCount < 1){
            throw new InvalidShuffleIterationCount(
                'Shuffle iteration count must be greater than zero!'
            );
        }

        for($i = 0; $i < $iterationCount; $i++){
            $array = Shuffler::fisherYatesShuffle($array, $this->rand);
        }

        return $array;
    }
}