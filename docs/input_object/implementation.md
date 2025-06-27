# Ray.InputObject Implementation Guide

This guide provides detailed information for implementing Ray.InputObject in your application
and understanding its internal architecture.

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Core Components](#core-components)
3. [Implementation Steps](#implementation-steps)
4. [Internal Workings](#internal-workings)
5. [Extension Points](#extension-points)
6. [Best Practices](#best-practices)
7. [Troubleshooting](#troubleshooting)

## Architecture Overview

Ray.InputObject consists of three main components:

InputObject
├── ArgumentResolver     (HTTP params → Method arguments)
├── ObjectFlattener     (Objects → Flat arrays)
└── AttributeProcessor  (Handle #[Input] attributes)

### Data Flow

HTTP Request
↓
Flat Parameters (?name=John&age=30)
↓
InputObject::getArgs()
↓
Domain Objects (User{name: "John", age: 30})
↓
Method Invocation
↓
Business Logic
↓
InputObject::flatten()
↓
SQL Parameters (['name' => 'John', 'age' => 30])

## Core Components

### InputObject Class

The main entry point that coordinates the conversion process:

  ```php
  namespace Ray\InputObject;

  use Ray\Di\InjectorInterface;
  use ReflectionMethod;

  final class InputObject
  {
      public function __construct(
          private ?InjectorInterface $injector = null,
          private ?ArgumentResolver $resolver = null,
          private ?ObjectFlattener $flattener = null
      ) {
          $this->resolver ??= new ArgumentResolver($injector);
          $this->flattener ??= new ObjectFlattener();
      }

      /**
       * Get method arguments including Input objects, DI, and scalars
       *
       * @return array Method arguments in correct order
       */
      public function getArgs(ReflectionMethod $method, array $data): array
      {
          return $this->resolver->resolve($method, $data);
      }

      /**
       * Flatten an object to array for SQL parameters
       *
       * @return array<string, scalar>
       */
      public function flatten(object $object): array
      {
          return $this->flattener->flatten($object);
      }
  }

  ArgumentResolver

  Resolves all method arguments including Input objects, DI dependencies, and scalar values:

  namespace Ray\InputObject;

  final class ArgumentResolver
  {
      public function __construct(
          private ?InjectorInterface $injector = null,
          private ?ObjectFactory $factory = null
      ) {
          $this->factory ??= new ObjectFactory();
      }

      public function resolve(ReflectionMethod $method, array $data): array
      {
          $args = [];

          foreach ($method->getParameters() as $param) {
              $args[] = $this->resolveParameter($param, $data);
          }

          return $args;
      }

      private function resolveParameter(ReflectionParameter $param, array $data): mixed
      {
          // Check for #[Input] attribute
          $inputAttr = $this->getInputAttribute($param);

          if ($inputAttr !== null) {
              return $this->createInputObject($param, $data);
          }

          // Try DI resolution
          if ($this->injector && $this->isDiType($param)) {
              return $this->injector->getInstance($param->getType()->getName());
          }

          // Scalar parameter
          return $this->resolveScalar($param, $data);
      }
  }

  ObjectFlattener

  Converts nested objects back to flat arrays:

  namespace Ray\InputObject;

  final class ObjectFlattener
  {
      public function flatten(object $object): array
      {
          $result = [];
          $reflection = new ReflectionObject($object);

          foreach ($reflection->getProperties() as $property) {
              $value = $property->getValue($object);

              if (is_object($value)) {
                  // Recursively flatten nested objects
                  $nested = $this->flatten($value);
                  $result = array_merge($result, $nested);
              } elseif (is_scalar($value) || is_null($value)) {
                  $result[$property->getName()] = $value;
              }
          }

          return $result;
      }
  }

  Implementation Steps

  Step 1: Basic Setup

  use Ray\InputObject\InputObject;
  use Ray\InputObject\Annotation\Input;
  use Ray\Di\Injector;

  // Create InputObject with DI container
  $injector = new Injector();
  $inputObject = new InputObject($injector);

  Step 2: Define Domain Objects

  final class UserRegistration
  {
      public function __construct(
          public readonly string $username,
          public readonly string $email,
          public readonly string $password
      ) {
          // Validation
          if (strlen($username) < 3) {
              throw new InvalidArgumentException('Username too short');
          }
          if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
              throw new InvalidArgumentException('Invalid email');
          }
          if (strlen($password) < 8) {
              throw new InvalidArgumentException('Password too short');
          }
      }
  }

  Step 3: Create Resource Classes

  class UserResource
  {
      public function __construct(
          private UserRepository $repository,
          private PasswordHasher $hasher
      ) {}

      public function onPost(
          #[Input] UserRegistration $registration,
          LoggerInterface $logger
      ): void {
          $logger->info('New user registration', ['username' => $registration->username]);

          $hashedPassword = $this->hasher->hash($registration->password);
          $this->repository->create(
              $registration->username,
              $registration->email,
              $hashedPassword
          );
      }
  }

  Step 4: Wire Everything Together

  // HTTP parameters
  $httpParams = [
      'username' => 'john_doe',
      'email' => 'john@example.com',
      'password' => 'securepassword123'
  ];

  // Get method arguments
  $method = new ReflectionMethod(UserResource::class, 'onPost');
  $args = $inputObject->getArgs($method, $httpParams);

  // Create resource and invoke method
  $resource = $injector->getInstance(UserResource::class);
  $resource->onPost(...$args);

  Step 5: Database Integration

  class UserRepository
  {
      public function __construct(
          private PDO $pdo,
          private InputObject $inputObject
      ) {}

      public function save(User $user): void
      {
          // Flatten object for SQL
          $params = $this->inputObject->flatten($user);

          $sql = 'INSERT INTO users (username, email, city, country) 
                  VALUES (:username, :email, :city, :country)';

          $stmt = $this->pdo->prepare($sql);
          $stmt->execute($params);
      }
  }

  Internal Workings

  Parameter Name Resolution

  Ray.InputObject attempts multiple naming conventions:

  // For property 'firstName', it tries:
  // 1. firstName (exact match)
  // 2. first_name (snake_case)
  // 3. first-name (kebab-case)

  Type Conversion

  Automatic type conversion is performed based on parameter types:

  // string → int
  'age' => '30' → age: 30

  // string → bool
  'active' => '1' → active: true
  'active' => 'true' → active: true

  // string → enum
  'status' => 'active' → status: Status::ACTIVE

  Error Handling

  Exceptions are thrown with clear messages:

  try {
      $args = $inputObject->getArgs($method, $data);
  } catch (InvalidArgumentException $e) {
      // Domain validation failed
      echo "Validation error: " . $e->getMessage();
  } catch (ParameterNotFoundException $e) {
      // Required parameter missing
      echo "Missing parameter: " . $e->getParameterName();
  } catch (TypeConversionException $e) {
      // Type conversion failed
      echo "Type error for " . $e->getParameterName();
  }

  Extension Points

  Custom Type Converters

  class DateTimeConverter implements TypeConverterInterface
  {
      public function convert(string $value, ReflectionType $type): ?object
      {
          if ($type->getName() !== DateTime::class) {
              return null;
          }

          return new DateTime($value);
      }
  }

  // Register converter
  $inputObject->addConverter(new DateTimeConverter());

  Custom Validation

  #[Attribute(Attribute::TARGET_PROPERTY)]
  class Email
  {
      public function validate(mixed $value): void
      {
          if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
              throw new ValidationException('Invalid email format');
          }
      }
  }

  class User
  {
      public function __construct(
          #[Email] public readonly string $email
      ) {}
  }

  Best Practices

  1. Keep Objects Immutable

  final class User
  {
      public function __construct(
          public readonly string $name,
          public readonly int $age
      ) {}
  }

  2. Validate in Constructors

  public function __construct(string $email)
  {
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          throw new InvalidArgumentException('Invalid email');
      }
      $this->email = $email;
  }

  3. Use Type Declarations

  // Good: Type information for automatic conversion
  public function __construct(
      public readonly int $age,
      public readonly bool $active,
      public readonly Status $status
  ) {}

  // Bad: No type information
  public function __construct(
      public $age,
      public $active,
      public $status
  ) {}

  4. Handle Optional Parameters

  public function __construct(
      public readonly string $name,
      public readonly ?string $nickname = null,
      public readonly int $age = 18
  ) {}

  Troubleshooting

  Common Issues

  1. Parameter Not Found
  // Check parameter naming
  // HTTP: first_name=John
  // PHP: firstName or first_name
  2. Type Conversion Errors
  // Ensure valid input
  // age=thirty → Error (not numeric)
  // age=30 → Success
  3. Nested Object Creation
  // Ensure #[Input] attribute on nested objects
  class User {
      public function __construct(
          public string $name,
          #[Input] public Address $address  // Don't forget #[Input]
      ) {}
  }

  Debug Mode

  Enable debug mode for detailed logging:

  $inputObject = new InputObject($injector);
  $inputObject->enableDebug();

  // Logs parameter resolution steps
  // Logs type conversions
  // Logs validation errors
