<?php

declare(strict_types=1);

namespace Koriym\SchemaLogger;

use function microtime;

abstract class AbstractSchemaLogContext
{
    public readonly string $sessionId;
    public readonly float $startTime;
    public readonly array $metadata;
    public readonly ?string $schemaUrl;
    
    protected const SCHEMA_BASE_PATH = null;
    
    public function __construct(
        string $sessionId,
        array $metadata = [],
        ?float $startTime = null,
    ) {
        $this->sessionId = $sessionId;
        $this->startTime = $startTime ?? microtime(true);
        $this->metadata = $metadata;
        $this->schemaUrl = static::SCHEMA_BASE_PATH ? static::SCHEMA_BASE_PATH . '/' . $sessionId . '.json' : null;
    }
    
    public function toArray(): array
    {
        return [
            'session_id' => $this->sessionId,
            'start_time' => $this->startTime,
            ...$this->metadata,
        ];
    }
}