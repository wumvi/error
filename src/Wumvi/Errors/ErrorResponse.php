<?php
declare(strict_types=1);

namespace Wumvi\Errors;

class ErrorResponse
{
    public function __construct(
        public readonly string $name,
        public readonly string $hint = ''
    ) {
    }
}
