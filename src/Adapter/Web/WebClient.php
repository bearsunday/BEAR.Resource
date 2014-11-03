<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter\Web;

use Aura\Di\Exception;
use BEAR\Resource\Exception\LogicException;
use BEAR\Resource\Exception\ResourceNotFound;
use BEAR\Resource\Exception\WebApiRequest;
use BEAR\Resource\Request;
use BEAR\Resource\ResourceObject;
use GuzzleHttp\Command\Exception\CommandException;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Subscriber\History;
use GuzzleHttp\Command\Guzzle\GuzzleClientInterface;
use BEAR\Resource\Exception\WebApiOperationNotFound;
use BEAR\Resource\Adapter\Http\HttpClientInterface;

use Ray\Di\Scope;

/**
 * Web resource
 *
 * @Scope("Singleton")
 */
class WebClient extends ResourceObject implements HttpClientInterface
{
    /**
     * Http client
     *
     * @var ClientInterface
     */
    private $client;

    /**
     * @var array
     */
    private $operations;

    /**
     * @var string
     */
    private $path;

    /**
     * @param GuzzleClientInterface $client
     * @param array                 $serviceDescription
     * @param string                $path
     */
    public function __construct
    (
        GuzzleClientInterface $client,
        array $serviceDescription,
        $path
    ) {
        $this->client = $client;
        $this->operations = $serviceDescription['operations'];
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function onGet()
    {
        return $this->onRequest('GET', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function onPost()
    {
        return $this->onRequest('POST', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function onPut()
    {
        return $this->onRequest('PUT', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function onDelete()
    {
        return $this->onRequest('DELETE', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function onPatch()
    {
        return $this->onRequest('PATCH', func_get_args());
    }

    public function onOptions()
    {
        throw new LogicException('OPTION not supported for web api resource');
    }

    public function onHead()
    {
        throw new LogicException('HEAD not supported for web api resource');
    }

    /**
     * @param string $method
     * @param array  $args
     *
     * @return $this
     * @throws \BEAR\Resource\Exception\WebApiOperationNotFound
     * @throws \BEAR\Resource\Exception\WebApiRequest
     */
    private function onRequest($method, array $args)
    {
        $args = isset($args[0]) ? $args[0] : [];
        $operation = $method . ':' . substr($this->path, 1);
        if (! isset($this->operations[$operation])) {
            throw new WebApiOperationNotFound($operation);
        }
        try {
            $response = $this->client->{$operation}($args);
            /** @var $response \GuzzleHttp\Command\Model */
            $this->body = $response->toArray();
            return $this;
        } catch (CommandException $e) {
            throw new WebApiRequest($operation, $e->getCode(), $e);
        }

    }
}
