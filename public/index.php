<?php

define('TINY_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

use App\Bootstrap;

$bootstrap = new Bootstrap();
$bootstrap->boot();
