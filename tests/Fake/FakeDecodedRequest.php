<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BadMethodCallException;
use Override;

/** A decorator like bear/async's AsyncRequest, whose jsonSerialize() answers the decoded view, not the resource */
final class FakeDecodedRequest extends AbstractRequest
{
    public function __construct(ResourceObject $resourceObject)
    {
        parent::__construct((new InvokerFactory())(), $resourceObject);
    }

    #[Override]
    public function jsonSerialize(): mixed
    {
        return ['decoded' => 'view'];
    }

    #[Override]
    public function withQuery(array $query): RequestInterface
    {
        throw new BadMethodCallException(__METHOD__);
    }

    #[Override]
    public function addQuery(array $query): RequestInterface
    {
        throw new BadMethodCallException(__METHOD__);
    }

    #[Override]
    public function toUri(): string
    {
        throw new BadMethodCallException(__METHOD__);
    }

    #[Override]
    public function toUriWithMethod(): string
    {
        throw new BadMethodCallException(__METHOD__);
    }

    #[Override]
    public function linkSelf(string $linkKey): RequestInterface
    {
        throw new BadMethodCallException(__METHOD__);
    }

    #[Override]
    public function linkNew(string $linkKey): RequestInterface
    {
        throw new BadMethodCallException(__METHOD__);
    }

    #[Override]
    public function linkCrawl(string $linkKey): RequestInterface
    {
        throw new BadMethodCallException(__METHOD__);
    }
}
