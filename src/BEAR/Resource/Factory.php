<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\Di\Inject;
use Ray\Di\Di\Scope;
use Ray\Di\Exception\NotReadable;

/**
 * Resource object factory
 *
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
     * @param SchemeCollectionInterface $scheme
     *
     * @Inject
     */
    public function __construct(SchemeCollectionInterface $scheme)
    {
        $this->scheme = $scheme;
    }

    /**
     * Set scheme collection
     *
     * @param SchemeCollectionInterface $scheme
     *
     * @Inject(optional = true)
     */
    public function setSchemeCollection(SchemeCollectionInterface $scheme)
    {
        $this->scheme = $scheme;
    }

    /**
     * {@inheritDoc}
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
        } catch (NotReadable $e) {
            throw new Exception\ResourceNotFound($uri, 0, $e);
        } catch (\Exception $e) {
            throw $e;
        }
        $adapter->uri = $uri;

        return $adapter;
    }
}
