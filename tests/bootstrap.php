<?php
// bootstrap for test

require_once dirname(__DIR__) . '/src.php';
require_once dirname(__DIR__) . '/vendors/Ray.Aop/src.php';

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
require_once __DIR__ . '/Mock/Page/News.php';

require_once __DIR__ . '/Mock/Interceptor/Log.php';

require_once __DIR__ . '/Mock/MockModule.php';
