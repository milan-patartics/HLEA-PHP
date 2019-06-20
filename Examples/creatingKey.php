<?php

namespace HLEA\Examples;

use Exception;
use HLEA\Generator\KeyGenerator;

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
 * @throws \HLEA\Exceptions\InvalidShuffleIterationCount
 */
function runExample()
{
    // Creating a key generator.
    $keyGenerator = new KeyGenerator();

    // Generating a key with default settings.
    $key = $keyGenerator->generateKey();

    // Saving the key to "./Data/key.hleakey".
    $key->saveAsFile(__DIR__ . DS . 'Data' . DS . 'key.hleakey');
}