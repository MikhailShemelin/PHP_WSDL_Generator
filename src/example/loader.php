<?php

spl_autoload_register(function ($class_name) {
    $file_name = '../'.str_replace('\\', DIRECTORY_SEPARATOR, $class_name).'.php';
    if (file_exists($file_name)) {
        include_once $file_name;
    }
});


