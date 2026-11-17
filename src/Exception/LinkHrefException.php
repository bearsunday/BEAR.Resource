<?php

declare(strict_types=1);

namespace BEAR\Resource\Exception;

use LogicException;

/** A #[Link] href has a fragment */
final class LinkHrefException extends LogicException implements ExceptionInterface
{
}
