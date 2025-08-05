<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog\Profile;

use JsonSerializable;
use Override;

use function function_exists;
use function xhprof_disable;
use function xhprof_enable;

use const XHPROF_FLAGS_CPU;
use const XHPROF_FLAGS_MEMORY;
use const XHPROF_FLAGS_NO_BUILTINS;

final class XHProfResult implements JsonSerializable
{
    /** @param array<string, mixed>|null $data */
    public function __construct(
        public readonly ?array $data = null,
    ) {
    }

    public static function start(): self
    {
        if (! function_exists('xhprof_enable')) {
            return new self(); // @codeCoverageIgnore
        }

        /** @psalm-suppress UndefinedConstant, MixedArgument */
        xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);

        return new self();
    }

    public function stop(string $uri): self
    {
        if (! function_exists('xhprof_disable')) {
            return new self(); // @codeCoverageIgnore
        }

        /** @var array<string, array<string, int>>|false $xhprofData */
        $xhprofData = xhprof_disable();

        if ($xhprofData === false || $xhprofData === []) {
            return new self();
        }

        return new self($xhprofData);
    }

    /** @return array<string, mixed> */
    #[Override]
    public function jsonSerialize(): array
    {
        if ($this->data === null) {
            return [];
        }

        return [
            'data' => $this->data,
            'spec_url' => 'https://github.com/tideways/php-xhprof-extension?tab=readme-ov-file#data-format',
        ];
    }
}
