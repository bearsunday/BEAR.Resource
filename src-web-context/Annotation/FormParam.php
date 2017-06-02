<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Ray\WebContextParam\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class FormParam extends AbstractWebContextParam
{
    const GLOBAL_KEY = '_POST';
}
