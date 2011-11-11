<?php

namespace PHP\Resource;

require_once __DIR__ . '/vendors/Ray.Di/src.php';

// interface
require_once __DIR__ . '/src/Provider.php';
require_once __DIR__ . '/src/Resource.php';
require_once __DIR__ . '/src/Object.php';
require_once __DIR__ . '/src/Invoke.php';

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
// resource adapter
require_once __DIR__ . '/src/Adapter/App.php';
require_once __DIR__ . '/src/Adapter/Page.php';

require_once __DIR__ . '/src/Code.php';

// exception
require_once __DIR__ . '/src/Exception.php';
require_once __DIR__ . '/src/Exception/Factory.php';
require_once __DIR__ . '/src/Exception/InvalidParameter.php';
require_once __DIR__ . '/src/Exception/InvalidMethod.php';
require_once __DIR__ . '/src/Exception/InvalidHost.php';
require_once __DIR__ . '/src/Exception/InvalidScheme.php';
require_once __DIR__ . '/src/Exception/InvalidRequest.php';