<?php

namespace BEAR\Resource;

use Aura\Signal\HandlerFactory;
use Aura\Signal\Manager;
use Aura\Signal\ResultCollection;
use Aura\Signal\ResultFactory;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Ray\Di\Definition;

class ResourceFactory
{
    private $cache;
    private $reader;
    private $logger;

    public function setResourceDir($nameSpace, $resourceDir)
    {
    }

    public function setCache(Cache $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    public function setAnnotationReader(Reader $reader)
    {
        $this->reader = $reader;

        return $this;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    public function getInstance()
    {
        $cache = $this->cache ?: new ArrayCache;
        $logger = $this->logger ?: new Logger;
        $reader = $this->reader ?: new AnnotationReader;
        $invoker = new Invoker(
            new Linker($reader, $cache),
            new NamedParameter(
                new SignalParameter(
                    new Manager(new HandlerFactory, new ResultFactory, new ResultCollection),
                    new Param
                )
            ),
            $logger
        );

        $resource = new Resource(
            new Factory(new SchemeCollection),
            $invoker,
            new Request($invoker),
            new Anchor($reader, new Request($invoker))
        );

        return $resource;
    }
}
