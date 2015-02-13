<?php

$packageDir = dirname(dirname(dirname(dirname(__DIR__))));
$loader = require $packageDir . '/vendor/autoload.php';
require dirname(__DIR__) . '/Sandbox/Resource/App/Blog.php';
require dirname(__DIR__) . '/Sandbox/Resource/App/User.php';
require_once $packageDir . '/src/Annotation/Embed.php';
