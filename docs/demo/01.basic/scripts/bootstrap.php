<?php
/**
 * This file is part of the BEAR.Sunday package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;

/** @var $loader ClassLoader */
$loader = require dirname(dirname(dirname(dirname(__DIR__)))) . '/vendor/autoload.php';
require dirname(__DIR__) . '/Sandbox/src/Resource/App/User.php';
AnnotationRegistry::registerLoader([$loader, 'loadClass']);
