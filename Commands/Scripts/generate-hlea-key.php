<?php

namespace HLEA\Commands\Scripts;

use Exception;
use HLEA\Generator\KeyGenerator;
use HLEA\Key\Key;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .
             'Autoloader' . DIRECTORY_SEPARATOR . 'Autoloader.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'CommandTools.php';

execute($argv);

/**
 * Executes this script.
 *
 * @param array $argv
 */
function execute(array &$argv): void
{
    try{
        generateHLEAKey(
            CommandTools::parseArgv($argv)
        );
    }
    catch(Exception $exception){
        echo PHP_EOL . 'Error (Code: ' . $exception->getCode() . ')' . PHP_EOL .
             $exception->getMessage() . PHP_EOL .
             '=========================================================================' . PHP_EOL .
             $exception->getTraceAsString();

        exit;
    }
}

/**
 * Generates and saves a HLEA key file.
 *
 * @param array $args
 *
 * @throws \HLEA\Exceptions\InvalidShuffleIterationCount
 * @throws \HLEA\Exceptions\InvalidStreamValue
 * @throws \HLEA\Exceptions\InvalidSwapTableSize
 * @throws \HLEA\Exceptions\InvalidSwapTableValue
 * @throws \Exception
 */
function generateHLEAKey(array $args): void
{
    echo PHP_EOL .
         '=========================================================================' . PHP_EOL .
         'Welcome to the HLEA (High Level Encryption Algorithm) key generator tool!' . PHP_EOL .
         '=========================================================================' . PHP_EOL .
         PHP_EOL;

    echo 'Checking argument...' . PHP_EOL;

    #region Checking "output" argument
    if(isset($args['output'])){
        if(file_exists($args['output'])){
            echo PHP_EOL . 'Error (Output File Already Exists)!' . PHP_EOL .
                 'The output file already exists at ' . $args['output'] . PHP_EOL . PHP_EOL .
                 'Terminating...' . PHP_EOL;

            exit;
        }

        echo 'The "output" argument seems to be OK.' . PHP_EOL . PHP_EOL;
    }
    else{
        echo PHP_EOL . 'Error (Missing Argument)!' . PHP_EOL .
             'The "output" argument is required.' . PHP_EOL . PHP_EOL .
             'You can define it by adding the output=PATH_TO_FILE argument when calling this script.' . PHP_EOL . PHP_EOL .
             'Linux example:' . PHP_EOL .
             'php generate-hlea-key output="/home/user/key.hleakey"' . PHP_EOL . PHP_EOL .
             'MacOS example:' . PHP_EOL .
             'php generate-hlea-key output="/Users/user/Documents/key.hleakey"' . PHP_EOL . PHP_EOL .
             'Windows example:' . PHP_EOL .
             'php generate-hlea-key output="C:\Users\user\Documents\key.hleakey"' . PHP_EOL . PHP_EOL .
             'Terminating...' . PHP_EOL;

        exit;
    }
    #endregion

    echo 'Starting HLEA key generation with the following parameters:' .
         PHP_EOL .
         '=========================================================================' . PHP_EOL .
         'Primary Byte Swap Table Shuffle Iteration Count           : ' . KeyGenerator::DEFAULT_BYTE_SWAP_TABLE_SHUFFLE_ITERATION_COUNT . PHP_EOL .
         'Byte Stream Length                                        : ' . KeyGenerator::DEFAULT_BYTE_STREAM_LENGTH . PHP_EOL .
         'Secondary Byte Swap Table Shuffle Iteration Count         : ' . KeyGenerator::DEFAULT_BYTE_SWAP_TABLE_SHUFFLE_ITERATION_COUNT . PHP_EOL .
         '-------------------------------------------------------------------------' . PHP_EOL .
         'Primary UInt16 Swap Table Shuffle Iteration Count         : ' . KeyGenerator::DEFAULT_UINT16_SWAP_TABLE_SHUFFLE_ITERATION_COUNT . PHP_EOL .
         'UInt16 Stream Length                                      : ' . KeyGenerator::DEFAULT_UINT16_STREAM_LENGTH . PHP_EOL .
         'Secondary UInt16 Swap Table Shuffle Iteration Count       : ' . KeyGenerator::DEFAULT_UINT16_SWAP_TABLE_SHUFFLE_ITERATION_COUNT . PHP_EOL .
         '=========================================================================' . PHP_EOL .
         PHP_EOL;

    sleep(1);
    echo 'Initializing a new KeyGenerator instance...' . PHP_EOL;
    $keyGenerator = new KeyGenerator();
    echo 'Key generator initialized successfully.' . PHP_EOL . PHP_EOL;

    sleep(1);
    echo 'Generating Primary Byte Swap Table data...' . PHP_EOL;
    $primaryByteSwapTableDataArray = $keyGenerator->generateByteSwapTableArray($keyGenerator::DEFAULT_BYTE_SWAP_TABLE_SHUFFLE_ITERATION_COUNT);
    echo 'Primary Byte Swap Table data generated successfully.' . PHP_EOL . PHP_EOL;

    sleep(1);
    echo 'Generating Byte Stream data...' . PHP_EOL;
    $byteStreamDataArray = $keyGenerator->generateByteStreamArray($keyGenerator::DEFAULT_BYTE_STREAM_LENGTH);
    echo 'Byte Stream data generated successfully.' . PHP_EOL . PHP_EOL;

    sleep(1);
    echo 'Generating Secondary Byte Swap Table data...' . PHP_EOL;
    $secondaryByteSwapTableDataArray = $keyGenerator->generateByteSwapTableArray($keyGenerator::DEFAULT_BYTE_SWAP_TABLE_SHUFFLE_ITERATION_COUNT);
    echo 'Secondary Byte Swap Table data generated successfully.' . PHP_EOL . PHP_EOL;

    sleep(1);
    echo 'Generating Primary UInt16 Swap Table data...' . PHP_EOL;
    $primaryUInt16SwapTableDataArray = $keyGenerator->generateUInt16SwapTableArray($keyGenerator::DEFAULT_UINT16_SWAP_TABLE_SHUFFLE_ITERATION_COUNT);
    echo 'Primary UInt16 Swap Table data generated successfully.' . PHP_EOL . PHP_EOL;

    sleep(1);
    echo 'Generating UInt16 Stream data...' . PHP_EOL;
    $uInt16StreamDataArray = $keyGenerator->generateUInt16StreamArray($keyGenerator::DEFAULT_UINT16_STREAM_LENGTH);
    echo 'UInt16 Stream data generated successfully.' . PHP_EOL . PHP_EOL;

    sleep(1);
    echo 'Generating Secondary UInt16 Swap Table data...' . PHP_EOL;
    $secondaryUInt16SwapTableDataArray = $keyGenerator->generateUInt16SwapTableArray($keyGenerator::DEFAULT_UINT16_SWAP_TABLE_SHUFFLE_ITERATION_COUNT);
    echo 'Secondary UInt16 Swap Table data generated successfully.' . PHP_EOL . PHP_EOL;

    echo '=========================================================================' . PHP_EOL;
    echo 'All data parts generated successfully.' . PHP_EOL;
    echo '=========================================================================' . PHP_EOL . PHP_EOL;

    sleep(1);
    echo 'Building Key from generated data...' . PHP_EOL;
    $key = new Key();
    $key->loadFromArrays(
        $primaryByteSwapTableDataArray,
        $byteStreamDataArray,
        $secondaryByteSwapTableDataArray,

        $primaryUInt16SwapTableDataArray,
        $uInt16StreamDataArray,
        $secondaryUInt16SwapTableDataArray
    );
    echo 'Key built successfully.' . PHP_EOL . PHP_EOL;

    sleep(1);
    echo 'Saving generated Key to ' . $args['output'] . PHP_EOL;
    $key->saveAsFile($args['output']);
    echo 'Key saved successfully.' . PHP_EOL . PHP_EOL;

    echo '=========================================================================' . PHP_EOL;
    echo 'Everything is done. Cheers!' . PHP_EOL;
    echo '=========================================================================' . PHP_EOL . PHP_EOL;

    echo 'Terminating...' . PHP_EOL;
}