<?php

namespace HLEA\Processor;

use HLEA\Converter\Converter;
use HLEA\Exceptions\InvalidBinaryData;
use HLEA\Generator\RandomGenerator;
use HLEA\IO\IO;
use HLEA\Key\ByteStream;
use HLEA\Key\Key;
use HLEA\Key\UInt16Stream;

class Processor
{
    public const DEFAULT_IO_BUFFER_SIZE = 4194304; // 4MB

    private $key = null;
    private $ioBufferSize = null;

    /**
     * Processor constructor.
     *
     * @param \HLEA\Key\Key|null $key          The Key to use (optional).
     * @param int                $ioBufferSize Size of the IO buffer in bytes (optional).
     */
    public function __construct(Key &$key = null, int $ioBufferSize = self::DEFAULT_IO_BUFFER_SIZE)
    {
        if($key !== null){
            $this->setKey($key);
        }
        else{
            $this->key = new Key();
        }

        $this->setIOBufferSize($ioBufferSize);
    }

    /**
     * Loads $key to the Processor.
     *
     * @param \HLEA\Key\Key $key The new Key to use.
     */
    public function setKey(Key &$key): void
    {
        $this->key = $key;
    }

    /**
     * Changes the IO buffer size of the Processor.
     *
     * @param int $ioBufferSize IO buffer size in bytes.
     */
    public function setIOBufferSize(int $ioBufferSize): void
    {
        $this->ioBufferSize = $ioBufferSize;
    }

    /**
     * Encrypts a file.
     *
     * @param string $inputFilePath  The path of the file to encrypt.
     * @param string $outputFilePath The path where the encrypted file will be saved.
     *
     * @throws \HLEA\Exceptions\InvalidStreamIndex if the input file is invalid.
     * @throws \HLEA\Exceptions\InvalidSwapTableIndex if the input file is invalid.
     */
    public function encryptFile(string $inputFilePath, string $outputFilePath): void
    {
        IO::preparePathBeforeCreatingFile($outputFilePath);

        $inputFileHandle = fopen($inputFilePath, 'rb');
        $outputFileHandle = fopen($outputFilePath, 'xb');

        $fileSize = filesize($inputFilePath);
        if($fileSize % 2 === 0){
            fwrite($outputFileHandle, Converter::byteToBinary(0));
        }
        else{
            fwrite($outputFileHandle, Converter::byteToBinary(1));
        }

        $offset = 0;
        while(!feof($inputFileHandle)){
            fwrite(
                $outputFileHandle,
                $this->encryptBinaryString(
                    fread($inputFileHandle, $this->ioBufferSize),
                    $offset
                )
            );
            $offset += $this->ioBufferSize;
        }

        fclose($inputFileHandle);
        fclose($outputFileHandle);
    }

    /**
     * Encrypts a binary string.
     *
     * @param string    $binary                           The binary string to encrypt.
     * @param int       $offset                           The offset of the $binary in it's context. It is used when
     *                                                    data is encrypted in parts.
     * @param bool|null $outHasAppendedRandomByteAtTheEnd Defines if the encrypted binary contains an appended random
     *                                                    byte at the end. It happens when the original $binary data
     *                                                    has odd number of bytes, and due to this, the encryption
     *                                                    process adds a random byte to the original data to ensure
     *                                                    even number of bytes before applying the encryption.
     *
     * @return string The encrypted binary.
     * @throws \HLEA\Exceptions\InvalidStreamIndex if $offset is lower than zero.
     * @throws \HLEA\Exceptions\InvalidSwapTableIndex if $binary has invalid data.
     * @throws \Exception if it was not possible to gather sufficient entropy for the random generator.
     */
    public function encryptBinaryString(string $binary, int $offset = 0, bool &$outHasAppendedRandomByteAtTheEnd = null): string
    {
        $encryptedBinary = '';
        $length = strlen($binary);

        if($length % 2 === 1){
            $rand = new RandomGenerator();
            $binary .= Converter::byteToBinary(
                $rand->randomByte()
            );
            $outHasAppendedRandomByteAtTheEnd = true;
            $length++;
        }
        else{
            $outHasAppendedRandomByteAtTheEnd = false;
        }

        for($i = 0; $i < $length; $i += 2){
            $encryptedBinary .= Converter::byteArrayToBinary(
                $this->encryptBytePair(
                    [
                        Converter::binaryToByte($binary[$i]),
                        Converter::binaryToByte($binary[$i + 1]),
                    ],
                    $offset + $i
                )
            );
        }

        return $encryptedBinary;
    }

