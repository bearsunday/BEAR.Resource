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
        $data = [];

        if ($this->xhprof !== null) {
            $data['xhprof'] = $this->xhprof;
        }

        if ($this->xdebug !== null) {
            $data['xdebug'] = $this->xdebug;
        }

        if ($this->php !== null) {
            $data['php'] = $this->php;
        }

        return $data;
    }
}
