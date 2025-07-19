<?php

declare(strict_types=1);

namespace Koriym\SchemaLogger;

interface SchemaLoggerInterface
{
    public function open(AbstractSchemaLogContext $context): void;
    
    public function add(SchemaLogEntry $entry): void;
    
    public function close(): array;
}