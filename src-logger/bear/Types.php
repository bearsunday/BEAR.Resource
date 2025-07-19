<?php

declare(strict_types=1);

namespace BEAR\SchemaLogger;

/**
 * Type definitions for BEAR SchemaLogger extensions
 *
 * @phpcs:disable SlevomatCodingStandard.Commenting.DocCommentSpacing
 *
 * BEAR-specific Types
 * @psalm-type BearResourceContext = array{
 *     session_id: string,
 *     start_time: float,
 *     uri: string,
 *     method: string,
 *     query: array<string, mixed>,
 *     type: 'resource_request'
 * }
 * @psalm-type BearResourceEntry = array{
 *     event_type: 'resource_invoke_start'|'resource_invoke_end'|'extra_method_invoked'|'error',
 *     id: string,
 *     timestamp: float
 * }
 *
 * @phpcs:enable
 */
final class Types
{
    /** @codeCoverageIgnore */
    private function __construct()
    {
    }
}