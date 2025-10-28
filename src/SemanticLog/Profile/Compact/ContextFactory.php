<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog\Profile\Compact;

use BEAR\Resource\AbstractRequest;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\SemanticLog\ContextFactoryInterface;
use Koriym\SemanticLogger\AbstractContext;
use Override;
use Throwable;

final class ContextFactory implements ContextFactoryInterface
{
    #[Override]
    public function createOpenContext(AbstractRequest $request): OpenContext
    {
        return OpenContext::create($request);
    }

    #[Override]
    public function createCompleteContext(ResourceObject $resource, AbstractContext $openContext): CompleteContext
    {
        /** @var OpenContext $openContext */
        return CompleteContext::create($resource, $openContext);
    }

    #[Override]
    public function createErrorContext(Throwable $exception, string $exceptionId = '', AbstractContext|null $openContext = null): ErrorContext
    {
        /** @var ?OpenContext $openContext */
        return ErrorContext::create($exception, $exceptionId, $openContext);
    }
}
