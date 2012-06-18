<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Render;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Exception;

/**
 * Interface for resource adapter provider.
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 * @Scope("prototype")
 */
final class Request implements Requestable
{
    /**
     * Renderer
     *
     * @var Render
     */
    private $render;

    /**
     * object URI scheme
     *
     * @var string
     */
    const SCHEME_OBJECT = 'object';

    /**
     * URI
     *
     * @var string
     */
    public $uri;

    /**
     * Resource Objcet
     *
     * @var BEAR\Resource\Object
     */
    public $ro;

    /**
     * Method
     *
     * @var string
     */
    public $method = '';

    /**
     * Query
     *
     * @var array
     */
    public $query = [];

    /**
     * Options
     *
     * @var array
     */
    public $options = [];

    /**
     * Request option (eager or lazy)
     *
     * @var string
     */
    public $in;


    /**
     * Links
     *
     * @var array
     */
    public $links = [];

    /**
     * Renderer
     *
     * @var Rendaerable
     */
    private $renderer;

    /**
     * Request Result
     *
     * @var Object
     */
    private $result;

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Requestable::__construct()
     *
     * @Inject
     */
    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Requestable::__invoke()
     */
    public function __invoke(array $query = null)
    {
        if (!is_null($query)) {
            $this->query = array_merge($this->query, $query);
        }
        $result = $this->invoker->invoke($this);
        return $result;
    }

    /**
     * Render view
     *
     * @return string
     */
    public function __toString()
    {
        try {
            if (is_null($this->result)) {
                $this->result = $this->__invoke();
            }
            if (is_string($this->result)) {
                return $this->result;
            }
            if (method_exists($this->result, '__toString')) {
                return (string)$this->result;
            }
            if ($this->result instanceof Object && is_string($this->result->body)) {
                return $this->result->body;
            }
        } catch (Exception $e) {
            return '';
        }
        return '';
    }

    /**
     * To Request URI string
     *
     * @return string
     */
    public function toUri()
    {
        $query = http_build_query($this->query, null, '&', PHP_QUERY_RFC3986);
        $uri = $this->ro->uri;
        $queryString = "{$uri}" . ($query ? '?' :  '') . $query;
        $linkString = '';
        foreach ($this->links as $link) {
            $linkString .= ", link {$link->type}:{$link->key}";
        }
        $string = $queryString . $linkString;
        return $string;
    }

    /**
     * To Request URI string with request method
     *
     * @return string
     */
    public function toUriWithMethod()
    {
        return "{$this->method} " . $this->toUri();
    }
}