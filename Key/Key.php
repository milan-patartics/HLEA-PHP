<?php

namespace HLEA\Key;

use HLEA\Converter\Converter;
use HLEA\IO\IO;

class Key
{
    private $primaryByteSwapTable = null;
    private $byteStream = null;
    private $secondaryByteSwapTable = null;

    private $primaryUInt16SwapTable = null;
    private $uInt16Stream = null;
    private $secondaryUInt16SwapTable = null;

    /**
     * Key constructor.
     */
    public function __construct()
    {
        $this->primaryByteSwapTable = new ByteSwapTable();
        $this->byteStream = new ByteStream();
        $this->secondaryByteSwapTable = new ByteSwapTable();

        $this->primaryUInt16SwapTable = new UInt16SwapTable();
        $this->uInt16Stream = new UInt16Stream();
        $this->secondaryUInt16SwapTable = new UInt16SwapTable();
    }

    /**
     * Loads the Key data from a binary file.
     *
     * @param string $inputFilePath The path of the key file.
     *
     * @throws \HLEA\Exceptions\InvalidStreamValue if any stream data array has invalid value.
     * @throws \HLEA\Exceptions\InvalidSwapTableSize if the size of any swap table data array is invalid.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if any swap table data array has invalid value.
     */
    public function loadFromFile(string $inputFilePath): void
    {
        $fileHandle = fopen($inputFilePath, 'rb');

        $byteStreamLength = Converter::binaryToUInt32(
            fread($fileHandle, 4)
        );
        $uInt16StreamLength = Converter::binaryToUInt32(
            fread($fileHandle, 4)
        );

        $primaryByteSwapTable = Converter::binaryToByteArray(
            fread($fileHandle, ByteSwapTable::SIZE)
        );
        $byteStream = Converter::binaryToByteArray(
            fread($fileHandle, $byteStreamLength)
        );
        $secondaryByteSwapTable = Converter::binaryToByteArray(
            fread($fileHandle, ByteSwapTable::SIZE)
        );

        $primaryUInt16SwapTable = Converter::binaryToUInt16Array(
            fread($fileHandle, 2 * UInt16SwapTable::SIZE)
        );
        $uInt16Stream = Converter::binaryToUInt16Array(
            fread($fileHandle, 2 * $uInt16StreamLength)
        );
        $secondaryUInt16SwapTable = Converter::binaryToUInt16Array(
            fread($fileHandle, 2 * UInt16SwapTable::SIZE)
        );

        fclose($fileHandle);

        $this->loadFromArrays(
            $primaryByteSwapTable,
            $byteStream,
            $secondaryByteSwapTable,

            $primaryUInt16SwapTable,
            $uInt16Stream,
            $secondaryUInt16SwapTable
        );
    }

    /**
     * Loads the Key data from a JSON string.
     *
     * @param string $jsonEncodedKeyData The JSON string to load.
     *
     * @throws \HLEA\Exceptions\InvalidStreamValue if any stream data array has invalid value.
     * @throws \HLEA\Exceptions\InvalidSwapTableSize if the size of any swap table data array is invalid.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if any swap table data array has invalid value.
     */
    public function loadFromJSON(string $jsonEncodedKeyData): void
    {
        $keyData = json_decode($jsonEncodedKeyData);
        $this->loadFromArrays(
            $keyData->primaryByteSwapTable,
            $keyData->byteStream,
            $keyData->secondaryByteSwapTable,

            $keyData->primaryUInt16SwapTable,
            $keyData->uInt16Stream,
            $keyData->secondaryUInt16SwapTable
        );
    }

    /**
     * Loads the Key data from data arrays.
     *
     * @param array $primaryByteSwapTable     The data array of the primary ByteSwapTable.
     * @param array $byteStream               The data array of the ByteStream.
     * @param array $secondaryByteSwapTable   The data array of the secondary ByteSwapTable.
     * @param array $primaryUInt16SwapTable   The data array of the primary UInt16SwapTable.
     * @param array $uInt16Stream             The data array of the UInt16Stream.
     * @param array $secondaryUInt16SwapTable The data array of the secondary UInt16SwapTable.
     *
     * @throws \HLEA\Exceptions\InvalidStreamValue if any stream data array has invalid value.
     * @throws \HLEA\Exceptions\InvalidSwapTableSize if the size of any swap table data array is invalid.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if any swap table data array has invalid value.
     */
    public function loadFromArrays(
        array &$primaryByteSwapTable,
        array &$byteStream,

        array &$secondaryByteSwapTable,
        array &$primaryUInt16SwapTable,
        array &$uInt16Stream,

        array &$secondaryUInt16SwapTable): void
    {
        $this->loadPrimaryByteSwapTableFromArray($primaryByteSwapTable);
        $this->loadByteStreamFromArray($byteStream);
        $this->loadSecondaryByteSwapTableFromArray($secondaryByteSwapTable);

        $this->loadPrimaryUInt16SwapTableFromArray($primaryUInt16SwapTable);
        $this->loadUInt16StreamFromArray($uInt16Stream);
        $this->loadSecondaryUInt16SwapTableFromArray($secondaryUInt16SwapTable);
    }

