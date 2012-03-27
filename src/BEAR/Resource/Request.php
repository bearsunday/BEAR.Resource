<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Render;
use Exception;

/**
 * Interface for resource adapter provider.
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 * @Scope("prototype")
 */
class Request
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
     * Constructor
     *
     * @param Invokable $invoker
     *
     * @Inject
     */
    public function __construct(Invokable $invoker)
    {
        $this->invoker = $invoker;
    }

    /**
     * Set renderer
     *
     * @param Render $renderer
     *
     * @return void
     */
    public function setRenderer(Renderable $renderer)
    {
        $this->renderer = $renderer;
    }

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
    public $query = array();

    /**
     * Options
     *
     * @var array
     */
    public $options = array();

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
    public $links = array();

    /**
     * Renderer
     *
     * @var Rendaerable
     */
    private $renderer;

    /**
     * Invokable resource request
     *
     * @param array $query
     */
    public function __invoke(array $query = null)
    {
        if (!is_null($query)) {
            $this->query = array_merge($this->query, $query);
        }
        return $this->invoker->invoke($this);
    }

    /**
     * Render view
     *
     * @return string
     */
    public function __toString()
    {
        if (is_null($this->renderer)) {
            $string = $this->toRequestString();
            return $string;
        }
        try {
            $data = $this->__invoke();
            $string = $this->renderer->render($this, $data);
            return $string;
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * To Request URI string
     *
     * @return string
     */
    private function toRequestString()
    {
        $query = http_build_query($this->query, '&');
        if (isset($this->ro->uri) === false) {
            $uri = self::SCHEME_OBJECT . '://' . str_replace('\\', '/', get_class($this->ro));
        } else {
            $uri = $this->ro->uri;
        }
        $queryString = "{$this->method} {$uri}" . ($query ? '?' :  '') . $query;
        $linkString = '';
        foreach ($this->links as $link) {
            $linkString .= ", link {$link->type}:{$link->key}";
        }
        $string = $queryString . $linkString;
        return $string;
    }
}