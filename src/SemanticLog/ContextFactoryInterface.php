<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog;

use BEAR\Resource\AbstractRequest;
use BEAR\Resource\ResourceObject;
use Koriym\SemanticLogger\AbstractContext;
use Throwable;

interface ContextFactoryInterface
{
    public function createOpenContext(AbstractRequest $request): AbstractContext;

    public function createCompleteContext(ResourceObject $resource, AbstractContext $openContext): AbstractContext;

    public function createErrorContext(Throwable $exception, string $exceptionId = '', ?AbstractContext $openContext = null): AbstractContext;
}