    /**
     * Encrypts a byte pair.
     *
     * @param array $bytePair The byte pair to encrypt.
     * @param int   $index    The index of the byte pair in it's context.
     *
     * @return array The encrypted byte pair.
     * @throws \HLEA\Exceptions\InvalidStreamIndex if $index is a negative number.
     * @throws \HLEA\Exceptions\InvalidSwapTableIndex if $bytePair has invalid value.
     */
    private function encryptBytePair(array $bytePair, int $index): array
    {
        $primarySwappedBytePair = [
            $this->getPrimarySwappedByte($bytePair[0]),
            $this->getPrimarySwappedByte($bytePair[1]),
        ];
        $streamAdjustedBytePair = [
            $this->getStreamAdjustedByte($primarySwappedBytePair[0], $index),
            $this->getStreamAdjustedByte($primarySwappedBytePair[1], $index + 1),
        ];
        $secondarySwappedBytePair = [
            $this->getSecondarySwappedByte($streamAdjustedBytePair[0]),
            $this->getSecondarySwappedByte($streamAdjustedBytePair[1]),
        ];

        $uInt16 = $this->getUInt16FromBytePair($secondarySwappedBytePair);

        $primarySwappedUInt16 = $this->getPrimarySwappedUInt16($uInt16);
        $streamAdjustedUInt16 = $this->getStreamAdjustedUInt16($primarySwappedUInt16, intdiv($index, 2));
        $secondarySwappedUInt16 = $this->getSecondarySwappedUInt16($streamAdjustedUInt16);

        $encryptedBytePair = $this->getBytePairFromUInt16($secondarySwappedUInt16);

        return $encryptedBytePair;
    }

    /**
     * Decrypts a file.
     *
     * @param string $inputFilePath  The path of the encrypted file.
     * @param string $outputFilePath The path where the decrypted file will be saved.
     *
     * @throws \HLEA\Exceptions\InvalidBinaryData if $binary has odd number of bytes.
     * @throws \HLEA\Exceptions\InvalidStreamIndex if the input file is invalid.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if the input file is invalid.
     */
    public function decryptFile(string $inputFilePath, string $outputFilePath): void
    {
        IO::preparePathBeforeCreatingFile($outputFilePath);

        $inputFileHandle = fopen($inputFilePath, 'rb');
        $outputFileHandle = fopen($outputFilePath, 'xb');

        $hasAppendedRandomByteAtTheEnd = (bool)Converter::binaryToByte(fread($inputFileHandle, 1));

        $offset = 0;
        while(!feof($inputFileHandle)){
            fwrite(
                $outputFileHandle,
                $this->decryptBinaryString(
                    fread($inputFileHandle, $this->ioBufferSize),
                    $offset
                )
            );
            $offset += $this->ioBufferSize;
        }

        if($hasAppendedRandomByteAtTheEnd){
            ftruncate($outputFileHandle, filesize($outputFilePath) - 1);
        }

        fclose($inputFileHandle);
        fclose($outputFileHandle);
    }

    /**
     * Decrypts a binary string.
     *
     * @param string    $binary                        The binary string to decrypt.
     * @param int       $offset                        The offset of the $binary in it's context. It is used when data
     *                                                 is decrypted in parts.
     * @param bool|null $hasAppendedRandomByteAtTheEnd Defines if the $binary contains an appended random byte at the
     *                                                 end. It happens when the original binary data had odd number of
     *                                                 bytes , and due to this, the encryption process added a random
     *                                                 byte to the original data to ensure even number of bytes before
     *                                                 applying the encryption.
     *
     * @return string
     * @throws \HLEA\Exceptions\InvalidBinaryData if $binary has odd number of bytes.
     * @throws \HLEA\Exceptions\InvalidStreamIndex if $offset is lower than zero.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if $binary has invalid data.
     */
    public function decryptBinaryString(string $binary, int $offset, bool $hasAppendedRandomByteAtTheEnd = null): string
    {
        $decryptedBinary = '';
        $length = strlen($binary);

        if($length % 2 === 1){
            throw new InvalidBinaryData(
                'Encrypted binary data must contains even number of bytes.'
            );
        }

        for($i = 0; $i < $length; $i += 2){
            $decryptedBinary .= Converter::byteArrayToBinary(
                $this->decryptBytePair(
                    [
                        Converter::binaryToByte($binary[$i]),
                        Converter::binaryToByte($binary[$i + 1]),
                    ],
                    $offset + $i
                )
            );
        }

        if($hasAppendedRandomByteAtTheEnd === true){
            $decryptedBinary = substr($decryptedBinary, 0, -1);
        }

        return $decryptedBinary;
    }

