<?php
// PHP Runtime Error
error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

// CLI Server
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) return false;
}

// Load external classes
require dirname(__DIR__).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

// Load config
require dirname(__DIR__).DIRECTORY_SEPARATOR.'config.php';

// Load internal classes
spl_autoload_register(function ($classname) {
    $path = realpath(__DIR__ . '/..').DIRECTORY_SEPARATOR.$classname.'.php';
    if (is_file($path)) require ($path);
});

// Set time zone
date_default_timezone_set($config['app']['timezone']);

// Declare Skeleton Version
define('SKELETON_VERSION','1.1.0');

session_start();

require 'app.php';
