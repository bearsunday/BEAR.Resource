<?php

$packageDir = dirname(dirname(dirname(dirname(__DIR__))));
$loader = require $packageDir . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/Sandbox/Resource/App/Blog.php';
require_once dirname(__DIR__) . '/Sandbox/Resource/App/User.php';
require_once $packageDir . '/src/Annotation/Embed.php';