    /**
     * Decrypts a byte pair.
     *
     * @param array $encryptedBytePair The encrypted byte pair to decrypt.
     * @param int   $index             The index of the byte pair in it's context.
     *
     * @return array The decrypted byte pair.
     * @throws \HLEA\Exceptions\InvalidStreamIndex if $index is a negative number.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if $encryptedBytePair has invalid value.
     */
    private function decryptBytePair(array $encryptedBytePair, int $index): array
    {
        $secondarySwappedUInt16 = $this->getUInt16FromBytePair($encryptedBytePair);
        $streamAdjustedUInt16 = $this->getReverseSecondarySwappedUInt16($secondarySwappedUInt16);
        $primarySwappedUInt16 = $this->getReverseStreamAdjustedUInt16($streamAdjustedUInt16, intdiv($index, 2));

        $uInt16 = $this->getReversePrimarySwappedUInt16($primarySwappedUInt16);

        $secondarySwappedBytePair = $this->getBytePairFromUInt16($uInt16);
        $streamAdjustedBytePair = [
            $this->getReverseSecondarySwappedByte($secondarySwappedBytePair[0]),
            $this->getReverseSecondarySwappedByte($secondarySwappedBytePair[1]),
        ];
        $primarySwappedBytePair = [
            $this->getReverseStreamAdjustedByte($streamAdjustedBytePair[0], $index),
            $this->getReverseStreamAdjustedByte($streamAdjustedBytePair[1], $index + 1),
        ];
        $bytePair = [
            $this->getReversePrimarySwappedByte($primarySwappedBytePair[0]),
            $this->getReversePrimarySwappedByte($primarySwappedBytePair[1]),
        ];

        return $bytePair;
    }

    /**
     * Swaps the $byte value using the primary ByteSwapTable of the Key.
     *
     * @param int $byte The byte to swap.
     *
     * @return int The swapped byte.
     * @throws \HLEA\Exceptions\InvalidSwapTableIndex if $byte has invalid value.
     */
    private function getPrimarySwappedByte(int &$byte): int
    {
        return $this->key->getPrimaryByteSwapTableValueByIndex($byte);
    }

    /**
     * Reverse swaps the $byte value using the primary ByteSwapTable of the Key.
     *
     * @param int $byte The byte to reverse swap.
     *
     * @return int The reverse swapped byte.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if $byte has invalid value.
     */
    private function getReversePrimarySwappedByte(int &$byte): int
    {
        return $this->key->getPrimaryByteSwapTableIndexByValue($byte);
    }

    /**
     * Returns the adjusted value of $byte.
     *
     * @param int $byte  The byte to adjust.
     * @param int $index The index to use to find the adjusting value in the ByteStream of the Key.
     *
     * @return int The adjusted byte.
     * @throws \HLEA\Exceptions\InvalidStreamIndex if $index is a negative number.
     */
    private function getStreamAdjustedByte(int &$byte, int $index): int
    {
        $adjustedByte = $byte + $this->getByteStreamValueByIndex($index);
        if($adjustedByte > ByteStream::MAX_VALUE){
            $adjustedByte -= ByteStream::MAX_VALUE + 1;
        }

        return $adjustedByte;
    }

    /**
     * Returns the reverse adjusted $byte.
     *
     * @param int $byte  The byte to reverse adjust.
     * @param int $index The index to use to find the adjusting value in the ByteStream of the Key.
     *
     * @return int The reverse adjusted byte.
     * @throws \HLEA\Exceptions\InvalidStreamIndex if $index is a negative number.
     */
    private function getReverseStreamAdjustedByte(int &$byte, int $index): int
    {
        $reverseAdjustedByte = $byte - $this->getByteStreamValueByIndex($index);

        if($reverseAdjustedByte < 0){
            $reverseAdjustedByte += ByteStream::MAX_VALUE + 1;
        }

        return $reverseAdjustedByte;
    }

    /**
     * Returns the value of the ByteStream of the Key by $index.
     *
     * @param int $index The index of the value.
     *
     * @return int The value at $index.
     * @throws \HLEA\Exceptions\InvalidStreamIndex if $index is a negative number.
     */
    private function getByteStreamValueByIndex(int &$index): int
    {
        return $this->key->getByteStreamValueByIndex($index);
    }

    /**
     * Swaps the $byte value using the secondary ByteSwapTable of the Key.
     *
     * @param int $byte The byte to swap.
     *
     * @return int The swapped byte.
     * @throws \HLEA\Exceptions\InvalidSwapTableIndex if $index is out of range.
     */
    private function getSecondarySwappedByte(int &$byte): int
    {
        return $this->key->getSecondaryByteSwapTableValueByIndex($byte);
    }

    /**
     * Reverse swaps $byte value using the secondary ByteSwapTable of the Key.
     *
     * @param int $byte The byte to reverse swap.
     *
     * @return int The reversed swapped byte.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if $byte has invalid value.
     */
    private function getReverseSecondarySwappedByte(int &$byte): int
    {
        return $this->key->getSecondaryByteSwapTableIndexByValue($byte);
    }

