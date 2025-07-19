<?php

declare(strict_types=1);

namespace BEAR\SchemaLogger;

use Koriym\SchemaLogger\AbstractSchemaLogContext;

/**
 * BEAR Resource Schema Context
 * 
 * Schema-aware context for BEAR applications with predefined schema base URL.
 * Schemas are available at: https://github.com/bearsunday/BEAR.Resource/blob/1.x/src-logger/bear/schema/
 */
final class BearSchemaLogContext extends AbstractSchemaLogContext
{
    public const RESOURCE_REQUEST = 'resource_request';
    public const API_CALL = 'api_call';
    public const DATABASE_QUERY = 'database_query';
    
    protected const SCHEMA_BASE_PATH = 'https://github.com/bearsunday/BEAR.Resource/blob/1.x/src-logger/bear/schema';
}