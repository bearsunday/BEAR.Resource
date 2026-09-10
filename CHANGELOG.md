# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.34.1] - 2026-09-11

### Fixed
- Bind `HttpResourceObject` explicitly in `HttpClientModule`; `HttpAdapter::get()`
  resolves it by class, which required just-in-time binding and failed under
  `CompiledInjector` (#379)

## [1.34.0] - 2026-08-29

### Fixed
- `#[Link]` now renders `title` and non-default `method` in HAL links

## [1.33.0] - 2026-06-21

### Added
- `HttpRequestException` for HTTP transport and response parsing failures
- `JsonSchemaException::getErrors()` returning typed `JsonSchemaError` /
  `ConstraintViolation` DTOs through a `JsonSchemaErrors` collection (#364)
- `JsonSchemaRequestException` / `JsonSchemaResponseException` subclasses for
  source discrimination at catch sites (#369)

### Changed
- Set default cURL timeouts for HTTP resource requests: 5 seconds to connect and 30 seconds overall

### Fixed
- Remove invalid Psalm taint-source annotation from `AbstractRequest::__invoke()`

## [1.32.0] - 2026-05-12

### Fixed
- Skip body schema validation for cached rendered responses (#359)
- Allow `HalRenderer` embed evaluation to handle `mixed` return from `jsonSerialize()` (#360)

## [1.31.2] - 2026-05-02

### Fixed
- Support Ray.InputQuery native `array` and `array|null` DTO inputs at the resource parameter boundary

### Changed
- Require `ray/input-query` ^1.1
- Normalize input query `InvalidArgumentException` code `0` to `Code::BAD_REQUEST` when wrapping it as `ParameterException`

## [1.31.1] - 2026-04-29

### Fixed
- Allow `JsonSchemaInterceptor` request validation to flatten Ray.InputQuery `#[Input]` DTO arguments before JSON Schema validation

## [1.31.0] - 2026-02-03

### Added
- `ResourceClient` class — stateless `ResourceInterface` implementation safe for coroutine/async environments
- `Method` enum for type-safe HTTP method parameters
- `ResourceClient::newRequest()` for direct request creation without fluent interface
- `LinkCrawler` extracted from `Linker` for coroutine safety

### Changed
- `Linker` delegates list detection to `LinkCrawler` for shared logic

## [1.30.0] - 2026-01-24

### Added
- `EmbedInterceptorInterface` for swappable embed implementations
  - Allows alternative implementations (e.g., async/parallel embed resolution) to be injected via DI
  - `EmbedInterceptor` now implements this interface
  - `EmbedResourceModule` binds via the interface for extensibility

## [1.29.0] - 2026-01-20

### Added
- Psalm taint annotations for improved security analysis
  - Added `@psalm-taint-specialize` to `AbstractUri::__toString()`
  - Enhanced static analysis for detecting potential security issues

## [1.28.0] - 2024-12-09

### Added
- Ray.InputQuery `#[Input]` attribute support for OPTIONS method
  - OPTIONS requests now properly expand `#[Input]` parameters to show constructor properties
  - Added comprehensive test coverage for Input parameter expansion scenarios

### Fixed
- Bug where non-Input required parameters were dropped when `#[Input]` attribute existed
- OPTIONS method now correctly handles mixed Input and regular parameters

### Changed
- Refactored `OptionsMethodRequest` parameter processing for improved maintainability
  - Extracted Input parameter processing to dedicated method
  - Improved code organization and readability

## [1.27.0] - 2025-11-11

### Changed
- **BREAKING**: Minimum PHP version requirement changed from 8.1 to 8.2
- Migrated from Doctrine Annotations to PHP 8 Attributes
- Upgraded PHPUnit from 9.6 to 11.0
- Migrated PHPUnit XML configuration to latest schema
- Converted PHPUnit annotations to PHP 8 attributes (`@depends` → `#[Depends]`, `@dataProvider` → `#[DataProvider]`)
- Updated Scrutinizer CI to PHP 8.4
- Replaced `nocarrier/hal` with `koriym/hal` ^1.1
- Restored stable Ray dependencies (ray/aop ^2.18.0, ray/di ^2.18.0)
- Applied readonly class optimization by Rector

### Removed
- **BREAKING**: Removed all Doctrine Annotations support
- Removed backward compatibility code for annotations (`getAnnotationParamMetas`, `getAssistedNames`, `addNamedParams`, `getWebContext`, `setAssistedAnnotation`, `getInsFromMethodAnnotations`)
- Removed `doctrine/annotations` dependency
- Removed unnecessary `symfony/polyfill-php83` dependency
- Removed obsolete `@CookieParam` annotation docblocks

**Migration Guide**: Use [bearsunday/rector-bearsunday](https://github.com/bearsunday/rector-bearsunday) to automatically convert annotations to attributes.

### Fixed
- CI workflow to remove PHP 8.1 from test matrix
- Parameter processing to handle methods without attributes correctly
- Updated `.gitignore` for `.phpunit.cache` directory

## [1.26.3] - 2025-07-30

### Changed
- Updated `ray/input-query` dependency to version ^1.0

## [1.26.2] - 2025-07-17

### Changed
- Updated `ray/input-query` dependency to version ^0.3.0

## [1.26.1] - 2025-07-11

### Fixed
- Exception handling in parameter resolution for Ray.InputQuery integration
- Consistent ParameterException throwing when InvalidArgumentException occurs in parameter injection
- Proper exception chaining to preserve debugging context

## [1.26.0] - 2025-07-07

### Added
- Ray.InputQuery integration for file upload handling
- Support for `AbstractFileUpload` return types in `InputFormParam` and `InputFormsParam`
- Ability to pass generated file upload objects directly to resource methods
- Integration tests for file upload functionality
- `InputParam` class for handling input query parameters
- Comprehensive file upload examples and documentation
- Comprehensive type definitions in `Types.php` for improved type safety

### Changed
- Updated `ray/input-query` dependency to version ^0.2.0
- Updated `koriym/file-upload` dependency to version ^0.2.0
- Refactored file upload handling for improved consistency and readability
- Replaced `create` method with `newInstance` for inputQuery instantiation

### Fixed
- Parameter validation to prevent silent errors
- Code style issues in test files

### Dependencies
- `ray/input-query`: ^0.2.0
- `koriym/file-upload`: ^0.2.0
- `ext-fileinfo`: * (required for Windows CI compatibility)

## [1.25.0] - 2024-12-22

### Added
- Request exception handler for JSON schema validation
- Arguments support for `JsonSchemaRequestExceptionHandlerInterface`

### Changed
- Enhanced JSON schema validation error handling

## [1.24.0] - 2024-11-30

### Added
- PHP 8.4 support in CI workflow
- PHPStan baseline configuration

### Changed
- Update `rize/uri-template` requirement from ^0.3 to ^0.4
- Update method parameters to allow nullable types
- Refactor codebase for improved readability and readonly properties
- Replace `assertSame` with `assertJsonStringEqualsJsonString` in tests
- Update copyright year to 2025

## [1.23.0] - 2024-10-01

### Changed
- Make `koriym/json-schema-faker` package optional

## Earlier versions

Please refer to the git history for changes in earlier versions.

[Unreleased]: https://github.com/bearsunday/BEAR.Resource/compare/1.31.2...HEAD
[1.31.2]: https://github.com/bearsunday/BEAR.Resource/compare/1.31.1...1.31.2
[1.31.1]: https://github.com/bearsunday/BEAR.Resource/compare/1.31.0...1.31.1
[1.31.0]: https://github.com/bearsunday/BEAR.Resource/compare/1.30.0...1.31.0
[1.30.0]: https://github.com/bearsunday/BEAR.Resource/compare/1.29.0...1.30.0
[1.29.0]: https://github.com/bearsunday/BEAR.Resource/compare/1.28.0...1.29.0
[1.28.0]: https://github.com/bearsunday/BEAR.Resource/compare/1.27.0...1.28.0
[1.27.0]: https://github.com/bearsunday/BEAR.Resource/compare/1.26.3...1.27.0
[1.26.0]: https://github.com/bearsunday/BEAR.Resource/compare/1.25.0...1.26.0
[1.25.0]: https://github.com/bearsunday/BEAR.Resource/compare/1.24.0...1.25.0
[1.24.0]: https://github.com/bearsunday/BEAR.Resource/compare/1.23.0...1.24.0
[1.23.0]: https://github.com/bearsunday/BEAR.Resource/compare/1.22.5...1.23.0
