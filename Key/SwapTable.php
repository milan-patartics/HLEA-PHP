<?php

namespace HLEA\Key;

use HLEA\Exceptions\InvalidSwapTableIndex;
use HLEA\Exceptions\InvalidSwapTableSize;
use HLEA\Exceptions\InvalidSwapTableValue;

class SwapTable
{
    public const SIZE = 256;

    private $table = [];
    private $inverseTable = [];

    /**
     * SwapTable constructor.
     *
     * @param array|null $table The SwapTable data array to load (optional).
     *
     * @throws \HLEA\Exceptions\InvalidSwapTableSize if the size of the $table array is invalid.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if $table array has invalid value.
     */
    public function __construct(array $table = null)
    {
        if($table !== null){
            $this->loadTableFromArray($table);
        }
    }

    /**
     * Loads SwapTable data from an array.
     *
     * @param array $table The SwapTable data array to load.
     *
     * @throws \HLEA\Exceptions\InvalidSwapTableSize if the size of the $table array is invalid.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if $table array has invalid value.
     */
    public function loadTableFromArray(array &$table): void
    {
        if(count($table) !== static::SIZE){
            throw new InvalidSwapTableSize(
                '$table array parameter has wrong size. ' .
                'Required size is ' . static::SIZE . ', but the used size is ' . count($table)
            );
        }

        $this->table = [];
        $this->inverseTable = [];
        for($i = 0; $i < static::SIZE; $i++){
            if($this->isValueInValidRange($table[$i])){
                $this->table[] = $table[$i];
                $this->inverseTable[$table[$i]] = $i;
            }
            else{
                throw new InvalidSwapTableValue(
                    '$table array parameter has wrong value at index ' . $i . '. ' .
                    'Required value is minimum 0 and the maximum ' . static::SIZE - 1 . ' (inclusive).'
                );
            }
        }

        ksort($this->inverseTable, SORT_ASC);
        $this->inverseTable = array_values($this->inverseTable);
    }

    /**
     * Returns the value by $index.
     *
     * @param int $index The $index of the value.
     *
     * @return int The value at $index.
     * @throws \HLEA\Exceptions\InvalidSwapTableIndex if $index is out of range.
     */
    public function getValueByIndex(int &$index): int
    {
        if(!$this->isValueInValidRange($index)){
            throw new InvalidSwapTableIndex(
                '$index has invalid value (' . $index . '). ' .
                'Required value is minimum 0 and the maximum ' . static::SIZE - 1 . ' (inclusive).'
            );
        }

        return $this->table[$index];
    }

    /**
     * Returns the index by $value.
     *
     * @param int $value The $value to find.
     *
     * @return int The index of the given $value.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if $value is invalid.
     */
    public function getIndexByValue(int &$value): int
    {
        if(!$this->isValueInValidRange($value)){
            throw new InvalidSwapTableValue(
                '$value has a wrong value (' . $value . '). ' .
                'Required value is minimum 0 and the maximum ' . static::SIZE - 1 . ' (inclusive).'
            );
        }

        return $this->inverseTable[$value];
    }

    /**
     * Returns the SwapTable data as an array.
     *
     * @return array The data array.
     */
    public function getDataArray(): array

    {
        return $this->table;
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
        if($value > -1 && $value < static::SIZE){
            return true;
        }

        return false;
    }
}