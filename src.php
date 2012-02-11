<?php

namespace PHP\Resource;

// interface
require_once __DIR__ . '/src/Provider.php';
require_once __DIR__ . '/src/Resource.php';
require_once __DIR__ . '/src/Object.php';
require_once __DIR__ . '/src/Invokable.php';
require_once __DIR__ . '/src/Uri.php';
require_once __DIR__ . '/src/Linkable.php';
require_once __DIR__ . '/src/LinkType.php';

// trait
require_once __DIR__ . '/src/ArrayAccess.php';

// abstract class
require_once __DIR__ . '/src/AbstractObject.php';

// cocrete class
require_once __DIR__ . '/src/Object.php';
require_once __DIR__ . '/src/Request.php';
require_once __DIR__ . '/src/Client.php';
require_once __DIR__ . '/src/ResourceFactory.php';
require_once __DIR__ . '/src/Factory.php';
require_once __DIR__ . '/src/Invoker.php';
require_once __DIR__ . '/src/Linker.php';
require_once __DIR__ . '/src/SchemeCollection.php';
require_once __DIR__ . '/src/Adapter/App.php';
require_once __DIR__ . '/src/Adapter/App/Link.php';
require_once __DIR__ . '/src/Adapter/Page.php';
require_once __DIR__ . '/src/Adapter/Http.php';
require_once __DIR__ . '/src/Adapter/Http/HttpClient.php';
require_once __DIR__ . '/src/Adapter/Http/Guzzle.php';

require_once __DIR__ . '/src/Annotation/Signal.php';
require_once __DIR__ . '/src/Annotation/ArgSignal.php';
require_once __DIR__ . '/src/Annotation/Provides.php';

// exception
require_once __DIR__ . '/src/Exception.php';
require_once __DIR__ . '/src/Exception/Factory.php';
require_once __DIR__ . '/src/Exception/InvalidParameter.php';
require_once __DIR__ . '/src/Exception/InvalidMethod.php';
require_once __DIR__ . '/src/Exception/InvalidHost.php';
require_once __DIR__ . '/src/Exception/InvalidScheme.php';
require_once __DIR__ . '/src/Exception/InvalidRequest.php';
require_once __DIR__ . '/src/Exception/InvalidLink.php';
require_once __DIR__ . '/src/Exception/InvalidUri.php';
require_once __DIR__ . '/src/Exception/BadRequest.php';
require_once __DIR__ . '/src/Exception/MethodNotAllowed.php';

// constants
require_once __DIR__ . '/src/Code.php';
