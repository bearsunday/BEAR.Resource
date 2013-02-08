<?php

// interface
require_once __DIR__ . '/src/BEAR/Resource/ProviderInterface.php';
require_once __DIR__ . '/src/BEAR/Resource/ResourceInterface.php';
require_once __DIR__ . '/src/BEAR/Resource/ObjectInterface.php';
require_once __DIR__ . '/src/BEAR/Resource/InvokerInterface.php';
require_once __DIR__ . '/src/BEAR/Resource/Uri.php';
require_once __DIR__ . '/src/BEAR/Resource/LinkerInterface.php';
require_once __DIR__ . '/src/BEAR/Resource/LinkType.php';

// trait
require_once __DIR__ . '/src/BEAR/Resource/BodyArrayAccessTrait.php';
require_once __DIR__ . '/src/BEAR/Resource/RenderTrait.php';

// abstract class
require_once __DIR__ . '/src/BEAR/Resource/AbstractObject.php';

// concrete class
require_once __DIR__ . '/src/BEAR/Resource/ObjectInterface.php';
require_once __DIR__ . '/src/BEAR/Resource/RequestInterface.php';
require_once __DIR__ . '/src/BEAR/Resource/Request.php';
require_once __DIR__ . '/src/BEAR/Resource/Result.php';
require_once __DIR__ . '/src/BEAR/Resource/Resource.php';
require_once __DIR__ . '/src/BEAR/Resource/FactoryInterface.php';
require_once __DIR__ . '/src/BEAR/Resource/Factory.php';
require_once __DIR__ . '/src/BEAR/Resource/Invoker.php';
require_once __DIR__ . '/src/BEAR/Resource/Linker.php';
require_once __DIR__ . '/src/BEAR/Resource/LoggerInterface.php';
require_once __DIR__ . '/src/BEAR/Resource/Logger.php';
require_once __DIR__ . '/src/BEAR/Resource/SchemeCollection.php';
require_once __DIR__ . '/src/BEAR/Resource/RenderInterface.php';
require_once __DIR__ . '/src/BEAR/Resource/LogWriterInterface.php';
require_once __DIR__ . '/src/BEAR/Resource/Adapter/AdapterInterface.php';
require_once __DIR__ . '/src/BEAR/Resource/Adapter/App.php';
require_once __DIR__ . '/src/BEAR/Resource/Adapter/App/Link.php';
require_once __DIR__ . '/src/BEAR/Resource/Adapter/Page.php';
require_once __DIR__ . '/src/BEAR/Resource/Adapter/Http.php';
require_once __DIR__ . '/src/BEAR/Resource/Adapter/Http/HttpClientInterface.php';
require_once __DIR__ . '/src/BEAR/Resource/Adapter/Http/Guzzle.php';

require_once __DIR__ . '/src/BEAR/Resource/Annotation/Signal.php';
require_once __DIR__ . '/src/BEAR/Resource/Annotation/ParamSignal.php';
require_once __DIR__ . '/src/BEAR/Resource/Annotation/Provides.php';
require_once __DIR__ . '/src/BEAR/Resource/Annotation/Link.php';

// exception
require_once __DIR__ . '/src/BEAR/Resource/Exception/Parameter.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/Method.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/Host.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/Scheme.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/Request.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/Link.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/Uri.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/BadRequest.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/MethodNotAllowed.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/ResourceNotFound.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/BadLinkRequest.php';

// signal handlers
require_once __DIR__ . '/src/BEAR/Resource/SignalHandler/HandleInterface.php';
require_once __DIR__ . '/src/BEAR/Resource/SignalHandler/Provides.php';

// constants
require_once __DIR__ . '/src/BEAR/Resource/Code.php';

