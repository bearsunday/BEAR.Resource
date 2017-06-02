<?php
/**
 * This file is part of the Ray.WebContextParam.
 *
 * @license http://opensource.org/licenses/bsd-license.php MIT
 */
namespace Ray\WebContextParam\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class CookieParam extends AbstractWebContextParam
{
    const GLOBAL_KEY = '_COOKIE';
}