    /**
     * Loads the primary ByteSwapTable data from an array.
     *
     * @param array $byteSwapTable The ByteSwapTable data array to load.
     *
     * @throws \HLEA\Exceptions\InvalidSwapTableSize if the size of the $byteSwapTable array is invalid.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if $byteSwapTable array has invalid value.
     */
    public function loadPrimaryByteSwapTableFromArray(array &$byteSwapTable): void
    {
        $this->primaryByteSwapTable->loadTableFromArray($byteSwapTable);
    }

    /**
     * Loads the ByteStream data from an array.
     *
     * @param array $byteStream The data array to load.
     *
     * @throws \HLEA\Exceptions\InvalidStreamValue if data array has invalid value.
     */
    public function loadByteStreamFromArray(array &$byteStream): void
    {
        $this->byteStream->loadStreamFromArray($byteStream);
    }

    /**
     * Loads the secondary ByteSwapTable data from an array.
     *
     * @param array $byteSwapTable The ByteSwapTable data array to load.
     *
     * @throws \HLEA\Exceptions\InvalidSwapTableSize if the size of the $byteSwapTable array is invalid.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if $byteSwapTable array has invalid value.
     */
    public function loadSecondaryByteSwapTableFromArray(array &$byteSwapTable): void
    {
        $this->secondaryByteSwapTable->loadTableFromArray($byteSwapTable);
    }

    /**
     * Loads the primary UInt16SwapTable data from an array.
     *
     * @param array $uInt16SwapTable The UInt16SwapTable data array to load.
     *
     * @throws \HLEA\Exceptions\InvalidSwapTableSize if the size of the $uInt16SwapTable array is invalid.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if $uInt16SwapTable array has invalid value.
     */
    public function loadPrimaryUInt16SwapTableFromArray(array &$uInt16SwapTable): void
    {
        $this->primaryUInt16SwapTable->loadTableFromArray($uInt16SwapTable);
    }

    /**
     * Loads the UInt16Stream data from an array.
     *
     * @param array $uInt16Stream The data array to load.
     *
     * @throws \HLEA\Exceptions\InvalidStreamValue if data array has invalid value.
     */
    public function loadUInt16StreamFromArray(array &$uInt16Stream): void
    {
        $this->uInt16Stream->loadStreamFromArray($uInt16Stream);
    }

    /**
     * Loads the secondary UInt16SwapTable data from an array.
     *
     * @param array $uInt16SwapTable The UInt16SwapTable data array to load.
     *
     * @throws \HLEA\Exceptions\InvalidSwapTableSize if the size of the $uInt16SwapTable array is invalid.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if $uInt16SwapTable array has invalid value.
     */
    public function loadSecondaryUInt16SwapTableFromArray(array &$uInt16SwapTable): void
    {
        $this->secondaryUInt16SwapTable->loadTableFromArray($uInt16SwapTable);
    }

    /**
     * Returns the value by $index from the primary ByteSwapTable.
     *
     * @param int $index The $index of the value.
     *
     * @return int The value at $index.
     * @throws \HLEA\Exceptions\InvalidSwapTableIndex if $index is out of range.
     */
    public function getPrimaryByteSwapTableValueByIndex(int &$index): int
    {
        return $this->primaryByteSwapTable->getValueByIndex($index);
    }

    /**
     * Returns the index of the given $value in the primary ByteSwapTable.
     *
     * @param int $value The $value to find.
     *
     * @return int The index of the given $value.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if $value is invalid.
     */
    public function getPrimaryByteSwapTableIndexByValue(int &$value): int
    {
        return $this->primaryByteSwapTable->getIndexByValue($value);
    }

    /**
     * Returns the value by $index from the ByteStream.
     *
     * @param int $index The $index of the value.
     *
     * @return int The value at $index.
     * @throws \HLEA\Exceptions\InvalidStreamIndex if index is a negative number.
     */
    public function getByteStreamValueByIndex(int &$index): int
    {
        return $this->byteStream->getValueByIndex($index);
    }

