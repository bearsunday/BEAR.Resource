<?php

declare(strict_types=1);

passthru('php ' . __DIR__ . '/1.basic.php');
passthru('php ' . __DIR__ . '/2.link-self.php');
passthru('php ' . __DIR__ . '/3.link-crawl.php');
passthru('php ' . __DIR__ . '/4.restbucks.php');
passthru('php ' . __DIR__ . '/5.embed.php');
passthru('XDEBUG_MODE=trace php -d extension=xhprof.so -d zend_extension=xdebug.so -d xdebug.mode=profile,trace -d xdebug.start_with_request=no -d xdebug.output_dir=/tmp -d xdebug.trace_format=1 -d "xdebug.trace_options=2|8" -d xdebug.use_compression=0 -d xdebug.collect_params=1 -d xdebug.collect_return=1 -d xdebug.collect_assignments=1 -d xhprof.output_dir=/tmp -d memory_limit=256M ' . __DIR__ . '/6.semantic-log.php');
