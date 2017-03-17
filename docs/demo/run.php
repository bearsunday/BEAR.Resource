<?php
/**
 * This file is part of the BEAR.Sunday package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
passthru('php ' . __DIR__ . '/00.min/run.php');
passthru('php ' . __DIR__ . '/01.basic/run.php');
passthru('php ' . __DIR__ . '/02.link-self/run.php');
passthru('php ' . __DIR__ . '/03.link-crawl/run.php');
passthru('php ' . __DIR__ . '/04.restbucks/run.php');
passthru('php ' . __DIR__ . '/06.HAL/run.php');
passthru('php ' . __DIR__ . '/10.cache/run.php');
