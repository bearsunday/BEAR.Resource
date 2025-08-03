# BEAR.Resource Link Relations

This document defines custom link relation types used by BEAR.Resource that are not available in the IANA Link Relations Registry.

## Custom Link Relations

### `https://bearsunday.github.io/BEAR.Resource/docs/link-relations#ai-docs`

**Name**: ai-docs  
**Description**: Refers to documentation specifically designed for AI and machine consumption, typically in llms.txt format for framework understanding and automated analysis.  
**Reference**: This specification  
**Notes**: Used to link JSON Schema to AI-friendly documentation that provides framework context and usage patterns.

### `https://bearsunday.github.io/BEAR.Resource/docs/link-relations#semantic-schema`

**Name**: semantic-schema  
**Description**: Refers to a JSON Schema document that describes the structure of semantic log data for observability and analysis purposes.  
**Reference**: This specification  
**Notes**: Used to link semantic log instances to their corresponding schema definitions.

## Usage Examples

### AI Documentation Link
```json
{
  "rel": "https://bearsunday.github.io/BEAR.Resource/docs/link-relations#ai-docs",
  "title": "BEAR.Resource Framework Documentation for AI",
  "href": "https://bearsunday.github.io/BEAR.Resource/docs/llms.txt"
}
```

### Semantic Schema Link
```json
{
  "rel": "https://bearsunday.github.io/BEAR.Resource/docs/link-relations#semantic-schema",
  "title": "Semantic Log Schema",
  "href": "https://bearsunday.github.io/BEAR.Resource/docs/schema/complete-context.json"
}
```

## Registration

These link relations follow the extension relation type format as defined in RFC 8988 Section 2.1.2, using absolute URIs as identifiers.

---

**Specification**: BEAR.Resource Link Relations  
**Version**: 1.0  
**Date**: 2025-01-02  
**Authors**: BEAR.Resource Team