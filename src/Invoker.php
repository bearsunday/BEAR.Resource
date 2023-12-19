<?php
// phpcs:ignoreFile

namespace BEAR\Resource;

use BEAR\Resource\Exception\BadRequestException;
use Throwable;
use function call_user_func_array;
use function is_callable;
use function ucfirst;

final class Invoker implements InvokerInterface
{
    public function __construct(
        private readonly NamedParameterInterface $params,
        private readonly ExtraMethodInvoker $extraMethod,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(AbstractRequest $request): ResourceObject
    {
        if ($request->resourceObject instanceof HttpResourceObject) {
            return $request->resourceObject->request($request);
        }

        $callable = [$request->resourceObject, 'on' . ucfirst($request->method)];
        if (! is_callable($callable)) {
            // OPTIONS or HEAD
            return ($this->extraMethod)($request, $this);
        }

        $params = $this->params->getParameters($callable, $request->query);
        /** @psalm-suppress MixedAssignment */
        try {
            $response = call_user_func_array($callable, $params);
        } catch (Throwable $e) {
            if ($e::class === \TypeError::class) {
                throw new BadRequestException('Invalid parameter type', Code::BAD_REQUEST, $e);
            }
            throw $e;
        }
        if (! $response instanceof ResourceObject) {
            $request->resourceObject->body = $response;
            $response = $request->resourceObject;
        }

        ($this->logger)($response);

        return $response;
    }
}
