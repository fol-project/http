<?php
function FolHttpLoader($name)
{
    if (strpos($name, 'Fol\\Http\\') !== 0) {
        return;
    }

    $file = dirname(__DIR__).'/src/'.str_replace('\\', DIRECTORY_SEPARATOR, substr($name, 8)).'.php';

    if (is_file($file)) {
        require $file;
    }
}

spl_autoload_register('FolHttpLoader');
