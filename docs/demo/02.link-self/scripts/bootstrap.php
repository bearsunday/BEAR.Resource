<?php

use BEAR\Resource\Module\ResourceModule;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Ray\Di\Injector;

$packageDir = dirname(dirname(dirname(dirname(__DIR__))));
$loader = require $packageDir . '/vendor/autoload.php';
require dirname(__DIR__) . '/Sandbox/Resource/App/Blog.php';
require dirname(__DIR__) . '/Sandbox/Resource/App/User.php';
require_once $packageDir . '/src/Annotation/Embed.php';
