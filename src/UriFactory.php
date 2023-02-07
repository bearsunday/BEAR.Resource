<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\ContextScheme;

use function array_key_exists;
use function parse_url;

final class UriFactory
{
    public function __construct(
        #[ContextScheme]
        private string $schemaHost = 'page://self',
    ) {
    }

    /** @param array<string, mixed> $query */
    public function __invoke(string $uri, array $query = []): Uri
    {
        $parsedUrl = (array) parse_url($uri);
        if (! array_key_exists('scheme', $parsedUrl)) {
            $uri = $this->schemaHost . $uri;
        }

        return new Uri($uri, $query);
    }
}
