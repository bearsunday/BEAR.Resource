<?php
// phpcs:ignoreFile

namespace BEAR\Resource;

use BEAR\Resource\Exception\BadRequestException;
use Throwable;
use function call_user_func_array;
use function is_callable;
use function ucfirst;

/** @psalm-import-type Query from Types */
final readonly class PhpClassInvoker implements InvokerInterface
{
    /** @psalm-pure */
    public function __construct(
        private NamedParameterInterface $params,
        private ExtraMethodInvoker $extraMethod,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function invoke(AbstractRequest $request): ResourceObject
    {
        $callable = [$request->resourceObject, 'on' . ucfirst($request->method->value)];
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
