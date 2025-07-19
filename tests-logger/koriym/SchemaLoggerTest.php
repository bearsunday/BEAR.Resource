<?php

declare(strict_types=1);

namespace Koriym\SchemaLogger;

use PHPUnit\Framework\TestCase;

class SchemaLoggerTest extends TestCase
{
    private SchemaLogger $logger;

    protected function setUp(): void
    {
        $this->logger = new SchemaLogger();
    }

    public function testOpenAndClose(): void
    {
        $context = new SchemaLogContext(
            'test-session-id',
            ['uri' => 'app://self/test', 'method' => 'get']
        );
        $this->logger->open($context);
        
        $result = $this->logger->close();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('context', $result);
        $this->assertArrayHasKey('entries', $result);
        
        $resultContext = $result['context'];
        $this->assertSame('test-session-id', $resultContext['session_id']);
        $this->assertSame('app://self/test', $resultContext['uri']);
        $this->assertSame('get', $resultContext['method']);
        $this->assertArrayHasKey('start_time', $resultContext);
        $this->assertIsFloat($resultContext['start_time']);
    }

    public function testAddEntry(): void
    {
        $context = new SchemaLogContext('test-session-id');
        $this->logger->open($context);
        
        $entry = new SchemaLogEntry('test_event', 'event-123');
        $this->logger->add($entry);
        
        $result = $this->logger->close();
        
        $this->assertCount(1, $result['entries']);
        $resultEntry = $result['entries'][0];
        
        $this->assertSame('test_event', $resultEntry['event_type']);
        $this->assertSame('event-123', $resultEntry['id']);
        $this->assertArrayHasKey('timestamp', $resultEntry);
        $this->assertIsFloat($resultEntry['timestamp']);
    }

    public function testMultipleEntries(): void
    {
        $context = new SchemaLogContext('test-session-id');
        $this->logger->open($context);
        
        $entry1 = new SchemaLogEntry('event1', 'event-1');
        $entry2 = new SchemaLogEntry('event2', 'event-2');
        $this->logger->add($entry1);
        $this->logger->add($entry2);
        
        $result = $this->logger->close();
        
        $this->assertCount(2, $result['entries']);
        $this->assertSame('event1', $result['entries'][0]['event_type']);
        $this->assertSame('event2', $result['entries'][1]['event_type']);
        $this->assertSame('event-1', $result['entries'][0]['id']);
        $this->assertSame('event-2', $result['entries'][1]['id']);
    }

    public function testAddWithoutOpenDoesNothing(): void
    {
        $entry = new SchemaLogEntry('test_event', 'event-123');
        $this->logger->add($entry);
        
        $result = $this->logger->close();
        
        $this->assertEmpty($result);
    }

    public function testCloseWithoutOpenReturnsEmpty(): void
    {
        $result = $this->logger->close();
        
        $this->assertEmpty($result);
    }

    public function testCloseResetsState(): void
    {
        $context = new SchemaLogContext('test-session-id');
        $this->logger->open($context);
        
        $entry = new SchemaLogEntry('test_event', 'event-123');
        $this->logger->add($entry);
        
        $result1 = $this->logger->close();
        $result2 = $this->logger->close();
        
        $this->assertCount(1, $result1['entries']);
        $this->assertEmpty($result2);
    }

    public function testGenerateSessionId(): void
    {
        $id1 = $this->logger->generateSessionId();
        $id2 = $this->logger->generateSessionId();
        
        $this->assertIsString($id1);
        $this->assertIsString($id2);
        $this->assertNotSame($id1, $id2);
        $this->assertStringStartsWith('session_', $id1);
        $this->assertStringStartsWith('session_', $id2);
    }

    public function testTimestampProgression(): void
    {
        $context = new SchemaLogContext('test-session-id');
        $this->logger->open($context);
        
        $entry1 = new SchemaLogEntry('event1', 'event-1');
        $entry2 = new SchemaLogEntry('event2', 'event-2');
        
        $this->logger->add($entry1);
        usleep(1000); // 1ms
        $this->logger->add($entry2);
        
        $result = $this->logger->close();
        
        $this->assertCount(2, $result['entries']);
        $this->assertLessThan(
            $result['entries'][1]['timestamp'],
            $result['entries'][0]['timestamp']
        );
    }
}