<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

final class ImportApp
{
    /**
     * Host name
     *
     * @var string
     */
    public $host;

    /**
     * App name
     *
     * @var string
     */
    public $appName;

    /**
     * Context
     *
     * @var string
     */
    public $context;

    /**
     * Script dir
     *
     * @var string
     */
    public $scriptDir;

    /**
     * @param string $host
     * @param string $appName
     * @param string $context
     */
    public function __construct($host, $appName, $context)
    {
        $this->host = $host;
        $this->appName = $appName;
        $this->context = $context;
        // contextual script dir
        $appModule = $appName . '\Module\AppModule';
        $tmpDir = dirname(dirname(dirname((new \ReflectionClass($appModule))->getFileName()))) . '/var/tmp';
        $this->scriptDir = sprintf('%s/%s', $tmpDir, $context);
        if (! file_exists($this->scriptDir)) {
            mkdir($this->scriptDir);
        }
    }
}
