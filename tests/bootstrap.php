<?php
// bootstrap for test

require_once dirname(__DIR__) . '/src.php';

require_once __DIR__ . '/Mock/User.php';
require_once __DIR__ . '/Mock/Blog.php';
require_once __DIR__ . '/Mock/Entry.php';
require_once __DIR__ . '/Mock/Comment.php';

require_once __DIR__ . '/Mock/Adapter/Nop.php';
require_once __DIR__ . '/Mock/Adapter/Prov.php';

require_once __DIR__ . '/Mock/ResourceObject/News.php';
require_once __DIR__ . '/Mock/ResourceObject/User.php';
