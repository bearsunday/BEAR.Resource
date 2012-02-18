<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Abstract resource object
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 */
abstract class AbstractObject implements Object, \ArrayAccess, \Countable, \IteratorAggregate
{
    use ArrayAccess;

    /**
     * URI
     *
     * @var string
     */
    public $uri = '';

    /**
     * Resource code
     *
     * @var int
     */
    public $code = 200;

    /**
     * Resource header
     *
     * @var array
     */
    public $headers = array();

    /**
     * Resource body
     *
     * @var mixed
     */
    public $body;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->body = new \ArrayObject;
    }

    /**
     * Return resource object string.
     *
     * @return string
     */
    public function __toString()
    {
        $code = new Code;
        $string = '[STATUS]' . $this->code . ' ' . $code->statusText[$this->code] . PHP_EOL;
        foreach ($this->headers as $key => $header) {
            $key = ($key) ? "{$key}: " : '';
            $string .= "[HEADER]{$key}{$header}" . PHP_EOL;
        }
        if (is_array($this->body) || $this->body instanceof \Traversable) {
            foreach ($this->body as $key => $body) {
                $string .= "[BODY]{$key}{$body}" . PHP_EOL;
            }
        } else {
            $string .= '[BODY]' . var_export($this->body, true) . PHP_EOL;
        }
        return $string . PHP_EOL;
    }
}