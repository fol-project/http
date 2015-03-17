<?php
error_reporting(E_ALL);

$autoload = dirname(__DIR__).'/vendor/autoload.php';

if (is_file($autoload)) {
	include_once $autoload;
}

PHPUnit_Framework_Error_Notice::$enabled = true;
