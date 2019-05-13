<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\ContextSchema;
use BEAR\Resource\Exception\UriException;
use function parse_url;

final class UriFactory
{
    /**
     * @var string
     */
    private $schemaHost;

    /**
     * @ContextSchema
     */
    public function __construct(string $schemaHost = 'page://self')
    {
        $this->schemaHost = $schemaHost;
    }

    public function __invoke($uri, array $query = [])
    {
        if (! array_key_exists('scheme', parse_url($uri))) {
            $uri = $this->schemaHost . $uri;
        }

        return new Uri($uri, $query);
    }

    /**
     * @throws UriException
     */
    private function validate(string $uri)
    {
        if (! filter_var($uri, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            $msg = is_string($uri) ? $uri : gettype($uri);

            throw new UriException($msg, 500);
        }
    }
}
