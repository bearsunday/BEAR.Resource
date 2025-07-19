<?php

declare(strict_types=1);

namespace Koriym\SchemaLogger;

use Override;

use function microtime;
use function uniqid;

final class SchemaLogger implements SchemaLoggerInterface
{
    private ?AbstractSchemaLogContext $context = null;
    
    /** @var list<SchemaLogEntry> */
    private array $entries = [];

    #[Override]
    public function open(SchemaLogContext $context): void
    {
        $this->context = $context;
        $this->entries = [];
    }

    #[Override]
    public function add(SchemaLogEntry $entry): void
    {
        if ($this->context === null) {
            return;
        }
        
        // SchemaLoggerが受信時に追加のメタデータを記録
        $entryWithMetadata = new SchemaLogEntry(
            $entry->eventType,
            $entry->id,
            microtime(true) // 受信時のタイムスタンプで上書き
        );
        
        $this->entries[] = $entryWithMetadata;
    }

    #[Override]
    public function close(): array
    {
        if ($this->context === null) {
            return [];
        }
        
        $result = [
            'context' => $this->context->toArray(),
            'entries' => array_map(fn(SchemaLogEntry $entry) => $entry->toArray(), $this->entries),
        ];
        
        $this->context = null;
        $this->entries = [];
        
        return $result;
    }
    
    public function generateSessionId(): string
    {
        return uniqid('session_', true);
    }
}