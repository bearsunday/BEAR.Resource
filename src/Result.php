<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Conventional class for reference value. (only for Invoker)
 *
 * @see      http://stackoverflow.com/questions/295016/is-it-possible-to-pass-parameters-by-reference-using-call-user-func-array
 * @see      http://d.hatena.ne.jp/sotarok/20090826/1251312215
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
    public $namedArgs;
}
