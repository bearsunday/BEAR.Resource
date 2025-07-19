<?php

declare(strict_types=1);

namespace Koriym\SchemaLogger;

use function microtime;

/**
 * Schema Log Context (no schema URL)
 * 
 * Generic context without predefined schema URL.
 * Use specific implementations like BearSchemaLogContext for schema-aware contexts.
 */
final class SchemaLogContext extends AbstractSchemaLogContext
{
    protected const SCHEMA_BASE_PATH = null;
}