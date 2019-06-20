<?php

namespace HLEA\Examples;

use Exception;
use HLEA\Generator\RandomGenerator;
use HLEA\Key\Key;
use HLEA\Processor\Processor;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Autoloader' . DIRECTORY_SEPARATOR .
             'Autoloader.php';

execute();

/**
 * Executes the example with error handling.
 */
function execute()
{
    try{
        runExample();
    }
    catch(Exception $exception){
        echo PHP_EOL . 'Error (Code: ' . $exception->getCode() . ')' . PHP_EOL .
             $exception->getMessage() . PHP_EOL .
             '========================================================================' . PHP_EOL .
             $exception->getTraceAsString();

        exit;
    }
}

/**
 * Runs the example code.
 *
 * @throws \HLEA\Exceptions\InvalidStreamIndex
 * @throws \HLEA\Exceptions\InvalidStreamValue
 * @throws \HLEA\Exceptions\InvalidSwapTableIndex
 * @throws \HLEA\Exceptions\InvalidSwapTableSize
 * @throws \HLEA\Exceptions\InvalidSwapTableValue
 * @throws \Exception
 */
function runExample()
{
    // Loading the key from a file.
    // First you need to generate is using the creatingKey.php example script.
    $key = new Key();
    $key->loadFromFile(__DIR__ . DS . 'Data' . DS . 'key.hleakey');

    // Creating the processor.
    $processor = new Processor($key);

    // Creating a random binary string of 128 bytes.
    $rand = new RandomGenerator();
    $binary = $rand->randomBinary(128);

    // Encrypting a binary string.
    $encryptedBinary = $processor->encryptBinaryString(
        $binary,
        0,
        $outHasAppendedRandomByteAtTheEnd
    );

    // Decrypting the encrypted binary string.
    $decryptedBinary = $processor->decryptBinaryString(
        $encryptedBinary,
        0,
        $outHasAppendedRandomByteAtTheEnd
    );

    // Displaying the results.
    var_dump($binary);
    var_dump($decryptedBinary);
}
