<?php

declare(strict_types=1);

namespace Koriym\SchemaLogger;

use function microtime;

final class SchemaLogEntry
{
    public readonly string $eventType;
    public readonly string $id;
    public readonly float $timestamp;
    
    public function __construct(
        string $eventType,
        string $id,
        ?float $timestamp = null,
    ) {
        $this->eventType = $eventType;
        $this->id = $id;
        $this->timestamp = $timestamp ?? microtime(true);
    }
    
    public function toArray(): array
    {
        return [
            'event_type' => $this->eventType,
            'id' => $this->id,
            'timestamp' => $this->timestamp,
        ];
    }
}