    /**
     * Converts the $bytePair to an UIn16.
     *
     * @param array $bytePair The byte pair to convert.
     *
     * @return int The UInt16 value of the $bytePair.
     */
    private function getUInt16FromBytePair(array &$bytePair): int
    {
        return Converter::bytePairToUInt16($bytePair);
    }

    /**
     * Converts the $uInt16 to a byte pair array.
     *
     * @param int $uInt16 The UInt16 to convert.
     *
     * @return array The byte pair value of the $uInt16.
     */
    private function getBytePairFromUInt16(int &$uInt16): array
    {
        return Converter::uInt16ToBytePair($uInt16);
    }

    /**
     * Swaps the $uInt16 value using the primary UInt16SwapTable of the Key.
     *
     * @param int $uInt16 The UInt16 to swap.
     *
     * @return int The swapped UInt16.
     * @throws \HLEA\Exceptions\InvalidSwapTableIndex if $uInt16 has invalid value.
     */
    private function getPrimarySwappedUInt16(int &$uInt16): int
    {
        return $this->key->getPrimaryUInt16SwapTableValueByIndex($uInt16);
    }

    /**
     * Reverse swaps $uInt16 value using the primary UInt16SwapTable of the Key.
     *
     * @param int $uInt16 The UInt16 to reverse swap.
     *
     * @return int The reverse swapped UInt16.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if $uInt16 has invalid value.
     */
    private function getReversePrimarySwappedUInt16(int &$uInt16): int
    {
        return $this->key->getPrimaryUInt16SwapTableIndexByValue($uInt16);
    }

    /**
     * Returns the adjusted value of $uInt16.
     *
     * @param int $uInt16 The UInt16 to adjust.
     * @param int $index  The index to use to find the adjusting value in the UInt16Stream of the Key.
     *
     * @return int The adjusted UInt16.
     * @throws \HLEA\Exceptions\InvalidStreamIndex if $index is a negative number.
     */
    private function getStreamAdjustedUInt16(int &$uInt16, int $index): int
    {
        $adjustedUInt16 = $uInt16 + $this->getUInt16StreamValueByIndex($index);
        if($adjustedUInt16 > UInt16Stream::MAX_VALUE){
            $adjustedUInt16 -= UInt16Stream::MAX_VALUE + 1;
        }

        return $adjustedUInt16;
    }

    /**
     * Returns the reverse adjusted $uInt16.
     *
     * @param int $uInt16 The UInt16 value to reverse adjust.
     * @param int $index  The index to use to find the adjusting value in the UInt16Stream of the Key.
     *
     * @return int The reverse adjusted UInt16.
     * @throws \HLEA\Exceptions\InvalidStreamIndex if $index is a negative number.
     */
    private function getReverseStreamAdjustedUInt16(int &$uInt16, int $index): int
    {
        $reverseAdjustedUInt16 = $uInt16 - $this->getUInt16StreamValueByIndex($index);

        if($reverseAdjustedUInt16 < 0){
            $reverseAdjustedUInt16 += UInt16Stream::MAX_VALUE + 1;
        }

        return $reverseAdjustedUInt16;
    }

    /**
     * Returns the value of the UInt16Stream of the Key by $index.
     *
     * @param int $index The index of the value.
     *
     * @return int The value at $index.
     * @throws \HLEA\Exceptions\InvalidStreamIndex if $index is a negative number.
     */
    private function getUInt16StreamValueByIndex(int &$index): int
    {
        return $this->key->getUInt16StreamValueByIndex($index);
    }

    /**
     * Swaps the $uInt16 value using the secondary UInt16SwapTable of the Key.
     *
     * @param int $uInt16 The UInt16 to swap.
     *
     * @return int The swapped UInt16.
     * @throws \HLEA\Exceptions\InvalidSwapTableIndex if $uInt16 has invalid value.
     */
    private function getSecondarySwappedUInt16(int &$uInt16): int
    {
        return $this->key->getSecondaryUInt16SwapTableValueByIndex($uInt16);
    }

    /**
     * Reverse swaps $uInt16 value using the secondary UInt16SwapTable of the Key.
     *
     * @param int $uInt16 The UInt16 to reverse swap.
     *
     * @return int The reverse swapped UInt16.
     * @throws \HLEA\Exceptions\InvalidSwapTableValue if $uInt16 has invalid value.
     */
    private function getReverseSecondarySwappedUInt16(int &$uInt16): int
    {
        return $this->key->getSecondaryUInt16SwapTableIndexByValue($uInt16);
    }
}