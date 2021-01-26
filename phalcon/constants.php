<?php
if(!defined('STDIN'))  define('STDIN',  fopen('php://stdin',  'rb'));
if(!defined('STDOUT')) define('STDOUT', fopen('php://stdout', 'wb'));
if(!defined('STDERR')) define('STDERR', fopen('php://stderr', 'wb'));
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if (!file_exists(__DIR__ . DS  .'../vendor/autoload.php')) {
	die("Composer autoloader missing.");
} elseif (!class_exists("\\Phalcon\Version")) {
	die("This application requires the Phalcon php extension to run.");
}
