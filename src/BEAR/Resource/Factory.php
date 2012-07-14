<?php
/**
 * BEAR.Resource;
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\InjectorInterface;
use Ray\Di\Di\Inject;

/**
 * Resource object factory.
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 * @Scope("singleton")
 */
class Factory implements FactoryInterface
{
    /**
     * Resource adapter biding config
     *
     * @var Scheme
     */
    private $scheme = [];

    /**
     * Construcotr
     *
     * @param InjectorInterface $injector
     * @param Scheme            $scheme
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
     * @throws Exception\InvalidScheme
     */
    public function newInstance($uri)
    {
        $parsedUrl = parse_url($uri);
        if (!(isset($parsedUrl['scheme']) && isset($parsedUrl['scheme']))) {
            throw new Exception\InvalidUri;
        }
        $scheme = $parsedUrl['scheme'];
        $host = $parsedUrl['host'];
        if (!isset($this->scheme[$scheme])) {
            throw new Exception\InvalidScheme($uri);
        }
        if (!isset($this->scheme[$scheme][$host])) {
            if (!(isset($this->scheme[$scheme]['*']))) {
                throw new Exception\InvalidScheme($uri);
            }
            $host = '*';
        }
        $adapter = $this->scheme[$scheme][$host];
        if ($adapter instanceof Provider) {
            $adapter = $adapter->get($uri);
        }
        $adapter->uri = $uri;

        return $adapter;
    }
}
