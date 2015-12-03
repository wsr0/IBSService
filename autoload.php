<?php
require_once 'library.php';
require_once 'soapServerClass.php';
require_once('nusoap/lib/nusoap.php');

function __autoload($class_name) {
    include __DIR__ .'/'.str_replace('\\', '/', $class_name)  . '.php';
}

