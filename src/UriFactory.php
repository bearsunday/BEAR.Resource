<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\ContextScheme;
use function parse_url;

final class UriFactory
{
    /**
     * @var string
     */
    private $schemaHost;

    /**
     * @ContextScheme
     */
    public function __construct(string $schemaHost = 'page://self')
    {
        $this->schemaHost = $schemaHost;
    }

    public function __invoke($uri, array $query = [])
    {
        $parsedUrl = (array) parse_url($uri);
        if (! array_key_exists('scheme', $parsedUrl)) {
            $uri = $this->schemaHost . $uri;
        }

        return new Uri($uri, $query);
    }
}
