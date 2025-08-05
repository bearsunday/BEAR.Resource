<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog\Profile;

use JsonSerializable;
use Override;

final class Profile implements JsonSerializable
{
    public function __construct(
        public ?XHProfResult $xhprof = null,
        public ?XdebugTrace $xdebug = null,
        public ?PhpProfile $php = null,
    ) {
    }

    /** @return array<string, mixed> */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'xhprof' => $this->xhprof ?? [],
            'xdebug' => $this->xdebug ?? [],
            'php' => $this->php ?? ['backtrace' => []],
        ];
    }
}
