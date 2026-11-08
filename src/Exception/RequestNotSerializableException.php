<?php

declare(strict_types=1);

namespace BEAR\Resource\Exception;

use LogicException;

final class RequestNotSerializableException extends LogicException implements ExceptionInterface
{
}
