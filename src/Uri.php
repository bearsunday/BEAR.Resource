<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\UriException;

final class Uri extends AbstractUri
{
    /**
     * @param string $uri
     * @param array  $query
     */
    public function __construct($uri, array $query = [])
    {
        if (! filter_var($uri, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            $this->invalidUri($uri);
        }
        $parsedUrl = parse_url($uri);
        list($this->scheme, $this->host, $this->path) = array_values($parsedUrl);
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $this->query);
        }
        if ($query) {
            $this->query = $query + $this->query;
        }
    }

    /**
     * @param string $uri
     */
    private function invalidUri($uri)
    {
        $msg = is_string($uri) ? $uri : gettype($uri);
        throw new UriException($msg, 500);
    }
}
