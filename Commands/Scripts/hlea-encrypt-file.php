<?php

namespace HLEA\Commands\Scripts;

use Exception;
use HLEA\Key\Key;
use HLEA\Processor\Processor;

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
        hleaEncryptFile(
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
 * Encrypts the input file and saves the encrypted results to the output file.
 *
 * @param array $args
 *
 * @throws \HLEA\Exceptions\InvalidStreamIndex
 * @throws \HLEA\Exceptions\InvalidStreamValue
 * @throws \HLEA\Exceptions\InvalidSwapTableIndex
 * @throws \HLEA\Exceptions\InvalidSwapTableSize
 * @throws \HLEA\Exceptions\InvalidSwapTableValue
 */
function hleaEncryptFile(array $args): void
{
    echo PHP_EOL .
         '===========================================================================' . PHP_EOL .
         'Welcome to the HLEA (High Level Encryption Algorithm) file encryption tool!' . PHP_EOL .
         '===========================================================================' . PHP_EOL .
         PHP_EOL;

    echo 'Checking arguments...' . PHP_EOL;

    #region Checking "input" argument
    if(isset($args['input'])){
        if(!file_exists($args['input'])){
            echo PHP_EOL . 'Error (Input File Does Not Exists)!' . PHP_EOL .
                 'The input file does not exists at ' . $args['input'] . PHP_EOL . PHP_EOL .
                 'Terminating...' . PHP_EOL;

            exit;
        }

        echo 'The "input" argument seems to be OK.' . PHP_EOL;
    }
    else{
        echo PHP_EOL . 'Error (Missing Argument)!' . PHP_EOL .
             'The "input" argument is required.' . PHP_EOL . PHP_EOL .
             'You can define it by adding the input=PATH_TO_INPUT_FILE argument when calling this script.' . PHP_EOL . PHP_EOL .
             'Linux example:' . PHP_EOL .
             'php hlea-encrypt-file input="/home/user/file.dat" key="/home/user/key.hleakey" output="/home/user/file.dat.hleafile"' . PHP_EOL . PHP_EOL .
             'MacOS example:' . PHP_EOL .
             'php hlea-encrypt-file input="/Users/user/Documents/file.dat" key="/Users/user/Documents/key.hleakey" output="/Users/user/Documents/file.dat.hleafile"' . PHP_EOL . PHP_EOL .
             'Windows example:' . PHP_EOL .
             'php hlea-encrypt-file input="C:\Users\user\Documents\file.dat" key="C:\Users\user\Documents\key.hleakey" output="C:\Users\user\Documents\file.dat.hleafile"' . PHP_EOL . PHP_EOL .
             'Terminating...' . PHP_EOL;

        exit;
    }
    #endregion

    #region Checking "key" argument
    if(isset($args['key'])){
        if(!file_exists($args['key'])){
            echo PHP_EOL . 'Error (Key File Does Not Exists)!' . PHP_EOL .
                 'The key file does not exists at ' . $args['key'] . PHP_EOL . PHP_EOL .
                 'Terminating...' . PHP_EOL;

            exit;
        }

        echo 'The "key" argument seems to be OK.' . PHP_EOL;
    }
    else{
        echo PHP_EOL . 'Error (Missing Argument)!' . PHP_EOL .
             'The "key" argument is required.' . PHP_EOL . PHP_EOL .
             'You can define it by adding the key=PATH_TO_KEY_FILE argument when calling this script.' . PHP_EOL . PHP_EOL .
             'Linux example:' . PHP_EOL .
             'php hlea-encrypt-file input="/home/user/file.dat" key="/home/user/key.hleakey" output="/home/user/file.dat.hleafile"' . PHP_EOL . PHP_EOL .
             'MacOS example:' . PHP_EOL .
             'php hlea-encrypt-file input="/Users/user/Documents/file.dat" key="/Users/user/Documents/key.hleakey" output="/Users/user/Documents/file.dat.hleafile"' . PHP_EOL . PHP_EOL .
             'Windows example:' . PHP_EOL .
             'php hlea-encrypt-file input="C:\Users\user\Documents\file.dat" key="C:\Users\user\Documents\key.hleakey" output="C:\Users\user\Documents\file.dat.hleafile"' . PHP_EOL . PHP_EOL .
             'Terminating...' . PHP_EOL;

        exit;
    }
    #endregion

    #region Checking "output" argument
    if(isset($args['output'])){
        if(file_exists($args['output'])){
            echo PHP_EOL . 'Error (Output File Already Exists)!' . PHP_EOL .
                 'The output file already exists at ' . $args['output'] . PHP_EOL . PHP_EOL .
                 'Terminating...' . PHP_EOL;

            exit;
        }

        echo 'The "output" argument seems to be OK.' . PHP_EOL;
    }
    else{
        echo PHP_EOL . 'Error (Missing Argument)!' . PHP_EOL .
             'The "output" argument is required.' . PHP_EOL . PHP_EOL .
             'You can define it by adding the output=PATH_TO_OUTPUT_FILE argument when calling this script.' . PHP_EOL . PHP_EOL .
             'Linux example:' . PHP_EOL .
             'php hlea-encrypt-file input="/home/user/file.dat" key="/home/user/key.hleakey" output="/home/user/file.dat.hleafile"' . PHP_EOL . PHP_EOL .
             'MacOS example:' . PHP_EOL .
             'php hlea-encrypt-file input="/Users/user/Documents/file.dat" key="/Users/user/Documents/key.hleakey" output="/Users/user/Documents/file.dat.hleafile"' . PHP_EOL . PHP_EOL .
             'Windows example:' . PHP_EOL .
             'php hlea-encrypt-file input="C:\Users\user\Documents\file.dat" key="C:\Users\user\Documents\key.hleakey" output="C:\Users\user\Documents\file.dat.hleafile"' . PHP_EOL . PHP_EOL .
             'Terminating...' . PHP_EOL;

        exit;
    }
    #endregion

    echo PHP_EOL . 'Starting HLEA file encryption with the following parameters:' . PHP_EOL .
         '===========================================================================' . PHP_EOL .
         'input file  : ' . $args['input'] . PHP_EOL .
         '---------------------------------------------------------------------------' . PHP_EOL .
         'key file    : ' . $args['key'] . PHP_EOL .
         '---------------------------------------------------------------------------' . PHP_EOL .
         'output file : ' . $args['output'] . PHP_EOL .
         '===========================================================================' . PHP_EOL;

    sleep(1);
    echo PHP_EOL . 'Loading key file...' . PHP_EOL;
    $key = new Key();
    $key->loadFromFile($args['key']);
    echo 'Key file loaded successfully to the system memory.' . PHP_EOL . PHP_EOL;

    sleep(1);
    echo 'Encrypting input file data...' . PHP_EOL .
         'This may take some time depending on the input file\'s size.' . PHP_EOL . PHP_EOL;
    $processor = new Processor($key);
    $processor->encryptFile($args['input'], $args['output']);
    echo 'HLEA file encryption finished successfully.' . PHP_EOL . PHP_EOL .
         'The encrypted file has been saved to:' . PHP_EOL .
         $args['output'] . PHP_EOL . PHP_EOL;

    echo '===========================================================================' . PHP_EOL;
    echo 'Everything is done. Cheers!' . PHP_EOL;
    echo '===========================================================================' . PHP_EOL . PHP_EOL;

    echo 'Terminating...' . PHP_EOL;
}