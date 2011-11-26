<?php

namespace PHP\Resource;

// interface
require_once __DIR__ . '/src/Provider.php';
require_once __DIR__ . '/src/Resource.php';
require_once __DIR__ . '/src/Object.php';
require_once __DIR__ . '/src/Invokable.php';
require_once __DIR__ . '/src/Link.php';
require_once __DIR__ . '/src/Linkable.php';

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
require_once __DIR__ . '/src/Adapter/App.php';
require_once __DIR__ . '/src/Adapter/Page.php';

// exception
require_once __DIR__ . '/src/Exception.php';
require_once __DIR__ . '/src/Exception/Factory.php';
require_once __DIR__ . '/src/Exception/InvalidParameter.php';
require_once __DIR__ . '/src/Exception/InvalidMethod.php';
require_once __DIR__ . '/src/Exception/InvalidHost.php';
require_once __DIR__ . '/src/Exception/InvalidScheme.php';
require_once __DIR__ . '/src/Exception/InvalidRequest.php';
require_once __DIR__ . '/src/Exception/InvalidLink.php';

// constants
require_once __DIR__ . '/src/Code.php';