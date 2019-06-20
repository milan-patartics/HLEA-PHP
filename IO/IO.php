<?php

namespace HLEA\IO;

class IO
{
    /**
     * Creates all parent directories for $filePath if they are not existing already.
     *
     * @param string $filePath The file path to use for parent directory structure detection.
     *
     * @return bool TRUE if parent directory tree already exists or created successfully; FALSE if failed to create the
     *              parent directory tree.
     */
    public static function preparePathBeforeCreatingFile(string $filePath): bool
    {
        $dirName = dirname($filePath);
        if(!file_exists($dirName)){
            return mkdir($dirName, 0777, true);
        }

        return true;
    }
}