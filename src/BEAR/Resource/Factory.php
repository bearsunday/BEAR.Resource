<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\InjectorInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Scope;

/**
 * Resource object factory.
 *
 * @package BEAR.Resource
 *
 * @Scope("singleton")
 */
class Factory implements FactoryInterface
{
    /**
     * Resource adapter biding config
     *
     * @var SchemeCollection
     */
    private $scheme = [];

    /**
     * Constructor
     *
     * @param SchemeCollection  $scheme
     *
     * @Inject
     */
    public function __construct(SchemeCollection $scheme)
    {
        $this->scheme = $scheme;
    }

    /**
     * Set scheme collection
     *
     * @param SchemeCollection $scheme
     *
     * @Inject(optional = true)
     */
    public function setSchemeCollection(SchemeCollection $scheme)
    {
        $this->scheme = $scheme;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.FactoryInterface::newInstance()
     * @throws Exception\Scheme
     */
    public function newInstance($uri)
    {
        $parsedUrl = parse_url($uri);
        if (!(isset($parsedUrl['scheme']) && isset($parsedUrl['scheme']))) {
            throw new Exception\Uri;
        }
        $scheme = $parsedUrl['scheme'];
        $host = $parsedUrl['host'];
        if (!isset($this->scheme[$scheme])) {
            throw new Exception\Scheme($uri);
        }
        if (!isset($this->scheme[$scheme][$host])) {
            if (!(isset($this->scheme[$scheme]['*']))) {
                throw new Exception\Scheme($uri);
            }
            $host = '*';
        }
        try {
            $adapter = $this->scheme[$scheme][$host];
            if ($adapter instanceof ProviderInterface) {
                $adapter = $adapter->get($uri);
            }
        } catch (\Exception $e) {
            throw new Exception\ResourceNotFound($uri);
        }
        $adapter->uri = $uri;

        return $adapter;
    }
}
