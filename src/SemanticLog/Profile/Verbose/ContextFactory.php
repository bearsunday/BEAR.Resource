<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog\Profile\Verbose;

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
        return new OpenContext($request);
    }

    #[Override]
    public function createCompleteContext(ResourceObject $resource, AbstractContext $openContext): CompleteContext
    {
        /** @var OpenContext $openContext */
        return CompleteContext::create($resource, $openContext);
    }

    #[Override]
    public function createErrorContext(Throwable $exception, string $exceptionId = '', ?AbstractContext $openContext = null): ErrorContext
    {
        return ErrorContext::create($exception, $exceptionId);
    }
}
