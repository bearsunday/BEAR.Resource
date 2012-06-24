<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * conventional class for refference value.
 *
 * @see      http://stackoverflow.com/questions/295016/is-it-possible-to-pass-parameters-by-reference-using-call-user-func-array
 * @see      http://d.hatena.ne.jp/sotarok/20090826/1251312215
 * @internal only for Invoker
 */
final class Result
{
    /**
     * Value
     *
     * @var mixed
     */
    public $value;

    /**
     * Arguments
     *
     * @var array
     */
    public $args;
}
