# Ray.InputObject

[![Continuous Integration](https://github.com/ray-di/Ray.InputObject/workflows/Continuous%20Inte
gration/badge.svg)](https://github.com/ray-di/Ray.InputObject/actions)
[![codecov](https://codecov.io/gh/ray-di/Ray.InputObject/branch/master/graph/badge.svg)](https:/
/codecov.io/gh/ray-di/Ray.InputObject)
[![Psalm](https://shepherd.dev/github/ray-di/Ray.InputObject/coverage.svg)](https://shepherd.dev
/github/ray-di/Ray.InputObject)

**Ray.InputObject** provides automatic object mapping between flat HTTP parameters and typed
domain objects, solving the impedance mismatch between HTTP, PHP, and SQL layers in web
applications.

## Features

- **Automatic Type Conversion**: Maps flat HTTP parameters to strongly-typed PHP objects
- **Nested Object Support**: Handles complex object hierarchies with `#[Input]` attributes
- **Dependency Injection**: Integrates seamlessly with Ray.Di for parameter resolution
- **Bidirectional Mapping**: Convert objects back to flat arrays for SQL parameters
- **Validation Support**: Built-in validation through constructor assertions
- **Clean Boundaries**: Maintains clear separation between HTTP, Domain, and SQL layers

## Installation

  ```bash
  composer require ray/input-object

  Basic Usage

  Simple Object Mapping

  use Ray\InputObject\InputObject;
  use Ray\InputObject\Annotation\Input;

  class User
  {
      public function __construct(
          public readonly string $name,
          public readonly int $age,
          public readonly string $email
      ) {
          // Validation in constructor
          if ($age < 0 || $age > 150) {
              throw new InvalidArgumentException('Age must be between 0 and 150');
          }
          if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
              throw new InvalidArgumentException('Invalid email format');
          }
      }
  }

  class UserResource
  {
      public function onPost(#[Input] User $user): void
      {
          // $user is automatically created from HTTP parameters
      }
  }

  // HTTP Request: POST /users?name=John&age=30&email=john@example.com

  $inputObject = new InputObject();
  $method = new ReflectionMethod(UserResource::class, 'onPost');
  $args = $inputObject->getArgs($method, [
      'name' => 'John',
      'age' => '30',
      'email' => 'john@example.com'
  ]);

  $resource = new UserResource();
  $resource->onPost(...$args);

  Nested Object Support

  class Address
  {
      public function __construct(
          public readonly string $city,
          public readonly string $street,
          public readonly string $zipCode
      ) {}
  }

  class UserProfile
  {
      public function __construct(
          public readonly string $name,
          public readonly string $email,
          #[Input] public readonly Address $address
      ) {}
  }

  // HTTP: POST /users?name=John&email=john@example.com&city=Tokyo&street=Shibuya&zipCode=150-0002

  $args = $inputObject->getArgs($method, [
      'name' => 'John',
      'email' => 'john@example.com',
      'city' => 'Tokyo',
      'street' => 'Shibuya',
      'zipCode' => '150-0002'
  ]);

  Multiple Input Objects

  class SearchCriteria
  {
      public function __construct(
          public readonly string $query,
          public readonly ?string $category = null
      ) {}
  }

  class Pagination  
  {
      public function __construct(
          public readonly int $page = 1,
          public readonly int $limit = 20
      ) {
          if ($page < 1) {
              throw new InvalidArgumentException('Page must be positive');
          }
          if ($limit < 1 || $limit > 100) {
              throw new InvalidArgumentException('Limit must be between 1 and 100');
          }
      }
  }

  class ProductResource
  {
      public function onGet(
          #[Input] SearchCriteria $criteria,
          #[Input] Pagination $pagination
      ): void {
          // Both objects are created from the same HTTP parameters
      }
  }

  // HTTP: GET /products?query=laptop&category=electronics&page=2&limit=50

  Flattening Objects for SQL

  // Convert domain objects back to flat arrays for SQL parameters
  $userProfile = new UserProfile(
      name: 'John',
      email: 'john@example.com',
      address: new Address(
          city: 'Tokyo',
          street: 'Shibuya',
          zipCode: '150-0002'
      )
  );

  $sqlParams = $inputObject->flatten($userProfile);
  // Result: ['name' => 'John', 'email' => 'john@example.com', 'city' => 'Tokyo', 'street' => 
  'Shibuya', 'zipCode' => '150-0002']

  // Use with your database layer
  $pdo->prepare('INSERT INTO users (name, email, city, street, zip_code) VALUES (:name, :email, 
  :city, :street, :zipCode)')
      ->execute($sqlParams);

  Advanced Features

  Integration with Dependency Injection

  use Ray\Di\Di\Inject;
  use Psr\Log\LoggerInterface;

  class UserService
  {
      public function createUser(
          #[Input] User $user,
          LoggerInterface $logger  // Resolved by DI container
      ): void {
          $logger->info('Creating user', ['name' => $user->name]);
          // Create user...
      }
  }

  Custom Parameter Names

  class User
  {
      public function __construct(
          public readonly string $firstName,  // Maps from 'first_name' or 'firstName'
          public readonly string $lastName    // Maps from 'last_name' or 'lastName'
      ) {}
  }

  Enum Support

  enum Status: string
  {
      case ACTIVE = 'active';
      case INACTIVE = 'inactive';
  }

  class Account
  {
      public function __construct(
          public readonly string $name,
          public readonly Status $status
      ) {}
  }

  // HTTP: POST /accounts?name=John&status=active

  Implementation Guide

  For detailed implementation guide, see docs/implementation.md.

  Why Ray.InputObject?

  The Problem

  Web applications constantly translate data between three different worlds:

  1. HTTP World: Flat key-value pairs (?name=John&city=Tokyo)
  2. PHP World: Rich object graphs with types and behavior
  3. SQL World: Flat relational data for persistence

  Manually converting between these representations leads to:
  - Boilerplate code scattered across your application
  - Type safety issues and runtime errors
  - Difficulty in maintaining and refactoring
  - Validation logic spread across layers

  The Solution

  Ray.InputObject provides automatic, type-safe conversion between these layers:

  HTTP (flat) → PHP Objects (hierarchical) → SQL (flat)
  ?name=John&city=Tokyo → User{Address{city: Tokyo}} → ['name' => 'John', 'city' => 'Tokyo']

  Each layer maintains its optimal representation while Ray.InputObject handles the impedance
  mismatch transparently.

  Requirements

  - PHP 8.1 or higher
  - ray/di ^2.0