    /**
     * Returns the value by $index from the secondary ByteSwapTable.
     *
     * @param int $index The $index of the value.
     *
     * @return int The value at $index.
     * @throws \HLEA\Exceptions\InvalidSwapTableIndex if $index is out of range.
     */
    public function getSecondaryByteSwapTableValueByIndex(int &$index): int
    {
        return $this->secondaryByteSwapTable->getValueByIndex($index);
    }

    /**
     * Returns the index of the given $value in the secondary ByteSwapTable.
     *
     * @param int $value The $value to find.
     *
     * @return int The index of the given $value.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if $value is invalid.
     */
    public function getSecondaryByteSwapTableIndexByValue(int &$value): int
    {
        return $this->secondaryByteSwapTable->getIndexByValue($value);
    }

    /**
     * Returns the value by $index from the primary UInt16SwapTable.
     *
     * @param int $index The $index of the value.
     *
     * @return int The value at $index.
     * @throws \HLEA\Exceptions\InvalidSwapTableIndex if $index is out of range.
     */
    public function getPrimaryUInt16SwapTableValueByIndex(int &$index): int
    {
        return $this->primaryUInt16SwapTable->getValueByIndex($index);
    }

    /**
     * Returns the index of the given $value in the primary UInt16SwapTable.
     *
     * @param int $value The $value to find.
     *
     * @return int The index of the given $value.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if $value is invalid.
     */
    public function getPrimaryUInt16SwapTableIndexByValue(int &$value): int
    {
        return $this->primaryUInt16SwapTable->getIndexByValue($value);
    }

    /**
     * Returns the value by $index from the UInt16Stream.
     *
     * @param int $index The $index of the value.
     *
     * @return int The value at $index.
     * @throws \HLEA\Exceptions\InvalidStreamIndex if index is a negative number.
     */
    public function getUInt16StreamValueByIndex(int &$index): int
    {
        return $this->uInt16Stream->getValueByIndex($index);
    }

    /**
     * Returns the value by $index from the secondary UInt16SwapTable.
     *
     * @param int $index The $index of the value.
     *
     * @return int The value at $index.
     * @throws \HLEA\Exceptions\InvalidSwapTableIndex if $index is out of range.
     */
    public function getSecondaryUInt16SwapTableValueByIndex(int &$index): int
    {
        return $this->secondaryUInt16SwapTable->getValueByIndex($index);
    }

    /**
     * Returns the index of the given $value in the secondary UInt16SwapTable.
     *
     * @param int $value The $value to find.
     *
     * @return int The index of the given $value.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if $value is invalid.
     */
    public function getSecondaryUInt16SwapTableIndexByValue(int &$value): int
    {
        return $this->secondaryUInt16SwapTable->getIndexByValue($value);
    }

    /**
     * Saves the Key data to a binary file.
     *
     * @param string $outputFilePath The path of the Key file.
     */
    public function saveAsFile(string $outputFilePath): void
    {
        IO::preparePathBeforeCreatingFile($outputFilePath);
        $fileHandle = fopen($outputFilePath, 'xb');

        fwrite(
            $fileHandle,
            Converter::uInt32ToBinary($this->byteStream->getStreamLength()) .
            Converter::uInt32ToBinary($this->uInt16Stream->getStreamLength()) .

            Converter::byteArrayToBinary($this->primaryByteSwapTable->getDataArray()) .
            Converter::byteArrayToBinary($this->byteStream->getDataArray()) .
            Converter::byteArrayToBinary($this->secondaryByteSwapTable->getDataArray()) .

            Converter::uInt16ArrayToBinary($this->primaryUInt16SwapTable->getDataArray()) .
            Converter::uInt16ArrayToBinary($this->uInt16Stream->getDataArray()) .
            Converter::uInt16ArrayToBinary($this->secondaryUInt16SwapTable->getDataArray())
        );

        fclose($fileHandle);
    }

    /**
     * Returns the Key data as JSON string.
     *
     * @return string The JSON string of the Key data.
     */
    public function toJSON(): string
    {
        return json_encode([
            'primaryByteSwapTable' => $this->primaryByteSwapTable->getDataArray(),
            'byteStream' => $this->byteStream->getDataArray(),
            'secondaryByteSwapTable' => $this->secondaryByteSwapTable->getDataArray(),
            'primaryUInt16SwapTable' => $this->primaryUInt16SwapTable->getDataArray(),
            'uInt16Stream' => $this->uInt16Stream->getDataArray(),
            'secondaryUInt16SwapTable' => $this->secondaryUInt16SwapTable->getDataArray(),
        ]);
    }
}