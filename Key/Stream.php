<?php

namespace HLEA\Key;

use HLEA\Exceptions\InvalidStreamIndex;
use HLEA\Exceptions\InvalidStreamValue;

class Stream
{
    public const MIN_VALUE = 0;
    public const MAX_VALUE = 255;

    private $stream = [];
    private $streamLength = 0;

    /**
     * Stream constructor.
     *
     * @param array|null $stream The array of Stream data to load (optional).
     *
     * @throws \HLEA\Exceptions\InvalidStreamValue if data array has invalid value.
     */
    public function __construct(array $stream = null)
    {
        if($stream !== null){
            $this->loadStreamFromArrayRef($stream);
        }
    }

    /**
     * Loads the data stream from an array.
     *
     * @param array $stream The data array to load.
     *
     * @throws \HLEA\Exceptions\InvalidStreamValue if data array has invalid value.
     */
    public function loadStreamFromArray(array $stream): void
    {
        $this->loadStreamFromArrayRef($stream);
    }

    /**
     * Loads the data stream from an array.
     *
     * @param array $stream The data array to load.
     *
     * @throws \HLEA\Exceptions\InvalidStreamValue if data array has invalid value.
     */
    private function loadStreamFromArrayRef(array &$stream): void
    {
        $size = count($stream);
        $this->stream = [];

        for($i = 0; $i < $size; $i++){
            if($this->isValueInValidRange($stream[$i])){
                $this->stream[$i] = $stream[$i];
            }
            else{
                throw new InvalidStreamValue(
                    '$stream array has wrong value at index ' . $i . ' (' . $stream[$i] . '). ' .
                    'Required value is minimum ' . static::MIN_VALUE . ' and the maximum ' . static::MAX_VALUE . '.'
                );
            }
        }

        $this->streamLength = $size;
    }

    /**
     * Returns the length of the Stream.
     *
     * @return int The length of the Stream.
     */
    public function getStreamLength(): int
    {
        return $this->streamLength;
    }

    /**
     * Returns the value of the data Stream by $index. Module operator applied to the $index ($index % lengthOfStream)
     * to ensure continuous, repeated Stream data.
     *
     * @param int $index The index of data element in the Stream.
     *
     * @return int The value at $index.
     * @throws \HLEA\Exceptions\InvalidStreamIndex if index is a negative number.
     */
    public function getValueByIndex(int &$index): int
    {
        if($index < 0){
            throw new InvalidStreamIndex(
                '$index must be a non negative number, but the used value is ' . $index . '.'
            );
        }

        return $this->stream[$index % $this->streamLength];
    }

    /**
     * Returns the Stream data as an array.
     *
     * @return array The data array of the Stream.
     */
    public function getDataArray(): array
    {
        return $this->stream;
    }

    /**
     * Checks if $value is in valid range.
     *
     * @param int $value The value to check.
     *
     * @return bool TRUE if $value is in valid range; FALSE if not.
     */
    private function isValueInValidRange(int &$value): bool
    {
        if($value > static::MIN_VALUE - 1 && $value < static::MAX_VALUE + 1){
            return true;
        }

        return false;
    }
}