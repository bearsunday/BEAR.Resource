<?php

declare(strict_types=1);

namespace Koriym\SchemaLogger;

/**
 * Type definitions for SchemaLogger
 *
 * Schema Convention:
 * sessionId should correspond to a JSON schema file named "$sessionId.json"
 * that defines the expected format of metadata. This provides structure
 * without enforcing validation within this library.
 *
 * @phpcs:disable SlevomatCodingStandard.Commenting.DocCommentSpacing
 *
 * Core Types
 * @psalm-type SchemaLogEntry = array{
 *     event_type: string,
 *     id: string,
 *     timestamp: float
 * }
 * @psalm-type SchemaLogContext = array{
 *     session_id: string,
 *     start_time: float,
 *     metadata?: array<string, mixed>
 * }
 * @psalm-type SchemaLogData = array{
 *     context: SchemaLogContext,
 *     entries: list<SchemaLogEntry>
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