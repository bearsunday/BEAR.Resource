<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * @property $this lazy
 * @property $this eager
 */
final class Request extends AbstractRequest implements RequestInterface
{
    const GET = 'get';
    const POST = 'post';
    const PUT = 'put';
    const PATCH = 'patch';
    const DELETE = 'delete';
    const HEAD = 'head';
    const OPTIONS = 'options';

    /**
     * Links
     *
     * @var LinkType[]
     */
    public $links = [];

    /**
     * @var InvokerInterface
     */
    protected $invoker;

    /**
     * Request option (eager or lazy)
     *
     * @var string
     */
    public $in = 'lazy';

    /**
     * {@inheritdoc}
     */
    public function withQuery(array $query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addQuery(array $query)
    {
        $this->query = array_merge($this->query, $query);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function toUriWithMethod()
    {
        $uri = $this->toUri();

        return "{$this->method} {$uri}";
    }

    /**
     * {@inheritDoc}
     */
    public function toUri()
    {
        $this->resourceObject->uri->query = $this->query;

        return (string) $this->resourceObject->uri;
    }

    /**
     * {@inheritDoc}
     */
    public function linkSelf($linkKey)
    {
        $this->links[] = new LinkType($linkKey, LinkType::SELF_LINK);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function linkNew($linkKey)
    {
        $this->links[] = new LinkType($linkKey, LinkType::NEW_LINK);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function linkCrawl($linkKey)
    {
        $this->links[] = new LinkType($linkKey, LinkType::CRAWL_LINK);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function __get($name)
    {
        $this->in = $name;

        return $this;
    }
}
