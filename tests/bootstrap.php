<?php
// bootstrap for test

require_once dirname(__DIR__) . '/src.php';
require_once dirname(__DIR__) . '/vendors/Ray.Aop/src.php';
require_once dirname(__DIR__) . '/vendors/Ray.Di/src.php';

require_once __DIR__ . '/Mock/Blog.php';
require_once __DIR__ . '/Mock/Entry.php';
require_once __DIR__ . '/Mock/Comment.php';

require_once __DIR__ . '/Mock/Adapter/Nop.php';
require_once __DIR__ . '/Mock/Adapter/Prov.php';

require_once __DIR__ . '/Mock/ResourceObject/News.php';
require_once __DIR__ . '/Mock/ResourceObject/User.php';
require_once __DIR__ . '/Mock/ResourceObject/Link.php';
require_once __DIR__ . '/Mock/ResourceObject/Blog.php';
require_once __DIR__ . '/Mock/ResourceObject/User/Entry.php';
require_once __DIR__ . '/Mock/ResourceObject/User/Entry/Comment.php';
require_once __DIR__ . '/Mock/ResourceObject/User/Entry/Comment/ThumbsUp.php';
require_once __DIR__ . '/Mock/ResourceObject/Weave/Book.php';
require_once __DIR__ . '/Mock/ResourceObject/RestBucks/Order.php';
require_once __DIR__ . '/Mock/ResourceObject/RestBucks/Payment.php';
require_once __DIR__ . '/Mock/ResourceObject/RestBucks/Menu.php';

require_once __DIR__ . '/Mock/Page/News.php';
require_once __DIR__ . '/Mock/Interceptor/Log.php';
require_once __DIR__ . '/Mock/MockModule.php';

$base = (dirname(__DIR__));
require_once $base . '/vendors/Guzzle/vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';
$classLoader = new \Symfony\Component\ClassLoader\UniversalClassLoader();
$classLoader->registerNamespaces(array(
            'Guzzle\Tests' => __DIR__,
            'Guzzle' => $base . '/vendors/Guzzle/src',
            'Doctrine' => $base . '/vendors/Guzzle/vendor/Doctrine/lib',
            'Monolog' => $base . '/vendors/Guzzle/vendor/Monolog/src'
));
$classLoader->registerPrefix('Zend_', $base . '/vendors/Guzzle/vendor');
$classLoader->register();
