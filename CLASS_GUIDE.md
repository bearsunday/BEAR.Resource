# BEAR.Resource Complete Class Guide

This comprehensive guide covers all classes in the BEAR.Resource framework, providing detailed information about their purpose, functionality, and relationships.

## Table of Contents

1. [Core Resource Classes](#core-resource-classes)
2. [Dependency Injection Modules](#dependency-injection-modules)
3. [Exception Hierarchy](#exception-hierarchy)
4. [JSON Schema Validation System](#json-schema-validation-system)
5. [Parameter Injection System](#parameter-injection-system)
6. [HTTP and HAL Implementation](#http-and-hal-implementation)
7. [Annotation/Attribute System](#annotationattribute-system)
8. [Supporting Classes](#supporting-classes)

---

## Core Resource Classes

### ResourceObject (`src/ResourceObject.php`)
**Purpose**: Abstract base class for all resources providing HTTP-compliant behavior.

**Key Properties**:
- `$uri`: Resource URI identifier
- `$code`: HTTP status code (default: 200)
- `$headers`: HTTP response headers
- `$body`: Resource data/content
- `$view`: Rendered representation

**Key Methods**:
- `__toString()`: Returns string representation
- Array access methods for body manipulation
- `setRenderer()`: Dependency injection for renderer
- `transfer()`: Response transfer to client

**Usage**: Extended by all concrete resource classes.

### ResourceInterface (`src/ResourceInterface.php`)
**Purpose**: Defines contract for resource operations and HTTP method handling.

**Key Methods**:
- `newInstance($uri)`: Creates resource instances
- `get()`, `post()`, `put()`, `delete()`: HTTP method implementations
- `href(string $rel, array $query)`: HATEOAS navigation

### Resource (`src/Resource.php`)
**Purpose**: Main implementation of ResourceInterface providing fluent API.

**Key Features**:
- Method chaining via magic `__get()`
- Request creation and execution
- Integration with factory and invoker systems

### AbstractRequest (`src/AbstractRequest.php`)
**Purpose**: Base class for resource requests with lazy/eager evaluation.

**Key Features**:
- Lazy evaluation support
- Request caching
- Link relation handling

### Request (`src/Request.php`)
**Purpose**: Concrete request implementation with HTTP method constants.

**Key Features**:
- Query manipulation methods
- Link traversal methods (`linkSelf`, `linkNew`, `linkCrawl`)
- URI generation

### Uri (`src/Uri.php`) & AbstractUri (`src/AbstractUri.php`)
**Purpose**: URI parsing, validation, and template expansion.

**Key Features**:
- RFC-compliant URI parsing
- Query parameter handling
- URI template support

### Factory (`src/Factory.php`) & FactoryInterface (`src/FactoryInterface.php`)
**Purpose**: Resource object creation via scheme-based resolution.

**Key Features**:
- Scheme collection management
- URI-to-class mapping
- Resource instantiation

### Invoker (`src/Invoker.php`) & InvokerInterface (`src/InvokerInterface.php`)
**Purpose**: Resource method invocation orchestration.

**Key Features**:
- Method dispatch
- Parameter injection coordination

---

## Dependency Injection Modules

### Core Modules

#### ResourceModule (`src/Module/ResourceModule.php`)
**Purpose**: Root module that aggregates all core functionality.
**Installs**: ResourceClientModule, EmbedResourceModule, HttpClientModule
**Parameters**: `$appName` - Application namespace

#### ResourceClientModule (`src/Module/ResourceClientModule.php`)
**Purpose**: Core resource client bindings.
**Bindings**: ResourceInterface, InvokerInterface, LinkerInterface, FactoryInterface, etc.

#### ResourceObjectModule (`src/Module/ResourceObjectModule.php`)
**Purpose**: Registers resource object classes for compilation.
**Parameters**: `$resourceObjects` - Array of resource class names

### Rendering Modules

#### HalModule (`src/Module/HalModule.php`)
**Purpose**: Enables HAL+JSON rendering.
**Bindings**: RenderInterface → HalRenderer

#### OptionsMethodModule (`src/Module/OptionsMethodModule.php`)
**Purpose**: Enables OPTIONS method with body rendering.

#### VoidOptionsMethodModule (`src/Module/VoidOptionsMethodModule.php`)
**Purpose**: Disables OPTIONS method rendering.

### Feature Modules

#### EmbedResourceModule (`src/Module/EmbedResourceModule.php`)
**Purpose**: Enables resource embedding via AOP interceptor.

#### HttpClientModule (`src/Module/HttpClientModule.php`)
**Purpose**: Provides HTTP client functionality.

#### ImportAppModule (`src/Module/ImportAppModule.php`)
**Purpose**: Enables importing resources from external applications.
**Parameters**: `$importApps` - Array of import configurations

### JSON Schema Modules

#### JsonSchemaModule (`src/JsonSchema/Module/JsonSchemaModule.php`)
**Purpose**: Enables JSON Schema validation.
**Parameters**: `$jsonSchemaDir`, `$jsonValidateDir`

#### JsonSchemaLinkHeaderModule (`src/JsonSchema/Module/JsonSchemaLinkHeaderModule.php`)
**Purpose**: Adds JSON Schema link headers.
**Parameters**: `$jsonSchemaHost`

#### NullJsonSchemaModule (`src/JsonSchema/Module/NullJsonSchemaModule.php`)
**Purpose**: Disables JSON Schema validation.

#### FakeJsonModule (`src/JsonSchema/Module/FakeJsonModule.php`)
**Purpose**: Provides fake data generation for development.

### Logging Modules

#### DevLoggerModule (`src/Module/DevLoggerModule.php`)
**Purpose**: Development logging with detailed output.

#### ProdLoggerModule (`src/Module/ProdLoggerModule.php`)
**Purpose**: Production logging with minimal overhead.

#### ErrorLogLoggerModule (`src/Module/ErrorLogLoggerModule.php`)
**Purpose**: System error log integration.

---

## Exception Hierarchy

### Base Exceptions

#### ExceptionInterface (`src/Exception/ExceptionInterface.php`)
**Purpose**: Marker interface for all framework exceptions.

#### BadRequestException (`src/Exception/BadRequestException.php`)
**Purpose**: Base for client error exceptions (HTTP 400).

### Client Error Exceptions (4xx)

#### ResourceNotFoundException (`src/Exception/ResourceNotFoundException.php`)
**HTTP Status**: 404
**Purpose**: Resource not found errors.

#### MethodNotAllowedException (`src/Exception/MethodNotAllowedException.php`)
**HTTP Status**: 405
**Purpose**: Unsupported HTTP method errors.

#### ParameterException (`src/Exception/ParameterException.php`)
**HTTP Status**: 400
**Purpose**: Parameter validation errors.

#### ParameterEnumTypeException (`src/Exception/ParameterEnumTypeException.php`)
**HTTP Status**: 400
**Purpose**: Enum parameter type validation errors.

#### ParameterInvalidEnumException (`src/Exception/ParameterInvalidEnumException.php`)
**HTTP Status**: 400
**Purpose**: Invalid enum value errors.

#### LinkException (`src/Exception/LinkException.php`)
**HTTP Status**: 400
**Purpose**: Link processing errors.

#### UriException (`src/Exception/UriException.php`)
**HTTP Status**: 400
**Purpose**: URI validation errors.

#### SchemeException (`src/Exception/SchemeException.php`)
**HTTP Status**: 400
**Purpose**: URI scheme validation errors.

### Server Error Exceptions (5xx)

#### ServerErrorException (`src/Exception/ServerErrorException.php`)
**HTTP Status**: 500
**Purpose**: Internal server errors.

### Runtime Exceptions

#### IlligalAccessException (`src/Exception/IlligalAccessException.php`)
**Purpose**: Illegal access to resource properties.

#### OutOfBoundsException (`src/Exception/OutOfBoundsException.php`)
**Purpose**: Array boundary violations.

---

## JSON Schema Validation System

### Core Components

#### JsonSchema Annotation (`src/JsonSchema/Annotation/JsonSchema.php`)
**Purpose**: Marks methods for JSON Schema validation.
**Properties**: `schema`, `params`, `key`, `target`

#### JsonSchemaInterceptor (`src/JsonSchema/Interceptor/JsonSchemaInterceptor.php`)
**Purpose**: Core validation engine using AOP.
**Features**: Request/response validation, type casting, nested validation

### Exception Handling

#### JsonSchemaException (`src/JsonSchema/Exception/JsonSchemaException.php`)
**Purpose**: Base for schema validation errors.

#### JsonSchemaErrorException (`src/JsonSchema/Exception/JsonSchemaErrorException.php`)
**Purpose**: Specific validation errors.

#### JsonSchemaNotFoundException (`src/JsonSchema/Exception/JsonSchemaNotFoundException.php`)
**Purpose**: Schema file not found errors.

### Exception Handlers

#### JsonSchemaExceptionNullHandler (`src/JsonSchema/JsonSchemaExceptionNullHandler.php`)
**Purpose**: Default handler that re-throws exceptions.

#### JsonSchemaExceptionFakeHandler (`src/JsonSchema/JsonSchemaExceptionFakeHandler.php`)
**Purpose**: Development handler that generates fake data.

---

## Parameter Injection System

### Core Interfaces

#### ParamInterface (`src/ParamInterface.php`)
**Purpose**: Contract for parameter resolution strategies.

#### NamedParameterInterface (`src/NamedParameterInterface.php`)
**Purpose**: Contract for parameter resolution from query data.

#### NamedParamMetasInterface (`src/NamedParamMetasInterface.php`)
**Purpose**: Contract for method parameter metadata.

### Orchestration Classes

#### PhpClassInvoker (`src/PhpClassInvoker.php`)
**Purpose**: Main orchestrator for method invocation and parameter injection.
**Features**: Method resolution, parameter injection, error handling

#### NamedParameter (`src/NamedParameter.php`)
**Purpose**: Coordinates parameter resolution process.

#### NamedParamMetas (`src/NamedParamMetas.php`)
**Purpose**: Analyzes method signatures and determines parameter handling.

### Parameter Handlers

#### RequiredParam (`src/RequiredParam.php`)
**Purpose**: Handles mandatory parameters.

#### OptionalParam (`src/OptionalParam.php`)
**Purpose**: Handles parameters with default values.

#### ClassParam (`src/ClassParam.php`)
**Purpose**: Handles complex object parameters and enums.

#### AssistedResourceParam (`src/AssistedResourceParam.php`)
**Purpose**: Injects data from other resources.

#### AssistedWebContextParam (`src/AssistedWebContextParam.php`)
**Purpose**: Injects HTTP context data.

### Ray.InputQuery Integration

#### InputParam (`src/InputParam.php`)
**Purpose**: Handles `#[Input]` attribute for form processing.

#### InputFormParam (`src/InputFormParam.php`)
**Purpose**: Handles single file uploads via `#[InputFile]`.

#### InputFormsParam (`src/InputFormsParam.php`)
**Purpose**: Handles multiple file uploads.

---

## HTTP and HAL Implementation

### HTTP Components

#### HttpAdapter (`src/HttpAdapter.php`)
**Purpose**: Adapter for HTTP scheme resources.

#### HttpRequestInterface (`src/HttpRequestInterface.php`)
**Purpose**: Contract for HTTP requests.

#### HttpRequestCurl (`src/HttpRequestCurl.php`)
**Purpose**: cURL-based HTTP client implementation.

#### HttpRequestHeaders (`src/HttpRequestHeaders.php`)
**Purpose**: HTTP header container.

#### HttpResourceObject (`src/HttpResourceObject.php`)
**Purpose**: Resource object for HTTP-based resources.

### HAL Implementation

#### HalRenderer (`src/HalRenderer.php`)
**Purpose**: Renders resources as HAL+JSON.
**Features**: Link generation, embedded resources, cross-scheme support

#### HalLinker (`src/HalLinker.php`)
**Purpose**: Processes `@Link` annotations for HAL links.
**Features**: URI template expansion, reverse linking

### Linking System

#### LinkerInterface (`src/LinkerInterface.php`)
**Purpose**: Contract for resource linking.

#### Linker (`src/Linker.php`)
**Purpose**: Complex link traversal implementation.
**Features**: Caching, collection handling, cycle detection

#### AnchorInterface (`src/AnchorInterface.php`)
**Purpose**: Contract for link resolution.

#### Anchor (`src/Anchor.php`)
**Purpose**: Resolves link relations to method/URI pairs.

#### LinkType (`src/LinkType.php`)
**Purpose**: Defines link types (SELF, NEW, CRAWL).

### App Resource System

#### AppAdapter (`src/AppAdapter.php`)
**Purpose**: Adapter for application-specific resources.

#### AppIterator (`src/AppIterator.php`)
**Purpose**: Scans and discovers application resources.

### OPTIONS Method Support

#### OptionsRenderer (`src/OptionsRenderer.php`)
**Purpose**: RFC 2616 compliant OPTIONS method support.

#### OptionsMethods (`src/OptionsMethods.php`)
**Purpose**: Extracts method documentation and metadata.

#### OptionsMethodRequest (`src/OptionsMethodRequest.php`)
**Purpose**: Analyzes method parameters for OPTIONS responses.

---

## Annotation/Attribute System

### Resource Relationship Annotations

#### Link (`src/Annotation/Link.php`)
**Purpose**: Defines HAL links between resources.
**Properties**: `rel`, `href`, `method`, `title`, `crawl`
**Target**: Methods (repeatable)

#### Embed (`src/Annotation/Embed.php`)
**Purpose**: Embeds related resources into responses.
**Properties**: `rel`, `src`
**Target**: Methods (repeatable)

### Parameter Injection Annotations

#### ResourceParam (`src/Annotation/ResourceParam.php`)
**Purpose**: Injects parameters from other resources.
**Properties**: `uri`, `param`, `templated`
**Target**: Methods and parameters

#### RequestParamInterface (`src/Annotation/RequestParamInterface.php`)
**Purpose**: Marker interface for parameter injection annotations.

### Dependency Injection Qualifiers

#### AppName (`src/Annotation/AppName.php`)
**Purpose**: Qualifies application name injection.

#### ContextScheme (`src/Annotation/ContextScheme.php`)
**Purpose**: Qualifies URI scheme context injection.

#### ImportAppConfig (`src/Annotation/ImportAppConfig.php`)
**Purpose**: Qualifies configuration import from external applications.

#### OptionsBody (`src/Annotation/OptionsBody.php`)
**Purpose**: Qualifies OPTIONS method body injection.

---

## Supporting Classes

### Type System

#### Types (`src/Types.php`)
**Purpose**: Comprehensive Psalm type definitions.
**Categories**: Domain types, HTTP types, HAL types, Options types, Annotation types

#### Code (`src/Code.php`)
**Purpose**: HTTP status code constants and mappings.

### Rendering

#### JsonRenderer (`src/JsonRenderer.php`)
**Purpose**: Basic JSON rendering.

#### PrettyJsonRenderer (`src/PrettyJsonRenderer.php`)
**Purpose**: Pretty-printed JSON rendering.

#### NullRenderer (`src/NullRenderer.php`)
**Purpose**: No-op renderer.

### Logging

#### LoggerInterface (`src/LoggerInterface.php`)
**Purpose**: Logging contract.

#### DevLogger (`src/DevLogger.php`)
**Purpose**: Development logger with detailed output.

#### ProdLogger (`src/ProdLogger.php`)
**Purpose**: Production logger.

#### ErrorLogLogger (`src/ErrorLogLogger.php`)
**Purpose**: System error log integration.

#### NullLogger (`src/NullLogger.php`)
**Purpose**: No-op logger.

### Utility Classes

#### Meta (`src/Meta.php`)
**Purpose**: Resource metadata extraction.

#### Params (`src/Params.php`)
**Purpose**: Parameter collection utilities.

#### UriFactory (`src/UriFactory.php`)
**Purpose**: URI creation factory.

#### ExtraMethodInvoker (`src/ExtraMethodInvoker.php`)
**Purpose**: Extra method invocation handling.

### Null Objects

#### NullRequest (`src/NullRequest.php`)
**Purpose**: Null object for requests.

#### NullResourceObject (`src/NullResourceObject.php`)
**Purpose**: Null object for resources.

#### NullResponder (`src/NullResponder.php`)
**Purpose**: Null object for responders.

#### NullUri (`src/NullUri.php`)
**Purpose**: Null object for URIs.

#### NullReverseLinker (`src/NullReverseLinker.php`)
**Purpose**: Null object for reverse linking.

---

## Architecture Patterns

### Design Patterns Used

1. **Resource-Oriented Architecture**: Everything is a resource with URI identification
2. **Dependency Injection**: Ray.Di for clean dependency management
3. **Factory Pattern**: Resource creation via scheme-based factories
4. **Strategy Pattern**: Parameter handling via strategy implementations
5. **Template Method**: Request processing pipeline
6. **Interceptor Pattern**: Cross-cutting concerns via AOP
7. **Null Object Pattern**: Default implementations for optional components
8. **Decorator Pattern**: Request/response enhancement

### Key Principles

1. **Separation of Concerns**: Clear separation between different responsibilities
2. **Open/Closed Principle**: Extensible via modules and interfaces
3. **Single Responsibility**: Each class has one clear responsibility
4. **Interface Segregation**: Small, focused interfaces
5. **Dependency Inversion**: Depend on abstractions, not concretions

This comprehensive guide provides a complete overview of the BEAR.Resource framework's class structure and architecture, enabling developers to understand and effectively use the framework for building RESTful web services.