# Semantic Logging Context Design Discussion

## Background
Discussion about whether to make logging contexts injectable in the semantic logging system for BEAR.Resource.

## Key Insights: AI vs Human Information Processing Differences

### The Core Problem
During review of PR #334 (semantic logging implementation), a question arose about whether logging contexts should be injectable rather than hardcoded.

### Initial Positions
- **AI perspective (Claude):** Initially suggested making contexts injectable for testability and flexibility
- **Human perspective (Akihito):** Questioned whether this was necessary since the entire Invoker could be replaced

### The Breakthrough: Information Processing Capacity Differences

**AI Capabilities:**
- Can process massive amounts of data instantly (1MB+ text files)
- Can understand raw, unstructured data (e.g., raw xhprof arrays)
- Can handle complex, nested, verbose data structures without cognitive load

**Human Limitations:**
- Need organized, structured information to understand quickly
- Require visual aids for complex data (xhprof needs flame graphs)
- Cognitive overload from verbose or unstructured information

### Design Implications

**Use Case Differentiation:**
- **Development environment (AI consumption):** Verbose contexts with complete debugging information
- **Production environment (Human consumption):** Concise contexts with essential information only

**Example:**
```php
// AI-oriented context (development)
{
  "resource": "app://self/user",
  "method": "GET", 
  "params": {"id": 123},
  "headers": {...},
  "memory_usage": 12345678,
  "debug_backtrace": [...],
  "execution_time": 0.025,
  "database_queries": [...],
  "full_object_state": {...}
}

// Human-oriented context (production)
{
  "resource": "app://self/user",
  "method": "GET",
  "user_id": 123
}
```

## Proposed Solution: Context Factory Pattern

### Interface Design
```php
interface ContextFactoryInterface 
{
    public function createResourceContext(SemanticResourceInvokerAdapter $invoker): ResourceContext;
    public function createCompleteContext(SemanticResourceInvokerAdapter $invoker, ResourceObject $result): ResourceCompleteContext;
    public function createErrorContext(SemanticResourceInvokerAdapter $invoker, Exception $exception): ResourceErrorContext;
}
```

### Implementation Strategy
1. **Rich Information Passing:** Pass the entire invoker instance to factory methods
2. **Selective Extraction:** Let each factory implementation decide what information to extract
3. **Environment-Specific Binding:** Use DI to bind appropriate factory for each environment

### Factory Implementations
- `VerboseContextFactory` - For development/AI consumption
- `ConciseContextFactory` - For production/human consumption  
- `MockContextFactory` - For testing scenarios

## Key Design Principle
**"Design for the consumer, not the producer"**

The information processing capabilities of the end consumer (AI vs Human) should drive the context structure design, not technical abstractions or general best practices.

## Related Considerations

### Why Not UUID for Session IDs?
- Semantic logging sessions are for single-process correlation
- `uniqid()` is sufficient for the use case
- UUID adds unnecessary overhead (performance, storage, readability)
- General best practices don't always apply to specific contexts

### Why Not Split the PR?
- Semantic logging is an integrated feature
- Splitting would make it harder to understand the complete picture
- Demo scripts and schemas are essential for understanding the feature

## Conclusion
The discussion revealed that AI and human information processing differences should be a primary consideration in logging system design. The context factory pattern provides the flexibility to serve both consumers optimally while maintaining clean architecture.