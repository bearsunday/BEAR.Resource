<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog\Profile\Verbose;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Types;
use JsonSerializable;
use Koriym\SemanticLogger\AbstractContext;
use Override;

use function file_exists;
use function file_put_contents;
use function function_exists;
use function is_string;
use function serialize;
use function sprintf;
use function str_replace;
use function sys_get_temp_dir;
use function uniqid;
use function xdebug_stop_trace;
use function xhprof_disable;

/**
 * @psalm-import-type Headers from Types
 * @psalm-import-type HttpBody from Types
 */
final class CompleteContext extends AbstractContext implements JsonSerializable
{
    /** @psalm-suppress InvalidClassConstantType */
    public const TYPE = 'bear_resource_complete';

    /** @psalm-suppress InvalidClassConstantType */
    public const SCHEMA_URL = 'https://bearsunday.github.io/BEAR.Resource/schemas/complete-context.json';

    public readonly string $uri;
    public readonly int $code;

    /** @var Headers */
    public readonly array $headers;

    /** @var HttpBody  */
    public readonly mixed $body;
    public readonly string $view;
    public ?string $xhprofFile = null;
    public ?string $xdebugTraceFile = null;

    public function __construct(ResourceObject $resource, OpenContext $openContext)
    {
        // Trigger rendering to get view
        $resourceString = (string) $resource;
        unset($resourceString);
        $this->uri = (string) $resource->uri;
        $this->code = $resource->code;
        $this->headers = $resource->headers;
        /** @psalm-suppress MixedAssignment */
        /** @phpstan-ignore-next-line */
        $this->body = $resource->body;
        /** @psalm-suppress PossiblyNullPropertyAssignmentValue */
        $this->view = $resource->view ?? '';
        // Stop profiling and save files
        $this->xhprofFile = null;
        $this->xdebugTraceFile = null;

        if (function_exists('xhprof_disable')) {
            $xhprofData = xhprof_disable();
            $filename = sprintf(
                '%s/xhprof_%s_%s.xhprof',
                sys_get_temp_dir(),
                str_replace(['/', ':', '?'], '_', $openContext->uri),
                uniqid('', true),
            );

            if (file_put_contents($filename, serialize($xhprofData)) !== false) {
                $this->xhprofFile = $filename;
            }
        }

        // Handle Xdebug trace
        $xdebugId = $openContext->getXdebugId();
        if ($xdebugId === null || ! function_exists('xdebug_stop_trace')) {
            return; // @codeCoverageIgnore
        }

        /** @var string|null $traceFile */
        $traceFile = @xdebug_stop_trace(); // @phpstan-ignore-line
        if (! is_string($traceFile) || ! file_exists($traceFile)) {
            return;
        }

        $this->xdebugTraceFile = $traceFile; // @codeCoverageIgnore
    }

    public static function create(ResourceObject $resource, OpenContext $openContext): self
    {
        return new self($resource, $openContext);
    }

    /** @return array<string, mixed> */
    #[Override]
    public function jsonSerialize(): array
    {
        $data = [
            'uri' => $this->uri,
            'code' => $this->code,
            'headers' => $this->headers,
            'body' => $this->body,
            'view' => $this->view,
        ];

        // Only include profiling files if they exist
        if ($this->xhprofFile !== null) {
            $data['xhprofFile'] = $this->xhprofFile;
        }

        if ($this->xdebugTraceFile !== null) {
            $data['xdebugTraceFile'] = $this->xdebugTraceFile; // @codeCoverageIgnore
        }

        return $data;
    }
}
