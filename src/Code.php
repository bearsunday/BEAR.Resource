<?php

declare(strict_types=1);

namespace BEAR\Resource;

/**
 * Resource object code
 */
final class Code
{
    // 20X Success
    public const OK = 200;

    public const CREATED = 201;

    public const ACCEPTED = 202;

    public const NO_CONTENT = 204;

    // 30X Redirection
    public const MOVED_PERMANENTLY = 301;

    public const FOUND = 302;

    public const SEE_OTHER = 303;

    public const NOT_MODIFIED = 304;

    public const TEMPORARY_REDIRECT = 307;

    public const PERMANENT_REDIRECT = 308;

    // 40X Client Error
    public const BAD_REQUEST = 400;

    public const UNAUTHORIZED = 401;

    public const FORBIDDEN = 403;

    public const NOT_FOUND = 404;

    // 50X Service Error
    public const ERROR = 500;

    public const SERVICE_UNAVAILABLE = 503;

    /**
     * Hypertext Transfer Protocol (HTTP) Status Code Registry
     *
     * <pre>
     * - 1xx: Informational - Request received, continuing process
     * - 2xx: Success - The action was successfully received, understood, and accepted
     * - 3xx: Redirection - Further action must be taken in order to complete the request
     * - 4xx: Client Error - The request contains bad syntax or cannot be fulfilled
     * - 5xx: Server Error - The server failed to fulfill an apparently valid request
     * </pre>
     *
     * @see http://www.iana.org/assignments/http-status-codes
     * @var array<int, string>
     */
    public $statusText = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // 103-199   Unassigned
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        // 209-225   Unassigned
        226 => 'IM Used',
        // 227-299   Unassigned
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // 309-399   Unassigned
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request Uri Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        // 427-499   Unassigned
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        // 509       Unassigned
        510 => 'Not Extended',
        // 511-599   Unassigned
    ];
}
