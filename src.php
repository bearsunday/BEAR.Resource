<?php

// interface
require_once __DIR__ . '/src/BEAR/Resource/Provider.php';
require_once __DIR__ . '/src/BEAR/Resource/ResourceInterface.php';
require_once __DIR__ . '/src/BEAR/Resource/Object.php';
require_once __DIR__ . '/src/BEAR/Resource/InvokerInterface.php';
require_once __DIR__ . '/src/BEAR/Resource/Uri.php';
require_once __DIR__ . '/src/BEAR/Resource/LinkerInterface.php';
require_once __DIR__ . '/src/BEAR/Resource/LinkType.php';

// trait
require_once __DIR__ . '/src/BEAR/Resource/BodyArrayAccess.php';

// abstract class
require_once __DIR__ . '/src/BEAR/Resource/AbstractObject.php';

// concrete class
require_once __DIR__ . '/src/BEAR/Resource/Object.php';
require_once __DIR__ . '/src/BEAR/Resource/Request.php';
require_once __DIR__ . '/src/BEAR/Resource/Result.php';
require_once __DIR__ . '/src/BEAR/Resource/Resource.php';
require_once __DIR__ . '/src/BEAR/Resource/FactoryInterface.php';
require_once __DIR__ . '/src/BEAR/Resource/Factory.php';
require_once __DIR__ . '/src/BEAR/Resource/Invoker.php';
require_once __DIR__ . '/src/BEAR/Resource/Linker.php';
require_once __DIR__ . '/src/BEAR/Resource/SchemeCollection.php';
require_once __DIR__ . '/src/BEAR/Resource/Renderable.php';
require_once __DIR__ . '/src/BEAR/Resource/Adapter/App.php';
require_once __DIR__ . '/src/BEAR/Resource/Adapter/App/Link.php';
require_once __DIR__ . '/src/BEAR/Resource/Adapter/Page.php';
require_once __DIR__ . '/src/BEAR/Resource/Adapter/Http.php';
require_once __DIR__ . '/src/BEAR/Resource/Adapter/Http/HttpClient.php';
require_once __DIR__ . '/src/BEAR/Resource/Adapter/Http/Guzzle.php';

require_once __DIR__ . '/src/BEAR/Resource/Annotation/Signal.php';
require_once __DIR__ . '/src/BEAR/Resource/Annotation/ParamSignal.php';
require_once __DIR__ . '/src/BEAR/Resource/Annotation/Provides.php';
require_once __DIR__ . '/src/BEAR/Resource/Annotation/Link.php';

// exception
require_once __DIR__ . '/src/BEAR/Resource/Exception.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/Factory.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/InvalidParameter.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/InvalidMethod.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/InvalidHost.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/InvalidScheme.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/InvalidRequest.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/InvalidLink.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/InvalidUri.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/BadRequest.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/MethodNotAllowed.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/ResourceNotFound.php';
require_once __DIR__ . '/src/BEAR/Resource/Exception/BadLinkRequest.php';

// signal handlers
require_once __DIR__ . '/src/BEAR/Resource/SignalHandler/Handle.php';
require_once __DIR__ . '/src/BEAR/Resource/SignalHandler/Provides.php';

// constants
require_once __DIR__ . '/src/BEAR/Resource/Code.php';

// vendor class
require_once __DIR__ . '/vendor/xhprof/xhprof/xhprof/xhprof_lib/utils/xhprof_lib.php';
require_once __DIR__ . '/vendor/xhprof/xhprof/xhprof/xhprof_lib/utils/xhprof_runs.php';

