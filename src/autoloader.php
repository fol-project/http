<?php
function FolHttpLoader($class)
{
    if (strpos($class, 'Fol\\Http') !== 0) {
        return;
    }

    $file = __DIR__.'/'.str_replace('\\', DIRECTORY_SEPARATOR, str_replace('Fol\\Http\\', '', $class)) . '.php';

    if (is_file($file)) {
        require $file;
    }
}

spl_autoload_register('FolHttpLoader');
