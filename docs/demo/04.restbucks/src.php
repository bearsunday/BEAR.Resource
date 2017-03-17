<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
$loader = require 'vendor/autoload.php';
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);
/* @var $loader \Composer\Autoload\ClassLoader */
$loader->add('Restbucks', [dirname(__DIR__)]);
