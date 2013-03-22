<?php

require_once __DIR__ . '/Mock/Blog.php';
require_once __DIR__ . '/Mock/Entry.php';
require_once __DIR__ . '/Mock/Comment.php';
require_once __DIR__ . '/Mock/User.php';

require_once __DIR__ . '/Mock/Adapter/Nop.php';
require_once __DIR__ . '/Mock/Adapter/Prov.php';
require_once __DIR__ . '/Mock/Adapter/Test.php';

require_once __DIR__ . '/Mock/Resource/App/Index.php';

require_once __DIR__ . '/Mock/ResourceObject/Index.php';
require_once __DIR__ . '/Mock/ResourceObject/News.php';
require_once __DIR__ . '/Mock/ResourceObject/User.php';
require_once __DIR__ . '/Mock/ResourceObject/User/Index.php';
require_once __DIR__ . '/Mock/ResourceObject/Link.php';
require_once __DIR__ . '/Mock/ResourceObject/Blog.php';
require_once __DIR__ . '/Mock/ResourceObject/User/Entry.php';
require_once __DIR__ . '/Mock/ResourceObject/User/Entry/Comment.php';
require_once __DIR__ . '/Mock/ResourceObject/User/Entry/Comment/ThumbsUp.php';
require_once __DIR__ . '/Mock/ResourceObject/Weave/Book.php';
require_once __DIR__ . '/Mock/ResourceObject/RestBucks/Order.php';
require_once __DIR__ . '/Mock/ResourceObject/RestBucks/Payment.php';
require_once __DIR__ . '/Mock/ResourceObject/RestBucks/Menu.php';
require_once __DIR__ . '/Mock/ResourceObject/MethodAnnotation.php';
require_once __DIR__ . '/Mock/ResourceObject/Cache/Pdo.php';

require_once __DIR__ . '/Mock/Page/News.php';
require_once __DIR__ . '/Mock/Interceptor/Log.php';
require_once __DIR__ . '/Mock/MockModule.php';
require_once __DIR__ . '/Mock/TestModule.php';
require_once __DIR__ . '/Mock/TestRenderer.php';
require_once __DIR__ . '/Mock/ErrorRenderer.php';

require_once __DIR__ . '/Mock/sandbox/App/Link/User.php';
require_once __DIR__ . '/Mock/sandbox/App/Link/Blog.php';

$restbucks = dirname(__DIR__) . '/docs/sample/01-rest-bucks/Resource/App';
require_once $restbucks . '/Menu.php';
require_once $restbucks . '/Order.php';
require_once $restbucks . '/Payment.php';
