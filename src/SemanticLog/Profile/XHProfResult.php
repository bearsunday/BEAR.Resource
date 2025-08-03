<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog\Profile;

use JsonSerializable;
use Override;

use function file_put_contents;
use function function_exists;
use function serialize;
use function sprintf;
use function str_replace;
use function sys_get_temp_dir;
use function uniqid;
use function xhprof_disable;
use function xhprof_enable;

use const XHPROF_FLAGS_CPU;
use const XHPROF_FLAGS_MEMORY;
use const XHPROF_FLAGS_NO_BUILTINS;

final class XHProfResult implements JsonSerializable
{
    private ?string $profileId = null;

    public function __construct(
        public readonly ?string $file = null,
    ) {
    }

    public static function start(): self
    {
        if (! function_exists('xhprof_enable')) {
            return new self(); // @codeCoverageIgnore
        }

        /** @psalm-suppress UndefinedConstant, MixedArgument */
        xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);

        $instance = new self();
        $instance->profileId = uniqid('xhprof_', true);

        return $instance;
    }

    public function stop(string $uri): self
    {
        if ($this->profileId === null || ! function_exists('xhprof_disable')) {
            return new self(); // @codeCoverageIgnore
        }

        $xhprofData = xhprof_disable();
        $filename = sprintf(
            '%s/xhprof_%s_%s.xhprof',
            sys_get_temp_dir(),
            str_replace(['/', ':', '?'], '_', $uri),
            $this->profileId,
        );

        if (file_put_contents($filename, serialize($xhprofData)) === false) {
            return new self(); // @codeCoverageIgnore
        }

        return new self($filename);
    }

    /** @return array<string, mixed> */
    #[Override]
    public function jsonSerialize(): array
    {
        if ($this->file === null) {
            return [];
        }

        return ['file' => $this->file];
    }
}
