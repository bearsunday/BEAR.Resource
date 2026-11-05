<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\DataLoader\DataLoaderInterface;
use Ray\WebContextParam\Annotation\AbstractWebContextParam;
use ReflectionParameter;

/**
 * Type definitions for BEAR.Resource
 *
 * @phpcs:disable SlevomatCodingStandard.Commenting.DocCommentSpacing
 *
 * Domain Types
 * @psalm-type ResourceClassName = class-string<ResourceObject>
 *
 * Base Types
 * @psalm-type Query = array<string, mixed>
 * @psalm-type QueryList = list<Query>
 * @psalm-type StringList = list<string>
 * @psalm-type Body = array<array-key, mixed>
 * @psalm-type BodyList = array<array-key, array<string, mixed>|string>
 * @psalm-type BodyOrStringList = array<Body|string>
 * @psalm-type Schema = array<array-key, mixed>
 * @psalm-type ObjectList = list<object>
 *
 * Options Method Types
 * @psalm-type ParameterMetadata = array{
 *     type?: string,
 *     description?: string,
 *     default?: string,
 *     in?: string
 * }
 * @psalm-type ParametersMap = array<string, ParameterMetadata>
 * @psalm-type RequiredParameterList = list<string>
 * @psalm-type OptionsResponse = array{
 *     parameters?: ParametersMap,
 *     required?: RequiredParameterList
 * }
 * @psalm-type OptionsDocumentation = array{
 *     summary?: string,
 *     description?: string
 * }
 * @psalm-type OptionsDocBlock = array{
 *     0: OptionsDocumentation,
 *     1: array<string, array{type: string, description?: string}>
 * }
 * @psalm-type DocBlockParams = array<string, array{type: string, description?: string}>
 * @psalm-type EmbedList = non-empty-list<Embed>
 * @psalm-type LinkList = non-empty-list<Link>
 * @psalm-type SchemaArray = non-empty-array<array-key, mixed>
 * @psalm-type OptionsMethodsResponse = array{
 *     summary?: string,
 *     description?: string,
 *     request?: OptionsResponse,
 *     links?: LinkList,
 *     embed?: EmbedList,
 *     schema?: SchemaArray
 * }
 * @psalm-type OptionsEntityBody = array<string, array<Body|string>>
 *
 * HTTP Request/Response Types
 * @psalm-type Headers = array<string, string>
 * @psalm-type HttpHeaders = array<string, string>
 * @psalm-type HttpBody = array<mixed>
 * @psalm-type HttpResponse = array{
 *     body: HttpBody,
 *     code: int,
 *     headers: HttpHeaders,
 *     view: string
 * }
 * @psalm-type RequestOptions = array<null>|array{
 *     body?: string,
 *     headers?: HttpHeaders
 * }
 *
 * HAL+JSON Types
 * @psalm-type HalLinkData = array{
 *     href: string,
 *     templated?: bool,
 *     type?: string,
 *     deprecation?: string,
 *     name?: string,
 *     profile?: string,
 *     title?: string,
 *     hreflang?: string
 * }
 * @psalm-type HalLinks = array<string, HalLinkData|list<HalLinkData>>
 *
 * Resource Metadata Types
 * @psalm-type PackageMetadata = array{
 *     vendor?: string,
 *     package?: string
 * }
 * @psalm-type ResourceObjectBody = array{
 *     0: ResourceObject,
 *     1: array<array-key, mixed>
 * }
 *
 * Link Relation Types
 * @psalm-type MethodUri = array{0: string, 1: string}
 *
 * Annotation Types
 * @psalm-type Annotations = array<class-string, object>
 * @psalm-type MethodAnnotations = array<string, Annotations>
 * @psalm-type WebContextParam = class-string
 * @psalm-type InsMap = array<string, string>
 *
 * Parameter/Injection Types
 * @psalm-type ParamMap = array<string, ParamInterface>
 * @psalm-type WebContextParamMap = array<string, AbstractWebContextParam>
 * @psalm-type SuperGlobalsMap = array<string, Query>
 * @psalm-type ReflectionParameterList = list<ReflectionParameter>
 * @psalm-type ReflectionParameterMap = array<string, ReflectionParameter>
 *
 * Request Types
 * @psalm-type RequestQuery = array<string, mixed>
 *
 * App/Module Types
 * @psalm-type MetaMap = array<string, Meta>
 * @psalm-type ClassNameList = list<class-string>
 * @psalm-type StatusMessageMap = array<int, string>
 *
 * DataLoader Types
 * @psalm-type DataLoaderQuery = array<string, string>
 * @psalm-type DataLoaderRow = array<string, mixed>
 * @psalm-type DataLoaderQueries = list<DataLoaderQuery>
 * @psalm-type DataLoaderRows = list<DataLoaderRow>
 * @psalm-type DataLoaderClass = class-string<DataLoaderInterface>
 *
 * JSON Schema Types
 * @psalm-type ConstraintName = 'type'|'required'|'pattern'|'minLength'|'maxLength'
 *     |'minimum'|'maximum'|'multipleOf'|'enum'|'const'|'format'
 *     |'minItems'|'maxItems'|'uniqueItems'|'minProperties'|'maxProperties'
 * @psalm-type JsonSchemaValidatorError = array{
 *     property?: mixed,
 *     pointer?: mixed,
 *     message?: mixed,
 *     constraint?: mixed,
 *     ...
 * }
 * @psalm-type JsonSchemaValidatorErrors = list<JsonSchemaValidatorError>
 *
 * Resource Code Ranges
 * @psalm-type ClientErrorCode = int<400, 499>
 * @psalm-type ServerErrorCode = int<500, 599>
 *
 * @phpcs:enable
 *
 * @psalm-immutable
 */
final class Types
{
    /** @codeCoverageIgnore */
    private function __construct()
    {
    }
}
