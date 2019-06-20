<?php

const DS = DIRECTORY_SEPARATOR;
const PROJECT_DIR = __DIR__ . DS . '..';

#region Settings
error_reporting(E_ALL);

ini_set('max_execution_time', 0);
ini_set('memory_limit', '128M');
#endregion

#region HLEA\Converter
require_once PROJECT_DIR . DS . 'Converter' . DS . 'Converter.php';
#endregion

#region HLEA\IO
require_once PROJECT_DIR . DS . 'IO' . DS . 'IO.php';
#endregion

#region HLEA\Key
require_once PROJECT_DIR . DS . 'Key' . DS . 'SwapTable.php';
require_once PROJECT_DIR . DS . 'Key' . DS . 'Stream.php';
require_once PROJECT_DIR . DS . 'Key' . DS . 'ByteSwapTable.php';
require_once PROJECT_DIR . DS . 'Key' . DS . 'ByteStream.php';
require_once PROJECT_DIR . DS . 'Key' . DS . 'UInt16SwapTable.php';
require_once PROJECT_DIR . DS . 'Key' . DS . 'UInt16Stream.php';
require_once PROJECT_DIR . DS . 'Key' . DS . 'Key.php';
#endregion

#region HLEA\Generator
require_once PROJECT_DIR . DS . 'Generator' . DS . 'RandomGenerator.php';
require_once PROJECT_DIR . DS . 'Generator' . DS . 'Shuffler.php';
require_once PROJECT_DIR . DS . 'Generator' . DS . 'KeyGenerator.php';
#endregion

#region HLEA\Processor
require_once PROJECT_DIR . DS . 'Processor' . DS . 'Processor.php';
#endregion

#region HLEA\Exceptions
require_once PROJECT_DIR . DS . 'Exceptions' . DS . 'InvalidSwapTableSize.php';
require_once PROJECT_DIR . DS . 'Exceptions' . DS . 'InvalidSwapTableIndex.php';
require_once PROJECT_DIR . DS . 'Exceptions' . DS . 'InvalidSwapTableValue.php';
require_once PROJECT_DIR . DS . 'Exceptions' . DS . 'InvalidStreamIndex.php';
require_once PROJECT_DIR . DS . 'Exceptions' . DS . 'InvalidStreamValue.php';
require_once PROJECT_DIR . DS . 'Exceptions' . DS . 'InvalidShuffleIterationCount.php';
require_once PROJECT_DIR . DS . 'Exceptions' . DS . 'InvalidBinaryData.php';
#endregion